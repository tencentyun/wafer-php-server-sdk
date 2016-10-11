<?php
namespace QCloud_WeApp_SDK\Tunnel;

use \QCloud_WeApp_SDK\Conf as Conf;
use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use \QCloud_WeApp_SDK\Helper\Util as Util;
use \QCloud_WeApp_SDK\Helper\Logger as Logger;

class TunnelService {
    public static function handle(ITunnelHandler $handler, $options) {
        if (!is_array($options)) {
            $options = array();
        }

        $options = array_merge(array('checkLogin' => FALSE), $options);

        switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            self::handleGet($handler, $options);
            break;

        case 'POST':
            self::handlePost($handler, $options);
            break;
        }
    }

    public static function broadcast($tunnelIds, $type, $message) {
        Logger::debug('TunnelService::broadcast', compact('tunnelIds', 'type', 'message'));
    }

    public static function emit($tunnelId, $type, $message) {
        Logger::debug('TunnelService::emit', compact('tunnelId', 'type', 'message'));
    }

    private static function handleGet(ITunnelHandler $handler, $options) {
        $userInfo = NULL;

        if ($options['checkLogin']) {
            $result = LoginService::check();

            if ($result['code'] !== 0) {
                return;
            }

            $userInfo = $result['data']['userInfo'];
        }

        $tunnelInfo = TunnelAPI::requestConnect(Conf::$SecretKey, self::buildReceiveUrl());
        $handler->onRequest($tunnelInfo['tunnelId'], $userInfo);

        Util::writeJsonResult(array('url' => $tunnelInfo['connectUrl']));
    }

    private static function handlePost(ITunnelHandler $handler, $options) {
        $contents = file_get_contents('php://input');
        Logger::debug('TunnelService::handle [post data] =>', $contents);

        $body = json_decode($contents, TRUE);
        if (!is_array($body)) {
            return Util::writeJsonResult(array(
                'code' => 9001,
                'message' => 'Bad request - invalid json',
            ));
        }

        if (!isset($body['data']) || !isset($body['signature'])) {
            return Util::writeJsonResult(array(
                'code' => 9002,
                'message' => 'Bad request - invalid data',
            ));
        }

        // 校验签名
        $input = json_encode($body['data']);
        if (!Signature::check($input, $body['signature'])) {
            return Util::writeJsonResult(array(
                'code' => 9003,
                'message' => 'Bad request - check signature failed',
            ));
        }

        foreach ($body['data'] as $packet) {
            $tunnelId = $packet['tunnelId'];

            try {
                switch ($packet['type']) {
                case 'connect':
                    $handler->onConnect($tunnelId);
                    break;

                // TODO: 完善逻辑
                case 'message':
                    $content = json_decode($packet['content'], TRUE);
                    $type = $content['type'];
                    $message = $content['message'];
                    $handler->onMessage($tunnelId, $type, $message);
                    break;

                case 'close':
                    $handler->onClose($tunnelId);
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        Util::writeJsonResult(array('code' => 0, 'message' => 'OK'));
    }

    /**
     * 构建提交给 WebSocket 信道服务器推送消息的地址
     *
     * 构建过程如下：
     *   1. 从信道服务器地址得到其通信协议（http/https），如 https
     *   2. 获取当前服务器主机名，如 109447.qcloud.la
     *   3. 获得当前 HTTP 请求的路径，如 /tunnel
     *   4. 拼接推送地址为 https://109447.qcloud.la/tunnel
     */
    private static function buildReceiveUrl() {
        $scheme = parse_url(Conf::$TunnelServerHost, PHP_URL_SCHEME);
        $hostname = Conf::$ServerHost;
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return "{$scheme}://{$hostname}{$path}";
    }
}
