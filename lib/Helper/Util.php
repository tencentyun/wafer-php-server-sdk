<?php
namespace QCloud_WeApp_SDK\Helper;

class Util {
    public static function getHttpHeader($headerKey) {
        $headerKey = strtoupper($headerKey);
        $headerKey = str_replace('-', '_', $headerKey);
        $headerKey = 'HTTP_' . $headerKey;
        return isset($_SERVER[$headerKey]) ? $_SERVER[$headerKey] : '';
    }

    public static function writeJsonResult($obj) {
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($obj, JSON_FORCE_OBJECT);
    }
}
