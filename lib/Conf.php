<?php
namespace QCloud_WeApp_SDK;

class Conf {
    // 当前使用 SDK 服务器的主机，该主机需要外网可访问
    public static $ServerHost;

    // 鉴权服务器服务地址
    public static $AuthServerHost;

    // 信道服务器服务地址
    public static $TunnelServerHost;

    // 信道服务签名密钥，该密钥需要保密
    public static $TunnelSignatureKey;

    public static function setup($config) {
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