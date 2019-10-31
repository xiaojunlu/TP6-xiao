<?php

namespace app\biz\common\dao;

abstract class GeneralDaoImpl implements GeneralDaoInterface
{
    protected $table = null;

    public function db()
    {
        return app('db');
    }

    /**
     * 插入记录
     *
     * @param array $fields
     * @return void
     */
    public function create(array $fields)
    {
        $affected = $this->db()->table($this->table)->strict(false)->insert($fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert error.');
        }

        $lastInsertId = isset($fields['id']) ? $fields['id'] : $this->db()->table($this->table)->getLastInsID();

        return $this->get($lastInsertId);
    }

    /**
     * 根据主键id获取记录
     *
     * @param [type] $id     主键id
     * @param array $options
     * @return void
     */
    public function get($id, array $options = array())
    {
        //是否加排他锁
        $lock = isset($options['lock']) && true === $options['lock'];
        if ($lock) {
            return $this->db()->table($this->table)->where(array("id" => $id))->lock(true)->find() ?: null;
        }

        return $this->db()->table($this->table)->where(array("id" => $id))->find() ?: null;
    }

    /**
     * 根据主键id删除记录
     *
     * @param [type] $id
     * @return void
     */
    public function delete($id)
    {
        return $this->db()->table($this->table)->delete($id);
    }

    /**
     * 根据条件删除记录
     *
     * @param array $conditions
     * @return void
     */
    public function deleteByConditions(array $conditions)
    {
        return $this->db()->table($this->table)->where($conditions)->delete();
    }

    /**
     * 更新记录
     *
     * @param [type] $identifier  更新标识
     * @param array $fields       更新字段
     * @return void
     */
    public function update($identifier, array $fields)
    {
        if (empty($identifier)) {
            return 0;
        }

        //根据主键id更新数据
        if (is_numeric($identifier) || is_string($identifier)) {
            return $this->updateById($identifier, $fields);
        }

        //根据条件更新数据
        if (is_array($identifier)) {
            return $this->updateByConditions($identifier, $fields);
        }
        throw new \Exception('update arguments type error');
    }

    /**
     * 根据主键id更新数据
     *
     * @param [type] $id
     * @param array $fields
     * @return void
     */
    protected function updateById($id, array $fields)
    {
        $this->db()->table($this->table)->where('id', $id)->update($fields);

        return $this->get($id);
    }

    /**
     * 根据查询条件更新数据
     *
     * @param array $conditions
     * @param array $fields
     * @return void
     */
    protected function updateByConditions(array $conditions, array $fields)
    {
        return $this->createQueryBuilder($conditions)->update($fields);
    }

    /**
     * 根据查询条件获取记录列表
     *
     * @param [array] $conditions 条件
     * @param [type] $orderBys 排序
     * @param [type] $start 开始记录 
     * @param [type] $limit 查询数量
     * @param array $columns 返回指定字段
     * @return void
     */
    public function search(array $conditions, $orderBys, $start, $limit, $columns = array())
    {
        $builder = $this->createQueryBuilder($conditions)
            ->limit($start, $limit);

        $this->addSelect($builder, $columns);

        $declares = $this->declares();
        foreach ($orderBys ?: array() as $order => $sort) {
            $this->checkOrderBy($order, $sort, $declares['orderbys']);
            $builder->order($order, $sort);
        }

        return  $builder->select()->toArray();
    }

    /**
     * 根据查询条件统计记录
     *
     * @param [type] $conditions
     * @return void
     */
    public function count(array $conditions)
    {
        $builder =  $this->createQueryBuilder($conditions);
        return $builder->count();
    }

    /**
     * 字段in查询获取记录
     *
     * @param [type] $field   字段名
     * @param [type] $values  字段查询值范围
     * @return void
     */
    protected function findInField($field, $values)
    {
        if (empty($values)) {
            return array();
        }
        return $this->db()->table($this->table)->where($field, 'in', $values)->select()->toArray();
    }

    /**
     * 根据字段和对应值获取记录数据  返回二维数据
     *
     * @param [type] $fields
     * @return void
     */
    protected function findByFields($fields)
    {
        return $this->db()->table($this->table)->where($fields)->select()->toArray();
    }

    /**
     * 根据字段和对应值获取记录  如getByIdAndType array('id' => $id, 'type' => $type)
     *
     * @param [type] $fields
     * @return void
     */
    protected function getByFields($fields)
    {
        return $this->db()->table($this->table)->where($fields)->find() ?: null;
    }


    /**
     * 字段更新（增减） +1 表示自增1  -1表示自减1
     *
     * @param array $ids  更新的记录id值 数组
     * @param array $diffs array('hits' => +1)
     * @return void
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

        return $this->db()->execute($sql, array_merge(array_values($diffs), $ids));
    }

    /**
     * 指定记录返回字段
     *
     * @param [type] $builder
     * @param [type] $columns
     * @return void
     */
    private function addSelect($builder, $columns)
    {
        if (!$columns) {
            return $builder->field('*');
        }

        foreach ($columns as $column) {
            if (!preg_match('/^\w+$/', $column)) {
                throw $this->createDaoException('Illegal column name: ' . $column);
            }
        }

        return $builder->field($columns);
    }

    protected function filterStartLimit(&$start, &$limit)
    {
        $start = (int) $start;
        $limit = (int) $limit;
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

        $builder = $this->getQueryBuilder($conditions)->table($this->table);

        $declares = $this->declares();
        $declares['conditions'] = isset($declares['conditions']) ? $declares['conditions'] : array();

        foreach ($declares['conditions'] as $condition) {
            $builder->andWhere($condition);
        }

        return $builder;
    }

    private function createDaoException($message = '', $code = 0)
    {
        return new DaoException($message, $code);
    }

    protected function getQueryBuilder($conditions)
    {
        return new DynamicQueryBuilder($this->db()->getConnection(), $conditions);
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
}
