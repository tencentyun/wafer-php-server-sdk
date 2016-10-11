<?php
namespace QCloud_WeApp_SDK;

class Conf {
    // SDK 密钥，该密钥需要保密
    const SECRET_KEY = 'eeb93ecd-ecb3-4c58-a347-8ce3617b8e8c';

    // 当前使用 SDK 服务器的主机，该主机需要外网可访问
    const SERVER_HOST = 'www.qcloua.la';

    // 鉴权服务器服务地址
    const AUTH_SERVER_URL = 'http://mina.auth.com:7575';

    // 信道服务器服务地址
    const TUNNEL_SERVER_URL = 'https://ws.qcloud.com';
}
