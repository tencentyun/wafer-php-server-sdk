<?php
namespace QCloud_WeApp_SDK\Tunnel;

use \Exception as Exception;

use \QCloud_WeApp_SDK\Conf as Conf;
use QCloud_WeApp_SDK\Helper\Request as Request;
use QCloud_WeApp_SDK\Helper\Logger as Logger;

class TunnelAPI {
    public static function requestConnect($skey, $receiveUrl) {
        $param = compact('skey', 'receiveUrl');
        return self::sendRequest('/get/wsurl', $param);
    }

    public static function emitMessage($tunnelIds, $messageType, $messageContent) {
        $packetType = 'message';
        $packetContent = implode(':', array($messageType, json_encode($messageContent)));
        return self::emitPacket($tunnelIds, $packetType, $packetContent);
    }

    public static function emitPacket($tunnelIds, $packetType, $packetContent = NULL) {
        $param = array('tunnelIds' => $tunnelIds, 'type' => $packetType);
        if ($packetContent) {
            $param['content'] = $packetContent;
        }

        return self::sendRequest('/ws/push', $param);
    }

    private static function sendRequest($apiPath, $apiParam) {
        $url = Conf::$TunnelServerHost . $apiPath;
        $timeout = 15 * 1000;
        $data = self::packReqData($apiParam);
        Logger::debug('TunnelAPI [request data] =>', $data);

        list($status, $body) = array_values(Request::jsonPost(compact('url', 'timeout', 'data')));
        Logger::debug('TunnelAPI [response result]', compact('status', 'body'));

        if ($status !== 200) {
            throw new Exception('请求信道 API 失败，网络异常或信道服务器错误');
        }

        if (!is_array($body)) {
            throw new Exception('信道服务器响应格式错误，无法解析 JSON 字符串');
        }

        if ($body['code'] !== 0) {
            throw new TunnelAPIException("信道服务调用失败：{$body['code']} - {$body['msg']}", $body['code']);
        }

        return $body;
    }

    private static function packReqData($data) {
        $signature = Signature::compute(json_encode($data));
        return compact('data', 'signature');
    }
}
