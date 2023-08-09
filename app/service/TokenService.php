<?php

namespace app\service;

use think\facade\Env;

class TokenService
{
    public static function generateToken($uuid)
    {
        $key = Env::get('app.token_key',  'token');
        $expireTime = Env::get('app.token_time',  7200);
        $delayTime = Env::get('app.delay_time',  0);
        $time = time() + $expireTime;
        $str = "{$uuid}\t{$time}";
        $tokenExpireTime = intval($time - $delayTime) <= 0 ? 0 : intval($time - $delayTime);
        return ['access_token' => base64_encode(simple_encrypt($str, $key)), 'time' => $tokenExpireTime];
    }

    public static function verifyToken($accessToken)
    {
        $key = Env::get('app.token_key',  'token');
        $str = simple_decrypt(base64_decode($accessToken), $key);
        @list($uuid, $time) = explode("\t", $str);
        //TODO 过期判断
        if ($uuid && ($time - time()) >= 0) {
            return [
                'uuid' => $uuid,
                'access_token' => $accessToken,
                'expired_at' => $time,
            ];
        }
        return false;
    }

    public static function refreshToken($u, $accessToken)
    {
        $key = Env::get('app.token_key',  'token');
        $str = simple_decrypt(base64_decode($accessToken), $key);
        @list($uuid, $time) = explode("\t", $str);
        if ($uuid == $u) {
            return self::generateToken($uuid);
        }
        return false;
    }
}