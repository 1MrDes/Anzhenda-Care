<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2018/11/7
 * Time: 15:23
 */

namespace vm\org;


use think\cache\driver\Redis;

class RedisCache extends Redis
{

    public function keys($pattern)
    {
        return $this->handler->keys($pattern);
    }

    public function deleteByPattern($pattern)
    {
        $keys = $this->keys($pattern);
        if(empty($keys)) {
            return 0;
        }
        return $this->handler->delete($keys);
    }
}