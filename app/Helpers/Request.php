<?php
namespace App\Helpers;

class Request
{
    public static function input()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true);
        }
        return $_POST;
    }
}
