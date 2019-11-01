<?php

namespace app\biz\scheduler\dao\impl;

use app\biz\scheduler\dao\JobLogDao;
use app\biz\common\dao\GeneralDaoImpl;

class JobLogDaoImpl extends GeneralDaoImpl implements JobLogDao
{
    protected $table = 'scheduler_job_log';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'serializes' => array(
                'args' => 'json',
            ),
            'orderbys' => array('created_time', 'id'),
            'conditions' => array(
                'job_fired_id = :job_fired_id ',
            ),
        );
    }
}
