<?php
namespace Modules\Auth;

use PDO;
use PDOException;
use App\Helpers\Logger;
use Exception;

class User
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Busca usuario por nombre de usuario (solo activos).
     * @param string $usuario
     * @return array|null
     * @throws \Exception
     */
    public function findByUsername(string $usuario): ?array
    {
        try {
            $sql = "SELECT usu_id, usu_pwr, up_id FROM usuarios WHERE usu_usuario = :usuario AND usu_est = 'A' LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            Logger::error("Error en findByUsername: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudo consultar el usuario");
        }
    }

    /**
     * Busca usuario por ID.
     * @param int $id
     * @return array|null
     * @throws \Exception
     */
    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT usu_id, usu_usuario, usu_nombre, up_id FROM usuarios WHERE usu_id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            Logger::error("Error en findById: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudo consultar el usuario por ID");
        }
    }

    /**
     * Obtiene el nombre del perfil por su ID.
     * @param int $up_id
     * @return string|null
     * @throws \Exception
     */
    public function getPerfilNombre(int $up_id): ?string
    {
        try {
            $sql = "SELECT up_nombre FROM usuarios_perfiles WHERE up_id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $up_id, PDO::PARAM_INT);
            $stmt->execute();
            $nombre = $stmt->fetchColumn();
            return $nombre !== false ? $nombre : null;
        } catch (PDOException $e) {
            Logger::error("Error en getPerfilNombre: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudo obtener el nombre del perfil");
        }
    }

    /**
     * Obtiene los permisos del perfil.
     * @param int $up_id
     * @return array
     * @throws \Exception
     */
    public function getPermisos(int $up_id): array
    {
        try {
            $sql = "
                SELECT bm.bm_descripcion as modulo, upe.upe_agregar, upe.upe_borrar, upe.upe_editar, upe.upe_listar
                FROM usuarios_permisos upe
                INNER JOIN bitacora_modulos bm ON upe.bm_id = bm.bm_id
                WHERE upe.up_id = :up_id
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':up_id', $up_id, PDO::PARAM_INT);
            $stmt->execute();

            $permisos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $acciones = [];
                if ($row['upe_listar'])  $acciones[] = 'listar';
                if ($row['upe_agregar']) $acciones[] = 'agregar';
                if ($row['upe_editar'])  $acciones[] = 'editar';
                if ($row['upe_borrar'])  $acciones[] = 'borrar';
                $permisos[$row['modulo']] = $acciones;
            }
            return $permisos;
        } catch (PDOException $e) {
            Logger::error("Error en getPermisos: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudieron obtener los permisos");
        }
    }

    public function validUserCode(String $code){
        try{
            $sql = "SELECT usu_id, usu_nombre, up_id FROM usuarios WHERE usu_clave_auth = :code AND usu_est = 'A'";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$result){
                return ['success' => false, 'error' => 'Clave incorrecta'];
            }

            return ['success' => true, 'data' => $result];
        }catch(PDOException $e){
            Logger::error("Error en validUserCode: {mensaje}", ['mensaje' => $e->getMessage()]);
            throw new \Exception("No se pudieron obtener los datos");
        }
    }
}
