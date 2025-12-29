<?php


namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class Adv extends BaseModel
{
    public function getByPositionId($positionId, $enabled = 1)
    {
        $data = $this->where('position_id', '=', $positionId)
                    ->where('enabled', '=', $enabled)
                    ->order('sort_order', 'ASC')
                    ->select()
                    ->toArray();
        return $data;
    }
}