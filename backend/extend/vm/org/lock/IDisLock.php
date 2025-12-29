<?php
/**
 * 分布式锁接口定义
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/1/11 15:41
 * @description
 */

namespace vm\org\lock;


interface IDisLock
{
    /**
     * 加锁
     * @param string $key 锁key
     * @param int $timeout 锁超时时间，单位：毫秒
     * @return boolean
     */
    public function lock($key, $timeout = 0);

    /**
     * 释放锁
     * @param string $key 锁key
     * @return boolean
     */
    public function unlock($key);

    /**
     * 检测是否为加锁状态
     * @param string $key
     * @return boolean
     */
    public function recover($key);
}