<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/4/2 00:10
 * @description
 */

namespace apps\base\app\op\controller;


use apps\base\core\service\SmsPlatformService;
use think\Exception;
use think\Request;

class SmsPlatformController extends BaseOpController
{
    /**
     * @var SmsPlatformService
     */
    protected $smsPlatformService;

    protected function init()
    {
        parent::init();
        $this->smsPlatformService = service('SmsPlatform', SERVICE_NAMESPACE);
    }

    public function lists()
    {
        $data = $this->smsPlatformService->getAll();
        return $this->success($data);
    }

    public function info(Request $request)
    {
        $data = $this->smsPlatformService->getByPk($request->param('id', 0));
        return $this->success($data);
    }

    public function submit(Request $request)
    {
        if($request->param('id', 0) == 0) {
            $result = $this->smsPlatformService->create($request->param());
        } else {
            $result = $this->smsPlatformService->updateByPk($request->param());
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $result = $this->smsPlatformService->deleteByPk($request->param('id', 0));
        if($result) {
            return $this->success();
        }
        throw new Exception('删除失败');
    }

    public function check_code_exists(Request $request)
    {
        $result = $this->smsPlatformService->checkCodeExists($request->param('id', 0), $request->param('code'));
        if($result) {
            return $this->error(-1, '编码已存在');
        } else {
            return $this->success();
        }
    }
}