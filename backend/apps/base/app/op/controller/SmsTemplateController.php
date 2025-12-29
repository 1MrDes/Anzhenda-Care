<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/4/2 00:10
 * @description
 */

namespace apps\base\app\op\controller;

use apps\base\core\service\SmsTemplateService;
use think\Exception;
use think\Request;

class SmsTemplateController extends BaseOpController
{
    /**
     * @var SmsTemplateService
     */
    protected $smsTemplateService;

    protected function init()
    {
        parent::init();
        $this->smsTemplateService = service('SmsTemplate', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10);
        $data = $this->smsTemplateService->pageList($pageSize);
        return $this->success($data);
    }

    public function info(Request $request)
    {
        $data = $this->smsTemplateService->getByPk($request->param('id', 0));
        if(empty($data)) {
            return $this->error(-1, '数据不存在');
        }
        $data['platforms'] = [];
        return $this->success($data);
    }

    public function submit(Request $request)
    {
        if($request->param('id', 0) == 0) {
            $result = $this->smsTemplateService->create($request->param());
        } else {
            $result = $this->smsTemplateService->updateByPk($request->param());
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $result = $this->smsTemplateService->deleteByPk($request->param('id', 0));
        if($result) {
            return $this->success();
        }
        throw new Exception('删除失败');
    }

    public function check_code_exists(Request $request)
    {
        $result = $this->smsTemplateService->checkCodeExists($request->param('id', 0), $request->param('code'));
        if($result) {
            return $this->error(-1, '编码已存在');
        } else {
            return $this->success();
        }
    }

    public function platforms(Request $request)
    {
        $templateId = $request->param('id', 0);
        $service = service('SmsPlatform', SERVICE_NAMESPACE);
        $relationPlatforms = null;
        if($templateId) {
            $relationPlatforms = $this->smsTemplateService->getRelationPlatforms($templateId);
        }
        $res = $service->getAll();
        $data = array();
        if($res) {
            foreach ($res as $rs) {
                $platformContent = '';
                if($relationPlatforms) {
                    foreach ($relationPlatforms as $v) {
                        if($v['platform_id'] == $rs['id']) {
                            $platformContent = $v['platform_content'];
                            break;
                        }
                    }
                }
                $r = [
                    'temp_id' => $templateId,
                    'platform_id' => $rs['id'],
                    'platform_content' => $platformContent,
                    'platform_name' => $rs['sms_name']
                ];
                $data[] = $r;
            }
        }
        return $this->success($data);
    }
}