<?php

namespace app\biz;

use Redis;
use RedisArray;
use think\Service;

class RedisServiceProvider extends Service
{
    public function register()
    {
        $this->app->bind('redis', function () {

            $defaultOptions = array(
                'host' => '127.0.0.1:6379',
                'password' => '',
                'timeout' => 1,
                'reserved' => null,
                'retry_interval' => 100,
                'key_prefix' => '',
            );

            $hosts = explode(',', $defaultOptions['host']);

            if (1 == count($hosts)) {
                list($host, $port) = explode(':', $hosts[0]);
                $redis = new Redis();

                if (!empty($defaultOptions['pconnect'])) {
                    $redis->pconnect($host, $port, $defaultOptions['timeout']);
                } else {
                    $redis->connect($host, $port, $defaultOptions['timeout'], $defaultOptions['reserved'], $defaultOptions['retry_interval']);
                }
            } else {
                $redis = new RedisArray($hosts);
            }

            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            if ($defaultOptions['key_prefix']) {
                $redis->setOption(Redis::OPT_PREFIX, $defaultOptions['key_prefix']);
            }
            if (!empty($defaultOptions['password'])) {
                $redis->auth($defaultOptions['password']);
            }

            return $redis;
        });
    }
}
