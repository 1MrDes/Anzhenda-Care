<?php


namespace apps\health_assist\app\mp\controller;


use think\Exception;

class TestController extends BaseHealthAssistMpController
{

    protected function init()
    {
        parent::init();
        if(!isCli()) {
            throw new Exception('hack attempt.');
        }
    }
}