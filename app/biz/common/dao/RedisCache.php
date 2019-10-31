<?php

namespace app\biz\common\dao;

class RedisCache
{
    /**
     * @var \Redis|\RedisArray
     */
    protected $redis;

    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function set($key, $value, $lifetime = 0)
    {
        $this->redis->set($key, $value, $lifetime);
    }

    public function incr($key)
    {
        $newValue = $this->redis->incr($key);
    }

    public function del($key)
    {
        $this->redis->del($key);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->redis, $name), $arguments);
    }
}
