<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/1/2 11:49
 * @description
 */

namespace apps\base\core\service;

use apps\base\core\model\ArticleCategory;
use vm\com\BaseService;

class ArticleCategoryService extends BaseService
{

    /**
     * @return ArticleCategory
     */
    protected function getModel()
    {
        return new ArticleCategory();
    }

    public function getByCode($code)
    {
        return $this->getModel()->getByCode($code);
    }

    public function getByParentId($parentId)
    {
        return $this->getModel()->getByParentId($parentId);
    }
}