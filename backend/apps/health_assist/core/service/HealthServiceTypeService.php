<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthServiceType;
use vm\com\BaseService;

class HealthServiceTypeService extends BaseService
{

    /**
     * @return HealthServiceType
     */
    protected function getModel()
    {
        return new HealthServiceType();
    }
}