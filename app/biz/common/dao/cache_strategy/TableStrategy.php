<?php

namespace app\biz\common\dao\cache_strategy;

use app\biz\common\dao\CacheStrategy;
use app\biz\common\dao\GeneralDaoInterface;

/**
 * 表级别缓存策略.
 */
class TableStrategy implements CacheStrategy
{
    private $redis;

    const LIFE_TIME = 3600;

    const MAX_WAVE_CACHEABLE_TIMES = 32;

    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = $this->key($dao, $method, $arguments);

        return $this->redis->get($key);
    }

    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $data)
    {
        $key = $this->key($dao, $method, $arguments);

        return $this->redis->set($key, $data, self::LIFE_TIME);
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->upTableVersion($dao);
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->upTableVersion($dao);
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        $this->upTableVersion($dao);
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        $declares = $dao->declares();
        if ('wave' === $method) {
            $cacheable = true;
            if ($cacheable) {
                $key = sprintf('dao:%s:%s:%s:wave_times', $dao->table(), $method, json_encode($arguments));
                $waveTimes = $this->redis->incr($key);
                if ($waveTimes > self::MAX_WAVE_CACHEABLE_TIMES) {
                    $this->redis->delete($key);
                    goto end;
                } else {
                    foreach ($arguments[0] as $id) {
                        $cachKey = $this->key($dao, 'get', array($id));
                        $row = $this->redis->get($cachKey);
                        if ($row) {
                            foreach ($arguments[1] as $key => $value) {
                                $row[$key] += $value;
                                $row[$key] = (string) $row[$key];
                            }
                            $this->redis->set($cachKey, $row, self::LIFE_TIME);
                        }
                    }
                }

                return;
            }
        }

        end: $this->upTableVersion($dao);
    }

    public function flush(GeneralDaoInterface $dao)
    {
        $this->upTableVersion($dao);
    }

    private function key(GeneralDaoInterface $dao, $method, $arguments)
    {
        $version = $this->getTableVersion($dao);
        $key = sprintf('dao:%s:v:%s:%s:%s', $dao->table(), $version, $method, json_encode($arguments));

        return $key;
    }

    private function getTableVersion($dao)
    {
        $key = sprintf('dao:%s:v', $dao->table());

        // 跑单元测试时，因为每个test会flushdb，而TableCacheStrategy又是单例，这里还缓存着原来的结果，会有问题，暂时注释，待重构

        $version = $this->redis->get($key);
        if (false === $version) {
            $version = $this->redis->incr($key);
        }

        return $version;
    }

    private function upTableVersion($dao)
    {
        $key = sprintf('dao:%s:v', $dao->table());
        $version =  $this->redis->incr($key);

        return $version;
    }
}
