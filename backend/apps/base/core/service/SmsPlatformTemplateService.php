<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/12/31 10:41
 * @description
 */

namespace apps\base\core\service;


use apps\base\core\model\SmsPlatformTemplate;
use vm\com\BaseService;

class SmsPlatformTemplateService extends BaseService
{

    /**
     * @return SmsPlatformTemplate
     */
    protected function getModel()
    {
        return new SmsPlatformTemplate();
    }

    public function deleteByTempId($tempId)
    {
        return $this->getModel()->deleteByTempId($tempId);
    }

    public function getByTempId($tempId)
    {
        return $this->getModel()->getByTempId($tempId);
    }
}