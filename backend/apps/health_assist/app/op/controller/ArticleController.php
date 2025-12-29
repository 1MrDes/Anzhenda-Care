<?php

namespace apps\health_assist\app\op\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\service\ArticleCategoryService;
use apps\health_assist\core\service\ArticleService;

class ArticleController extends BaseHealthAssistOpController
{
    /**
     * @var ArticleService
     */
    private $articleService;

    /**
     * @var ArticleCategoryService
     */
    protected $articleCategoryService;

    protected function init()
    {
        parent::init();
        $this->articleCategoryService = service('ArticleCategory', SERVICE_NAMESPACE);
        $this->articleService = service('Article', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 0, 'intval');
        $categoryId = $request->param('category_id', 0, 'intval');
        $params = [];
        if($categoryId > 0) {
            $params['category_id'] = $categoryId;
        }
        $res = $this->articleService->pageListByParams($params, $pageSize);
        foreach ($res['data'] as &$rs) {
            $rs = $this->articleService->format($rs);
            $rs['category'] = $this->articleCategoryService->getByPk($rs['category_id']);
        }
        return $this->success($res);
    }

    public function info(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $article = $this->articleService->getByPk($id);
        if($article) {
            $article = $this->articleService->format($article);
            $article['category'] = $this->articleCategoryService->getByPk($article['category_id']);
        }
        return $this->success(['article' => $article]);
    }

    public function save(Request $request)
    {
        $data = $request->param();
        if($data['id'] == 0) {
            $this->articleService->create($data);
        } else {
            $this->articleService->updateByPk($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $this->articleService->deleteByPk($request->param('id', 0, 'intval'));
        return $this->success();
    }
}