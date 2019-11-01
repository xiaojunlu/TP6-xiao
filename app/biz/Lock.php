<?php

namespace app\biz;

use think\facade\Db;

/**
 * MySQL锁机制
 * @deprecated 2.0
 */
class Lock
{
    public function get($lockName, $lockTime = 30)
    {
        $result = Db::query("SELECT GET_LOCK('locker_{$lockName}', {$lockTime}) AS getLock");

        return $result[0]['getLock'];
    }

    public function release($lockName)
    {
        $result = Db::query("SELECT RELEASE_LOCK('locker_{$lockName}') AS releaseLock");

        return $result[0]['releaseLock'];
    }
}
