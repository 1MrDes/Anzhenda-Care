<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/4/2 11:42
 * @description
 */

namespace apps\base\app\op\controller;

use apps\base\core\service\ResourceService;
use think\Request;

class ResourceController extends BaseOpController
{
    /**
     * @var ResourceService
     */
    protected $resourceService;

    protected function init()
    {
        parent::init();
        $this->resourceService = service('Resource', SERVICE_NAMESPACE);
    }

    public function info(Request $request)
    {
        $params = $request->param();
        $data = $this->resourceService->getByPk($params['id']);
        return $this->success($data);
    }

    public function lists()
    {
        return $this->success($this->resourceService->getTree());
    }

    public function options()
    {
        $res = $this->resourceService->getOptions();
        return $this->success(array_values($res));
//        $data = array();
//        if(!empty($res)) {
//            foreach ($res as $rs) {
//                $rs['full_name'] = str_repeat('&nbsp&nbsp' , $rs['level']) . $rs['name'];
//                $data[] = $rs;
//            }
//        }
//        return $this->success($data);
    }

    public function submit(Request $request)
    {
        $params = $request->param();
        if($params['id'] == 0) {
            $this->resourceService->create($params);
        } else {
            $this->resourceService->update($params);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $params = $request->param();
        $this->resourceService->deleteByPk($params['id']);
        return $this->success();
    }
}