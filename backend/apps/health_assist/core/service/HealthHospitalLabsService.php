<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\HealthHospitalLabs;
use vm\com\BaseModel;
use vm\com\BaseService;

class HealthHospitalLabsService extends BaseService
{
    const CACHE_KEY_PREFIX = 'health_hospital_labs:';

    /**
     * @return HealthHospitalLabs
     */
    protected function getModel()
    {
        return new HealthHospitalLabs();
    }

    public function getByHospitalId($hospitalId)
    {
        if($data = cache(self::CACHE_KEY_PREFIX . $hospitalId)) {
            return $data;
        }
        $data = $this->getAll([
            'parent_id' => 0,
            'health_hospital_id' => $hospitalId
        ]);
        foreach ($data as &$datum) {
            $datum['children'] = $this->getAll([
                'parent_id' => $datum['id'],
                'health_hospital_id' => $hospitalId
            ]);
        }
        cache(self::CACHE_KEY_PREFIX . $hospitalId, $data, 3600*24*365);
        return $data;
    }
}