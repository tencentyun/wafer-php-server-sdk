<?php
/**
 * Created by PhpStorm.
 * User: aaron
 * Date: 2018/11/19
 * Time: 15:46
 */

namespace QCloud_WeApp_SDK\Helper;

use QCloud_WeApp_SDK\Conf;
use QCloud_WeApp_SDK\Model\AppInfo;

class Initializer
{
    public static function getApp($id)
    {
        $app = AppInfo::getApp($id);
        if (!$app) {
            throw new \Exception(sprintf('Application id %s not found.', $id));
        }

        return $app;
    }

    public static function setConfiguration($app)
    {
        Conf::setAppId($app->appid);
        Conf::setAppSecret($app->secret);
    }
}
