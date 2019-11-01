<?php

namespace app\biz\scheduler\dao\impl;

use app\biz\scheduler\dao\JobPoolDao;
use app\biz\common\dao\GeneralDaoImpl;

class JobPoolDaoImpl extends GeneralDaoImpl implements JobPoolDao
{
    protected $table = 'scheduler_job_pool';

    public function getByName($name = 'default')
    {
        return $this->getByFields(array('name' => $name));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
        );
    }
}
