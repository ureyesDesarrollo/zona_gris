<?php

namespace Modules\ZonaGris\Funciones\Clarificador;

use PDO;
use PDOException;
use App\Helpers\Logger;

/**
 * Clase Clarificador
 * 
 * Maneja la lógica de negocio relacionada con los clarificadores:
 * - Estado de clarificadores
 * - Inicio y finalización de procesos
 * - Inserción de registros horarios
 * - Validación de parámetros
 * - Aplicación de químicos
 * - Consultas de procesos activos y últimos detalles
 */
class Clarificador
{
    /** @var PDO $db Conexión a la base de datos */
    private $db;

    /**
     * Constructor
     *
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Obtener el estado de todos los clarificadores activos e inactivos.
     *
     * @return array Lista de clarificadores con procesos, materiales y validaciones.
     */
    public function obtenerEstadoClarificador()
    {
        try {
            $sql = "SELECT 
    c.clarificador_id,
    c.nombre,
    c.estatus,
    r.relacion_id,
    r.proceso_agrupado_id,
    GROUP_CONCAT(DISTINCT p.pro_id ORDER BY p.pro_id ASC) AS procesos,
    GROUP_CONCAT(CONCAT(m.mat_nombre, ' (', IFNULL(pm.pma_kg, 0), ' kg)')
                ORDER BY m.mat_nombre SEPARATOR ', ') AS materiales,
    r.fecha_inicio,
    pcd.fecha_hora,
    pcd.param_solidos_entrada,
    pcd.supervisor_validado,
    pcd.control_procesos_validado,

    -- Métricas de la ventana seleccionada (hora anterior completa o hora actual en curso)
    IFNULL(vh.registros_ventana, 0) AS registros_ultima_hora,
    IFNULL(vh.count_reg_00a29, 0) AS count_reg_00a29,
    IFNULL(vh.count_reg_30a59, 0) AS count_reg_30a59,
    -- Indicador: se puede validar si hay registros en ambas mitades de la ventana
    CASE 
        WHEN IFNULL(vh.count_reg_00a29, 0) > 0 AND IFNULL(vh.count_reg_30a59, 0) > 0 THEN 1 
        ELSE 0 
    END AS puede_validar_hora
FROM clarificadores c
LEFT JOIN procesos_clarificador_relacion r 
    ON c.clarificador_id = r.clarificador_id 
    AND r.fecha_fin IS NULL
LEFT JOIN zn_procesos_agrupados_detalle pad 
    ON pad.proceso_agrupado_id = r.proceso_agrupado_id
LEFT JOIN procesos p 
    ON p.pro_id = pad.pro_id
LEFT JOIN procesos_materiales pm 
    ON pm.pro_id = p.pro_id
LEFT JOIN materiales m 
    ON m.mat_id = pm.mat_id

-- Subquery: último registro por relación (para mostrar últimos datos puntuales)
LEFT JOIN (
    SELECT relacion_id, MAX(fecha_hora) AS ultima_fecha
    FROM procesos_clarificador_detalle
    GROUP BY relacion_id
) ult 
    ON ult.relacion_id = r.relacion_id
LEFT JOIN procesos_clarificador_detalle pcd 
    ON pcd.relacion_id = ult.relacion_id 
    AND pcd.fecha_hora = ult.ultima_fecha

-- Subquery: ventana inteligente (hora anterior completa si minuto actual <= 10; si no, hora actual en curso)
LEFT JOIN (
    SELECT
        pcd2.relacion_id,
        COUNT(*) AS registros_ventana,
        SUM(CASE WHEN MINUTE(pcd2.fecha_hora) BETWEEN 0 AND 29 THEN 1 ELSE 0 END) AS count_reg_00a29,
        SUM(CASE WHEN MINUTE(pcd2.fecha_hora) BETWEEN 30 AND 59 THEN 1 ELSE 0 END) AS count_reg_30a59,
        MAX(CASE WHEN MINUTE(pcd2.fecha_hora) BETWEEN 0 AND 29 THEN COALESCE(pcd2.supervisor_validado, 0) ELSE 0 END) AS supervisor_valida_00a29,
        MAX(CASE WHEN MINUTE(pcd2.fecha_hora) BETWEEN 30 AND 59 THEN COALESCE(pcd2.supervisor_validado, 0) ELSE 0 END) AS supervisor_valida_30a59,
        MAX(CASE WHEN MINUTE(pcd2.fecha_hora) BETWEEN 0 AND 29 THEN COALESCE(pcd2.control_procesos_validado, 0) ELSE 0 END) AS control_valida_00a29,
        MAX(CASE WHEN MINUTE(pcd2.fecha_hora) BETWEEN 30 AND 59 THEN COALESCE(pcd2.control_procesos_validado, 0) ELSE 0 END) AS control_valida_30a59
    FROM procesos_clarificador_detalle pcd2
    WHERE pcd2.fecha_hora >= CASE 
              WHEN MINUTE(NOW()) <= 10
                   THEN DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 HOUR), '%Y-%m-%d %H:00:00')  -- hora anterior completa
              ELSE DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')                                   -- inicio de la hora actual
          END
      AND pcd2.fecha_hora < CASE 
              WHEN MINUTE(NOW()) <= 10
                   THEN DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')                               -- fin de la hora anterior
              ELSE NOW()                                                                      -- hasta ahora (hora actual)
          END
    GROUP BY pcd2.relacion_id
) vh 
    ON vh.relacion_id = r.relacion_id

GROUP BY 
    c.clarificador_id, c.nombre, 
    r.relacion_id, r.proceso_agrupado_id, 
    r.fecha_inicio
ORDER BY c.clarificador_id;";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            Logger::error("Error al obtener estado clarificador: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    /** 
     * Obtiene los procesos activos.
     *
     * @return array
     */
    public function obtenerProcesosActivos() {
        try {
            $sql = "SELECT
            za.proceso_agrupado_id,
            zd.pro_id,
            p.pro_total_kg,
            pt.pt_descripcion,
            za.descripcion AS agrupacion_descripcion,
            GROUP_CONCAT(
                CONCAT(m.mat_nombre, ' (', IFNULL(pm.pma_kg, 0), ' kg)')
                ORDER BY m.mat_nombre
                SEPARATOR ', '
            ) AS materiales_con_cantidad
        FROM zn_procesos_agrupados AS za
        INNER JOIN zn_procesos_agrupados_detalle AS zd
            ON zd.proceso_agrupado_id = za.proceso_agrupado_id
        INNER JOIN procesos AS p
            ON p.pro_id = zd.pro_id
        INNER JOIN preparacion_tipo AS pt
            ON pt.pt_id = p.pt_id
        INNER JOIN procesos_materiales AS pm
            ON pm.pro_id = p.pro_id
        INNER JOIN materiales AS m
            ON m.mat_id = pm.mat_id
        WHERE EXISTS (
            SELECT 1
            FROM procesos_cocedores_relacion AS pcr
            INNER JOIN procesos_cocedores_detalle AS pcd
                ON pcd.relacion_id = pcr.relacion_id
            WHERE pcr.proceso_agrupado_id = za.proceso_agrupado_id
            GROUP BY pcr.relacion_id
            HAVING COUNT(*) >= 2   -- 2 o más registros = 2+ horas
        )
        GROUP BY za.proceso_agrupado_id, za.descripcion, zd.pro_id, p.pro_total_kg, pt.pt_descripcion";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            Logger::error("Error al obtener procesos activos: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Iniciar un proceso en un clarificador.
     *
     * @param array $data ['proceso_agrupado_id', 'clarificador_id']
     * @return array
     */
    public function iniciarProcesoClarificador(array $data): array
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO procesos_clarificador_relacion (proceso_agrupado_id, clarificador_id, fecha_inicio) 
            VALUES (:proceso_agrupado_id, :clarificador_id, :fecha_inicio)");
            $fecha_inicio = date('Y-m-d H:i:s');
            $stmt->bindParam(':proceso_agrupado_id', $data['proceso_agrupado_id'], PDO::PARAM_INT);
            $stmt->bindParam(':clarificador_id', $data['clarificador_id'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            Logger::error("Error al iniciar proceso clarificador: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Finalizar un proceso en un clarificador.
     *
     * @param array $data ['proceso_agrupado_id']
     * @return array
     */
    public function finalizarProcesoClarificador(array $data): array
    {
        try {
            $stmt = $this->db->prepare("UPDATE procesos_clarificador_relacion 
            SET fecha_fin = :fecha_fin 
            WHERE proceso_agrupado_id = :proceso_agrupado_id");
            $fecha_fin = date('Y-m-d H:i:s');
            $stmt->bindParam(':proceso_agrupado_id', $data['proceso_agrupado_id'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            Logger::error("Error al finalizar proceso clarificador: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Insertar un registro horario con parámetros de operación.
     *
     * @param array $data
     * @return array
     */
    public function insertarRegistroHorario(array $data): array
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO procesos_clarificador_detalle (
                relacion_id, usuario_id, responsable_tipo, param_solidos_entrada,
                param_flujo_salida, param_ntu_entrada, param_ntu_salida, param_ph_entrada,
                param_ph_electrodo, param_ph_control, param_dosificacion_polimero, tanque,
                tanque_hora_inicio, tanque_hora_fin, param_presion, param_entrada_aire,
                param_varometro, param_nivel_nata, param_filtro_1, param_filtro_2, param_filtro_3,
                param_filtro_4, param_filtro_5, cambio_filtro, observaciones
            ) VALUES (
                :relacion_id, :usuario_id, :responsable_tipo, :param_solidos_entrada,
                :param_flujo_salida, :param_ntu_entrada, :param_ntu_salida, :param_ph_entrada,
                :param_ph_electrodo, :param_ph_control, :param_dosificacion_polimero, :tanque,
                :tanque_hora_inicio, :tanque_hora_fin, :param_presion, :param_entrada_aire,
                :param_varometro, :param_nivel_nata, :param_filtro_1, :param_filtro_2, :param_filtro_3,
                :param_filtro_4, :param_filtro_5, :cambio_filtro, :observaciones
            )");

            $stmt->bindParam(':relacion_id', $data['relacion_id'], PDO::PARAM_INT);
            $stmt->bindParam(':usuario_id', $data['usuario_id'], PDO::PARAM_INT);
            $stmt->bindParam(':responsable_tipo', $data['responsable_tipo'], PDO::PARAM_STR);
            $stmt->bindParam(':param_solidos_entrada', $data['param_solidos_entrada'], PDO::PARAM_STR);
            $stmt->bindParam(':param_flujo_salida', $data['param_flujo_salida'], PDO::PARAM_STR);
            $stmt->bindParam(':param_ntu_entrada', $data['param_ntu_entrada'], PDO::PARAM_STR);
            $stmt->bindParam(':param_ntu_salida', $data['param_ntu_salida'], PDO::PARAM_STR);
            $stmt->bindParam(':param_ph_entrada', $data['param_ph_entrada'], PDO::PARAM_STR);
            $stmt->bindParam(':param_ph_electrodo', $data['param_ph_electrodo'], PDO::PARAM_STR);
            $stmt->bindParam(':param_ph_control', $data['param_ph_control'], PDO::PARAM_STR);
            $stmt->bindParam(':param_dosificacion_polimero', $data['param_dosificacion_polimero'], PDO::PARAM_STR);
            $stmt->bindParam(':tanque', $data['tanque'], PDO::PARAM_INT);
            $stmt->bindParam(':tanque_hora_inicio', $data['tanque_hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(':tanque_hora_fin', $data['tanque_hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(':param_presion', $data['param_presion'], PDO::PARAM_STR);
            $stmt->bindParam(':param_entrada_aire', $data['param_entrada_aire'], PDO::PARAM_STR);
            $stmt->bindParam(':param_varometro', $data['param_varometro'], PDO::PARAM_STR);
            $stmt->bindParam(':param_nivel_nata', $data['param_nivel_nata'], PDO::PARAM_STR);
            $stmt->bindParam(':param_filtro_1', $data['param_filtro_1'], PDO::PARAM_STR);
            $stmt->bindParam(':param_filtro_2', $data['param_filtro_2'], PDO::PARAM_STR);
            $stmt->bindParam(':param_filtro_3', $data['param_filtro_3'], PDO::PARAM_STR);
            $stmt->bindParam(':param_filtro_4', $data['param_filtro_4'], PDO::PARAM_STR);
            $stmt->bindParam(':param_filtro_5', $data['param_filtro_5'], PDO::PARAM_STR);
            $stmt->bindParam(':cambio_filtro', $data['cambio_filtro'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $data['observaciones'], PDO::PARAM_STR);

            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            Logger::error("Error al insertar registro horario: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Validar un registro horario por supervisor o control de procesos.
     *
     * @param array $data 
     *  - Si supervisor: ['isSupervisor' => true, 'supervisor_id', 'relacion_id']
     *  - Si control procesos: ['isSupervisor' => false, 'control_procesos_id', 'relacion_id']
     * @return array Resultado de la operación ['success' => bool, 'error' => string|null]
     */
    public function validacionHora(array $data): array
    {
        try {
            $isSupervisor = $data['isSupervisor'];
            $fecha_validacion = date('Y-m-d H:i:s');
            if ($isSupervisor) {
                $stmt = $this->db->prepare("UPDATE procesos_clarificador_detalle SET supervisor_validado = 1, 
                supervisor_id = :id, fecha_validacion_supervisor = :fecha_validacion_supervisor, supervisor_observaciones = :observaciones
                WHERE detalle_id = :detalle_id");
                $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
                $stmt->bindParam(':fecha_validacion_supervisor', $fecha_validacion, PDO::PARAM_STR);
                $stmt->bindParam(':observaciones', $data['observaciones'], PDO::PARAM_STR);
                $stmt->bindParam(':detalle_id', $data['detalle_id'], PDO::PARAM_INT);
                $stmt->execute();
                return ['success' => true];
            } else {
                $stmt = $this->db->prepare("UPDATE procesos_clarificador_detalle SET control_procesos_validado = 1, 
                control_procesos_id = :id, fecha_validacion_control = :fecha_validacion_control, 
                control_proceso_observaciones = :observaciones
                WHERE detalle_id = :detalle_id");
                $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
                $stmt->bindParam(':fecha_validacion_control', $fecha_validacion, PDO::PARAM_STR);
                $stmt->bindParam(':observaciones', $data['observaciones'], PDO::PARAM_STR);
                $stmt->bindParam(':detalle_id', $data['detalle_id'], PDO::PARAM_INT);
                $stmt->execute();
                return ['success' => true];
            }
        } catch (PDOException $e) {
            Logger::error("Error al validar hora: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Insertar aplicación de químicos en un clarificador.
     *
     * @param array $data ['clarificador_id', 'quimico_lote', 'cantidad', 'unidad_medida', 'usuario_id', 'fecha_hora', 'control_procesos_id']
     * @return array Resultado de la operación ['success' => bool, 'error' => string|null]
     */

    public function insertarQuimicosClarificador(array $data): array
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO aplicacion_quimicos_clarificador (clarificador_id, quimico_lote, cantidad
            unidad_medida, usuario_id, fecha_hora, control_procesos_id) 
            VALUES (:clarificador_id, :quimico_lote, :cantidad, :unidad_medida, :usuario_id, :fecha_hora, :control_procesos_id)");
            $stmt->bindParam(':clarificador_id', $data['clarificador_id'], PDO::PARAM_INT);
            $stmt->bindParam(':quimico_lote', $data['quimico_lote'], PDO::PARAM_STR);
            $stmt->bindParam(':cantidad', $data['cantidad'], PDO::PARAM_STR);
            $stmt->bindParam(':unidad_medida', $data['unidad_medida'], PDO::PARAM_STR);
            $stmt->bindParam(':usuario_id', $data['usuario_id'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_hora', $data['fecha_hora'], PDO::PARAM_STR);
            $stmt->bindParam(':control_procesos_id', $data['control_procesos_id'], PDO::PARAM_INT);
            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            Logger::error("Error al insertar quimicos clarificador: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    /**
     * Obtener el último lote aplicado en clarificador y validar si corresponde al lote indicado.
     *
     * @param string $lote Código del lote a validar
     * @return array ['success' => bool, 'lote' => string, 'control_procesos_id' => int] o ['success' => false, 'error' => string]
     */

    public function obtenerUltimoLote(String $lote)
    {
        try {
            $stmt = $this->db->prepare("SELECT quimico_lote, control_procesos_id FROM aplicacion_quimicos_clarificador 
            LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['quimico_lote'] == $lote) {
                return ['success' => true, 'lote' => $result['quimico_lote'], 'control_procesos_id' => $result['control_procesos_id']];
            } else {
                return ['success' => false, 'error' => 'Lote no validado por control de procesos'];
            }
        } catch (PDOException $e) {
            Logger::error("Error al obtener ultimo lote: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }



    /**
     * Obtener proceso activo de un clarificador por su ID.
     *
     * @param int $id Identificador del clarificador
     * @return array|null Datos del proceso activo o null si no hay
     */
    public function obtenerClarificadorProcesoById(int $id): ?array
    {
        try {
            $sql = "SELECT 
                r.relacion_id,
                r.proceso_agrupado_id,
                a.descripcion AS agrupacion,
                r.fecha_inicio,
                c.clarificador_id,
                c.nombre AS clarificador
            FROM procesos_clarificador_relacion r
            INNER JOIN zn_procesos_agrupados a 
                ON r.proceso_agrupado_id = a.proceso_agrupado_id
            INNER JOIN clarificadores c 
                ON r.clarificador_id = c.clarificador_id
            WHERE r.clarificador_id = :id
              AND r.fecha_fin IS NULL
            ORDER BY r.fecha_inicio DESC
            LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?? [];
        } catch (PDOException $e) {
            Logger::error("Error al obtener proceso activo del clarificador: {mensaje}", ['mensaje' => $e->getMessage()]);
            return [];
        }
    }


    /**
     * Obtener el último detalle de parámetros registrados para un clarificador.
     *
     * @param int $id Identificador del clarificador
     * @return array Detalle del último registro (puede estar vacío si no hay registros)
     */
    public function obtenerDetalleClarificadorProceso(int $id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT 
                r.relacion_id,
                r.proceso_agrupado_id,
                a.descripcion AS agrupacion,
                r.fecha_inicio,
                c.clarificador_id,
                c.nombre AS clarificador,
                pcd.*,
                u.usu_nombre AS responsable
            FROM procesos_clarificador_relacion r
            INNER JOIN zn_procesos_agrupados a 
                ON r.proceso_agrupado_id = a.proceso_agrupado_id
            INNER JOIN clarificadores c 
                ON r.clarificador_id = c.clarificador_id
            INNER JOIN procesos_clarificador_detalle pcd 
                ON pcd.relacion_id = r.relacion_id
            INNER JOIN usuarios u 
                ON u.usu_id = pcd.usuario_id
            WHERE r.clarificador_id = :id
              AND r.fecha_fin IS NULL
            ORDER BY pcd.fecha_hora DESC
            LIMIT 1");

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['success' => $result ?: []];
        } catch (PDOException $e) {
            Logger::error("Error al obtener detalle de clarificador-proceso: {mensaje}", ['mensaje' => $e->getMessage()]);
            return [];
        }
    }
}
