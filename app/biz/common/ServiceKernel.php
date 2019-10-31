<?php

namespace app\biz\common;

use app\biz\common\dao\DaoProxy;
use app\biz\common\dao\ArrayStorage;
use app\biz\common\dao\FieldSerializer;

class ServiceKernel
{
    private static $_instance;

    protected $pool = array();

    /**
     * @return ServiceKernel
     */
    public static function instance()
    {
        if (empty(self::$_instance)) {
            throw new \RuntimeException('The instance of ServiceKernel is not created!');
        }
        return self::$_instance;
    }

    public static function create()
    {
        if (self::$_instance) {
            return self::$_instance;
        }

        $instance = new self();
        self::$_instance = $instance;

        return $instance;
    }

    public function getRedis($group = 'default')
    {
        $redis = app('redis');
        if (empty($redis)) {
            return false;
        }

        return $redis;
    }

    public function setCurrentUser($currentUser)
    {
        //绑定容器 注入user
        bind('user', $currentUser);
        return $this;
    }

    /**
     * 获取当前登录用户信息
     */
    public function getCurrentUser()
    {
        $currentUser = app('user');
        if (!isset($currentUser)) {
            throw new \RuntimeException('The `CurrentUser` of ServiceKernel is not setted!');
        }

        return $currentUser;
    }

    public function trans($message, $arguments = array(), $domain = null, $locale = null)
    {
        return strtr((string) $message, $arguments);
    }

    public function createValidate($name)
    {
        if (empty($this->pool[$name])) {
            $class = $this->getClassName('validate', $name);
            $this->pool[$name] = new $class();
        }

        return $this->pool[$name];
    }

    public function createService($name)
    {
        if (empty($this->pool[$name])) {
            $class = $this->getClassName('service', $name);
            $this->pool[$name] = new $class();
        }

        return $this->pool[$name];
    }

    public function createDao($name)
    {
        if (empty($this->pool[$name])) {
            $class = $this->getClassName('dao', $name);
            $dao = new $class();
            $this->pool[$name] = $dao;
        }

        //Dao 代理类 启用 Dao 层的缓存
        return new DaoProxy($this->pool[$name], new FieldSerializer(), new ArrayStorage());
        //return $this->pool[$name];
    }

    protected function getClassName($type, $name)
    {
        if (strpos($name, ':') > 0) {
            list($namespace, $name) = explode(':', $name, 2);
            $namespace .= '\\service';
        } else {
            $namespace = substr(__NAMESPACE__, 0, -strlen('common') - 1);
        }

        list($module, $className) = explode('.', $name);

        $type = strtolower($type);

        if ($type == 'dao') {
            return $namespace . '\\' . $module . '\\dao\\impl\\' . $className . 'Impl';
        }

        return $namespace . '\\' . $module . '\\service\\impl\\' . $className . 'Impl';
    }
}
