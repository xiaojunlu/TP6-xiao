<?php

namespace app\biz\scheduler\dao;

use app\biz\common\dao\GeneralDaoInterface;

interface JobFiredDao extends GeneralDaoInterface
{
    public function getByStatus($status);

    public function findByJobId($jobId);

    public function findByJobIdAndStatus($jobId, $status);

    public function deleteUnacquiredBeforeCreatedTime($time);
}
