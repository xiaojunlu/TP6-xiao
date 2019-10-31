<?php

namespace app\biz\user\service;

interface UserService
{
    public function getUser($id, $lock = false);

    public function searchUsers(array $conditions, array $orderBy, $start, $limit, $columns = array());
}
