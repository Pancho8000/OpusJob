<?php
class Logger {
    private static function ensureDir($dir){
        if(!is_dir($dir)){
            @mkdir($dir, 0775, true);
        }
    }

    public static function log($level, $message, $context = []){
        $base = defined('APPROOT') ? dirname(APPROOT) : dirname(__DIR__);
        $dir = $base . DIRECTORY_SEPARATOR . 'logs';
        self::ensureDir($dir);

        $record = [
            'ts' => gmdate('c'),
            'level' => $level,
            'msg' => (string)$message,
            'ctx' => is_array($context) ? $context : ['context' => (string)$context]
        ];

        $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'app.log', $line, FILE_APPEND | LOCK_EX);
    }

    public static function info($message, $context = []){
        self::log('info', $message, $context);
    }

    public static function warning($message, $context = []){
        self::log('warning', $message, $context);
    }

    public static function error($message, $context = []){
        self::log('error', $message, $context);
    }
}

