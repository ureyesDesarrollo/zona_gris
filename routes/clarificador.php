<?php

use Modules\ZonaGris\Catalogos\Clarificador\CatalogoClarificadorController;
use Modules\ZonaGris\Funciones\Clarificador\ClarificadorController;

$catalogo = new CatalogoClarificadorController();
$funciones = new ClarificadorController();

$router->add(
    'GET',
    '/api/zonagris/catalogos/clarificador',
    function () use ($catalogo) {
        $catalogo->index();
    }
);

$router->add(
    'GET',
    '/api/zonagris/funciones/clarificador/obtenerProcesosActivos',
    function () use ($funciones) {
        $funciones->obtenerProcesosActivos();
    }
);

$router->add(
    'GET',
    '/api/zonagris/catalogos/clarificador/(\d+)',
    function ($id) use ($catalogo) {
        $catalogo->show($id);
    }
);

$router->add(
    'POST',
    '/api/zonagris/catalogos/clarificador/estatus',
    function () use ($catalogo) {
        $catalogo->changeStatus();
    }
);

$router->add(
    'GET',
    '/api/zonagris/funciones/clarificador/obtenerEstadoClarificador',
    function () use ($funciones) {
        $funciones->obtenerEstadoClarificador();
    }
);

$router->add(
    'POST',
    '/api/zonagris/funciones/clarificador/iniciarProcesoClarificador',
    function () use ($funciones) {
        $funciones->iniciarProcesoClarificador();
    }
);

$router->add(
    'POST',
    '/api/zonagris/funciones/clarificador/finalizarProcesoClarificador',
    function () use ($funciones) {
        $funciones->finalizarProcesoClarificador();
    }
);

$router->add(
    'POST',
    '/api/zonagris/funciones/clarificador/insertarRegistroHorario',
    function () use ($funciones) {
        $funciones->insertarRegistroHorario();
    }
);

$router->add(
    'POST',
    '/api/zonagris/funciones/clarificador/validacionHora',
    function () use ($funciones) {
        $funciones->validacionHora();
    }
);

$router->add(
    'POST',
    '/api/zonagris/funciones/clarificador/insertarQuimicosClarificador',
    function () use ($funciones) {
        $funciones->insertarQuimicosClarificador();
    }
);

$router->add(
    'POST',
    '/api/zonagris/funciones/clarificador/obtenerUltimoLote',
    function () use ($funciones) {
        $funciones->obtenerUltimoLote();
    }
);

$router->add(
    'GET',
    '/api/zonagris/funciones/clarificador/obtenerClarificadorProcesoById/(\d+)',
    function ($id) use ($funciones) {
        $funciones->obtenerClarificadorProcesoById($id);
    }
);

$router->add(
    'GET',
    '/api/zonagris/funciones/clarificador/obtenerDetalleClarificadorProceso/(\d+)',
    function ($id) use ($funciones) {
        $funciones->obtenerDetalleClarificadorProceso($id);
    }
);


