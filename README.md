# 腾讯云微信小程序服务端 SDK - PHP

本 SDK 需要和 [微信小程序客户端腾讯云增强 SDK](https://github.com/tencentyun/weapp-client-sdk) 配合一起使用，提供的服务有：

+ 登录鉴权服务
+ 信道服务

## 安装

- 方法一（推荐）：使用 PHP 包依赖管理工具 `composer` 执行以下命令安装

```sh
composer require qcloud/weapp-sdk
```

- 方法二： 直接下载本仓库 `ZIP` 包解压到项目目录中

> 本 SDK 支持 PHP v5.4.0 以上的版本

## 使用

### 加载 SDK

```php
// 方法一：使用 composer 加载
require_once 'path/to/vendor/autoload.php';

// 方法二：不使用 composer 加载
require_once 'path/to/qcloud/weapp-sdk/AutoLoader.php';
```

### 初始化 SDK 配置项

```php
use \QCloud_WeApp_SDK\Conf;

Config::setup(array(
    'ServerHost' => '业务服务器的主机名',
    'AuthServerUrl' => '鉴权服务器服务地址',
    'TunnelServerUrl' => '信道服务器服务地址',
    'TunnelSignatureKey' => '和信道服务器通信的签名密钥',
));
```

### 样例 1：使用 `LoginService::login()` 处理用户登录

处理用户登录需要指定单独的路由，如 `https://www.qcloud.la/login`

```php
use \QCloud_WeApp_SDK\Auth\LoginService;

$result = LoginService::login();

if ($result['code'] === 0) {
    // 微信用户信息：`$result['data']['userInfo']`
} else {
    // 登录失败原因：`$result['message']`
}
```

### 样例 2：使用 `LoginService::check()` 处理业务 cgi 请求时校验登录态

```php
use \QCloud_WeApp_SDK\Auth\LoginService;

$result = LoginService::check();

if ($result['code'] !== 0) {
    // 登录态失败原因：`$result['message']`
    return;
}

// 使用微信用户信息（`$result['data']['userInfo']`）处理其它业务逻辑
// ...
```

### 样例 3：使用 `TunnelService::handle()` 处理信道请求

处理信道请求需要指定单独的路由，如 `https://www.qcloud.la/tunnel`

```php
use \QCloud_WeApp_SDK\Tunnel\TunnelService;
use \QCloud_WeApp_SDK\Tunnel\ITunnelHandler;

class TunnelHandler implements ITunnelHandler {
    // TODO: 实现 onRequest 方法
    public function onRequest($tunnelId, $userInfo) {

    }

    // TODO: 实现 onConnect 方法
    public function onConnect($tunnelId) {

    }

    // TODO: 实现 onMessage 方法
    public function onMessage($tunnelId, $type, $content) {

    }

    // TODO: 实现 onClose 方法
    public function onClose($tunnelId) {

    }
}

$handler = new TunnelHandler();
TunnelService::handle($handler, array('checkLogin' => TRUE));
```

### 详细示例

参见项目：[腾讯云微信小程序服务端 DEMO - PHP](https://github.com/tencentyun/weapp-php-server-demo)

## API

参见文档 [API.md](./API.md)

## 附：信道服务交互流程图

![信道服务流程图](http://easyimage-10028115.file.myqcloud.com/internal/ozy5zc4q.njb.jpg)

## LICENSE

[MIT](LICENSE)