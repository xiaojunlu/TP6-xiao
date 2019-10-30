<?php

namespace app\biz\common\dao;

interface SerializerInterface
{
    public function serialize($method, $value);

    public function unserialize($method, $value);
}
