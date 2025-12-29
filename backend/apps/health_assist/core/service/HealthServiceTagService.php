<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthServiceTag;
use vm\com\BaseService;

class HealthServiceTagService extends BaseService
{
    /**
     * @return HealthServiceTag
     */
    protected function getModel()
    {
        return new HealthServiceTag();
    }

    public function saveTags(array $tags)
    {
        if(!empty($tags)) {
            foreach ($tags as $tag) {
                if(!$this->info(['name' => $tag])) {
                    $this->create(['name' => $tag]);
                }
            }
        }
    }
}