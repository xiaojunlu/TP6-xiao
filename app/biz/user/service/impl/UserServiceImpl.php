<?php

namespace app\biz\user\service\impl;

use app\biz\BaseService;
use app\biz\user\service\UserService;

class UserServiceImpl extends BaseService implements UserService
{
    public function getUser($id, $lock = false)
    {
        $user = $this->getUserDao()->get($id, array('lock' => $lock));

        return !$user ? null : $user;
    }

    public function searchUsers(array $conditions, array $orderBy, $start, $limit, $columns = array())
    {
        return $this->getUserDao()->search($conditions, $orderBy, $start, $limit, $columns = array());
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createDao('user.UserDao');
    }
}
