<?php

namespace Modules\Auth;

use App\BaseController;
use App\Database;
use App\Helpers\Logger;

class AuthController extends BaseController
{
    private $user;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->user = new User($db);
    }

    public function login()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $usuario = $data['usuario'] ?? '';
            $password = $data['password'] ?? '';

            // Validación básica
            if (empty($usuario) || empty($password)) {
                $this->json(['error' => 'Usuario y contraseña son requeridos.'], 422);
                return;
            }

            $user = $this->user->findByUsername($usuario);

            if (!$user) {
                $this->json(['error' => 'Credenciales incorrectas'], 401);
                return;
            }

            // Seguridad: Reemplaza esto por password_verify si usas hash seguro
            // if (!password_verify($password, $user['usu_pwr'])) {
            if (md5($password) !== $user['usu_pwr']) {
                $this->json(['error' => 'Credenciales incorrectas'], 401);
                return;
            }

            $usuario = [ 'user_id' => $user['usu_id'] ];
            $this->json($usuario, 200);
        } catch (\Exception $e) {
            Logger::error("Error en login: {mensaje}", ['mensaje' => $e->getMessage()]);
            $this->json(['error' => 'Error interno al autenticar.'], 500);
        }
    }
}
