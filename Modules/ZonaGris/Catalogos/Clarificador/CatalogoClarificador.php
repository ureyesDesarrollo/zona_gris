<?php

use App\Helpers\Logger;
use PDO;
use PDOException;

class CatalogoClarificador
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM clarificadores");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Error al listar los clarificadores: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudieron obtener los clarificadores.", 500);
        }
    }

    public function find(int $id): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clarificadores WHERE clarificador_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new \Exception("Clarificador no encontrado.", 404);
            }
            return $result;
        } catch (PDOException $e) {
            Logger::error("Error al obtener clarificador {id}: {mensaje}", ['id' => $id, 'mensaje' => $e->getMessage()]);
            throw new \Exception("Error en la base de datos al buscar el clarificador.", 500);
        }
    }

    public function changeStatus(int $id, string $estatus): bool {
        try {
            $estatusPermitidos = ['ACTIVO', 'INACTIVO', 'MANTENIMIENTO'];
            if (!in_array($estatus, $estatusPermitidos)) {
                throw new \Exception("Estatus no permitido", 400);
            }
            $stmt = $this->db->prepare("UPDATE clarificadores SET estatus = :estatus WHERE clarificador_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':estatus', $estatus, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                throw new \Exception("Clarificador no encontrado o sin cambios.", 404);
            }
            return true;
        } catch (PDOException $e) {
            Logger::error("Error al cambiar estatus de clarificador: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("Error en la base de datos al cambiar el estatus.", 500);
        }
    }
}