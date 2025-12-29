<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/1/1 18:06
 * @description
 */

namespace apps\base\app\op\controller;

use apps\base\core\service\RoleResourceService;
use apps\base\core\service\RoleService;
use think\Request;

class RoleController extends BaseOpController
{
    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * @var RoleResourceService
     */
    protected $roleResourceService;

    protected function init()
    {
        parent::init();
        $this->roleService = service('Role', SERVICE_NAMESPACE);
        $this->roleResourceService = service('RoleResource', SERVICE_NAMESPACE);
    }

    public function info(Request $request)
    {
        $params = $request->param();
        $role = $this->roleService->getByPk($params['id']);
        if($role) {
            $resouces = $this->roleResourceService->getByRoleId($params['id']);
            $role['resource_ids'] = array();
            if(!empty($resouces)) {
                foreach ($resouces as $resouce) {
                    $role['resource_ids'][] = $resouce['resource_id'];
                }
            }
        }
        return $this->success($role);
    }

    public function all()
    {
        return $this->success($this->roleService->getAll());
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10, 'intval');
        return $this->success($this->roleService->pageList($pageSize));
    }

    public function add(Request $request)
    {
        $params = $request->param();
        $this->roleService->create($params);
        return $this->success();
    }

    public function edit(Request $request)
    {
        $params = $request->param();
        $this->roleService->updateByPk($params);
        return $this->success();
    }

    public function save(Request $request)
    {
        $data = $request->param();
        if($data['id'] == 0) {
            $this->roleService->create($data);
        } else {
            $this->roleService->updateByPk($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $params = $request->param();
        $this->roleService->deleteByPk($params['id']);
        return $this->success();
    }

    public function resources(Request $request)
    {
        $params = $request->param();
        if(!isset($params['resource_ids'])) {
            $params['resource_ids'] = [];
        }
        $this->roleService->resources($params['id'], $params['resource_ids']);
        return $this->success();
    }
}