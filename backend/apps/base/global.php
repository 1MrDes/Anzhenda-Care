<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/30 23:58
 * @description
 */
namespace think;

defined('IN_VM') or die('hack attemping');

// 定义应用目录
define('DOC_PATH', __DIR__ . '/');
define('APP_PATH', __DIR__ . '/app/');
define('EXTEND_PATH', __DIR__ . '/../../extend/');
define('VENDOR_PATH', __DIR__ . '/../../vendor/');
define('WWW_PATH', __DIR__ . '/public/');

require __DIR__ . '/../../vendor/autoload.php';

// 执行HTTP应用并响应
$app = new App(__DIR__ . '/');
$app->setNamespace(MODULE_NAMESPACE);
$http = $app->http;

$response = $http->path(MODULE_PATH)->run();

$response->send();

$http->end($response);