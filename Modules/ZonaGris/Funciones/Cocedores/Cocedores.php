<?php

namespace Modules\ZonaGris\Funciones\Cocedores;

use PDO;
use PDOException;
use App\Helpers\Logger;

class Cocedores
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }


    //Muestra los procesos que se encuentran en los receptores
    public function obtenerProcesosDisponibles(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT 
    a.pro_id, 
    p.pro_total_kg, 
    pt.pt_descripcion,
    GROUP_CONCAT(
        CONCAT(m.mat_nombre, ' (', IFNULL(pm.pma_kg, 0), ' kg)') 
        ORDER BY m.mat_nombre 
        SEPARATOR ', '
    ) AS materiales_con_cantidad
        FROM lotes_anio AS l
        INNER JOIN procesos_agrupados AS a ON l.lote_id = a.lote_id
        INNER JOIN procesos AS p ON p.pro_id = a.pro_id
        INNER JOIN procesos_materiales pm ON pm.pro_id = p.pro_id
        INNER JOIN materiales m ON m.mat_id = pm.mat_id
        INNER JOIN preparacion_tipo AS pt ON p.pt_id = pt.pt_id
        WHERE l.lote_estatus = 0
        GROUP BY a.pro_id, p.pro_total_kg, pt.pt_descripcion
        ORDER BY MIN(l.lote_fecha), MIN(l.lote_hora) ASC
        ");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            Logger::error('Error al obtener procesos disponibles: {mensaje}', ['mensaje' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Crea una nueva combinación de procesos (mezcla real) y la distribuye en uno o varios cocedores.
     *
     * @param array $data
     * @return array
     */
    public function crearCombinacionProcesos(array $data): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Insertar mezcla principal
            $stmt = $this->db->prepare("
            INSERT INTO zn_procesos_agrupados (descripcion, usuario_id) 
            VALUES (:descripcion, :usuario)
        ");
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':usuario', $data['usuario_id']);
            $stmt->execute();
            $proceso_agrupado_id = $this->db->lastInsertId();

            // 2. Insertar detalle de procesos y actualizar lote_estatus
            $stmtDetalle = $this->db->prepare("
            INSERT INTO zn_procesos_agrupados_detalle (proceso_agrupado_id, pro_id)
            VALUES (:proceso_agrupado_id, :pro_id)
        ");

            $stmtUpdateLote = $this->db->prepare("
            UPDATE lotes_anio 
            SET lote_estatus = '1' 
            WHERE lote_id = (SELECT lote_id FROM procesos_agrupados WHERE pro_id = :pro_id)
        ");

            foreach ($data['procesos'] as $pro_id) {
                $stmtDetalle->bindValue(':proceso_agrupado_id', $proceso_agrupado_id, PDO::PARAM_INT);
                $stmtDetalle->bindValue(':pro_id', $pro_id, PDO::PARAM_INT);
                $stmtDetalle->execute();

                $stmtUpdateLote->bindValue(':pro_id', $pro_id, PDO::PARAM_INT);
                $stmtUpdateLote->execute();
            }

            // 3. Registrar relación con cocedores activos
            $fecha_inicio = date('Y-m-d H:i:s');
            $stmtCocedor = $this->db->prepare("
            INSERT INTO procesos_cocedores_relacion (proceso_agrupado_id, cocedor_id, fecha_inicio) 
            VALUES (:proceso_agrupado_id, :cocedor_id, :fecha_inicio)
        ");

            foreach ($data['cocedores'] as $cocedor_id) {
                $stmtCocedor->bindParam(':proceso_agrupado_id', $proceso_agrupado_id, PDO::PARAM_INT);
                $stmtCocedor->bindParam(':cocedor_id', $cocedor_id, PDO::PARAM_INT);
                $stmtCocedor->bindParam(':fecha_inicio', $fecha_inicio);
                $stmtCocedor->execute();
            }

            $this->db->commit();

            Logger::info("Combinación de procesos creada en múltiples cocedores", [
                'usuario_id' => $data['usuario_id'],
                'proceso_agrupado_id' => $proceso_agrupado_id,
                'procesos' => $data['procesos'],
                'cocedores' => $data['cocedores']
            ]);

            return ['success' => true, 'proceso_agrupado_id' => $proceso_agrupado_id];
        } catch (PDOException $e) {
            $this->db->rollBack();
            Logger::error("Error al crear combinación de procesos: " . $e->getMessage());
            throw new \Exception("Error en la base de datos al combinar procesos.", 500);
        }
    }



    /**
     * Finaliza (cierra) la mezcla actual en un cocedor.
     * Actualiza la fecha_fin en la relación proceso-cocedor correspondiente.
     *
     * @param array $data Debe incluir: proceso_agrupado_id, cocedor_id, [opcional] fecha_fin
     * @return array
     */
    public function finalizarMezcla(array $data): array
    {
        try {
            // Validar y preparar datos
            $proceso_agrupado_id = $data['proceso_agrupado_id'] ?? null;
            $cocedor_id = $data['cocedor_id'] ?? null;
            $fecha_fin = $data['fecha_fin'] ?? date('Y-m-d H:i:s');

            if (!$proceso_agrupado_id || !$cocedor_id) {
                return [
                    'success' => false,
                    'error' => 'Faltan datos obligatorios (proceso_agrupado_id y/o cocedor_id)'
                ];
            }

            $stmt = $this->db->prepare("
            UPDATE procesos_cocedores_relacion
            SET fecha_fin = :fecha_fin
            WHERE proceso_agrupado_id = :proceso_agrupado_id
              AND cocedor_id = :cocedor_id
              AND fecha_fin IS NULL
        ");
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':proceso_agrupado_id', $proceso_agrupado_id, PDO::PARAM_INT);
            $stmt->bindParam(':cocedor_id', $cocedor_id, PDO::PARAM_INT);
            $stmt->execute();

            Logger::info("Mezcla finalizada en cocedor", [
                'proceso_agrupado_id' => $proceso_agrupado_id,
                'cocedor_id' => $cocedor_id,
                'fecha_fin' => $fecha_fin
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            Logger::error("Error al finalizar mezcla: {mensaje}", ['mensaje' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtiene el estado actual de todos los cocedores (libres u ocupados).
     * Si el cocedor está ocupado, muestra el proceso/agrupación actual y fecha de inicio.
     */
    public function obtenerEstadoCocedores()
    {
        try {
            $sql = "SELECT 
            c.cocedor_id,
            c.nombre,
            c.capacidad,
            c.estatus,
            r.relacion_id,
            r.proceso_agrupado_id,
            GROUP_CONCAT(DISTINCT pad.pro_id ORDER BY pad.pro_id ASC) AS procesos,
            -- Agregar materiales con cantidades (Opción 5)
             GROUP_CONCAT(
        CONCAT(m.mat_nombre, ' (', IFNULL(pm.pma_kg, 0), ' kg)') 
        ORDER BY m.mat_nombre 
        SEPARATOR ', '
    ) AS materiales,
            r.fecha_inicio,
            r.fecha_fin,
            pcd.param_temp_entrada AS temperatura_entrada,
            pcd.param_temp_salida AS temperatura_salida,
            pcd.fecha_hora AS fecha_registro,
            pcd.responsable_tipo,
            pcd.supervisor_validado
        FROM cocedores c
        LEFT JOIN procesos_cocedores_relacion r 
            ON c.cocedor_id = r.cocedor_id AND r.fecha_fin IS NULL
        LEFT JOIN zn_procesos_agrupados_detalle pad 
            ON r.proceso_agrupado_id = pad.proceso_agrupado_id
        -- Agregar JOINs para obtener materiales y cantidades
        LEFT JOIN procesos p 
            ON p.pro_id = pad.pro_id
        LEFT JOIN procesos_materiales pm 
            ON pm.pro_id = p.pro_id
        LEFT JOIN materiales m 
            ON m.mat_id = pm.mat_id
        LEFT JOIN (
            SELECT 
                relacion_id,
                MAX(fecha_hora) AS ultima_fecha
            FROM procesos_cocedores_detalle
            GROUP BY relacion_id
        ) ult ON ult.relacion_id = r.relacion_id

        LEFT JOIN procesos_cocedores_detalle pcd 
            ON pcd.relacion_id = ult.relacion_id AND pcd.fecha_hora = ult.ultima_fecha

        WHERE c.estatus IN ('ACTIVO', 'OCUPADO', 'MANTENIMIENTO')
        GROUP BY c.cocedor_id, c.nombre, c.capacidad, c.estatus, r.relacion_id, r.proceso_agrupado_id, 
                r.fecha_inicio, r.fecha_fin, pcd.param_temp_entrada, pcd.param_temp_salida, 
                pcd.fecha_hora, pcd.responsable_tipo, pcd.supervisor_validado
        ORDER BY c.cocedor_id;
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            Logger::error('Error al obtener estado de cocedores: {mensaje}', ['mensaje' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Inserta un registro horario de detalle en procesos_cocedores_detalle.
     * @param array $data Arreglo con todos los campos requeridos.
     * @return array
     */
    public function insertarRegistroHorario(array $data): array
    {
        try {
            // 1. Buscar la última hora registrada ANTES del nuevo registro
            $sql = "SELECT detalle_id, fecha_hora, supervisor_validado 
                FROM procesos_cocedores_detalle
                WHERE relacion_id = :relacion_id
                  AND fecha_hora < :fecha_hora
                ORDER BY fecha_hora DESC
                LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':relacion_id', $data['relacion_id'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_hora', $data['fecha_hora']);
            $stmt->execute();
            $anterior = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Validar si falta validación del supervisor
            if ($anterior && empty($anterior['supervisor_validado'])) {
                return [
                    'success' => false,
                    'error' => 'No puedes guardar la nueva hora: la anterior (' . $anterior['fecha_hora'] . ') aún no ha sido validada por el supervisor.'
                ];
            }

            // 3. (continúa el insert normal)
            $stmt = $this->db->prepare("
            INSERT INTO procesos_cocedores_detalle (
                relacion_id, fecha_hora, usuario_id, responsable_tipo, tipo_registro,
                param_agua, param_temp_entrada, param_temp_salida, param_solidos, param_ph,
                param_ntu, peso_consumido, muestra_tomada, observaciones, agitacion, desengrasador
            ) VALUES (
                :relacion_id, :fecha_hora, :usuario_id, :responsable_tipo, :tipo_registro,
                :param_agua, :param_temp_entrada, :param_temp_salida, :param_solidos, :param_ph,
                :param_ntu, :peso_consumido, :muestra_tomada, :observaciones, :agitacion, :desengrasador
            )
        ");
            // ... binds ...
            $stmt->bindParam(':relacion_id',         $data['relacion_id'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_hora',          $data['fecha_hora']);
            $stmt->bindParam(':usuario_id',          $data['usuario_id'], PDO::PARAM_INT);
            $stmt->bindParam(':responsable_tipo',    $data['responsable_tipo']);
            $stmt->bindParam(':tipo_registro',       $data['tipo_registro']);
            $stmt->bindParam(':param_agua',          $data['param_agua']);
            $stmt->bindParam(':param_temp_entrada',  $data['param_temp_entrada']);
            $stmt->bindParam(':param_temp_salida',   $data['param_temp_salida']);
            $stmt->bindParam(':param_solidos',       $data['param_solidos']);
            $stmt->bindParam(':param_ph',            $data['param_ph']);
            $stmt->bindParam(':param_ntu',           $data['param_ntu']);
            $stmt->bindParam(':peso_consumido',      $data['peso_consumido']);
            $stmt->bindParam(':muestra_tomada',      $data['muestra_tomada'], PDO::PARAM_BOOL);
            $stmt->bindParam(':observaciones',       $data['observaciones']);
            $stmt->bindParam(':agitacion',           $data['agitacion']);
            $stmt->bindParam(':desengrasador',       $data['desengrasador']);
            $stmt->execute();

            Logger::info("Registro horario insertado", [
                'detalle_id' => $this->db->lastInsertId(),
                'relacion_id' => $data['relacion_id'],
                'fecha_hora' => $data['fecha_hora']
            ]);
            return [
                'success' => true,
                'detalle_id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            Logger::error("Error al insertar registro horario: {mensaje}", ['mensaje' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }


    /**
     * Valida que el registro horario sea consecutivo (no hay huecos mayores a 1 hora) en la relación cocedor-proceso.
     * 
     * @param int $relacion_id
     * @param string $fecha_hora (formato 'Y-m-d H:i:s')
     * @return array
     *   - 'ok' => true si puede insertar sin problema
     *   - 'ok' => false y 'last_hora' => fecha de la última registrada, si falta una o más horas
     */
    public function validarConsecutividadHora(int $relacion_id, string $fecha_hora): array
    {
        // Busca la última hora registrada ANTES del nuevo registro
        $sql = "SELECT fecha_hora
            FROM procesos_cocedores_detalle
            WHERE relacion_id = :relacion_id
              AND fecha_hora < :fecha_hora
            ORDER BY fecha_hora DESC
            LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':relacion_id', $relacion_id, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_hora', $fecha_hora);
        $stmt->execute();
        $ultima = $stmt->fetchColumn();

        if (!$ultima) {
            // No hay registro anterior (es el primero): permitir
            return ['ok' => true];
        }

        // Calcula diferencia en horas
        $diferencia = (strtotime($fecha_hora) - strtotime($ultima)) / 3600;

        if ($diferencia > 1) {
            return [
                'ok' => false,
                'last_hora' => $ultima,
                'error' => 'Faltan registros horarios previos. Última hora registrada: ' . $ultima
            ];
        }
        // Si hay registro previo y es consecutivo, permitir
        return ['ok' => true];
    }

    public function validadarSupervisor(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE procesos_cocedores_detalle SET 
            supervisor_validado = '1',
            supervisor_id = :id,
            fecha_validacion = :fecha_validacion WHERE detalle_id = :detalle_id");
            $stmt->bindParam(":id", $data['id'], PDO::PARAM_INT);
            $stmt->bindParam(":detalle_id", $data['detalle_id'], PDO::PARAM_INT);
            $fecha = date('Y-m-d H:i:s');
            $stmt->bindParam(":fecha_validacion",  $fecha);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            Logger::error("Error al validar registro por el supervisor, {mensaje}", ["mensaje" => $e->getMessage()]);
            throw new \Exception("Error al validad registro");
        }
    }

    public function proximaRevision(): array
    {
        try {
            $stmt = $this->db->query("SELECT 
            NOW() AS ahora,
            DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00') AS hora_actual_redondeada,
            DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00'), INTERVAL 1 HOUR) AS proxima_revision");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Error al tomar la proxima revision: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudo obtener la hora de la proxima revision.", 500);
        }
    }

    public function registrarParo(array $data): array
    {
        try {
            $fecha = date('Y-m-d H:i:s');
            $stmt = $this->db->prepare("INSERT INTO paros_cocedores (cocedor_id, fecha_inicio, usuario_id, motivo) 
            VALUES (:cocedor_id, :fecha_inicio, :usuario_id, :motivo)");
            $stmt->bindParam(":cocedor_id", $data['cocedor_id'], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_inicio", $fecha, PDO::PARAM_STR);
            $stmt->bindParam(":usuario_id", $data['usuario_id'], PDO::PARAM_INT);
            $stmt->bindParam(":motivo", $data['motivo'], PDO::PARAM_STR);
            $stmt->execute();
            return ['ok' => true, 'paro_id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            Logger::error("Error al registrar paro, {mensaje}", ["mensaje" => $e->getMessage()]);
            throw new \Exception("Error al registrar paro");
        }
    }

    public function finalizarParo(array $data): array
    {
        try {
            $fecha = date('Y-m-d H:i:s');
            $stmt = $this->db->prepare("UPDATE paros_cocedores SET fecha_fin = :fecha_fin, observaciones = :observaciones, 
            usuario_activa = :usuario_activa 
            WHERE cocedor_id = :cocedor_id AND fecha_fin IS NULL");
            $stmt->bindParam(":fecha_fin", $fecha, PDO::PARAM_STR);
            $stmt->bindParam(":cocedor_id", $data['cocedor_id'], PDO::PARAM_INT);
            $stmt->bindParam(":observaciones", $data['observaciones'], PDO::PARAM_STR);
            $stmt->bindParam(":usuario_activa", $data['usuario_id'], PDO::PARAM_INT);
            $stmt->execute();
            return ['ok' => true];
        } catch (PDOException $e) {
            Logger::error("Error al finalizar paro, {mensaje}", ["mensaje" => $e->getMessage()]);
            throw new \Exception("Error al finalizar paro");
        }
    }

    public function obtenerFlujoCocedores(): array
    {
        try {
            $stmt = $this->db->query("SELECT TOP 1 Flujo_cocedor_1, Flujo_cocedor_2, Flujo_cocedor_3, Flujo_cocedor_4, 
            Flujo_cocedor_5, Flujo_cocedor_6, Flujo_cocedor_7 FROM TREND001 ORDER BY Time_Stamp DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Error al obtener datos del PLC: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudieron obtener los cocedores.", 500);
        }
    }

    public function obtenerTemperaturaCocedores(): array
    {
        try {
            $stmt = $this->db->query("SELECT TOP 1 COCEDORES_TEMPERATURA_DE_ENTRADA, COCEDORES_TEMPERATURA_DE_SALIDA FROM TREND001 ORDER BY Time_Stamp DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Error al obtener datos del PLC: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudieron obtener los cocedores.", 500);
        }
    }

    public function obtenerCocedoresProcesoById(int $id)
    {
        try {
            $sql = "SELECT 
                r.relacion_id,
                r.proceso_agrupado_id,
                a.descripcion AS agrupacion,
                r.fecha_inicio,
                c.cocedor_id,
                c.nombre AS cocedor
            FROM procesos_cocedores_relacion r
            INNER JOIN zn_procesos_agrupados a 
                ON r.proceso_agrupado_id = a.proceso_agrupado_id
            INNER JOIN cocedores c 
                ON r.cocedor_id = c.cocedor_id
            WHERE r.cocedor_id = :id
              AND r.fecha_fin IS NULL
            ORDER BY r.fecha_inicio DESC
            LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam('id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Error al obtener datos del PLC: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudieron obtener los cocedores.", 500);
        }
    }

    public function obtenerDetalleCocedorProceso(int $id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT 
                r.relacion_id,
                r.proceso_agrupado_id,
                a.descripcion AS agrupacion,
                r.fecha_inicio,
                c.cocedor_id,
                c.nombre AS cocedor,
                pcd.*,
                u.usu_nombre AS responsable
            FROM procesos_cocedores_relacion r
            INNER JOIN zn_procesos_agrupados a 
                ON r.proceso_agrupado_id = a.proceso_agrupado_id
            INNER JOIN cocedores c 
                ON r.cocedor_id = c.cocedor_id
            INNER JOIN procesos_cocedores_detalle pcd ON pcd.relacion_id = r.relacion_id
            INNER JOIN usuarios u ON u.usu_id = pcd.usuario_id
            WHERE r.cocedor_id = :id
            AND r.fecha_fin IS NULL
            ORDER BY r.fecha_inicio DESC
            LIMIT 1");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            Logger::error("Error al obtener detalle de cocedor-proceso: {mensaje}", ['mensaje' => $e->getMessage()]);
            return [];
        }
    }
}
