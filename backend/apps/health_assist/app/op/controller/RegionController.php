<?php

namespace apps\health_assist\app\op\controller;

use vm\com\logic\RegionLogic;

class RegionController extends BaseHealthAssistOpController
{
    /**
     * @var RegionLogic
     */
    private $regionLogic;

    protected function init()
    {
        parent::init();
        $this->regionLogic = logic('Region', '\vm\com\logic\\');
        $this->regionLogic->init([
            'rpc_server' => env('rpc_base.host') . '/region',
            'rpc_provider' => env('rpc_base.provider'),
            'rpc_token' => env('rpc_base.token'),
        ]);
    }

    public function tree()
    {
        return $this->success(['regions' => $this->regionLogic->tree()]);
    }

    public function all()
    {
        return $this->success(['regions' => $this->regionLogic->all()]);
    }
}