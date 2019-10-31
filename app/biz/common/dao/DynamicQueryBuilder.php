<?php

namespace app\biz\common\dao;

use think\Db\Connection;
use think\Db\Query;

class DynamicQueryBuilder extends Query
{
    protected $conditions;

    public function __construct(Connection $connection, $conditions)
    {
        parent::__construct($connection);
        $this->conditions = $conditions;
    }

    // public function where($where)
    // {
    //     if (!$this->isWhereInConditions($where)) {
    //         return $this;
    //     }

    //     return $this->db->where($where);
    // }

    public function andWhere($where)
    {
        $conditionName = $this->getConditionName($where);

        if (!$this->isWhereInConditions($where)) {
            return $this;
        }

        // if ($this->matchNotInCondition($where)) {
        //     $columnName = $this->getColumnName($where);
        //     $this->db->whereNotIn($columnName, $this->conditions[$conditionName]);
        //     return $this;
        // }

        //in查询
        // if ($this->matchInCondition($where)) {
        //     $columnName = $this->getColumnName($where);
        //     $this->db->whereIn($columnName, $this->conditions[$conditionName]);
        //     return $this;
        // }

        //模糊查询
        // if ($likeType = $this->matchLikeCondition($condition)) {
        //     //PRE_LIKE
        //     if ('pre_like' == $likeType) {
        //         $condition = preg_replace('/pre_like/i', 'LIKE', $condition, 1);
        //         $conditions[$conditionName] = "{$conditions[$conditionName]}%";
        //     } elseif ('suf_like' == $likeType) {
        //         $condition = preg_replace('/suf_like/i', 'LIKE', $condition, 1);
        //         $conditions[$conditionName] = "%{$conditions[$conditionName]}";
        //     } else {
        //         $conditions[$conditionName] = "%{$conditions[$conditionName]}%";
        //     }
        // }


        return   $query->whereRaw($where, array($conditionName => $this->conditions[$conditionName]));


        if ($likeType = $this->matchLikeCondition($where)) {
            return $this->addWhereLike($where, $likeType);
        }

        return  $this->db->where($where);
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
}
