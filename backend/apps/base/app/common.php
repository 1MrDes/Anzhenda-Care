<?php
// 应用公共文件
define('SERVICE_NAMESPACE', 'apps\base\core\service\\');
define('LOGIC_NAMESPACE', 'apps\base\core\logic\\');

require_once __DIR__ . '/../../../extend/vm/functions.php';

function base_api_get($app, $path, array $params = null)
{
    $cfg = config('api');
    $uri = $cfg['apps'][$app]['host'] . $path . '?token=' . $cfg['apps'][$app]['token'];
    if(!empty($params)) {
        $uri .= '&' . http_build_query($params);
    }
    $httpUtil = new \vm\org\HttpUtil();
    $response = $httpUtil->curl($uri);
    $ret = json_decode($response, true);
    if($ret['code'] == 0) {
        return $ret['data'];
    }
    throw new \think\Exception($ret['msg']);
}

function base_api_post($app, $path, array $data = null)
{
    $cfg = config('api');
    $uri = $cfg['apps'][$app]['host'] . $path . '?token=' . $cfg['apps'][$app]['token'];
    $httpUtil = new \vm\org\HttpUtil();
    $response = $httpUtil->curl($uri, \vm\org\HttpUtil::REQUEST_METHOD_POST, $data);
    $ret = json_decode($response, true);
    if($ret['code'] == 0) {
        return $ret['data'];
    }
    throw new \think\Exception($ret['msg']);
}