<?php
namespace QCloud_WeApp_SDK\Auth;

class Constants {
    const WX_HEADER_CODE = 'X-WX-Code';
    const WX_HEADER_ENCRYPT_DATA = 'X-WX-Encrypt-Data';
    const WX_HEADER_ID = 'X-WX-Id';
    const WX_HEADER_SKEY = 'X-WX-Skey';

    const WX_SESSION_MAGIC_ID = 'F2C224D4-2BCE-4C64-AF9F-A6D872000D1A';

    const ERR_LOGIN_FAILED = 'ERR_LOGIN_FAILED';
    const ERR_SESSION_EXPIRED = 'ERR_SESSION_EXPIRED';
    const ERR_CHECK_LOGIN_FAILED = 'ERR_CHECK_LOGIN_FAILED';

    const INTERFACE_LOGIN = 'qcloud.cam.id_skey';
    const INTERFACE_CHECK = 'qcloud.cam.auth';
}