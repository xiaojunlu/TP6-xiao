<?php

namespace app\biz\scheduler\dao;

use app\biz\common\dao\GeneralDaoInterface;

interface JobPoolDao extends GeneralDaoInterface
{
    public function getByName($name);
}
