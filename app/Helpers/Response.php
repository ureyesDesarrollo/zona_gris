<?php
namespace App\Helpers;

class Response
{
    /**
     * Envía una respuesta JSON con el código de estado adecuado.
     */
    public static function json($data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
