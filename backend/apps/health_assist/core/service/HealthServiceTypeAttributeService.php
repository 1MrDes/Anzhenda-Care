<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthServiceTypeAttribute;
use vm\com\BaseService;

class HealthServiceTypeAttributeService extends BaseService
{

    /**
     * @return HealthServiceTypeAttribute
     */
    protected function getModel()
    {
        return new HealthServiceTypeAttribute();
    }

    public function findAll($typeId)
    {
        return $this->getModel()->findAll($typeId);
    }
}