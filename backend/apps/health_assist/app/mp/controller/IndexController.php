<?php


namespace apps\health_assist\app\mp\controller;


class IndexController extends BaseHealthAssistMpController
{
    public function site_config()
    {
        $site = config('site');
        $weapp = config('weapp');
        $site['msg_template'] = $weapp['msg_template'];
        return $this->success(['site_config' => $site]);
    }
}