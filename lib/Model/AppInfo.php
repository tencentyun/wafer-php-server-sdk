<?php
/**
 * Created by PhpStorm.
 * User: aaron
 * Date: 2018/11/19
 * Time: 17:21
 */

namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;

class AppInfo
{
    public static function getApp($id)
    {
        return DB::row('cAppInfo', ['*'], compact('id'));
    }
}
