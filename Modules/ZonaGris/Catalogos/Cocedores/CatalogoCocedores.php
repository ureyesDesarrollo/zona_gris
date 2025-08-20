<?php

namespace Modules\ZonaGris\Catalogos\Cocedores;

use PDO;
use PDOException;
use App\Helpers\Logger;

class CatalogoCocedores
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Listar todos los cocedores
    public function all(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM cocedores");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Error al listar los cocedores: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudieron obtener los cocedores.", 500);
        }
    }

    // Buscar un cocedor por ID
    public function find($id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM cocedores WHERE cocedor_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new \Exception("Cocedor no encontrado.", 404);
            }
            return $result;
        } catch (PDOException $e) {
            Logger::error("Error al obtener cocedor {id}: {mensaje}", ['id' => $id, 'mensaje' => $e->getMessage()]);
            throw new \Exception("Error en la base de datos al buscar el cocedor.", 500);
        }
    }

    // Crea un nuevo cocedor
    public function create(array $data): int
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO cocedores (nombre, capacidad, observaciones) VALUES (:nombre, :capacidad, :observaciones)"
            );
            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':capacidad', $data['capacidad']);
            $stmt->bindParam(':observaciones', $data['observaciones']);
            $stmt->execute();
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            Logger::error("Error al insertar nuevo cocedor: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("Error en la base de datos al crear el cocedor.", 500);
        }
    }

    // Actualiza un cocedor existente
    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE cocedores SET nombre = :nombre, capacidad = :capacidad, estatus = :estatus WHERE cocedor_id = :id"
            );
            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':capacidad', $data['capacidad']);
            $stmt->bindParam(':estatus', $data['estatus'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                throw new \Exception("Cocedor no encontrado o sin cambios.", 404);
            }
            return true;
        } catch (PDOException $e) {
            Logger::error("Error al actualizar cocedor: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("Error en la base de datos al actualizar el cocedor.", 500);
        }
    }

    /**
 * Cambia el estatus de un cocedor (ACTIVO, INACTIVO, MANTENIMIENTO, etc.)
 * 
 * @param int $id ID del cocedor
 * @param string $estatus Nuevo estatus ('ACTIVO', 'INACTIVO', 'MANTENIMIENTO')
 * @return bool
 * @throws \Exception
 */
public function changeStatus($id, $estatus): bool
{
    // Opcional: Valida que el estatus sea uno permitido
    $estatusPermitidos = ['ACTIVO', 'INACTIVO', 'MANTENIMIENTO'];
    if (!in_array($estatus, $estatusPermitidos)) {
        throw new \Exception("Estatus no permitido", 400);
    }

    try {
        $stmt = $this->db->prepare(
            "UPDATE cocedores SET estatus = :estatus WHERE cocedor_id = :id"
        );
        $stmt->bindParam(":estatus", $estatus, PDO::PARAM_STR);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new \Exception("Cocedor no encontrado o sin cambios.", 404);
        }
        return true;
    } catch (PDOException $e) {
        Logger::error("Error al cambiar estatus de cocedor: {mensaje}", ['mensaje' => $e->getMessage()]);
        throw new \Exception("Error en la base de datos al cambiar el estatus.", 500);
    }
}


    // Verifica si existe un cocedor con ese nombre
    public function existsByName(string $nombre, $excludeId = null): bool
    {
        try {
            $query = "SELECT 1 FROM cocedores WHERE nombre = :nombre";
            if ($excludeId) {
                $query .= " AND cocedor_id != :id";
            }
            $query .= " LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            if ($excludeId) {
                $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            Logger::error("Error al buscar duplicados de cocedores: {mensaje}", [
                'mensaje' => $e->getMessage()
            ]);
            throw new \Exception("Error en la base de datos al verificar duplicados.", 500);
        }
    }
}
