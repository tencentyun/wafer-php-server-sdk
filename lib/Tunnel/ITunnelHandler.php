<?php
namespace QCloud_WeApp_SDK\Tunnel;

interface ITunnelHandler {
    public function onRequest($tunnelId, $userInfo);
    public function onConnect($tunnelId);
    public function onMessage($tunnelId, $type, $content);
    public function onClose($tunnelId);
}
