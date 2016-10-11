<?php
namespace QCloud_WeApp_SDK\Tunnel;

interface ITunnelHandler {
    public function onRequest($tunnel, $userInfo);
    public function onConnect($tunnel);
    public function onMessage($tunnel, $message);
    public function onClose($tunnel);
}
