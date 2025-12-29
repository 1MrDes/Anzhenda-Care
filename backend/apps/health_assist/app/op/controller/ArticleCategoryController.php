<?php

namespace apps\health_assist\app\op\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\service\ArticleCategoryService;

class ArticleCategoryController extends BaseHealthAssistOpController
{
    /**
     * @var ArticleCategoryService
     */
    protected $articleCategoryService;

    protected function init()
    {
        parent::init();
        $this->articleCategoryService = service('ArticleCategory', SERVICE_NAMESPACE);
    }

    public function save(Request $request)
    {
        $data = $request->param();
        if($data['id'] == 0) {
            $this->articleCategoryService->create($data);
        } else {
            $this->articleCategoryService->updateByPk($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $this->articleCategoryService->deleteByPk($request->param('id', 0, 'intval'));
        return $this->success();
    }

    public function lists()
    {
        $data = $this->articleCategoryService->findAll();
        return $this->success(['categories' => $data]);
    }

    public function tree()
    {
        $data = $this->articleCategoryService->getTree();
        return $this->success(['categories' => $data]);
    }
}