<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/4/12 10:23
 * @description
 */

namespace apps\base\app\op\controller;

use apps\base\core\service\ArticleService;
use think\Request;

class ArticleController extends BaseOpController
{
    /**
     * @var ArticleService
     */
    protected $service;

    protected function init()
    {
        parent::init();
        $this->service = service('Article', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $data = $this->service->pageList($request->param('pageSize/d', 10));
        return $this->success($data);
    }

    public function info(Request $request)
    {
        $data = $this->service->info([
            'id' => $request->param('id/d', 0)
        ]);
        return $this->success($data);
    }

    public function detail(Request $request)
    {
        $code = $request->param('code');
        $data = $this->service->getByCode($code);
        return $this->success($data);
    }

    public function submit(Request $request)
    {
        $data = $request->param();
        if($data['id'] == 0) {
            $this->service->create($data);
        } else {
            $this->service->update($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $result = $this->service->deleteByPk($request->param('id/d', 0));
        return $this->success();
    }
}