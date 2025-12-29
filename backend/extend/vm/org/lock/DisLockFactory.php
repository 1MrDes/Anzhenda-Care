<?php
/**
 * 分布式锁工厂类
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/1/11 15:43
 * @description
 */

namespace vm\org\lock;

class DisLockFactory
{

    /**
     * 获取一个分布式锁实例
     * @param string $driver
     * @param array $cfg
     * @return null|IDisLock
     */
    public static function instance($driver = '', array $cfg = array())
    {
        static $return = [];
        if ((empty($driver) && isset($return['default']) && $return['default'] instanceof IDisLock)
            || ($driver && isset($return[$driver]) && $return[$driver] instanceof IDisLock)) {
            return empty($driver) ? $return['default'] : $return[$driver];
        }
        $config = config('lock');
        if(!empty($cfg)) {
            $config = array_merge($config, $cfg);
        }
        $driver = empty($driver) ? $config['driver'] : $driver;

        $className = __NAMESPACE__ . '\\driver\\' . ucfirst($driver) . 'Lock';
        if(empty($driver)) {
            return $return['default'] = new $className($config);
        } else {
            return $return[$driver] = new $className($config);
        }
    }

}