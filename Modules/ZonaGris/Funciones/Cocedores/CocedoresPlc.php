<?php

namespace Modules\ZonaGris\Funciones\Cocedores;

use App\Database;
use App\BaseController;
use App\Helpers\Logger;

class CocedoresPlc extends BaseController
{

    private $CocedoresModel;

    public function __construct()
    {
        $db = Database::getInstance('sqlsrv')->getConnection();
        $this->CocedoresModel = new Cocedores($db);
    }

    //GET /api/zonagris/funciones/obtener-flujo
    public function obtenerFlujo()
    {
        try {
            $result = $this->CocedoresModel->obtenerFlujoCocedores();
            $this->json($result, 200);
        } catch (\Exception $e) {
            Logger::error("Error al consultar flujo {mensaje}", ['mensaje' => $e->getMessage()]);
            $this->json(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function obtenerTemperaturaCocedor(){
        try {
            $result = $this->CocedoresModel->obtenerTemperaturaCocedores();
            $this->json($result, 200);
        } catch (\Exception $e) {
            Logger::error("Error al consultar temperatura {mensaje}", ['mensaje' => $e->getMessage()]);
            $this->json(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
