<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/21 13:07
 * @description
 */
return [
    // 驱动方式
    'driver'   => 'redis',
    // 服务器地址
    'host'       => env('redis.host', '127.0.0.1'),
    'port'       => env('redis.port', 6379),
    'password'   => env('redis.password', ''),
    'prefix' => 'vm_base:queue:',
    'queues' => [
        'mail' => 'vm_mails',
        'sms' => 'vm_sms',
    ]
];