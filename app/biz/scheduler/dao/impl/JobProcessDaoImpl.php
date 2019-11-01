<?php

namespace app\biz\scheduler\dao\impl;

use app\biz\scheduler\dao\JobProcessDao;
use app\biz\common\dao\GeneralDaoImpl;

class JobProcessDaoImpl extends GeneralDaoImpl implements JobProcessDao
{
    protected $table = 'scheduler_job_process';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
        );
    }
}
