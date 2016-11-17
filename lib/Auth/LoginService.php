<?php
namespace QCloud_WeApp_SDK\Auth;

use \Exception as Exception;

use \QCloud_WeApp_SDK\Helper\Util as Util;

class LoginService {
    public static function login() {
        try {
            $code = self::getHttpHeader(Constants::WX_HEADER_CODE);
            $encryptedData = self::getHttpHeader(Constants::WX_HEADER_ENCRYPTED_DATA);
            $iv = self::getHttpHeader(Constants::WX_HEADER_IV);

            $loginResult = AuthAPI::login($code, $encryptedData, $iv);

            $result = array();
            $result[Constants::WX_SESSION_MAGIC_ID] = 1;
            $result['session'] = array(
                'id' => $loginResult['id'],
                'skey' => $loginResult['skey'],
            );

            Util::writeJsonResult($result);

            return array(
                'code' => 0,
                'message' => 'ok',
                'data' => array(
                    'userInfo' => $loginResult['user_info'],
                ),
            );

        } catch (Exception $e) {
            $error = new LoginServiceException(Constants::ERR_LOGIN_FAILED, $e->getMessage());
            self::writeError($error);

            return array(
                'code' => -1,
                'message' => $error->getMessage(),
                'data' => array(),
            );
        }
    }

    public static function check() {
        try {
            $id = self::getHttpHeader(Constants::WX_HEADER_ID);
            $skey = self::getHttpHeader(Constants::WX_HEADER_SKEY);

            $checkResult = AuthAPI::checkLogin($id, $skey);

            return array(
                'code' => 0,
                'message' => 'ok',
                'data' => array(
                    'userInfo' => $checkResult['user_info'],
                ),
            );
        } catch (Exception $e) {
            if ($e instanceof AuthAPIException) {
                switch ($e->getCode()) {
                case Constants::RETURN_CODE_SKEY_EXPIRED:
                case Constants::RETURN_CODE_WX_SESSION_FAILED:
                    $error = new LoginServiceException(Constants::ERR_INVALID_SESSION, $e->getMessage());
                    break;

                default:
                    $error = new LoginServiceException(Constants::ERR_CHECK_LOGIN_FAILED, $e->getMessage());
                    break;
                }
            } else {
                $error = new LoginServiceException(Constants::ERR_CHECK_LOGIN_FAILED, $e->getMessage());
            }

            self::writeError($error);

            return array(
                'code' => -1,
                'message' => $error->getMessage(),
                'data' => array(),
            );
        }
    }

    private static function writeError(LoginServiceException $err) {
        $result = array();
        $result[Constants::WX_SESSION_MAGIC_ID] = 1;
        $result['error'] = $err->getType();
        $result['message'] = $err->getMessage();

        Util::writeJsonResult($result);
    }

    private static function getHttpHeader($headerKey) {
        $headerValue = Util::getHttpHeader($headerKey);

        if (!$headerValue) {
            throw new Exception("请求头未包含 {$headerKey}，请配合客户端 SDK 登录后再进行请求");
        }

        return $headerValue;
    }
}
