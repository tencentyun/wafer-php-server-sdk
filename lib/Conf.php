<?php
namespace QCloud_WeApp_SDK;

class Conf {
    // SDK 密钥，该密钥需要保密
    public static $SecretKey;

    // 当前使用 SDK 服务器的主机，该主机需要外网可访问
    public static $ServerHost;

    // 鉴权服务器服务地址
    public static $AuthServerHost;

    // 信道服务器服务地址
    public static $TunnelServerHost;

    public static function setup() {
        self::$SecretKey = 'eeb93ecd-ecb3-4c58-a347-8ce3617b8e8c';
        self::$ServerHost = 'www.qcloua.la';
        self::$AuthServerHost = 'http://mina.auth.com:7575';
        self::$TunnelServerHost = 'https://ws.qcloud.com';
    }
}

// TODO: 从配置文件读取
Conf::setup();
