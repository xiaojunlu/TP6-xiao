<?php

namespace app\biz;

use think\Service;
use app\biz\common\dao\RedisCache;
use app\biz\common\dao\ArrayStorage;
use app\biz\common\dao\FieldSerializer;
use app\biz\common\dao\cache_strategy\TableStrategy;
use app\biz\common\dao\cache_strategy\RowStrategy;

class BizServiceProvider extends Service
{
    public function register()
    {
        $this->app->bind('interceptors', function () {
            return new \ArrayObject();
        });

        $this->app->bind('array_storage', function () {
            return new ArrayStorage();
        });

        $this->app->bind('dao.serializer', function () {
            return new FieldSerializer();
        });

        $this->app->bind('dao.cache.redis_wrapper', function () {
            return new RedisCache($this->app->redis);
        });

        //dao层缓存开关
        $this->app->bind('dao.cache.array_storage', function () {
            return null;
        });

        $this->app->bind('dao.cache.enabled', function () {
            return false;
        });

        $this->app->bind('dao.cache.strategy.default', function () {
            return app('dao.cache.strategy.table');
        });

        $this->app->bind('dao.cache.strategy.table', function () {
            return new TableStrategy(app('dao.cache.redis_wrapper'), app('dao.cache.array_storage'));
        });

        $this->app->bind('dao.cache.strategy.row', function () {
            return new RowStrategy(app('dao.cache.redis_wrapper'), app('dao.metadata_reader'));
        });

        $this->app->bind('lock', function () {
            return new Lock();
        });
    }
}
