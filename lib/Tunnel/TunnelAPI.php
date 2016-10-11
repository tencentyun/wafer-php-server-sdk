<?php
namespace QCloud_WeApp_SDK\Tunnel;

use \Exception as Exception;

use \QCloud_WeApp_SDK\Conf as Conf;
use QCloud_WeApp_SDK\Helper\Request as Request;
use QCloud_WeApp_SDK\Helper\Logger as Logger;

class TunnelAPI {
    const APIEndpoint = Conf::TUNNEL_SERVER_URL;

    public static function requestConnect($skey, $receiveUrl) {
        $param = compact('skey', 'receiveUrl');
        return self::sendRequest('/get/wsurl', 'RequestConnect', $param);
    }

    private static function sendRequest($apiPath, $apiName, $apiParam) {
        $url = self::APIEndpoint . $apiPath;
        $timeout = 15 * 1000;
        $data = self::packReqData($apiName, $apiParam);
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
            throw new Exception("信道服务调用失败：{$body['code']} - {$body['msg']}");
        }

        // TODO: 校验签名
        return $body['data'];
    }

    private static function packReqData($api, $param, $signature = NULL) {
        return compact('api', 'param', 'signature');
    }
}
