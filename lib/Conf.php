<?php
namespace QCloud_WeApp_SDK;

class Conf {
    // 当前使用 SDK 服务器的主机，该主机需要外网可访问
    private static $ServerHost = '';

    // 鉴权服务器服务地址
    private static $AuthServerUrl = '';

    // 信道服务器服务地址
    private static $TunnelServerUrl = '';

    // 信道服务签名密钥，该密钥需要保密
    private static $TunnelSignatureKey = '';

    public static function __callStatic($name, $arguemnts) {
        $class = get_class();
        $key = preg_replace('/^get/', '', $name);

        if (property_exists($class, $key)) {
            return self::$$key;
        }

        throw new Exception("Call to undefined method {$class}::{$name}()", 1);
    }

    public static function setup($config = NULL) {
        if (!is_array($config)) {
            return;
        }

        $class = get_class();

        foreach ($config as $key => $value) {
            if (property_exists($class, $key) && is_string($value)) {
                self::$$key = $value;
            }
        }
    }
}
