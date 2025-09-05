<?php

date_default_timezone_set('America/Mazatlan');
require_once __DIR__ . '/../vendor/autoload.php';

use Modules\Alertas\Jobs\AlertasValidacion;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

AlertasValidacion::verificarValidacionesPendientes();

