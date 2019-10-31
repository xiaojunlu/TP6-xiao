<?php

namespace app\biz\common\dao;

use think\db\Query;
use think\db\Connection;

class DynamicQueryBuilder extends Query
{
    protected $conditions;

    public function __construct(Connection $connection, $conditions)
    {
        parent::__construct($connection);
        $this->conditions = $conditions;
    }

    public function andWhere($where)
    {
        $conditionName = $this->getConditionName($where);

        if (!$this->isWhereInConditions($where)) {
            return $this;
        }

        if ($this->matchNotInCondition($where)) {
            $columnName = $this->getColumnName($where);
            return parent::whereNotIn($columnName, $this->conditions[$conditionName]);
        }

        //in查询
        if ($this->matchInCondition($where)) {
            $columnName = $this->getColumnName($where);
            return parent::whereIn($columnName, $this->conditions[$conditionName]);
        }

        if ($likeType = $this->matchLikeCondition($where)) {
            return $this->addWhereLike($where, $likeType);
        }

        return parent::whereRaw($where, array($conditionName => $this->conditions[$conditionName]));
    }

    /**
     * 模糊查询
     *
     * @param [type] $where
     * @param [type] $likeType
     * @return void
     */
    private function addWhereLike($where, $likeType)
    {
        $conditionName = $this->getConditionName($where);

        //PRE_LIKE
        if ('pre_like' == $likeType) {
            $where = preg_replace('/pre_like/i', 'LIKE', $where, 1);
            $this->conditions[$conditionName] = "{$this->conditions[$conditionName]}%";
        } elseif ('suf_like' == $likeType) {
            $where = preg_replace('/suf_like/i', 'LIKE', $where, 1);
            $this->conditions[$conditionName] = "%{$this->conditions[$conditionName]}";
        } else {
            $this->conditions[$conditionName] = "%{$this->conditions[$conditionName]}%";
        }

        return parent::whereRaw($where);
    }

    private function isWhereInConditions($where)
    {
        $conditionName = $this->getConditionName($where);
        if (!$conditionName) {
            return false;
        }

        return array_key_exists($conditionName, $this->conditions) && !is_null($this->conditions[$conditionName]);
    }

    private function getConditionName($where)
    {
        $matched = preg_match('/:([a-zA-z0-9_]+)/', $where, $matches);
        if (!$matched) {
            return false;
        }

        return $matches[1];
    }

    protected function getColumnName($where)
    {
        $matched = preg_match('/([a-zA-z0-9_]+)/', $where, $matches);
        if (!$matched) {
            return false;
        }

        return $matches[1];
    }

    private function matchLikeCondition($where)
    {
        $matched = preg_match('/\s+((PRE_|SUF_)?LIKE)\s+/i', $where, $matches);
        if (!$matched) {
            return false;
        }

        return strtolower($matches[1]);
    }

    /**
     * 匹配not in 查询
     *
     * @param [type] $where
     * @return void
     */
    protected function matchNotInCondition($where)
    {
        $matched = preg_match('/\s+(NOT IN)\s+/i', $where, $matches);
        if (!$matched) {
            return false;
        }

        return strtolower($matches[1]);
    }

    /**
     * 匹配in查询
     *
     * @param [type] $where
     * @return void
     */
    protected function matchInCondition($where)
    {
        $matched = preg_match('/\s+(IN)\s+/i', $where, $matches);
        if (!$matched) {
            return false;
        }

        return strtolower($matches[1]);
    }
}
