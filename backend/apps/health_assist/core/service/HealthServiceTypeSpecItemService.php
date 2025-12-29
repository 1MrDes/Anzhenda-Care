<?php


namespace apps\health_assist\core\service;

use apps\health_assist\core\model\HealthServiceTypeSpecItem;
use vm\com\BaseService;

class HealthServiceTypeSpecItemService extends BaseService
{

    /**
     * @return HealthServiceTypeSpecItem
     */
    protected function getModel()
    {
        return new HealthServiceTypeSpecItem();
    }

    public function saveSpecItems($specId, array $items)
    {
        $oldSpecItems = $this->getAll(['spec_id' => $specId]);
        if(empty($oldSpecItems)) {
            foreach ($items as $item) {
                $data = [
                    'spec_id' => $specId,
                    'item' => $item
                ];
                $this->create($data);
            }
        } else {
            /* 提交过来的 跟数据库中比较 不存在 插入*/
            foreach ($items as $item) {
                $isNew = true;
                foreach ($oldSpecItems as $oldSpecItem) {
                    if($item == $oldSpecItem['item']) {
                        $isNew = false;
                        break;
                    }
                }
                if($isNew) {
                    $data = [
                        'spec_id' => $specId,
                        'item' => $item
                    ];
                    $this->create($data);
                }
            }
            /* 数据库中的 跟提交过来的比较 不存在删除*/
            foreach ($oldSpecItems as $oldSpecItem) {
                $isDeleted = true;
                foreach ($items as $item) {
                    if($item == $oldSpecItem['item']) {
                        $isDeleted = false;
                        break;
                    }
                }
                if($isDeleted) {
                    $this->deleteByPk($oldSpecItem['id']);
                }
            }
        }
    }
}