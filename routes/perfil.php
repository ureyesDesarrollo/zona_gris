<?php
use Modules\Auth\PerfilController;

$perfilController = new PerfilController();

$router->add('GET', '/api/perfil/(\d+)', function($id) use ($perfilController) {
    $perfilController->showPerfil($id);
});


$router->add('POST','/api/validUserCode', function() use($perfilController) {
    $perfilController->validUserCode();
});