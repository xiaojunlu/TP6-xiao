<?php

namespace app\biz\common\dao;

interface AdvancedDaoInterface
{
    /**
     * 批量删除记录
     *
     * @param array $conditions
     * @return void
     */
    public function batchDelete(array $conditions);

    /**
     * 批量添加记录
     *
     * @param [type] $rows
     * @return void
     */
    public function batchCreate($rows);

    /**
     * 批量更新记录
     *
     * @param [type] $identifies
     * @param [type] $updateColumnsList
     * @param string $identifyColumn
     * @return void
     */
    public function batchUpdate($identifies, $updateColumnsList, $identifyColumn = 'id');
}
