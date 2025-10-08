<?php

namespace Modules\ZonaGris\Funciones\Clarificador;

use App\BaseController;
use App\Database;
use App\Helpers\Request;
use App\Helpers\Validator;

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
        if ($res) {
            return $this->json($res);
        } else {
            return $this->json(['ok' => false, 'error' => 'Error al obtener procesos activos'], 500);
        }
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
            'isSupervisor',
            'relacion_id'
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

    public function insertarQuimicosClarificador()
    {
        $data = Request::input();
        $validator = new Validator($data);
        $validator->required([
            'clarificador_id',
            'quimico_lote',
            'cantidad',
            'unidad_medida',
            'usuario_id',
            'fecha_hora',
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

        $res = $this->clarificador->obtenerUltimoLote($data);
        if ($res) {
            return $this->json($res);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
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
            return $this->json(['ok' => $res['success']]);
        } else {
            return $this->json(['ok' => false, 'error' => $res['error']], 500);
        }
    }
}
