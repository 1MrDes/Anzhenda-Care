<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

define('IN_VM', true);
define('IN_API', true);
define('BIND_MODULE', 'api');
define('APP_NAME', 'base');
define('MODULE_NAMESPACE', 'apps\base\app\api');
define('MODULE_PATH', __DIR__ . '/../app/api/');
define('MODULE_NAME', 'api');

require __DIR__ . '/../global.php';
