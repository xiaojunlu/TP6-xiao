<?php

namespace app\biz\scheduler\dao\impl;

use app\biz\scheduler\dao\JobDao;
use app\biz\common\dao\GeneralDaoImpl;

class JobDaoImpl extends GeneralDaoImpl implements JobDao
{
  protected $table = 'scheduler_job';

  public function findWaitingJobsByLessThanFireTime($fireTime)
  {
    $sql = "SELECT * FROM 
                (
                  SELECT * FROM {$this->table} 
                  WHERE enabled = 1 AND next_fire_time <= ?
                ) as {$this->table} 
                ORDER BY priority DESC , next_fire_time ASC";

    return $this->db()->table($this->table)->query($sql, array($fireTime));
  }

  public function declares()
  {
    return array(
      'timestamps' => array('created_time', 'updated_time'),
      'serializes' => array(
        'args' => 'json',
      ),
      'conditions' => array(
        'name LIKE :name',
        'class LIKE :class',
        'source LIKE :source',
        'enabled = :enabled',
        'next_fire_time <= :next_fire_time_LE',
      ),
    );
  }
}
