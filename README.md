# 腾讯云微信小程序服务端 SDK

本 SDK 需要和 [微信小程序客户端腾讯云增强 SDK](https://github.com/CFETeam/weapp-client-sdk) 配合一起使用，提供的服务有：

+ 登录鉴权服务
+ 信道服务

## API

### 登录鉴权服务

#### LoginService::login()

该静态方法主要用于处理用户登录。

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

该静态方法主要用于校验登录态。

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

### 信道服务

#### TunnelService::handle(ITunnelHandler $handler[, Array $options])

该静态方法主要处理信道请求。

## 使用示例

```php
/**
 * 引入 SDK
 */

// 方法一：使用 composer
require_once 'path/to/vendor/autoload.php';

// 方法二：不使用 composer
require_once 'path/to/qcloud/weapp-sdk/AutoLoader.php';
```

```php
/**
 * 样例1: 使用 `LoginService::login` 处理用户登录
 */

$result = LoginService::login();

if ($result['code'] === 0) {
    // 微信用户信息：`$result['data']['userInfo']`
} else {
    // 登录失败原因：`$result['message']`
}
```

```php
/**
 * 样例2: 使用 `LoginService::check` 处理业务 cgi 请求时校验登录态
 */

$result = LoginService::check();

if ($result['code'] !== 0) {
    // 登录态失败原因：`$result['message']`
    return;
}

// 使用微信用户信息（`$result['data']['userInfo']`）处理其它业务逻辑
```

## LICENSE

[MIT](LICENSE)