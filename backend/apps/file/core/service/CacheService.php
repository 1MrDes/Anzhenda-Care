<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/13 16:37
 * @description
 */

namespace apps\file\core\service;

use apps\file\core\model\Cache;
use vm\com\BaseModel;
use vm\com\BaseService;

class CacheService extends BaseService
{
    /**
     * @return Cache|BaseModel
     */
    protected function getModel()
    {
        return new Cache();
    }

    public function set($name, $val, $expire = 0)
    {
        $cache = new Cache();
        $cache->name = $name;
        $cache->val = json_encode($val);
        $cache->expire = $expire;
        $result = $cache->replace()->save();
        return $result;
    }

    public function get($name, $default = null)
    {
        $data = $this->info([
            'name' => $name
        ]);
        if(empty($data)) {
            return $default;
        }
        $nowtime = time();
        if($data['expire'] > 0 && $nowtime > $data['expire']) {    // 已过期
            $this->rm($name);
            return $default;
        }
        $val = $data['val'] ? json_decode($data['val'], true) : '';
        return $val;
    }

    public function rm($name)
    {
        return $this->delete([
            'name' => $name
        ]);
    }
}