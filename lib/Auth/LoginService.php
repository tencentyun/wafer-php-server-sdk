<?php
namespace QCloud_WeApp_SDK\Auth;

use \Exception as Exception;

use \QCloud_WeApp_SDK\Conf as Conf;
use \QCloud_WeApp_SDK\Helper\Util as Util;
use \QCloud_WeApp_SDK\Helper\Http as Http;

class LoginService {
    public static function login() {
        try {
            $data = self::getLoginData();
            list($status, $body) = array_values(self::sendRequest($data));
            // var_export(compact('status', 'body'));

            if ($status !== 200) {
                throw new Exception('请求鉴权 API 失败，网络异常或鉴权服务器错误');
            }

            if (!is_array($body)) {
                throw new Exception('鉴权服务器响应格式错误，无法解析 JSON 字符串');
            }

            if ($body['returnCode'] === 0) {
                $returnData = $body['returnData'];

                $result = array();
                $result[Constants::WX_SESSION_MAGIC_ID] = 1;
                $result['session'] = array(
                    'id' => $returnData['id'],
                    'skey' => $returnData['skey'],
                );

                Util::writeJsonResult($result);
                return array('userInfo' => $returnData['user_info']);

            } else {
                throw new Exception("#{$body['returnCode']} - {$body['returnMessage']}");
            }

        } catch (Exception $e) {
            $error = new LoginServiceException(Constants::ERR_LOGIN_FAILED, $e->getMessage());
            self::writeError($error);
            throw $error;
        }
    }

    public static function check() {
        try {
            $data = self::getCheckData();
            list($status, $body) = array_values(self::sendRequest($data));
            // var_export(compact('status', 'body'));

            if ($status !== 200) {
                throw new Exception('请求鉴权 API 失败，网络异常或鉴权服务器错误');
            }

            if (!is_array($body)) {
                throw new Exception('鉴权服务器响应格式错误，无法解析 JSON 字符串');
            }

            switch ($body['returnCode']) {
            case 0:
                $returnData = $body['returnData'];
                return array('userInfo' => $returnData['user_info']);
                break;

            case 60011:
                throw new LoginServiceException(Constants::ERR_SESSION_EXPIRED, $body['returnMessage']);
                break;

            default:
                throw new Exception("#{$body['returnCode']} - {$body['returnMessage']}");
                break;
            }

        } catch (Exception $e) {
            if ($e instanceof LoginServiceException) {
                $error = $e;
            } else {
                $error = new LoginServiceException(Constants::ERR_CHECK_LOGIN_FAILED, $e->getMessage());
            }

            self::writeError($error);
            throw $error;
        }
    }

    private static function writeError($err) {
        $result = array();
        $result[Constants::WX_SESSION_MAGIC_ID] = 1;
        $result['error'] = $err->getType();
        $result['message'] = $err->getMessage();

        Util::writeJsonResult($result);
    }

    private static function sendRequest($data) {
        return Http::jsonPost(array(
            'url' => Conf::AUTH_URL,
            'data' => $data,
            'timeout' => 15 * 1000,
        ));
    }

    private static function getLoginData() {
        $data = array(
            'code' => Util::getHttpHeader(Constants::WX_HEADER_CODE),
            'encrypt_data' => Util::getHttpHeader(Constants::WX_HEADER_ENCRYPT_DATA),
        );

        return self::packReqData(Constants::INTERFACE_LOGIN, $data);
    }

    private static function getCheckData() {
        $data = array(
            'id' => Util::getHttpHeader(Constants::WX_HEADER_ID),
            'skey' => Util::getHttpHeader(Constants::WX_HEADER_SKEY),
        );

        return self::packReqData(Constants::INTERFACE_CHECK, $data);
    }

    private static function packReqData($interfaceName, $data) {
        return array(
            'version' => 1,
            'componentName' => 'MA',
            'interface' => array(
                'interfaceName' => $interfaceName,
                'para' => $data,
            ),
        );
    }
}