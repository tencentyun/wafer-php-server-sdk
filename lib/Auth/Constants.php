<?php
namespace QCloud_WeApp_SDK\Auth;

class Constants {
    const WX_HEADER_CODE = 'X-WX-Code';
    const WX_HEADER_ENCRYPTED_DATA = 'X-WX-Encrypted-Data';
    const WX_HEADER_IV = 'X-WX-IV';

    const WX_HEADER_ID = 'X-WX-Id';
    const WX_HEADER_SKEY = 'X-WX-Skey';

    const WX_SESSION_MAGIC_ID = 'F2C224D4-2BCE-4C64-AF9F-A6D872000D1A';

    const ERR_LOGIN_FAILED = 'ERR_LOGIN_FAILED';
    const ERR_INVALID_SESSION = 'ERR_INVALID_SESSION';
    const ERR_CHECK_LOGIN_FAILED = 'ERR_CHECK_LOGIN_FAILED';

    const INTERFACE_LOGIN = 'qcloud.cam.id_skey';
    const INTERFACE_CHECK = 'qcloud.cam.auth';

    const RETURN_CODE_SUCCESS = 0;
    const RETURN_CODE_SKEY_EXPIRED = 60011;
    const RETURN_CODE_WX_SESSION_FAILED = 60012;
}
