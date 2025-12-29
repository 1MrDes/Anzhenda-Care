<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\Consigneer;
use think\Exception;
use vm\com\BaseService;
use vm\com\logic\RegionLogic;

class ConsigneerService extends BaseService
{
    /**
     * @var RegionLogic
     */
    private $regionLogic;

    /**
     * @return Consigneer
     */
    protected function getModel()
    {
        return new Consigneer();
    }

    private function getRegionLogic()
    {
        if($this->regionLogic !== null) {
            return $this->regionLogic;
        }
        $this->regionLogic = logic('Region', '\vm\com\logic\\');
        $this->regionLogic->init([
            'rpc_server' => env('rpc_base.host') . '/region',
            'rpc_provider' => env('rpc_base.provider'),
            'rpc_token' => env('rpc_base.token'),
        ]);
        return $this->regionLogic;
    }

    private function getRegions()
    {
        static $regions = null;
        if($regions != null) {
            return $regions;
        }
        $regions = file_get_contents(DOC_PATH . 'data/39regions.json');
        $regions = json_decode($regions, true);
        return $regions;
    }

    public function create(array $data)
    {
        $dataList = $this->getByUserId($data['user_id']);
        if(empty($dataList)) {
            $data['is_default'] = 1;
        }
        $id = parent::create($data);
        if(!empty($dataList) && $id && $data['is_default'] == 1) {
            foreach ($dataList as $rs) {
                if($rs['id'] == $id) {
                    continue;
                }
                $rs['is_default'] = 0;
                parent::updateByPk($rs);
            }
        }
        return $id;
    }

    public function updateByPk(array $data)
    {
        $result = parent::updateByPk($data);
        if($result) {
            $dataList = $this->getByUserId($data['user_id']);
            if(!empty($dataList) && $data['is_default'] == 1) {
                foreach ($dataList as $rs) {
                    if($rs['id'] == $data['id']) {
                        continue;
                    }
                    $rs['is_default'] = 0;
                    parent::updateByPk($rs);
                }
            }
        }
        return $result;
    }

    public function deleteByPk($id)
    {
        $data = $this->getByPk($id);
        if($data['is_default'] == 1) {
            throw new Exception('默认地址不可删除');
        }
        return parent::deleteByPk($id);
    }

    public function getByUserId($userId)
    {
        return $this->getAll(['user_id' => $userId]);
    }

    public function getDefaultByUserId($userId)
    {
        return $this->info([
            'user_id' => $userId,
            'is_default' => 1
        ]);
    }

    public function format(array $data)
    {
        $regions = $this->getRegions();
        $data['region_name'] = '';
        foreach ($regions as $region) {
            if($region['id'] == $data['province_id']) {
                $data['region_name'] .= $region['name'];
                foreach ($region['cities'] as $city) {
                    if($city['id'] == $data['city_id']) {
                        $data['region_name'] .= $city['name'];
                        break;
                    }
                }
                break;
            }
        }
        return $data;
    }
}