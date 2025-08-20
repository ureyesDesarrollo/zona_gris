<?php
namespace App\Helpers;

class Logger
{
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_INFO = 'INFO';
    const LEVEL_DEBUG = 'DEBUG';

    private static $logFile = __DIR__ . '/../logs/app.log';

    public static function setLogFile($path)
    {
        self::$logFile = $path;
    }

    public static function log($level, $message, array $context = [])
    {
        $date = date('Y-m-d H:i:s');
        $interpolated = self::interpolate($message, $context);
        $entry = "[$date] [$level] $interpolated" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }

    public static function error($message, array $context = [])
    {
        self::log(self::LEVEL_ERROR, $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        self::log(self::LEVEL_WARNING, $message, $context);
    }

    public static function info($message, array $context = [])
    {
        self::log(self::LEVEL_INFO, $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }

    // Interpolación estilo PSR-3
    private static function interpolate($message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
