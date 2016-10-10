# 腾讯云微信小程序服务端 SDK

SDK 提供的服务有：

+ 登录鉴权服务
+ 信道服务

## 登录鉴权服务

### API

#### LoginService::login() => 处理用户登录

登录成功时返回

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

登录失败返回

```php
array(
    'code' => -1,
    'message' => '失败原因',
    'data' => array(),
)
```

#### LoginService::check() => 校验登录态

校验登录态成功时返回

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

校验登录态失败返回

```php
array(
    'code' => -1,
    'message' => '失败原因',
    'data' => array(),
)
```

### 使用示例

```php
// 第一步，引入 SDK
require_once 'path/to/QCloud_WeApp_SDK/AutoLoader.php';

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
```

```php
// 样例1: 提供专门的 cgi 处理用户登录
$result = LoginService::login();

if ($result['code'] === 0) {
    // 微信用户信息：`$result['data']['userInfo']`
} else {
    // 登录失败原因：`$result['message']`
}
```

```php
// 样例2: 处理业务 cgi 请求时校验登录态
$result = LoginService::check();

if ($result['code'] !== 0) {
    // 校验登录态失败原因：`$result['message']`
    return;
}

// 使用微信用户信息（`$result['data']['userInfo']`）处理其它业务逻辑
```

## 信道服务

## LICENSE

[MIT](LICENSE)