<?php

use App\Helpers\Logger;
use Modules\ZonaGris\Catalogos\Cocedores\CatalogoCocedoresController;
use Modules\ZonaGris\Funciones\Cocedores\CocedoresController;
use Modules\ZonaGris\Funciones\Cocedores\CocedoresPlc;
use Modules\ZonaGris\Reportes\Cocedores\CocedoresReportesController;

$catalogo = new CatalogoCocedoresController();
$funciones = new CocedoresController();
$funcionesPlc = new CocedoresPlc();
$reportes = new CocedoresReportesController();

// Listar todas
$router->add(
    'GET',
    '/api/zonagris/catalogos/cocedor',
    function () use ($catalogo) {
        $catalogo->index();
    }
);

// Obtener por ID
$router->add(
    'GET',
    '/api/zonagris/catalogos/cocedor/(\d+)',
    function ($id) use ($catalogo) {
        $catalogo->show($id);
    }
);

// Crear nuevo
$router->add(
    'POST',
    '/api/zonagris/catalogos/cocedor',
    function () use ($catalogo) {
        $catalogo->store();
    }
);

// Actualizar por ID
$router->add(
    'PUT',
    '/api/zonagris/catalogos/cocedor/(\d+)',
    function ($id) use ($catalogo) {
        $catalogo->update($id);
    }
);

$router->add(
    'PUT',
    '/api/zonagris/catalogos/cocedor/(\d+)/estatus',
    function ($id) use ($catalogo) {
        $catalogo->changeStatus($id);
    }
);



// --- Endpoints especiales para operaciones en cocedores ---

// Procesos disponibles en los receptores
$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/procesos-disponibles',
    function () use ($funciones) {
        $funciones->procesosDisponibles();
    }
);

// Combinar (agrupar) procesos
$router->add(
    'POST',
    '/api/zonagris/funciones/cocedores/combinar-procesos',
    function () use ($funciones) {
        $funciones->combinarProcesos();
    }
);

// Finalizar mezcla
$router->add(
    'POST',
    '/api/zonagris/funciones/cocedores/finalizar-mezcla',
    function () use ($funciones) {
        $funciones->finalizarMezcla();
    }
);

// Estado de cocedores
$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/estado',
    function () use ($funciones) {
        $funciones->estadoCocedores();
    }
);

// Registrar horario
$router->add(
    'POST',
    '/api/zonagris/funciones/cocedores/registro-horario',
    function () use ($funciones) {
        $funciones->insertarRegistroHorario();
    }
);

// Validar consecutividad horaria (útil para monitoreo, alertas y frontend reactivo)
$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/validar-consecutividad/(\d+)/(.+)',
    function ($relacion_id, $fecha_hora) use ($funciones) {
        $s = rawurldecode($fecha_hora);
        try {
            $dt = new DateTimeImmutable($s); // ISO ok (maneja Z/offset)
            $dt = $dt->setTimezone(new DateTimeZone('America/Mazatlan'));
            $fechaSql = $dt->format('Y-m-d H:i:s'); // para DATETIME
            Logger::info("Validando consecutividad para relación $relacion_id con fecha $fechaSql");
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Fecha inválida']);
            return;
        }
        $funciones->validarConsecutividadHora($relacion_id, $fechaSql);
    }
);

$router->add(
    'POST',
    '/api/zonagris/funciones/cocedores/validar-supervisor',
    function () use ($funciones) {
        $funciones->validarSupervisor();
    }
);

$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/proxima-revision',
    function () use ($funciones) {
        $funciones->proximaRevision();
    }
);

// Registrar paro
$router->add(
    'POST',
    '/api/zonagris/funciones/cocedores/paro',
    function () use ($funciones) {
        $funciones->registrarParo();
    }
);

// Finalizar paro
$router->add(
    'POST',
    '/api/zonagris/funciones/cocedores/finalizar-paro',
    function () use ($funciones) {
        $funciones->finalizarParo();
    }
);

//Consultar flujo
$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/obtener-flujo',
    function () use ($funcionesPlc) {
        $funcionesPlc->obtenerFlujo();
    }
);

//Consultar cocedores proceso
$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/obtener-cocedores-proceso-by-id/(\d+)',
    function ($id) use ($funciones) {
        $funciones->obtenerCocedoresProcesoById($id);
    }
);

//Consultar temperatura
$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/obtener-temperatura',
    function () use ($funcionesPlc) {
        $funcionesPlc->obtenerTemperaturaCocedor();
    }
);

$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/obtener-detalle-cocedor-proceso/(\d+)',
    function ($id) use ($funciones) {
        $funciones->obtenerDetalleCocedorProceso($id);
    }
);

$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/obtener-mezcla-en-proceso',
    function () use ($funciones) {
        $funciones->obtenerMezclaEnProceso();
    }
);

$router->add(
    'GET',
    '/api/zonagris/funciones/cocedores/obtener-mezcla-by-id/(\d+)',
    function ($id) use ($funciones) {
        $funciones->obtenerMezclaById($id);
    }
);

//Reporte cocedores
$router->add(
    'POST',
    '/api/zonagris/reportes/cocedores',
    function () use ($reportes) {
        $reportes->index();
    }
);
