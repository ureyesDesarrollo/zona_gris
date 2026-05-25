<?php

namespace Modules\ZonaGris\Funciones\Clarificador;

use App\BaseController;
use App\Database;
use App\Helpers\Request;
use App\Helpers\Validator;
use Exception;

class ClarificadorController extends BaseController
{
    private $clarificador;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->clarificador = new Clarificador($db);
    }

    public function obtenerEstadoClarificador()
    {
        $res = $this->clarificador->obtenerEstadoClarificador();
        if ($res) {
            return $this->json($res);
        } else {
            return $this->json(['ok' => false, 'error' => 'Error al obtener estado clarificador'], 500);
        }
    }

    public function obtenerProcesosActivos()
    {
        $res = $this->clarificador->obtenerProcesosActivos();
        return $this->json($res);
    }

    public function iniciarProcesoClarificador()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required([
            'proceso_agrupado_id'
        ]);

        if ($validator->fails()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 400);
        }
        $res = $this->clarificador->iniciarProcesoClarificador($data);
        if ($res['success']) {
            return $this->json(['ok' => $res['success']]);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
    }

    public function finalizarProcesoClarificador()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required([
            'proceso_agrupado_id'
        ]);

        if ($validator->fails()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 400);
        }
        $res = $this->clarificador->finalizarProcesoClarificador($data);
        if ($res['success']) {
            return $this->json(['ok' => $res['success']]);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
    }

    public function insertarRegistroHorario()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required([
            'relacion_id',
            'usuario_id',
            'responsable_tipo',
            'param_solidos_entrada',
            'param_flujo_salida',
            'param_ntu_entrada',
            'param_ntu_salida',
            'param_ph_entrada',
            'param_ph_electrodo',
            'param_ph_control',
            'param_dosificacion_polimero',
            'tanque',
            'tanque_hora_inicio',
            'tanque_hora_fin',
            'param_presion',
            'param_entrada_aire',
            'param_varometro',
            'param_nivel_nata',
            'param_filtro_1',
            'param_filtro_2',
            'param_filtro_3',
            'param_filtro_4',
            'param_filtro_5',
            'cambio_filtro'
        ]);

        if ($validator->fails()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $res = $this->clarificador->insertarRegistroHorario($data);
        if ($res['success']) {
            return $this->json(['ok' => $res['success']]);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
    }

    public function validacionHora()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required([
            'id',
            'observaciones',
            'detalle_id',
        ]);

        if ($validator->fails()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 400);
        }
        $res = $this->clarificador->validacionHora($data);
        if ($res['success']) {
            return $this->json(['ok' => $res['success']]);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
    }


    public function insertarQuimico()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['lote', 'nombre_quimico']);

        if ($validator->fails()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $res = $this->clarificador->insertarQuimico($data['lote'], $data['nombre_quimico']);
        if ($res['success']) {
            return $this->json(['ok' => $res['success']]);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
    }

    public function consultarQuimicos()
    {
        $res = $this->clarificador->consultarLotesQuimicos();
        return $this->json($res);
    }

    public function insertarQuimicosClarificador()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required([
            'clarificador_id',
            'quimico_lote',
            'cantidad',
            'usuario_id',
            'control_procesos_id'
        ]);

        if ($validator->fails()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $res = $this->clarificador->insertarQuimicosClarificador($data);
        if ($res['success']) {
            return $this->json(['ok' => $res['success']]);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
    }

    public function obtenerUltimoLote()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['lote']);

        if ($validator->fails()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $res = $this->clarificador->obtenerUltimoLote($data['lote']);

        return $this->json($res, $res['success'] ? 200 : 400);
    }

    public function cambiarEstatusQuimico(){
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required(['lote']);

        if($validator->fails()){
            return $this->json(['success' => false, 'error' => $validator->errors()], 400);
        }

        $res = $this->clarificador->cambiarEstatusQuimico($data['lote']);
        return $this->json($res, $res['success'] ? 200 : 400);
    }

    

    public function obtenerClarificadorProcesoById($id)
    {
        $res = $this->clarificador->obtenerClarificadorProcesoById($id);
        if ($res) {
            return $this->json($res);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
    }

    public function obtenerDetalleClarificadorProceso($id)
    {

        $res = $this->clarificador->obtenerDetalleClarificadorProceso($id);
        if ($res['success']) {
            return $this->json($res['success']);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
    }

    public function obtenerLoteQuimico($lote)
    {
        try {
            $res = $this->clarificador->obtenerLoteQuimico($lote);

            // Si hay error, retornar con código apropiado
            if (!$res['success']) {
                return $this->json([
                    'success' => false,
                    'error' => $res['error'],
                    'data' => null
                ], 400);
            }

            // Verificar que existan los datos esperados
            if (!isset($res['data']['QUIMICOS']) || empty($res['data']['QUIMICOS'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'No se encontraron datos del lote',
                    'data' => null
                ], 404);
            }

            $quimico = $res['data']['QUIMICOS'][0];

            return $this->json([
                'success' => true,
                'error' => null,
                'data' => $quimico
            ], 200);
        } catch (Exception $ex) {
            return $this->json([
                'success' => false,
                'error' => 'Error al procesar la solicitud: ' . $ex->getMessage(),
                'data' => null
            ], 500);
        }
    }


    public function obtenerMezclaEnProceso(){
        try {
            $res = $this->clarificador->obtenerMezclaEnProceso();
            $this->json($res);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function obtenerMezclaById($id){
        try {
            $res = $this->clarificador->obtenerMezclaById($id);
            $this->json($res);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }    
}
