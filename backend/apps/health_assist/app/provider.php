<?php
use apps\health_assist\app\ExceptionHandle;
use apps\health_assist\app\Request;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
];
