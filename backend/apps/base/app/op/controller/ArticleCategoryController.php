<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/4/12 10:25
 * @description
 */

namespace apps\base\app\op\controller;


use apps\base\core\service\ArticleCategoryService;
use think\Request;

class ArticleCategoryController extends BaseOpController
{
    /**
     * @var ArticleCategoryService
     */
    protected $service;

    protected function init()
    {
        parent::init();
        $this->service = service('ArticleCategory', SERVICE_NAMESPACE);
    }

    public function lists()
    {
        $data = $this->service->getAll();
        return $this->success($data);
    }

    public function info(Request $request)
    {
        $category = $this->service->getByPk($request->param('id', 0));
        return $this->success($category);
    }

    public function submit(Request $request)
    {
        $category = $request->param();
        if($category['id'] == 0) {
            $this->service->create($category);
        } else {
            $this->service->updateByPk($category);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $result = $this->service->deleteByPk($request->param('id', 0));
        return $this->success();
    }
}