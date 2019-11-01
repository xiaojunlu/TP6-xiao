<?php

namespace app\biz\scheduler\dao\impl;

use app\biz\scheduler\dao\JobFiredDao;
use app\biz\common\dao\GeneralDaoImpl;

class JobFiredDaoImpl extends GeneralDaoImpl implements JobFiredDao
{
    protected $table = 'scheduler_job_fired';

    public function getByStatus($status)
    {
        $where = array(
            ['fired_time', '<=', strtotime('+1 minutes')],
            ['status', '=', $status]
        );
        $orderBys = array(
            'fired_time' => 'ASC',
            'priority'   => 'DESC',
        );
        return  $this->db()->table($this->table)->where($where)->order($orderBys)->find();
    }

    public function findByJobId($jobId)
    {
        return $this->findByFields(array(
            'job_id' => $jobId,
        ));
    }

    public function findByJobIdAndStatus($jobId, $status)
    {
        return $this->findByFields(array(
            'job_id' => $jobId,
            'status' => $status,
        ));
    }

    public function deleteUnacquiredBeforeCreatedTime($time)
    {
        return $this->db()->table($this->table)->where(array(
            ['created_time', '<', $time],
            ['status', '<>', 'acquired'],
        ))->delete();
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array('created_time', 'id'),
            'serializes' => array(
                'job_detail' => 'json',
            ),
            'conditions' => array(
                'job_id = :job_id',
                'status = :status',
                'fired_time < :fired_time_LT',
                'job_name = :job_name',
            ),
        );
    }
}
