<?php

use app\handler\ExceptionHandle;
use app\Request;
use app\Viewer;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'api_response_viewer'    => Viewer::class,
];
