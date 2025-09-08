<?php

date_default_timezone_set('America/Mazatlan');
require_once __DIR__ . '/../vendor/autoload.php';

use Modules\Alertas\Jobs\AlertasValidacion;
// Carga las variables de entorno (.env)
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();
echo '🚀 Iniciando verificación de validaciones pendientes';
AlertasValidacion::verificarValidacionesPendientes();

