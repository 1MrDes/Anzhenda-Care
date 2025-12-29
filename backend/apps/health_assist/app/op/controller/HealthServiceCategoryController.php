<?php


namespace apps\health_assist\app\op\controller;


use apps\health_assist\app\Request;
use apps\health_assist\core\service\HealthServiceCategoryService;
use vm\com\logic\FileLogic;

class HealthServiceCategoryController extends BaseHealthAssistOpController
{
    /**
     * @var HealthServiceCategoryService
     */
    protected $categoryService;

    /**
     * @var FileLogic
     */
    private $fileLogic;

    protected function init()
    {
        parent::init();
        $this->categoryService = service('HealthServiceCategory', SERVICE_NAMESPACE);
        $this->fileLogic = logic('File', 'vm\com\logic\\');
        $this->fileLogic->init([
            'rpc_server' => env('rpc_file.host') . '/file',
            'rpc_provider' => env('rpc_file.provider'),
            'rpc_token' => env('rpc_file.token'),
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->param();
        if($data['id'] == 0) {
            $this->categoryService->create($data);
        } else {
            $this->categoryService->updateByPk($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $this->categoryService->deleteByPk($request->param('id', 0, 'intval'));
        return $this->success();
    }

    public function info(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $category = $this->categoryService->getByPk($id);
        if($category) {
            if($category['parent_id'] > 0) {
                $category['parent'] = $this->categoryService->getByPk($category['parent_id']);
            } else {
                $category['parent'] = [
                    'id' => 0,
                    'name' => ''
                ];
            }
            $category['img_url'] = '';
            if($category['img_id'] > 0) {
                $file = $this->fileLogic->file($category['img_id']);
                $category['img_url'] = $file['url'];
            }
        }

        return $this->success([
            'category' => $category
        ]);
    }

    public function lists()
    {
        $data = $this->categoryService->findAll();
        return $this->success(['categories' => $data]);
    }

    public function tree()
    {
        $data = $this->categoryService->getTree();
        return $this->success(['categories' => $data]);
    }
}