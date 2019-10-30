<?php

namespace app\biz\common\dao;

use think\facade\Db;

abstract class GeneralDaoImpl implements GeneralDaoInterface
{
    protected $table = null;

    /**
     * @return Connection
     */
    public function db()
    {
        return Db::connect('mysql')->table('user')->find();
    }

    /**
     * 插入数据
     *
     * @param [type] $fields
     * @return void
     * @Description
     */
    public function create(array $fields)
    {
        $affected = Db::table($this->table)->strict(false)->insert($fields);
        if ($affected <= 0) {
            throw new \Exception('Insert error.', 0);
        }

        $lastInsertId = isset($fields['id']) ? $fields['id'] : Db::table($this->table)->getLastInsID();

        return $this->get($lastInsertId);
    }

    public function get($id, array $options = array())
    {
        //是否加锁
        $lock = isset($options['lock']) && true === $options['lock'];

        if ($lock) {
            return Db::table($this->table)->where(array("id" => $id))->lock(true)->find() ?: null;
        }

        return Db::table($this->table)->where(array("id" => $id))->find() ?: null;
    }

    /**
     * 根据主键id删除记录
     *
     * @param [type] $id
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public function delete($id)
    {
        return Db::table($this->table)->delete($id);
    }


    /**
     * 根据条件删除记录
     *
     * @param array $conditions
     * @return void
     * @description 
     * @author
     */
    public function deleteByConditions(array $conditions)
    {
        return Db::table($this->table)->where($conditions)->delete();
    }

    /**
     * 软删除
     *
     * @param [type] $conditions
     * @return void
     * @description 
     * @author
     */
    public function softDelete($conditions)
    {
        if (!isset($this->deleteTime)) {
            throw new \Exception('deleteTime hit.', 0);
        }
        return Db::table($this->table)->where($conditions)->useSoftDelete($this->deleteTime, time())->delete();
    }


    /**
     * 更新数据
     *
     * @param [type] $identifier 标识符
     * @param array $fields 数据
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public function update($identifier, array $fields)
    {
        if (empty($identifier)) {
            return 0;
        }

        if (is_numeric($identifier) || is_string($identifier)) {
            return $this->updateById($identifier, $fields); //根据id更新数据
        }

        if (is_array($identifier)) {
            return $this->updateByConditions($identifier, $fields); //根据条件更新数据
        }
        throw new \Exception('update arguments type error');
    }

    /**
     * 根据id更新数据
     *
     * @param [type] $id
     * @param [type] $fields
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    protected function updateById($id, array $fields)
    {
        Db::table($this->table)->where('id', $id)->update($fields);
        return $this->get($id);
    }

    /**
     * 根据查询条件更新数据
     *
     * @param array $conditions
     * @param array $fields
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    protected function updateByConditions(array $conditions, array $fields)
    {
        return  $this->createQueryBuilder($conditions)->update($fields);
    }

    /**
     * 查询数据
     *
     * @param [array] $conditions 条件
     * @param [type] $orderBys 排序
     * @param [type] $start 开始记录 
     * @param [type] $limit 查询数量
     * @param array $columns 返回指定字段
     * @return void
     * @description 
     * @author
     */
    public function search(array $conditions, $orderBys, $start, $limit, $columns = array())
    {
        $builder =  $this->createQueryBuilder($conditions);
        return  $builder->field($columns)->order($orderBys)->limit($start, $limit)->select();
    }

    /**
     * 统计
     *
     * @param [type] $conditions
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public function count(array $conditions)
    {
        $builder =  $this->createQueryBuilder($conditions);
        return $builder->count();
    }


    /**
     * 根据数组查询相关字段的数据
     *
     * @param [type] $field     例如findByIds
     * @param [type] $values    $ids = array(1,2,3)
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    protected function findInField($field, $values)
    {
        if (empty($values)) {
            return array();
        }

        return Db::table($this->table)->where($field, 'in', $values)->select();
    }


    /**
     * 根据条件查询数据
     *
     * @param [type] $fields array('title' => $title)
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    protected function findByFields($fields)
    {
        return Db::table($this->table)->where($fields)->select();
    }


    /**
     * 根据条件查询一条数据
     *
     * @param [type] $fields getByIdAndType array('id' => $id, 'type' => $type)
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    protected function getByFields($fields)
    {
        return Db::table($this->table)->where($fields)->find() ?: null;
    }


    /**
     * 字段更新（增减） +1 表示自增1  -1表示自减1
     *
     * @param array $ids  更新的记录id值 数组
     * @param array $diffs array('hits' => +1)
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public function wave(array $ids, array $diffs)
    {
        $sets = array_map(
            function ($name) {
                return "{$name} = {$name} + ?";
            },
            array_keys($diffs)
        );

        $marks = str_repeat('?,', count($ids) - 1) . '?';

        $sql = "UPDATE $this->table SET " . implode(', ', $sets) . " WHERE id IN ($marks)";

        return Db::execute($sql, array_merge(array_values($diffs), $ids));
    }

    /**
     * 查询数据带分页
     *
     * @param [type] $conditions 条件
     * @param string $orderBys 排序
     * @param integer $pageSize 
     * @param array $join
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public function searchPage($conditions, $orderBys = '', $pageSize = 20, $filed = ['*'])
    {
        $config = array(
            'query' => input('param.'),
            'type' => 'bootstrap', //分页类名
            'var_page' => 'page' //分页变量
        );
        return Db::table($this->table)->alias('a')->field($filed)->where($conditions)->order($orderBys)->paginate($pageSize, false, $config);
    }

    /**
     * @param $sql
     * @return mixed
     * 执行原生的sql
     */
    public function query($sql)
    {
        return Db::query($sql);
    }

    public function execute($sql)
    {
        return Db::execute($sql);
    }

    public function table()
    {
        return $this->table;
    }

    /**
     * 创建查询构造器
     *
     * @param [type] $conditions
     * @return void
     */
    protected function createQueryBuilder(array $conditions)
    {
        $conditions = array_filter(
            $conditions,
            function ($value) {
                if ('' === $value || null === $value) {
                    return false;
                }

                if (is_array($value) && empty($value)) {
                    return false;
                }

                return true;
            }
        );

        $builder = Db::table($this->table);

        $declares = $this->declares();
        $declares['conditions'] = isset($declares['conditions']) ? $declares['conditions'] : array();

        foreach ($declares['conditions'] as $condition) {
            $conditionName = $this->getConditionName($condition);
            if (!$this->isWhereInConditions($condition, $conditions)) {
                continue;
            }

            // dump($this->matchNotInCondition($condition));

            if ($this->matchNotInCondition($condition)) {
                $columnName = $this->getColumnName($condition);
                $builder->whereNotIn($columnName, $conditions[$conditionName]);
                continue;
            }

            //in查询
            if ($this->matchInCondition($condition)) {
                $columnName = $this->getColumnName($condition);
                $builder->whereIn($columnName, $conditions[$conditionName]);
                continue;
            }

            //模糊查询
            if ($likeType = $this->matchLikeCondition($condition)) {
                //PRE_LIKE
                if ('pre_like' == $likeType) {
                    $condition = preg_replace('/pre_like/i', 'LIKE', $condition, 1);
                    $conditions[$conditionName] = "{$conditions[$conditionName]}%";
                } elseif ('suf_like' == $likeType) {
                    $condition = preg_replace('/suf_like/i', 'LIKE', $condition, 1);
                    $conditions[$conditionName] = "%{$conditions[$conditionName]}";
                } else {
                    $conditions[$conditionName] = "%{$conditions[$conditionName]}%";
                }
            }


            $builder->whereRaw($condition, array($conditionName => $conditions[$conditionName]));
        }

        return $builder;
    }

    private function createDaoException($message = '', $code = 0)
    {
        return new DaoException($message, $code);
    }

    private function checkOrderBy($order, $sort, $allowOrderBys)
    {
        if (!in_array($order, $allowOrderBys, true)) {
            throw $this->createDaoException(
                sprintf("SQL order by field is only allowed '%s', but you give `{$order}`.", implode(',', $allowOrderBys))
            );
        }
        if (!in_array(strtoupper($sort), array('ASC', 'DESC'), true)) {
            throw $this->createDaoException("SQL order by direction is only allowed `ASC`, `DESC`, but you give `{$sort}`.");
        }
    }

    protected function getConditionName($where)
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

    protected function isWhereInConditions($where, $conditions)
    {
        $conditionName = $this->getConditionName($where);
        if (!$conditionName) {
            return false;
        }

        return array_key_exists($conditionName, $conditions) && !is_null($conditions[$conditionName]);
    }

    protected function matchLikeCondition($where)
    {
        $matched = preg_match('/\s+((PRE_|SUF_)?LIKE)\s+/i', $where, $matches);
        if (!$matched) {
            return false;
        }

        return strtolower($matches[1]);
    }

    protected function matchInCondition($where)
    {
        $matched = preg_match('/\s+(IN)\s+/i', $where, $matches);
        if (!$matched) {
            return false;
        }

        return strtolower($matches[1]);
    }

    protected function matchNotInCondition($where)
    {
        $matched = preg_match('/\s+(NOT IN)\s+/i', $where, $matches);
        if (!$matched) {
            return false;
        }

        return strtolower($matches[1]);
    }
}
