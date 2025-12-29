<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\HealthServiceBelongCategory;
use vm\com\BaseModel;
use vm\com\BaseService;

class HealthServiceBelongCategoryService extends BaseService
{
    /**
     * @return HealthServiceBelongCategory
     */
    protected function getModel()
    {
        return new HealthServiceBelongCategory();
    }
}