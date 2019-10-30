<?php

namespace app\biz\common\dao;

interface GeneralDaoInterface
{
    /**
     * 插入新纪录
     *
     * @param [type] $fields
     * @return void
     */
    public function create(array $fields);

    /**
     * 更新记录
     *
     * @param [type] $identifier id、或条件
     * @param array $fields 更新数据
     * @return void
     */
    public function update($identifier, array $fields);

    /**
     * 删除记录
     *
     * @param [type] $id
     * @return void
     */
    public function delete($id);


    /**
     * 软删除记录
     *
     * @return void
     */
    public function softDelete($id);

    /**
     * 精确查询记录
     *
     * @param [type] $id
     * @param array $options
     * @return array
     */
    public function get($id, array $options = array());

    /**
     * 条件搜索数据
     *
     * @param [array] $conditions 条件
     * @param [type] $orderBys 排序
     * @param [type] $start 开始记录
     * @param [type] $limit 取得记录数
     * @param array $columns 标识查询返回字段
     * @return array
     */
    public function search(array $conditions, $orderBys, $start, $limit, $columns = array());

    /**
     * 统计记录数目
     *
     * @param [array] $conditions 条件
     * @return void
     */
    public function count(array $conditions);

    /**
     * 更新字段值
     *
     * @param array $ids 更新记录id数组
     * @param array $diffs array('hits' => +1)
     * @return void
     */
    public function wave(array $ids, array $diffs);


    /**
     * 分页查询
     *
     * @param [type] $conditions 条件
     * @param [type] $orderBys 排序
     * @param [type] $pageSize 单页记录数目
     * @param array $columns  标识查询返回字段
     * @return void
     */
    public function searchPage($conditions, $orderBys, $pageSize, $columns = array());
}
