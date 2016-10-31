<?php
namespace QCloud_WeApp_SDK;

use \Exception as Exception;

class Conf {
    // 是否输出 SDK 日志
    private static $EnableOutputLog = FALSE;

    // SDK 日志输出目录
    private static $LogPath = '';

    // SDK 日志输出级别
    private static $LogThreshold = 0;

    // SDK 日志输出级别（数组）
    private static $LogThresholdArray = array();

    // 当前使用 SDK 服务器的主机，该主机需要外网可访问
    private static $ServerHost = '';

    // 鉴权服务器服务地址
    private static $AuthServerUrl = '';

    // 信道服务器服务地址
    private static $TunnelServerUrl = '';

    // 和信道服务器通信的签名密钥，该密钥需要保密
    private static $TunnelSignatureKey = '';

    // 信道服务通信是否需要校验签名
    private static $TunnelCheckSignature = TRUE;

    // 网络请求超时时长（单位：毫秒）
    private static $NetworkTimeout = 30000;

    public static function __callStatic($name, $arguemnts) {
        $class = get_class();

        if (strpos($name, 'get') === 0) {
            $key = preg_replace('/^get/', '', $name);

            if (property_exists($class, $key)) {
                $value = self::$$key;

                if (strpos($key, 'Log') === 0) {
                    return $value;
                }

                if (is_string($value) && !$value) {
                    throw new Exception("`{$key}`不能为空，请确保 SDK 配置已正确初始化", 1);
                }

                return $value;
            }
        }

        if (strpos($name, 'set') === 0) {
            $key = preg_replace('/^set/', '', $name);
            $value = isset($arguemnts[0]) ? $arguemnts[0] : NULL;

            if (property_exists($class, $key)) {
                if (gettype($value) === gettype(self::$$key)) {
                    self::$$key = $value;
                } else {
                    throw new Exception("Call to method {$class}::{$name}() with invalid arguements", 1);
                }
                return;
            }
        }

        throw new Exception("Call to undefined method {$class}::{$name}()", 1);
    }

    public static function setup($config = NULL) {
        if (!is_array($config)) {
            return;
        }

        $class = get_class();

        foreach ($config as $key => $value) {
            if (property_exists($class, $key) && gettype($value) === gettype(self::$$key)) {
                self::$$key = $value;
            }
        }
    }
}
