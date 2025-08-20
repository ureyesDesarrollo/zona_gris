<?php

namespace Modules\Auth;

use App\BaseController;
use App\Database;
use App\Helpers\Logger;

class PerfilController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->userModel = new User($db);
    }

    public function showPerfil(int $user)
    {
        try {
            
            $usuario = $this->userModel->findById($user);
            if (!$usuario) {
                $this->json(['error' => 'Usuario no encontrado'], 404);
                return;
            }

            $perfilNombre = $this->userModel->getPerfilNombre((int)$usuario['up_id']);
            $permisos = $this->userModel->getPermisos((int)$usuario['up_id']);

            $this->json([
                'user_id'        => $usuario['usu_id'],
                'usuario'        => $usuario['usu_usuario'],
                'usuario_nombre' => $usuario['usu_nombre'],
                'perfil'         => $perfilNombre,
                'permisos'       => $permisos
            ], 200);

        } catch (\Exception $e) {
            Logger::error("Error en showPerfil: {mensaje}", ['mensaje' => $e->getMessage()]);
            $this->json(['error' => 'Error al obtener el perfil'], 500);
        }
    }
}
