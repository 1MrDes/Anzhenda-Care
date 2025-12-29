<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\UserSystemNotice;
use vm\com\BaseService;

class UserSystemNoticeService extends BaseService
{
    /**
     * @return UserSystemNotice
     */
    protected function getModel()
    {
        return new UserSystemNotice();
    }
}