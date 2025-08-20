<?php
namespace App;

use App\Helpers\Response;

class BaseController
{
    protected function json($data, $status = 200)
    {
        Response::json($data, $status);
    }

    protected function handleException(\Throwable $e)
    {
        // Aquí puedes loguear el error con Logger
        Response::json(['error' => 'Error interno del servidor'], 500);
    }
}
