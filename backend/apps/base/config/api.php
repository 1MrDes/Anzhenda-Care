<?php
return [
    'apps' => [
        'file' => [
            'host' => env('app_file.host', ''),     //  file应用
            'token' => env('app_file.token', ''),  // 内部通信接口密钥
        ]
    ]
];