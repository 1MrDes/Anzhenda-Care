<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\UserLogging;
use vm\com\BaseModel;
use vm\com\BaseService;

class UserLoggingService extends BaseService
{

    /**
     * @inheritDoc
     */
    protected function getModel()
    {
        return new UserLogging();
    }
}