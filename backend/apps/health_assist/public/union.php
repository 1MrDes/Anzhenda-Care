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

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");

define('IN_VM', true);
define('IN_UNION', true);
define('BIND_MODULE', 'union');
define('APP_NAME', 'health_assist');
define('MODULE_NAMESPACE', 'apps\health_assist\app\union');
define('MODULE_PATH', __DIR__ . '/../app/union/');
define('MODULE_NAME', 'union');

require __DIR__ . '/../global.php';
