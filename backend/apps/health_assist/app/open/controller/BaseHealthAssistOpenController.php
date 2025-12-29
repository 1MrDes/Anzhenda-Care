<?php

namespace apps\health_assist\app\open\controller;

use vm\com\RestController;

abstract class BaseHealthAssistOpenController extends RestController
{
    protected function init()
    {
        if(!defined('IN_OPEN')) {
            die('hack attemping');
        }
        parent::init();
    }
}