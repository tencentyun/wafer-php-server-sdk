<?php
namespace QCloud_WeApp_SDK\Helper;

class Util {
    public static function getHttpHeader($headerKey) {
        $headerKey = strtoupper($headerKey);
        $headerKey = str_replace('-', '_', $headerKey);
        $headerKey = 'HTTP_' . $headerKey;
        return isset($_SERVER[$headerKey]) ? $_SERVER[$headerKey] : '';
    }

    public static function writeJsonResult($obj, $statusCode = 200) {
        header('Content-type: application/json; charset=utf-8');

        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            http_response_code($statusCode);
            echo json_encode($obj, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode($obj, JSON_FORCE_OBJECT);
        }
    }
}
