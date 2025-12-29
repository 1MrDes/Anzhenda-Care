<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/21 12:46
 * @description
 */

namespace vm\org\queue\driver;

use vm\org\queue\IQueue;

class Redis implements IQueue
{
    /**
     * @var null|\Predis\Client|\Redis
     */
    protected $handler = null;
    protected $options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'persistent' => false,
        'prefix'     => ''
    ];

    public function __construct($options)
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        if (extension_loaded('redis')) {
            $this->handler = new \Redis;

            if ($this->options['persistent']) {
                $this->handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
            } else {
                $this->handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
            }

            if ('' != $this->options['password']) {
                $this->handler->auth($this->options['password']);
            }

            if (0 != $this->options['select']) {
                $this->handler->select($this->options['select']);
            }
        } elseif (class_exists('\Predis\Client')) {
            $params = [];
            foreach ($this->options as $key => $val) {
                if (in_array($key, ['aggregate', 'cluster', 'connections', 'exceptions', 'prefix', 'profile', 'replication'])) {
                    $params[$key] = $val;
                    unset($this->options[$key]);
                }
            }
            $this->handler = new \Predis\Client($this->options, $params);
        } else {
            throw new \BadFunctionCallException('not support: redis');
        }
    }

    public function __destruct()
    {
        if ($this->handler !== null) {
            $this->handler->close();
        }
    }

    public function setOption($key, $val)
    {
        $this->options[$key] = $val;
    }

    public function getOption($key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    /**
     * 从队列中取一条消息
     * @param string $key 队列名
     * @return bool
     */
    public function get($key)
    {
        if ($this->handler === null) {
            return null;
        }
        $key = $this->options['prefix'] . $key;
        $result = $this->handler->rPop($key);
        return $result ? json_decode($result, true) : null;
    }

    /**
     * 往队列中存入一条消息
     * @param string $key 队列名
     * @param mixed $val 消息内容
     * @param int $flag 是否压缩
     * @return mixed
     */
    public function set($key, $val, $flag = 0)
    {
        if ($this->handler === null) {
            return false;
        }
        $key = $this->options['prefix'] . $key;
        $val = json_encode($val);
        return $this->handler->lPush($key, $val);
    }

    /**
     * 删除一个队列
     * @param $key
     * @return bool|int
     */
    public function delete($key)
    {
        if ($this->handler === null) {
            return false;
        }
        $key = $this->options['prefix'] . $key;
        return $this->handler->delete($key);
    }

    /**
     * 计算消息队列长度
     * @author 南极村民 < yinpoo@126.com >
     * @time   2016-04-07 12:49
     */
    public function length($key)
    {
        if ($this->handler === null) {
            return 0;
        }
        $key = $this->options['prefix'] . $key;
        return $this->handler->lLen($key);
    }

    /**
     * 返回列表 key 中指定区间内的元素，区间以偏移量 start 和 stop 指定。
     * @param string $key
     * @param int $start
     * @param int $end
     * @return array|bool
     */
    public function lists($key, $start, $end)
    {
        if ($this->handler === null) {
            return false;
        }
        $key = $this->options['prefix'] . $key;
        return $this->handler->lRange($key, $start, $end);
    }

    /**
     * 清空消息队列
     */
    public function flush()
    {
        return false;
    }
}