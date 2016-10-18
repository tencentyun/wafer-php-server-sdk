# 腾讯云微信小程序服务端 SDK - PHP

本 SDK 需要和 [微信小程序客户端腾讯云增强 SDK](https://github.com/CFETeam/weapp-client-sdk) 配合一起使用，提供的服务有：

+ 登录鉴权服务
+ 信道服务

## 目录

- [使用示例](使用示例)

- [SDK 配置](#sdk-配置)
    - [命名空间](#命名空间)
    - [API](#api)

- [登录鉴权服务](#登录鉴权服务)
    - [命名空间](#命名空间-1)
    - [API](#api-1)

- [信道服务](#信道服务)
    - [命名空间](#命名空间-2)
    - [API](#api-2)

- [附：信道服务交互流程图](附信道服务交互流程图)

## 使用示例

```php
/*----------------------------------------------------------------
 * 加载 SDK
 *----------------------------------------------------------------
 */

// 方法一：使用 composer 加载
require_once 'path/to/vendor/autoload.php';

// 方法二：不使用 composer 加载
require_once 'path/to/qcloud/weapp-sdk/AutoLoader.php';
```

```php
/*----------------------------------------------------------------
 * 使用 SDK 前需指定各种配置项
 *----------------------------------------------------------------
 */

use \QCloud_WeApp_SDK\Conf;

Config::setup(array(
    'ServerHost' => '业务服务器的主机名',
    'AuthServerUrl' => '鉴权服务器服务地址',
    'TunnelServerUrl' => '信道服务器服务地址',
    'TunnelSignatureKey' => '通信签名密钥',
));
```

```php
/*----------------------------------------------------------------
 * 样例 1
 *----------------------------------------------------------------
 * 使用 `LoginService::login` 处理用户登录
 * 需要指定单独的路由处理用户登录，如`https://www.qcloud.la/login`
 *----------------------------------------------------------------
 */

use \QCloud_WeApp_SDK\Auth\LoginService;

$result = LoginService::login();

if ($result['code'] === 0) {
    // 微信用户信息：`$result['data']['userInfo']`
} else {
    // 登录失败原因：`$result['message']`
}
```

```php
/*----------------------------------------------------------------
 * 样例 2
 *----------------------------------------------------------------
 * 使用 `LoginService::check` 处理业务 cgi 请求时校验登录态
 *----------------------------------------------------------------
 */

use \QCloud_WeApp_SDK\Auth\LoginService;

$result = LoginService::check();

if ($result['code'] !== 0) {
    // 登录态失败原因：`$result['message']`
    return;
}

// 使用微信用户信息（`$result['data']['userInfo']`）处理其它业务逻辑
// ...
```

```php
/*----------------------------------------------------------------
 * 样例 3
 *----------------------------------------------------------------
 * 使用 `TunnelService::handle` 处理信道请求
 * 需要指定单独的路由处理信道请求，如`https://www.qcloud.la/tunnel`
 *----------------------------------------------------------------
 */

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

## SDK 配置

### 命名空间

`QCloud_WeApp_SDK`

### API

#### Conf::setServerHost(string $serverHost)

该静态方法用于设置使用本 SDK 的业务服务器的主机名，如`www.qcloud.la`，该主机需要外网可访问。

参数说明：

- `$serverHost`: 业务服务器的主机名

#### Conf::setAuthServerUrl(string $authServerUrl)

该静态方法用于指定鉴权服务器服务地址，如`http://mina.auth.com`。

参数说明：

- `$authServerUrl`: 鉴权服务器服务地址

#### Conf::setTunnelServerUrl(string $tunnelServerUrl)

该静态方法用于指定信道服务器服务地址，如`https://ws.qcloud.com`。

参数说明：

- `$tunnelServerUrl`: 信道服务器服务地址

#### Conf::setTunnelSignatureKey(string $tunnelSignatureKey)

该静态方法用于指定和信道服务通信的签名密钥，如`27fb7d1c161b7ca52d73cce0f1d833f9f5b5ec89`，该密钥需要保密。

参数说明：

- `$tunnelSignatureKey`: 通信签名密钥

#### Conf::setup(array $config)

可以使用本方法批量设置以上所有配置。

参数说明：

- `$config`: 需要指定的配置项，可选配置项如下
    - `ServerHost`: 业务服务器的主机名
    - `AuthServerUrl`: 鉴权服务器服务地址
    - `TunnelServerUrl`: 信道服务器服务地址
    - `TunnelSignatureKey`: 通信签名密钥

## 登录鉴权服务

### 命名空间

`QCloud_WeApp_SDK\Auth`

### API

#### LoginService::login()

该静态方法用于处理用户登录。

登录成功时，方法会返回：

```php
array(
    'code' => 0,
    'message' => 'ok',
    'data' => array(
        // 微信用户信息
        'userInfo' => array(...),
    ),
)
```

登录失败时，方法会返回：

```php
array(
    'code' => -1,
    'message' => '失败原因',
    'data' => array(),
)
```

#### LoginService::check()

该静态方法用于校验登录态。

校验登录态成功时，方法会返回：

```php
array(
    'code' => 0,
    'message' => 'ok',
    'data' => array(
        // 微信用户信息
        'userInfo' => array(...),
    ),
)
```

校验登录态失败时，方法会返回：

```php
array(
    'code' => -1,
    'message' => '失败原因',
    'data' => array(),
)
```

## 信道服务

### 命名空间

`QCloud_WeApp_SDK\Tunnel`

### API

#### ITunnelHandler

处理信道请求需实现该接口，接口定义和描述如下：

```php
interface ITunnelHandler {
    /*----------------------------------------------------------------
     * 在客户端请求 WebSocket 信道连接之后会调用该方法
     * 此时可以把信道 ID 和用户信息关联起来
     *----------------------------------------------------------------
     * @param string $tunnelId  信道 ID
     * @param array  $userInfo  微信用户信息
     *----------------------------------------------------------------
     */
    public function onRequest($tunnelId, $userInfo);

    /*----------------------------------------------------------------
     * 在客户端成功连接 WebSocket 信道服务之后会调用该方法
     * 此时可以通知所有其它在线的用户当前总人数以及刚加入的用户是谁
     *----------------------------------------------------------------
     * @param string $tunnelId  信道 ID
     *----------------------------------------------------------------
     */
    public function onConnect($tunnelId);

    /*----------------------------------------------------------------
     * 客户端推送消息到 WebSocket 信道服务器上后会调用该方法
     * 此时可以处理信道的消息
     *----------------------------------------------------------------
     * @param string $tunnelId  信道 ID
     * @param string $type      消息类型
     * @param mixed  $content   消息内容
     *----------------------------------------------------------------
     */
    public function onMessage($tunnelId, $type, $content);

    /*----------------------------------------------------------------
     * 客户端关闭 WebSocket 信道或者被信道服务器判断为已断开后会调用该方法
     * 此时可以进行清理及通知操作
     *----------------------------------------------------------------
     * @param string $tunnelId  信道 ID
     *----------------------------------------------------------------
     */
    public function onClose($tunnelId);
}
```

#### TunnelService::handle(ITunnelHandler $handler[, array $options])

该静态方法用于处理信道请求。

参数说明：

- `$handler`: 该参数须实现接口 `ITunnelHandler`（必填）
- `$options`: 该参数接收的可选选项如下
    - `checkLogin`: 是否校验登录态（默认为 `FALSE`）

> 当`checkLogin`为`FALSE`时，传递给`ITunnelHandler->onRequest`的参数 `$userInfo` 值为`NULL`

#### TunnelService::broadcast(array $tunnelIds, string $messageType, mixed $messageContent)

该静态方法用于广播消息到多个信道。

参数说明：

- `$tunnelIds`: 要广播消息的信道 ID 列表（必填）
- `$messageType`: 要广播消息的消息类型（必填）
- `$messageContent`: 要广播消息的消息内容（必填）

#### TunnelService::emit(string $tunnelId, string $messageType, mixed $messageContent)

该静态方法用于发送消息到指定信道。

参数说明：

- `$tunnelId`: 要发送消息的信道 ID（必填）
- `$messageType`: 要发送消息的消息类型（必填）
- `$messageContent`: 要发送消息的消息内容（必填）

#### TunnelService::closeTunnel(string $tunnelId)

该静态方法用于关闭指定信道。

参数说明：

- `$tunnelId`: 要关闭的信道 ID（必填）

## 附：信道服务交互流程图

![信道服务流程图](http://easyimage-10028115.file.myqcloud.com/internal/ozy5zc4q.njb.jpg)

## LICENSE

[MIT](LICENSE)