<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/12 10:48
 * @description
 */

namespace vm\com\behavior;


class ModuleInit
{
    public function run()
    {
        if(!defined('MODULE_NAME')) {
            define('MODULE_NAME', request()->module());
        }
        if(!defined('CONTROLLER_NAME')) {
            define('CONTROLLER_NAME', request()->controller());
        }
    }
}