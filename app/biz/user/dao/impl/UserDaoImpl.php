<?php

namespace app\biz\user\dao\impl;

use app\biz\user\dao\UserDao;
use app\biz\common\dao\GeneralDaoImpl;

class UserDaoImpl extends GeneralDaoImpl implements UserDao
{
    protected $table = 'user';

    public function declares()
    {
        return array(
            'serializes' => array(
                'roles' => 'delimiter',
            ),
            'orderbys' => array(
                'id',
                'created_time',
                'updated_time',
                'login_time',
            ),
            'timestamps' => array(
                'created_time',
                'updated_time',
            ),
            'conditions' => array(
                'id = :id',
                'id > :id_GT',
                'id IN ( :ids)',
                'username LIKE :username',
                'roles LIKE :roles',
                'roles = :role',
                'roles <> :not_role',
                'locked = :locked',
                'login_ip = :login_ip',
                'created_time >= :start_date_time',
                'created_time <= :end_date_time',
                'type != :no_type',
                'created_time >= :start_time',
                'created_time <= :end_time',
            )
        );
    }
}
