<?php

use Modules\ZonaGris\Catalogos\Clarificador\CatalogoClarificadorController;

$catalogo = new CatalogoClarificadorController();

$router->add(
    'GET',
    '/api/zonagris/catalogos/clarificador',
    function () use ($catalogo) {
        $catalogo->index();
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
