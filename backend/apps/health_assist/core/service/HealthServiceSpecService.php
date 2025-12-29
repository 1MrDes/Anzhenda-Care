<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthServiceSpec;
use vm\com\BaseService;

class HealthServiceSpecService extends BaseService
{

    /**
     * @return HealthServiceSpecItemService
     */
    public function getHealthServiceSpecItemService()
    {
        return service('HealthServiceSpecItem', SERVICE_NAMESPACE);
    }

    /**
     * @return HealthServiceSpec
     */
    protected function getModel()
    {
        return new HealthServiceSpec();
    }

    public function create(array $data)
    {
//        $items = $data['items'];
//        unset($data['items']);
        $id = parent::create($data);
//        if($id) {
//            $this->getHealthServiceSpecItemService()->saveSpecItems($id, $items);
//        }
        return $id;
    }

    public function updateByPk(array $data)
    {
//        $items = $data['items'];
//        unset($data['items']);
        $result = parent::updateByPk($data);
//        if($result) {
//            $this->getHealthServiceSpecItemService()->saveSpecItems($data['id'], $items);
//        }
        return $result;
    }

    public function save(array $data)
    {
        if($old = $this->info(['health_service_id' => $data['health_service_id'], 'name' => $data['name']])) {
            $data['id'] = $old['id'];
            $this->updateByPk($data);
            return $data['id'];
        } else {
            return $this->create($data);
        }
    }

    public function deleteByPk($id)
    {
        $result = parent::deleteByPk($id);
        if($result) {
            $this->getHealthServiceSpecItemService()->delete(['spec_id' => $id]);
        }
        return $result;
    }

    public function getByPk($id)
    {
        $spec = parent::getByPk($id);
        $items = $this->getHealthServiceSpecItemService()->getAll(['spec_id' => $id]);
        $spec['items'] = $items;
        return $spec;
    }

    public function getAll(array $params = null)
    {
        $specs = parent::getAll($params);
        foreach ($specs as &$spec) {
            $items = $this->getHealthServiceSpecItemService()->getAll(['spec_id' => $spec['id']]);
            $spec['items'] = $items;
        }
        return $specs;
    }

    public function findAll($healthServiceId)
    {
        return $this->getModel()->findAll($healthServiceId);
    }
}