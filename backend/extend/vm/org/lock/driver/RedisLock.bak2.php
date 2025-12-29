<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/1/11 15:45
 * @description
 */

namespace vm\org\lock\driver;


use think\Exception;
use vm\org\lock\IDisLock;

class RedisLock implements IDisLock
{
    private $_config;
    private $retryDelay;
    private $retryCount;
    private $clockDriftFactor = 0.01;

    private $quorum;

    private $servers = array();
    private $instances = array();

    public function __construct(array $config)
    {
        $this->_config = $config;
        $servers = [
            [$this->_config['host'], $this->_config['port'], $this->_config['password'], 0.01],
        ];
        $this->init($servers);
    }

    private function init(array $servers, $retryDelay = 200, $retryCount = 3)
    {
        $this->servers = $servers;

        $this->retryDelay = $retryDelay;
        $this->retryCount = $retryCount;

        $this->quorum = min(count($servers), (count($servers) / 2 + 1));
    }

    /**
     * 加锁
     * @param string $resource 锁key
     * @param int $ttl 锁超时时间，单位：毫秒
     * @return false|array
     */
    public function lock($resource, $ttl = 0)
    {
        $this->initInstances();

//        $token = uniqid();
        $token = md5($resource);
        $retry = $this->retryCount;

        do {
            $n = 0;

            $startTime = microtime(true) * 1000;

            foreach ($this->instances as $instance) {
                if ($this->lockInstance($instance, $resource, $token, $ttl)) {
                    $n++;
                }
            }

            # Add 2 milliseconds to the drift to account for Redis expires
            # precision, which is 1 millisecond, plus 1 millisecond min drift
            # for small TTLs.
            $drift = ($ttl * $this->clockDriftFactor) + 2;

            $validityTime = $ttl - (microtime(true) * 1000 - $startTime) - $drift;

            if ($n >= $this->quorum && $validityTime > 0) {
//                return [
//                    'validity' => $validityTime,
//                    'resource' => $resource,
//                    'token' => $token,
//                ];
                return true;
            } else {
                foreach ($this->instances as $instance) {
                    $this->unlockInstance($instance, $resource, $token);
                }
            }

            // Wait a random delay before to retry
            $delay = mt_rand(floor($this->retryDelay / 2), $this->retryDelay);
            usleep($delay * 1000);

            $retry--;

        } while ($retry > 0);

        return false;
    }

    /**
     * 释放锁
     * @param string $resource
     * @return bool|void
     * @throws Exception
     */
    public function unlock($resource)
    {
        $this->initInstances();
//        $resource = $lock['resource'];
//        $token = $lock['token'];
        $token = md5($resource);

        foreach ($this->instances as $instance) {
            $this->unlockInstance($instance, $resource, $token);
        }
    }

    private function initInstances()
    {
        if (empty($this->instances)) {
            foreach ($this->servers as $server) {
                list($host, $port, $password, $timeout) = $server;
                $redis = new \Redis();
                $redis->connect($host, $port, $timeout);
                if(!empty($password) && !$redis->auth($password)) {
                    throw new Exception('redis无法连接');
                }

                $this->instances[] = $redis;
            }
        }
    }

    private function lockInstance($instance, $resource, $token, $ttl)
    {
        return $instance->set($resource, $token, ['NX', 'PX' => $ttl]);
    }

    private function unlockInstance($instance, $resource, $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        return $instance->eval($script, [$resource, $token], 1);
    }

    public function recover($key)
    {
        // TODO: Implement recover() method.
    }

    private function setCache($key, $val, $ttl)
    {
        return cache($key, $val, $ttl);
    }

    private function getCache($key)
    {
        return cache($key);
    }
}