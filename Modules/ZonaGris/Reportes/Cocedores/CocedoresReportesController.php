<?php

namespace Modules\ZonaGris\Reportes\Cocedores;

use App\BaseController;
use App\Database;
use App\Helpers\Request;
use App\Helpers\Validator;

class CocedoresReportesController extends BaseController
{
    private $CocedoresReportesModel;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->CocedoresReportesModel = new CocedoresReportes($db);
    }

    public function index()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['fecha_inicio', 'fecha_fin']);

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }
        $this->json($this->CocedoresReportesModel->obtenerReporteCocedores($data['fecha_inicio'], $data['fecha_fin']));
    }
}
