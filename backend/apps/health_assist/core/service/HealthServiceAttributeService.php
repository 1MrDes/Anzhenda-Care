<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthServiceAttribute;
use vm\com\BaseModel;
use vm\com\BaseService;

class HealthServiceAttributeService extends BaseService
{

    /**
     * @return HealthServiceAttribute
     */
    protected function getModel()
    {
        return new HealthServiceAttribute();
    }

    public function saveHealthServiceAttrs($healthServiceId, array $attrs)
    {
        foreach ($attrs as $item) {
            $item['health_service_id'] = $healthServiceId;
            $this->create($item);
        }
    }

    public function getByHealthServiceId($healthServiceId)
    {
        return $this->getAll(['health_service_id' => $healthServiceId]);
    }
}