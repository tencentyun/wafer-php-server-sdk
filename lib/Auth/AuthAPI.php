<?php
namespace QCloud_WeApp_SDK\Auth;

use \Exception as Exception;

use \QCloud_WeApp_SDK\Conf as Conf;
use \QCloud_WeApp_SDK\Helper\Logger as Logger;
use \QCloud_WeApp_SDK\Helper\Request as Request;

class AuthAPI {
    public static function login($code, $encrypt_data, $iv) {
        $param = compact('code', 'encrypt_data', 'iv');
        return self::sendRequest(Constants::INTERFACE_LOGIN, $param);
    }

    public static function checkLogin($id, $skey) {
        $param = compact('id', 'skey');
        return self::sendRequest(Constants::INTERFACE_CHECK, $param);
    }

    private static function sendRequest($apiName, $apiParam) {
        $url = Conf::getAuthServerUrl();
        $timeout = Conf::getNetworkTimeout();
        $data = self::packReqData($apiName, $apiParam);

        $begin = round(microtime(TRUE) * 1000);
        list($status, $body) = array_values(Request::jsonPost(compact('url', 'timeout', 'data')));
        $end = round(microtime(TRUE) * 1000);

        // 记录请求日志
        Logger::debug("POST {$url} => [{$status}]", array(
            '[请求]' => $data,
            '[响应]' => $body,
            '[耗时]' => sprintf('%sms', $end - $begin),
        ));

        if ($status !== 200) {
            throw new Exception('请求鉴权 API 失败，网络异常或鉴权服务器错误');
        }

        if (!is_array($body)) {
            throw new Exception('鉴权服务器响应格式错误，无法解析 JSON 字符串');
        }

        if ($body['returnCode'] !== Constants::RETURN_CODE_SUCCESS) {
            throw new AuthAPIException("鉴权服务调用失败：{$body['returnCode']} - {$body['returnMessage']}", $body['returnCode']);
        }

        return $body['returnData'];
    }

    private static function packReqData($api, $param) {
        return array(
            'version' => 1,
            'componentName' => 'MA',
            'interface' => array(
                'interfaceName' => $api,
                'para' => $param,
            ),
        );
    }
}
