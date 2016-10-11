<?php
namespace QCloud_WeApp_SDK\Helper;

class Logger {
    const SWITCH_ON = FALSE;

    public static function debug(/* $message1 [, $...] */) {
        if (self::SWITCH_ON !== TRUE) {
            return;
        }

        if (!function_exists('log_message')) {
            return;
        }

        try {
            $numargs = func_num_args();
            $arg_list = func_get_args();
            $messages = array();

            for ($i = 0; $i < $numargs; $i += 1) {
                $message = $arg_list[$i];

                if (is_array($message)) {
                    if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
                        $message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    } else {
                        $message = json_encode($message);
                    }
                }

                if (is_string($message) || is_numeric($message)) {
                    $messages[] = $message;
                }
            }

            log_message('debug', implode(' ', $messages) . "\n");
        } catch (Exception $e) {
            // do nothing
        }
    }
}
