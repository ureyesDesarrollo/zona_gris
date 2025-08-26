<?php

use Modules\Alertas\AlertasController;
$alertasController = new AlertasController();

$router->add('POST', '/api/alertas/enviar', function () use ($alertasController) {
    $alertasController->enviarAlerta();
});