<?php


namespace apps\base\app\api\controller;


use think\Exception;
use vm\com\RestController;

abstract class BaseApiController extends RestController
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