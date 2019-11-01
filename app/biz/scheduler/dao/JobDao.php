<?php

namespace app\biz\scheduler\dao;

use app\biz\common\dao\GeneralDaoInterface;

interface JobDao extends GeneralDaoInterface
{ 
    public function findWaitingJobsByLessThanFireTime($fireTime);
}
