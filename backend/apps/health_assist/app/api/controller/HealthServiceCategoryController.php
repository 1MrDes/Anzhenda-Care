<?php


namespace apps\health_assist\app\api\controller;


use apps\health_assist\app\Request;
use apps\health_assist\core\service\HealthServiceCategoryService;

class HealthServiceCategoryController extends BaseHealthAssistApiController
{
    /**
     * @var HealthServiceCategoryService
     */
    private $goodsCategoryService;

    protected function init()
    {
        parent::init();
        $this->goodsCategoryService = service('HealthServiceCategory', SERVICE_NAMESPACE);
    }

    public function all()
    {
        $categories = $this->goodsCategoryService->findAll();
        return $this->success(['categories' => $categories]);
    }

    public function info(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $category = $this->goodsCategoryService->getByPk($id);
        return $this->success(['category' => $category]);
    }

    public function nested_tree()
    {
        $categories = $this->goodsCategoryService->getNestedTree();
        return $this->success(['categories' => $categories]);
    }
}