<?php
namespace QCloud_WeApp_SDK;

class Http {
    public static function jsonPost($options) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, $options['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));

        if (isset($options['timeout'])) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['timeout']);
        }

        if (isset($options['data'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($options['data']));
        }

        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $body = json_decode($result, TRUE);
        if ($body === NULL) {
            $body = $result;
        }

        curl_close($ch);
        return compact('status', 'body');
    }
}