<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [
            'vm\\com\\behavior\\AppInit'
        ],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogWrite' => [],
        'LogRecord' => [
            'vm\\com\\behavior\\LogRecord'
        ],
        'RouteLoaded' => []
    ],

    'subscribe' => [
    ],
];
