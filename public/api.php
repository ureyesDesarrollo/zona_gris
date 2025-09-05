<?php
date_default_timezone_set('America/Mazatlan');
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$router = new \App\Router();
require_once __DIR__ . '/../routes/api.php';
$router->dispatch();
