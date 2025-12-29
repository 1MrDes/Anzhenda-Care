<?php
return [
    'apps' => [
        'base' => [
            'host' => env('app_base.host', ''),     //  base应用
            'token' => env('app_base.token', ''),  // 内部通信接口密钥
        ],
        'file' => [
            'host' => env('app_file.host', ''),     //  file应用
            'token' => env('app_file.token', ''),  // 内部通信接口密钥
        ]
    ]
];