<?php
use Modules\Auth\AuthController;

$auth = new AuthController();

$router->add('POST', '/api/login', function() use ($auth) {
    $auth->login();
});
