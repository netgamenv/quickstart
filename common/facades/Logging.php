<?php

namespace common\facades;

class Logging {
    private static function log($message, string $level) {
        $data = date("Y-m-d H:i:s") . " [$level] ";
        if ( is_string($message)) {
            $data .= $message;
        } else if( $message instanceof \Throwable ) {
            /** @var \Throwable $e */
            $e = $message;
            $data .= $e->getMessage() . "\n" . $e->getTraceAsString();
        } else {
            $data .= print_r($message,true);
        }
        $data .= "\n";
        file_put_contents(__DIR__ . "/../../runtime/application.log", $data, FILE_APPEND);
    }

    public static function info($message){
        self::log($message, 'info');
    }

    public static function error($message){
        self::log($message, 'error');
    }

    public static function warning($message){
        self::log($message, 'error');
    }

}