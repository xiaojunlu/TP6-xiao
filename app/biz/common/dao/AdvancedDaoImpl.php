<?php

namespace app\biz\common\dao;

use think\Db;

abstract class AdvancedDaoImpl extends GeneralDaoImpl implements AdvancedDaoInterface
{
    public function batchDelete(array $conditions)
    {
        $declares = $this->declares();
        $declareConditions = isset($declares['conditions']) ? $declares['conditions'] : array();
        array_walk($conditions, function (&$condition, $key) use ($declareConditions) {
            $isInDeclareCondition = false;
            foreach ($declareConditions as $declareCondition) {
                if (preg_match('/:' . $key . '/', $declareCondition)) {
                    $isInDeclareCondition = true;
                }
            }

            if (!$isInDeclareCondition) {
                $condition = null;
            }
        });

        $conditions = array_filter($conditions);

        if (empty($conditions) || empty($declareConditions)) {
            throw new DaoException('Please make sure at least one restricted condition');
        }

        return $this->createQueryBuilder($conditions)->delete();
    }

    public function batchCreate($rows)
    {
        if (empty($rows)) {
            return array();
        }

        $columns = array_keys(reset($rows));
        
        $this->checkFieldNames($columns);

        return $this->db()->table($this->table)->insertAll($rows);
    }

    public function batchUpdate($identifies, $updateColumnsList, $identifyColumn = 'id')
    {
        $updateColumns = array_keys(reset($updateColumnsList));

        $this->checkFieldNames($updateColumns);
        $this->checkFieldNames(array($identifyColumn));

        $count = count($identifies);
        $pageSize = 500;
        $pageCount = ceil($count / $pageSize);

        for ($i = 1; $i <= $pageCount; ++$i) {
            $start = ($i - 1) * $pageSize;
            $partIdentifies = array_slice($identifies, $start, $pageSize);
            $partUpdateColumnsList = array_slice($updateColumnsList, $start, $pageSize);
            $this->partUpdate($partIdentifies, $partUpdateColumnsList, $identifyColumn, $updateColumns);
        }
    }

    /**
     * @param $identifies
     * @param $updateColumnsList
     * @param $identifyColumn
     * @param $updateColumns
     *
     * @return int
     */
    private function partUpdate($identifies, $updateColumnsList, $identifyColumn, $updateColumns)
    {
        $sql = "UPDATE {$this->table} SET ";

        $updateSql = array();

        $params = array();
        foreach ($updateColumns as $updateColumn) {
            $caseWhenSql = "{$updateColumn} = CASE {$identifyColumn} ";

            foreach ($identifies as $identifyIndex => $identify) {
                $caseWhenSql .= ' WHEN ? THEN ? ';
                $params[] = $identify;
                $params[] = $updateColumnsList[$identifyIndex][$updateColumn];
                if ($identifyIndex === count($identifies) - 1) {
                    $caseWhenSql .= " ELSE {$updateColumn} END";
                }
            }

            $updateSql[] = $caseWhenSql;
        }

        $sql .= implode(',', $updateSql);

        $marks = str_repeat('?,', count($identifies) - 1) . '?';
        $sql .= " WHERE {$identifyColumn} IN ({$marks})";
        $params = array_merge($params, $identifies);

        return $this->db()->execute($sql, $params);
    }

    protected function checkFieldNames($names)
    {
        foreach ($names as $name) {
            if (!ctype_alnum(str_replace('_', '', $name))) {
                throw new \InvalidArgumentException('Field name is invalid.');
            }
        }

        return true;
    }
}
