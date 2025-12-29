<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthServiceTypeSpec;
use vm\com\BaseService;

class HealthServiceTypeSpecService extends BaseService
{

    /**
     * @var HealthServiceTypeSpecItemService
     */
    private $healthServiceTypeSpecItemService;

    protected function init()
    {
        parent::init();
        $this->healthServiceTypeSpecItemService = service('HealthServiceTypeSpecItem', SERVICE_NAMESPACE);
    }

    /**
     * @return HealthServiceTypeSpec
     */
    protected function getModel()
    {
        return new HealthServiceTypeSpec();
    }

    public function create(array $data)
    {
        $items = $data['items'];
        unset($data['items']);
        $id = parent::create($data);
        if($id) {
            $items = explode("\n", $items);
            $this->healthServiceTypeSpecItemService->saveSpecItems($id, $items);
        }
        return $id;
    }

    public function updateByPk(array $data)
    {
        $items = $data['items'];
        unset($data['items']);
        $result = parent::updateByPk($data);
        if($result) {
            $items = explode("\n", $items);
            $this->healthServiceTypeSpecItemService->saveSpecItems($data['id'], $items);
        }
        return $result;
    }

    public function deleteByPk($id)
    {
        $result = parent::deleteByPk($id);
        if($result) {
            $this->healthServiceTypeSpecItemService->delete(['spec_id' => $id]);
        }
        return $result;
    }

    public function getByPk($id)
    {
        $spec = parent::getByPk($id);
        $items = $this->healthServiceTypeSpecItemService->getAll(['spec_id' => $id]);
        $spec['items'] = $items;
        return $spec;
    }

    public function getAll(array $params = null)
    {
        $specs = parent::getAll($params);
        foreach ($specs as &$spec) {
            $items = $this->healthServiceTypeSpecItemService->getAll(['spec_id' => $spec['id']]);
            $spec['items'] = $items;
        }
        return $specs;
    }

    public function findAll($typeId)
    {
        return $this->getModel()->findAll($typeId);
    }
}