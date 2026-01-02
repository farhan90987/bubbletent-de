<?php 
namespace MWEW\Inc\Logger;


class Logger {
    private static $logFile = MWEW_DIR_PATH . 'mwew.log';

    private static function log($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }

    public static function debug($message) {
        self::log('DEBUG', $message);
    }

    public static function info($message) {
        self::log('INFO', $message);
    }

    public static function error($message) {
        self::log('ERROR', $message);
    }
}