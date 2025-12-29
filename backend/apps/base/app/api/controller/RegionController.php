<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/8/13 11:15
 * @description
 */

namespace apps\base\app\api\controller;


use apps\base\core\service\RegionService;
use think\Request;

class RegionController extends BaseApiController
{
    /**
     * @var RegionService
     */
    protected $regionService;

    protected function init()
    {
        parent::init();
        $this->regionService = service('Region', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $data = $this->regionService->getByParentId($request->param('parent_id'));
        return $this->success($data);
    }

    public function all()
    {
        $data = $this->regionService->getAll();
        return $this->success($data);
    }

    public function all_regions()
    {
        $data = $this->regionService->getAll();
        return $this->success(array_values($data));
    }

    public function info(Request $request)
    {
        $data = $this->regionService->getByPk($request->param('region_id'));
        if($data && $data['parent_id'] > 0) {
            $data['parent'] = $this->regionService->getByPk($data['parent_id']);
        }
        return $this->success($data);
    }

    public function submit(Request $request)
    {
        $result = $this->regionService->submit($request->param());
        return $this->success();
    }

    public function delete(Request $request)
    {
        $result = $this->regionService->deleteByPk($request->param('region_id'));
        return $this->success();
    }

    public function children(Request $request)
    {
        $regions = $this->regionService->getChildren($request->param('region_id'));
        return $this->success($regions);
    }

    public function parents(Request $request)
    {
        $regions = $this->regionService->getParents($request->param('region_id'));
        return $this->success($regions);
    }

    public function tree()
    {
        return $this->success($this->regionService->getTree());
    }
}