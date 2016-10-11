<?php
namespace QCloud_WeApp_SDK\Helper;

class Logger {
    const SWITCH_ON = TRUE;

    public static function debug($message) {
        if (self::SWITCH_ON !== TRUE) {
            return;
        }

        if (function_exists('log_message')) {
            try {
                if (is_array($message)) {
                    $message = json_encode($message, JSON_FORCE_OBJECT);
                }

                if (is_string($message)) {
                    log_message('debug', $message);
                }
            } catch (Exception $e) {
                // do nothing
            }
        }
    }
}
