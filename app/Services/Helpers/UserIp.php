<?php

namespace App\Services\Helpers;

class UserIp
{
    public function getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTPX_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTPS_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return request()->ip();
    }
}
