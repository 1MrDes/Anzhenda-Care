<?php
/**
 * 分布式锁的配置
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/1/11 16:03
 * @description
 */
return [
    'driver' => 'redis',
    'host' => env('redis.host', '127.0.0.1'),
    'port' => env('redis.port', 6379),
    'password' => env('redis.password', ''),
];