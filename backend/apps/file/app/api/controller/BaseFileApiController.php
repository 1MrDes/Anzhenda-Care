<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/12 10:57
 * @description
 */

namespace apps\file\app\api\controller;


use think\Exception;
use vm\com\RestController;

abstract class BaseFileApiController extends RestController
{
    protected function init()
    {
        if(!defined('IN_API')) {
            die('hack attemping');
        }
        parent::init();
    }

    protected function auth()
    {
        $site = config('site');
        $token = request()->param('token', '');
        if(empty($token) || $token != $site['api_token']) {
            throw new Exception("token:".$token."\napi token:".$site['api_token']."\n非法访问");
        }
        return true;
    }
}