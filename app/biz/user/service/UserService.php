<?php

namespace app\biz\user\service;

interface UserService
{
    public function getUser($id, $lock = false);
}
