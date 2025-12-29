<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\AdvPosition;
use vm\com\BaseService;

class AdvPositionService extends BaseService
{
    /**
     * @var AdvService
     */
    private $advService;

    protected function init()
    {
        parent::init();
        $this->advService = service('Adv', SERVICE_NAMESPACE);
    }

    /**
     * @inheritDoc
     * @return AdvPosition
     */
    protected function getModel()
    {
        return new AdvPosition();
    }

    public function deleteByPk($id)
    {
        if(parent::deleteByPk($id)) {
            return $this->advService->deleteByPositionId($id);
        }
        return false;
    }

    public function getByCode($code)
    {
        return $this->info(['code' => $code]);
    }
}