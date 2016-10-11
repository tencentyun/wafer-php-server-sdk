<?php
namespace QCloud_WeApp_SDK\Tunnel;

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use \QCloud_WeApp_SDK\Helper\Util as Util;

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

    private static function handleGet(ITunnelHandler $handler, $options) {
        $userInfo = NULL;

        if ($options['checkLogin']) {
            $result = LoginService::check();

            if ($result['code'] !== 0) {
                return;
            }

            $userInfo = $result['data']['userInfo'];
        }

        // TODO: 更改 skey 和 receiveUrl
        $tunnelInfo = TunnelAPI::requestConnect('kdi309c32', 'https://www.qcloud.la/tunnel');
        $handler->onRequest($tunnelInfo, $userInfo);

        Util::writeJsonResult(array('url' => $tunnelInfo['connectUrl']));
    }

    private static function handlePost(ITunnelHandler $handler, $options) {
        echo 'handlePost';
    }
}
