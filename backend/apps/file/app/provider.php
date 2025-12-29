<?php
use apps\file\app\ExceptionHandle;
use apps\file\app\Request;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
];
