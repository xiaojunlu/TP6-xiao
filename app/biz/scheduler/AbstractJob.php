<?php

namespace app\biz\scheduler;

use think\facade\Db;
use app\biz\common\ServiceKernel;

abstract class AbstractJob implements Job, \ArrayAccess
{
    const SUCCESS = 'success';
    const FAILURE = 'failure';
    const RETRY = 'retry';

    private $params = array();

    public function __construct($params = array())
    {
        $this->params = $params;
    }

    abstract public function execute();

    public function __get($name)
    {
        return empty($this->params[$name]) ? '' : $this->params[$name];
    }

    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->params[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }

    protected function beginTransaction()
    {
        Db::startTrans();
    }

    protected function commit()
    {
        Db::commit();
    }

    protected function rollback()
    {
        Db::rollback();
    }

    protected function createService($alias)
    {
        return $this->getServiceKernel()->createService($alias);
    }

    protected function createDao($alias)
    {
        return $this->getServiceKernel()->createDao($alias);
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
