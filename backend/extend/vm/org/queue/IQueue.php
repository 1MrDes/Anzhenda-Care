<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/21 12:29
 * @description
 */

namespace vm\org\queue;


interface IQueue
{
    public function setOption($key, $val);

    public function getOption($key);

    /**
     * 从队列中取一条消息
     * @param string $key 队列名
     * @return bool
     */
    public function get($key);

    /**
     * 往队列中存入一条消息
     * @param string $key 队列名
     * @param mixed $val 消息内容
     * @param int $flag 是否压缩
     * @return mixed
     */
    public function set($key, $val, $flag = 0);

    /**
     * 删除一个消息队列
     * @param unknown $key
     */
    public function delete($key);

    /**
     * 计算消息队列长度
     * @author 南极村民 < yinpoo@126.com >
     * @time   2016-04-07 12:49
     */
    public function length($key);

    /**
     * @see  返回列表 key 中指定区间内的元素，区间以偏移量 start 和 stop 指定。
     * @link http://redis.io/commands/lrange
     * @author 南极村民 < yinpoo@126.com >
     * @time   2016-04-07 12:49
     * @param string $key
     * @param int $start
     * @param int $end
     * @return bool
     */
    public function lists($key, $start, $end);

    /**
     * 清空消息队列
     */
    public function flush();
}