## 目录

- [SDK 配置](#sdk-配置)
- [会话服务](#会话服务)
- [信道服务](#信道服务)

## SDK 配置

> 在使用本 SDK 提供的其他 API 之前，需调用以下和配置项相关的 API 进行初始化。

### 命名空间

`QCloud_WeApp_SDK`

### API

#### Conf::setServerHost(string $serverHost)

该静态方法用于设置使用本 SDK 的业务服务器的主机名，如 `www.qcloud.la`，该主机需要外网可访问。

##### 参数

- `$serverHost` - 业务服务器的主机名

##### 返回值

`void`

#### Conf::setAuthServerUrl(string $authServerUrl)

该静态方法用于指定鉴权服务器服务地址，如 `http://mina.auth.com`。

##### 参数

- `$authServerUrl` - 鉴权服务器服务地址

##### 返回值

`void`

#### Conf::setTunnelServerUrl(string $tunnelServerUrl)

该静态方法用于指定信道服务器服务地址，如 `https://ws.qcloud.com`。

##### 参数

- `$tunnelServerUrl` - 信道服务器服务地址

##### 返回值

`void`

#### Conf::setTunnelSignatureKey(string $tunnelSignatureKey)

该静态方法用于指定和信道服务器通信的签名密钥，如 `9f338d1f0ecc37d25ac7b161c1d7bf72` ，该密钥需要保密。

##### 参数

- `$tunnelSignatureKey` - 通信签名密钥

##### 返回值

`void`

#### Conf::setNetworkTimeout(int $networkTimeout)

该静态方法用于设置网络请求超时时长（单位：毫秒），默认值为 30,000 毫秒，即 30 秒。

##### 参数

- `$networkTimeout` - 网络请求超时时长

##### 返回值

`void`

#### Conf::setup(array $config)

可以使用本方法批量设置以上所有配置。

##### 参数

- `$config` 支持的配置选项如下：
    - `ServerHost` - 业务服务器的主机名
    - `AuthServerUrl` - 鉴权服务器服务地址
    - `TunnelServerUrl` - 信道服务器服务地址
    - `TunnelSignatureKey` - 通信签名密钥
    - `NetworkTimeout` - 网络请求超时时长（单位：毫秒）

##### 返回值

`void`

## 会话服务

### 命名空间

`QCloud_WeApp_SDK\Auth`

### API

#### LoginService::login()

该静态方法用于处理用户登录。

##### 参数

无

##### 返回值

登录成功时，返回：

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

登录失败时，返回：

```php
array(
    'code' => -1,
    'message' => '失败原因',
    'data' => array(),
)
```

#### LoginService::check()

该静态方法用于校验登录态。

##### 参数

无

##### 返回值

校验登录态成功时，返回：

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

校验登录态失败时，返回：

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

##### 参数

- `$handler` - 该参数须实现接口 `ITunnelHandler`（必填）
- `$options` - 该参数支持的可选选项如下：
    - `checkLogin` - 是否校验登录态（默认为 `FALSE`）

##### 返回值

`void`

> 当 `checkLogin` 为 `FALSE` 时，传递给 `ITunnelHandler->onRequest` 的参数 `$userInfo` 值为 `NULL`。

#### TunnelService::broadcast(array $tunnelIds, string $messageType, mixed $messageContent)

该静态方法用于广播消息到多个信道。

##### 参数

- `$tunnelIds` - 要广播消息的信道 ID 列表（必填）
- `$messageType` - 要广播消息的消息类型（必填）
- `$messageContent` - 要广播消息的消息内容（必填）

##### 返回值

消息广播成功时，返回：

```php
array(
    'code' => 0,
    'message' => 'OK',
    'data' => array(
        // 广播消息时无效的信道 IDs
        'invalidTunnelIds' => array(...),
    ),
)
```

消息广播失败时，返回：

```php
array(
    'code' => '失败错误码（非0）',
    'message' => '失败原因',
)
```

#### TunnelService::emit(string $tunnelId, string $messageType, mixed $messageContent)

该静态方法用于发送消息到指定信道。

##### 参数

- `$tunnelId` - 要发送消息的信道 ID（必填）
- `$messageType` - 要发送消息的消息类型（必填）
- `$messageContent` - 要发送消息的消息内容（必填）

##### 返回值

消息发送成功时，返回：

```php
array(
    'code' => 0,
    'message' => 'OK',
)
```

消息发送失败时，返回：

```php
array(
    'code' => '失败错误码（非0）',
    'message' => '失败原因',
)
```

#### TunnelService::closeTunnel(string $tunnelId)

该静态方法用于关闭指定信道。

##### 参数

- `$tunnelId` - 要关闭的信道 ID（必填）

##### 返回值

信道关闭成功时，返回：

```php
array(
    'code' => 0,
    'message' => 'OK',
)
```

信道关闭失败时，返回：

```php
array(
    'code' => '失败错误码（非0）',
    'message' => '失败原因',
)
```
