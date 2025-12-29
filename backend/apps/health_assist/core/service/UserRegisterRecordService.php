<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\UserRegisterRecord;
use vm\com\BaseModel;
use vm\com\BaseService;

class UserRegisterRecordService extends BaseService
{

    /**
     * @return UserRegisterRecord
     */
    protected function getModel()
    {
        return new UserRegisterRecord();
    }

}