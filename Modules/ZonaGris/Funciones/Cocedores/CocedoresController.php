<?php

namespace Modules\ZonaGris\Funciones\Cocedores;

use App\Database;
use App\Helpers\Request;
use App\BaseController;
use App\Helpers\Logger;
use App\Helpers\Validator;

class CocedoresController extends BaseController
{

    private $CocedoresModel;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->CocedoresModel = new Cocedores($db);
    }


    // GET /api/zonagris/funciones/cocedores/procesos-disponibles
    public function procesosDisponibles()
    {
        $result = $this->CocedoresModel->obtenerProcesosDisponibles();
        $this->json($result);
    }

    // POST /api/zonagris/funciones/cocedores/combinar-procesos
    public function combinarProcesos()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['descripcion', 'usuario_id', 'procesos', 'cocedores']);

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        try {
            $res = $this->CocedoresModel->crearCombinacionProcesos($data);
            $this->json(['success' => true, 'proceso_agrupado_id' => $res['proceso_agrupado_id']]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // POST /api/zonagris/funciones/cocedores/finalizar-mezcla
    public function finalizarMezcla()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['proceso_agrupado_id']);

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        try {
            $res = $this->CocedoresModel->finalizarMezcla($data);
            $this->json($res);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/zonagris/funciones/cocedores/estado
    public function estadoCocedores()
    {
        $res = $this->CocedoresModel->obtenerEstadoCocedores();
        $this->json($res);
    }

    // POST /api/zonagris/funciones/cocedores/registro-horario
    public function insertarRegistroHorario()
    {
        $data = Request::input();

        $validacion = $this->CocedoresModel->validarConsecutividadHora($data['relacion_id'], $data['fecha_hora']);
        if (!$validacion['ok']) {
            $this->json([
                'success' => false,
                'alerta' => true,
                'msg' => $validacion['error'],
                'last_hora' => $validacion['last_hora']
            ], 400);
            return;
        }

        $validator = new Validator($data);
        $validator->required([
            'relacion_id',
            'fecha_hora',
            'usuario_id',
            'responsable_tipo',
            'tipo_registro',
            'param_agua',
            'param_temp_entrada',
            'param_temp_salida',
            'param_solidos',
            'param_ph',
            'param_ntu',
            'peso_consumido',
            'muestra_tomada',
            'observaciones',
            'agitacion',
            'desengrasador'
        ]);

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        try {
            $res = $this->CocedoresModel->insertarRegistroHorario($data);
            $this->json($res);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/zonagris/funciones/cocedores/validar-consecutividad/{relacion_id}/{fecha_hora}
    public function validarConsecutividadHora($relacion_id, $fecha_hora)
    {
        try {
            $res = $this->CocedoresModel->validarConsecutividadHora($relacion_id, $fecha_hora);
            $this->json($res);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // POST /api/zonagris/funciones/cocedores/validar-supervisor
    public function validarSupervisor()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['detalle_id', 'id']); // detalle_id: registro a validar, id: supervisor

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        try {
            $ok = $this->CocedoresModel->validadarSupervisor($data);
            $this->json(['success' => $ok, 'msg' => $ok ? 'Registro validado' : 'No validado']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/zonagris/funciones/cocedores/proxima-revision
    public function proximaRevision()
    {
        try {
            $ok = $this->CocedoresModel->proximaRevision();
            $this->json($ok);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // POST /api/zonagris/funciones/cocedores/paro
    public function registrarParo()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['cocedor_id', 'usuario_id', 'motivo']);

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        try {
            $ok = $this->CocedoresModel->registrarParo($data);
            $this->json($ok);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // POST /api/zonagris/funciones/cocedores/finalizar-paro
    public function finalizarParo()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['cocedor_id', 'observaciones', 'usuario_id']);

        if ($validator->fails()) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        try {
            $ok = $this->CocedoresModel->finalizarParo($data);
            $this->json($ok);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/zonagris/funciones/cocedores/obtener-cocedores-proceso-by-id
    public function obtenerCocedoresProcesoById($id)
    {
        try {
            $res = $this->CocedoresModel->obtenerCocedoresProcesoById($id);
            $this->json($res);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function obtenerDetalleCocedorProceso($id)
    {
        try {
            $res = $this->CocedoresModel->obtenerDetalleCocedorProceso($id);
            $this->json($res);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function obtenerMezclaEnProceso(){
        try {
            $res = $this->CocedoresModel->obtenerMezclaEnProceso();
            $this->json($res);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function obtenerMezclaById($id){
        try {
            $res = $this->CocedoresModel->obtenerMezclaById($id);
            $this->json($res);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }    
}
