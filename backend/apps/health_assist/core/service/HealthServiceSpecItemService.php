<?php


namespace apps\health_assist\core\service;

use apps\health_assist\core\model\HealthServiceSpecItem;
use vm\com\BaseService;

class HealthServiceSpecItemService extends BaseService
{

    /**
     * @return HealthServiceSpecItem
     */
    protected function getModel()
    {
        return new HealthServiceSpecItem();
    }

    public function save(array $data)
    {
        if($last = $this->info([
            'spec_id' => $data['spec_id'],
            'item' => $data['item']
        ])) {
            $data['id'] = $last['id'];
            $this->updateByPk($data);
            return $data['id'];
        } else {
            return $this->create($data);
        }
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