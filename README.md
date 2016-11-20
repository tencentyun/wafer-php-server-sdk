# Wafer 服务端 SDK - PHP

[![Latest Stable Version][packagist-image]][packagist-url]
[![Minimum PHP Version][php-image]][php-url]
[![Build Status][travis-image]][travis-url]
[![Coverage Status][coveralls-image]][coveralls-url]
[![License][license-image]][license-url]

本项目是 [Wafer](https://github.com/tencentyun/wafer) 组成部分，以 SDK 的形式为业务服务器提供以下服务：

+ [会话服务](https://github.com/tencentyun/wafer/wiki/会话服务)
+ [信道服务](https://github.com/tencentyun/wafer/wiki/信道服务)

## 安装

- 方法一（推荐）：使用 PHP 包依赖管理工具 `composer` 执行以下命令安装

```sh
composer require qcloud/weapp-sdk
```

- 方法二： 直接下载本仓库 `ZIP` 包解压到项目目录中

## API

参见 [API 文档](./API.md)

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

关于 SDK 配置字段的含义以及配置文件格式的更多信息，[请参考服务端 SDK 配置](https://github.com/tencentyun/wafer/wiki/%E6%9C%8D%E5%8A%A1%E7%AB%AF-SDK-%E9%85%8D%E7%BD%AE)。

### 使用会话服务

#### 处理用户登录请求

业务服务器提供一个路由（如 `/login`）处理客户端的登录请求，直接使用 SDK 的 [LoginService::login()](https://github.com/tencentyun/wafer-php-server-sdk/blob/master/API.md#loginservicelogin) 方法即可完成登录处理。登录成功后，可以获取用户信息。

```php
use \QCloud_WeApp_SDK\Auth\LoginService;

$result = LoginService::login();

if ($result['code'] === 0) {
    // 微信用户信息：`$result['data']['userInfo']`
} else {
    // 登录失败原因：`$result['message']`
}
```

#### 检查请求登录态

客户端交给业务服务器的请求，业务服务器可以通过 SDK 的 [LoginService::check()](https://github.com/tencentyun/wafer-php-server-sdk/blob/master/API.md#loginservicecheck) 方法来检查该请求是否包含合法的会话。如果包含，则会返回会话对应的用户信息。

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

阅读 Wafer Wiki 文档中的[会话服务](https://github.com/tencentyun/wafer/wiki/%E4%BC%9A%E8%AF%9D%E6%9C%8D%E5%8A%A1)了解更多关于会话服务的技术资料。

### 使用信道服务

业务在一个路由上（如 `/tunnel`）提供信道服务，只需把该路由上的请求都交给 SDK 的信道服务处理即可。

```php
use \QCloud_WeApp_SDK\Tunnel\TunnelService;
use \QCloud_WeApp_SDK\Tunnel\ITunnelHandler;

class TunnelHandler implements ITunnelHandler {
    // TODO: 实现 onRequest 方法，处理信道连接请求
    public function onRequest($tunnelId, $userInfo) {

    }

    // TODO: 实现 onConnect 方法，处理信道连接事件
    public function onConnect($tunnelId) {

    }

    // TODO: 实现 onMessage 方法，处理信道消息
    public function onMessage($tunnelId, $type, $content) {

    }

    // TODO: 实现 onClose 方法，处理信道关闭事件
    public function onClose($tunnelId) {

    }
}

$handler = new TunnelHandler();
TunnelService::handle($handler, array('checkLogin' => TRUE));
```

使用信道服务需要实现处理器，来获取处理信道的各种事件，具体可参考接口 [ITunnelHandler](https://github.com/tencentyun/wafer-php-server-sdk/blob/master/API.md#itunnelhandler) 的 API 文档以及配套 Demo 中的 [ChatTunnelHandler](https://github.com/tencentyun/wafer-php-server-demo/blob/master/application/business/ChatTunnelHandler.php) 的实现。

阅读 Wafer Wiki 中的[信道服务](https://github.com/tencentyun/wafer/wiki/%E4%BF%A1%E9%81%93%E6%9C%8D%E5%8A%A1)了解更多解决方案中关于信道服务的技术资料。

### 详细示例

参见项目：[Wafer 服务端 DEMO - PHP](https://github.com/tencentyun/wafer-php-server-demo)

## LICENSE

[MIT](LICENSE)

[packagist-image]: https://img.shields.io/packagist/v/qcloud/weapp-sdk.svg
[packagist-url]: https://packagist.org/packages/qcloud/weapp-sdk
[php-image]: https://img.shields.io/badge/PHP-%3E%3D%205.4-8892BF.svg
[php-url]: https://secure.php.net/
[travis-image]: https://travis-ci.org/tencentyun/wafer-php-server-sdk.svg?branch=master
[travis-url]: https://travis-ci.org/tencentyun/wafer-php-server-sdk
[coveralls-image]: https://coveralls.io/repos/github/tencentyun/wafer-php-server-sdk/badge.svg?branch=master
[coveralls-url]: https://coveralls.io/github/tencentyun/wafer-php-server-sdk?branch=master
[license-image]: https://img.shields.io/github/license/tencentyun/wafer-php-server-sdk.svg
[license-url]: LICENSE
