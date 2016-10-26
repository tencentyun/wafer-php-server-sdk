<?php

use \QCloud_WeApp_SDK\Conf;
use \QCloud_WeApp_SDK\Auth\LoginService;
use \QCloud_WeApp_SDK\Auth\Constants;

/**
 * @runTestsInSeparateProcesses
 */
class LoginServiceTest extends PHPUnit_Framework_TestCase {
    public static function setUpBeforeClass() {
        Conf::setup(array(
            'ServerHost' => SERVER_HOST,
            'AuthServerUrl' => AUTH_SERVER_URL,
            'TunnelServerUrl' => TUNNEL_SERVER_URL,
            'TunnelSignatureKey' => TUNNEL_SIGNATURE_KEY,
        ));
    }

    public function testLoginUseCase() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_CODE, 'valid-code');
        $this->setHttpHeader(Constants::WX_HEADER_ENCRYPT_DATA, 'valid-data');

        $result = LoginService::login();
        $this->assertSame(0, $result['code']);
        $this->assertArrayHasKey('userInfo', $result['data']);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('session', $body);
        $this->assertArrayHasKey('id', $body['session']);
        $this->assertArrayHasKey('skey', $body['session']);
    }

    public function testLoginWithoutCodeAndEncryptData() {
        $this->setOutputCallback(function () {});

        $result = LoginService::login();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testLoginWithInvalidCode() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_CODE, 'invalid-code');
        $this->setHttpHeader(Constants::WX_HEADER_ENCRYPT_DATA, 'valid-data');

        $result = LoginService::login();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testLoginWithInvalidEncryptData() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_CODE, 'valid-code');
        $this->setHttpHeader(Constants::WX_HEADER_ENCRYPT_DATA, 'invalid-data');

        $result = LoginService::login();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testLoginWhenAuthServerRespondWithInvalidData() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_CODE, 'expect-invalid-json');
        $this->setHttpHeader(Constants::WX_HEADER_ENCRYPT_DATA, 'valid-data');

        $result = LoginService::login();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testLoginWhenAuthServerRespondWith500() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_CODE, 'expect-500');
        $this->setHttpHeader(Constants::WX_HEADER_ENCRYPT_DATA, 'valid-data');

        $result = LoginService::login();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testLoginWhenAuthServerTimedout() {
        $this->setOutputCallback(function () {});

        Conf::setNetworkTimeout(1000);
        $this->setHttpHeader(Constants::WX_HEADER_CODE, 'expect-timeout');
        $this->setHttpHeader(Constants::WX_HEADER_ENCRYPT_DATA, 'valid-data');

        $result = LoginService::login();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCheckUseCase() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_ID, 'valid-id');
        $this->setHttpHeader(Constants::WX_HEADER_SKEY, 'valid-key');

        $result = LoginService::check();
        $this->assertSame(0, $result['code']);
        $this->assertArrayHasKey('userInfo', $result['data']);

        $this->expectOutputString('');
    }

    public function testCheckWithoutIdAndSkey() {
        $this->setOutputCallback(function () {});

        $result = LoginService::check();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCheckWithInvalidId() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_ID, 'invalid-id');
        $this->setHttpHeader(Constants::WX_HEADER_SKEY, 'valid-key');

        $result = LoginService::check();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCheckWithInvalidSkey() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_ID, 'valid-id');
        $this->setHttpHeader(Constants::WX_HEADER_SKEY, 'invalid-key');

        $result = LoginService::check();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCheckWhenAuthServerRespondWithInvalidData() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_ID, 'expect-invalid-json');
        $this->setHttpHeader(Constants::WX_HEADER_SKEY, 'valid-key');

        $result = LoginService::check();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCheckWhenAuthServerRespondWith500() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_ID, 'expect-500');
        $this->setHttpHeader(Constants::WX_HEADER_SKEY, 'valid-key');

        $result = LoginService::check();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCheckWhenAuthServerTimedout() {
        $this->setOutputCallback(function () {});

        Conf::setNetworkTimeout(1000);
        $this->setHttpHeader(Constants::WX_HEADER_ID, 'expect-timeout');
        $this->setHttpHeader(Constants::WX_HEADER_SKEY, 'valid-key');

        $result = LoginService::check();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCheckWhenAuthServerRespondWith60011ErrorCode() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_ID, 'expect-60011');
        $this->setHttpHeader(Constants::WX_HEADER_SKEY, 'valid-key');

        $result = LoginService::check();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCheckWhenAuthServerRespondWith60012ErrorCode() {
        $this->setOutputCallback(function () {});

        $this->setHttpHeader(Constants::WX_HEADER_ID, 'expect-60012');
        $this->setHttpHeader(Constants::WX_HEADER_SKEY, 'valid-key');

        $result = LoginService::check();
        $this->assertInternalType('int', $result['code']);
        $this->assertFalse($result['code'] === 0);

        $body = json_decode($this->getActualOutput(), TRUE);
        $this->assertSame(1, $body[Constants::WX_SESSION_MAGIC_ID]);
        $this->assertArrayHasKey('error', $body);
    }

    private function setHttpHeader($headerKey, $headerVal) {
        $headerKey = strtoupper($headerKey);
        $headerKey = str_replace('-', '_', $headerKey);
        $headerKey = 'HTTP_' . $headerKey;
        $_SERVER[$headerKey] = $headerVal;
    }
}