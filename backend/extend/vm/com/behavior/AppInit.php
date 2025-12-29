<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/11 16:29
 * @description
 */

namespace vm\com\behavior;


use think\facade\Env;

class AppInit
{
    public function handle()
    {
        if(!defined('ROOT_PATH')) {
            define('ROOT_PATH', Env::get('root_path'));
        }
        if(!defined('ENV')) {
            define('ENV', Env::get('ENV'));
        }
    }
}