<?php
use apps\base\app\ExceptionHandle;
use apps\base\app\Request;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
];
