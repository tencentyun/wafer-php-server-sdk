<?php
namespace QCloud_WeApp_SDK\Tunnel;

use \Exception as Exception;

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

        default:
            Util::writeJsonResult(array('code' => 501, 'message' => 'Not Implemented'), 501);
            break;
        }
    }

    public static function broadcast($tunnelIds, $messageType, $messageContent) {
        Logger::debug('TunnelService::broadcast =>', compact('tunnelIds', 'messageType', 'messageContent'));
        TunnelAPI::emitMessage($tunnelIds, $messageType, $messageContent);
    }

    public static function emit($tunnelId, $messageType, $messageContent) {
        Logger::debug('TunnelService::emit =>', compact('tunnelId', 'messageType', 'messageContent'));
        TunnelAPI::emitMessage(array($tunnelId), $messageType, $messageContent);
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

        try {
            $body = TunnelAPI::requestConnect(Conf::$SecretKey, self::buildReceiveUrl());

            $data = $body['data'];
            $signature = $body['signature'];

            // 校验签名
            if (!Signature::check(json_encode($data), $signature)) {
                throw new Exception('签名校验失败');
            }

        } catch (Exception $e) {
            Util::writeJsonResult(array('error' => $e->getMessage()));
            return;
        }

        Util::writeJsonResult(array('url' => $data['connectUrl']));
        $handler->onRequest($data['tunnelId'], $userInfo);
    }

    private static function handlePost(ITunnelHandler $handler, $options) {
        // $data => array(
        //  array('tunnelId' => '', 'type' => '', 'content'? => ''),
        //  array('tunnelId' => '', 'type' => '', 'content'? => ''),
        //  ...
        // )
        if (!($data = self::parsePostPayloadData())) {
            return;
        }

        foreach ($data as $packet) {
            $tunnelId = $packet['tunnelId'];

            try {
                switch ($packet['type']) {
                case 'connect':
                    $handler->onConnect($tunnelId);
                    break;

                case 'message':
                    list($type, $content) = self::decodePacketContent($packet);
                    $handler->onMessage($tunnelId, $type, $content);
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

    /**
     * 解析 Post Payload 数据
     */
    private static function parsePostPayloadData() {
        $contents = file_get_contents('php://input');
        Logger::debug('TunnelService::handle [post payload] =>', $contents);

        $body = json_decode($contents, TRUE);
        if (!is_array($body)) {
            Util::writeJsonResult(array(
                'code' => 9001,
                'message' => 'Bad request - request data is not json',
            ), 400);
            return FALSE;
        }

        if (!isset($body['data']) || !isset($body['signature'])) {
            Util::writeJsonResult(array(
                'code' => 9002,
                'message' => 'Bad request - invalid request data',
            ), 400);
            return FALSE;
        }

        // 校验签名
        $input = json_encode($body['data']);
        if (!Signature::check($input, $body['signature'])) {
            Util::writeJsonResult(array(
                'code' => 9003,
                'message' => 'Bad request - check signature failed',
            ), 400);
            return FALSE;
        }

        return $body['data'];
    }

    private static function decodePacketContent($packet)  {
        if (isset($packet['content'])) {
            $content = json_decode($packet['content'], TRUE);

            if (!is_array($content)) {
                $content = array();
            }
        } else {
            $content = array();
        }

        if (!isset($content['type'])) {
            $content['type'] = 'UnknownRaw';
        }

        if (!isset($content['content'])) {
            $content['content'] = $packet['content'];
        }

        return array($content['type'], $content['content']);;
    }
}
