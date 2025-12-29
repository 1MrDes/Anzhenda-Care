<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/21 12:28
 * @description
 */

namespace vm\org\queue;


class QueueFactory
{
    /**
     * 获取实例
     * @param string $driver
     * @param array $cfg
     * @return null|IQueue
     */
    public static function instance($driver = '', array $cfg = [])
    {
        static $return = [];
        if ((empty($driver) && isset($return['default']) && $return['default'] instanceof IQueue)
            || ($driver && isset($return[$driver]) && $return[$driver] instanceof IQueue)) {
            return empty($driver) ? $return['default'] : $return[$driver];
        }
        $config = config('queue');
        if(!empty($cfg)) {
            $config = array_merge($config, $cfg);
        }
        $driver = empty($driver) ? $config['driver'] : $driver;

        $className = __NAMESPACE__ . '\\driver\\' . ucfirst($driver);
        if(empty($driver)) {
            return $return['default'] = new $className($config);
        } else {
            return $return[$driver] = new $className($config);
        }
    }
}