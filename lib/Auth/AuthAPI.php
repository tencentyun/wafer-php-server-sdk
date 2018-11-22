<?php
namespace QCloud_WeApp_SDK\Auth;

use \Exception as Exception;

use \QCloud_WeApp_SDK\Conf as Conf;
use \QCloud_WeApp_SDK\Model\User as User;
use \QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Helper\Logger as Logger;
use \QCloud_WeApp_SDK\Helper\Request as Request;

class AuthAPI
{
    private $appInfo;

    public function __construct($appInfo)
    {
        $this->appInfo = $appInfo;
    }

    /**
     * 用户登录接口
     * @param {string} $code        wx.login 颁发的 code
     * @param {string} $encryptData 加密过的用户信息
     * @param {string} $iv          解密用户信息的向量
     * @return {array} { loginState, userinfo }
     */
    public function login($code, $encryptData, $iv) {
        // 1. 获取 session key
        $sessionKey = $this->getSessionKey($code);

        // 2. 生成 3rd key (skey)
        $skey = sha1($sessionKey . mt_rand());
        
        /**
         * 3. 解密数据
         * 由于官方的解密方法不兼容 PHP 7.1+ 的版本
         * 这里弃用微信官方的解密方法
         * 采用推荐的 openssl_decrypt 方法（支持 >= 5.3.0 的 PHP）
         * @see http://php.net/manual/zh/function.openssl-decrypt.php
         */
        $decryptData = \openssl_decrypt(
            base64_decode($encryptData),
            'AES-128-CBC',
            base64_decode($sessionKey),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );
        $userinfo = json_decode($decryptData);

        // 4. 储存到数据库中
        $userModel = new User($this->appInfo);
        $userModel->storeUserInfo($userinfo, $skey, $sessionKey);

        return [
            'loginState' => Constants::S_AUTH,
            'userinfo' => compact('userinfo', 'skey')
        ];
    }

    public function decrypt_by_session_key($session_key, $encryptData, $iv){
        /**
         * 3. 解密数据
         * 由于官方的解密方法不兼容 PHP 7.1+ 的版本
         * 这里弃用微信官方的解密方法
         * 采用推荐的 openssl_decrypt 方法（支持 >= 5.3.0 的 PHP）
         * @see http://php.net/manual/zh/function.openssl-decrypt.php
         */
        $userModel = new User($this->appInfo);
        $userinfo = $userModel->findUserBySKey($session_key);
        if ($userinfo === NULL) {
            return [
                'loginState' => Constants::E_AUTH,
                'userinfo' => []
            ];
        }

        $wxLoginExpires = $this->appInfo->session_duration;
        $timeDifference = time() - strtotime($userinfo->last_visit_time);

        if ($timeDifference > $wxLoginExpires) {
            return [
                'loginState' => Constants::E_AUTH,
                'userinfo' => []
            ];
        }
        $sessionKey = $userinfo->session_key;

        $decryptData = \openssl_decrypt(
            base64_decode($encryptData),
            'AES-128-CBC',
            base64_decode($sessionKey),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );
        $dinfo = json_decode($decryptData);
        return [
            'loginState' => Constants::S_AUTH,
            'userinfo' => $dinfo
        ];
    }

    public function checkLogin($skey) {
        $userModel = new User($this->appInfo);
        $userinfo = $userModel->findUserBySKey($skey);
        if ($userinfo === NULL) {
            return [
                'loginState' => Constants::E_AUTH,
                'userinfo' => []
            ];
        }
        $wxLoginExpires = $this->appInfo->session_duration;
        $timeDifference = time() - strtotime($userinfo->last_visit_time);

        if ($timeDifference > $wxLoginExpires) {
            return [
                'loginState' => Constants::E_AUTH,
                'userinfo' => []
            ];
        } else {
            return [
                'loginState' => Constants::S_AUTH,
                'userinfo' => json_decode($userinfo->user_info, true)
            ];
        }
    }

    /**
     * 通过 code 换取 session key
     * @param {string} $code
     */
    public function getSessionKey ($code) {
        list($session_key, $openid) = array_values(
            self::getSessionKeyDirectly($this->appInfo->appid, $this->appInfo->secret, $code)
        );
        return $session_key;
    }

    /**
     * 直接请求微信获取 session key
     * @param {string} $secretId  腾讯云的 secretId
     * @param {string} $secretKey 腾讯云的 secretKey
     * @param {string} $code
     * @return {array} { $session_key, $openid }
     */
    private static function getSessionKeyDirectly ($appId, $appSecret, $code) {
        $requestParams = [
            'appid' => $appId,
            'secret' => $appSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];

        list($status, $body) = array_values(Request::get([
            'url' => 'https://api.weixin.qq.com/sns/jscode2session?' . http_build_query($requestParams),
            'timeout' => Conf::getNetworkTimeout()
        ]));

        if ($status !== 200 || !$body || isset($body['errcode'])) {
            throw new Exception(Constants::E_PROXY_LOGIN_FAILED . ': ' . json_encode($body));
        }

        return $body;
    }
}
