<?php
//public/api.php

// Zona horaria
date_default_timezone_set('America/Mazatlan');

// Carga el autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carga las variables de entorno (.env)
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad(); // Evita error si .env no existe aún

// Inicializa el router (con espacio de nombres)
use App\Router;

$router = new Router();

// Carga las rutas
require_once dirname(__DIR__) . '/routes/api.php';

// Ejecuta el despacho
$router->dispatch();