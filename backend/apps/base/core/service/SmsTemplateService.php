<?php
/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-15 14:27
 * Description:
 */

namespace apps\base\core\service;

use apps\base\core\model\SmsTemplate;
use think\Exception;
use vm\com\BaseService;

class SmsTemplateService extends BaseService
{

    /**
     * @return SmsTemplate
     */
    protected function getModel()
    {
        return new SmsTemplate();
    }

    public function create(array $data)
    {
        if($this->getModel()->getByCode($data['code'])) {
            throw new Exception('编码已存在');
        }
        $platforms = $data['platforms'] ? json_decode($data['platforms'], true) : [];
        $data['add_time'] = time();
        $data['last_time'] = time();
        $templateId = parent::create($data);
        if($templateId && !empty($platforms)) {
            $smsPlatformTemplateService = service('SmsPlatformTemplate', SERVICE_NAMESPACE);
            foreach ($platforms as $key => $platform) {
                if(empty($platform['platform_content'])) {
                    continue;
                }
                $rs = [
                    'temp_id' => $templateId,
                    'platform_id' => $platform['platform_id'],
                    'platform_content' => $platform['platform_content']
                ];
                $smsPlatformTemplateService->create($rs);
            }
        }
        return $templateId;
    }

    public function update(array $data, array $where = [])
    {
        $platforms = $data['platforms'];
        $data['last_time'] = time();
        $template = parent::updateByPk($data);
        if($template) {
            $smsPlatformTemplateService = service('SmsPlatformTemplate', SERVICE_NAMESPACE);
            $smsPlatformTemplateService->deleteByTempId($data['id']);
            if(!empty($platforms)) {
                foreach ($platforms as $key => $platform) {
                    if(empty($platform['platform_content'])) {
                        continue;
                    }
                    $rs = [
                        'temp_id' => $data['id'],
                        'platform_id' => $platform['platform_id'],
                        'platform_content' => $platform['platform_content']
                    ];
                    $smsPlatformTemplateService->create($rs);
                }
            }
        }
        return $template;
    }

    public function updateByPk(array $data)
    {
        $platforms = $data['platforms'] ? json_decode($data['platforms'], true) : [];
        $data['last_time'] = time();
        $template = parent::updateByPk($data);
        if($template) {
            $smsPlatformTemplateService = service('SmsPlatformTemplate', SERVICE_NAMESPACE);
            $smsPlatformTemplateService->deleteByTempId($data['id']);
            if(!empty($platforms)) {
                foreach ($platforms as $key => $platform) {
                    if(empty($platform['platform_content'])) {
                        continue;
                    }
                    $rs = [
                        'temp_id' => $data['id'],
                        'platform_id' => $platform['platform_id'],
                        'platform_content' => $platform['platform_content']
                    ];
                    $smsPlatformTemplateService->create($rs);
                }
            }
        }
        return $template;
    }

    public function deleteByPk($id)
    {
        $result = parent::deleteByPk($id);
        if($result) {
            $smsPlatformTemplateService = service('SmsPlatformTemplate', SERVICE_NAMESPACE);
            $smsPlatformTemplateService->deleteByTempId($id);
        }
        return $result;
    }

    public function checkCodeExists($id, $code)
    {
        $template = $this->info(['code' => $code]);
        if(($template && $id == 0) || ($template && $template['id'] != $id)) {
            return true;
        } else {
            return false;
        }
    }

    public function getRelationPlatforms($templateId)
    {
        $smsPlatformTemplateService = service('SmsPlatformTemplate', SERVICE_NAMESPACE);
        return $smsPlatformTemplateService->getByTempId($templateId);
    }

    public function findByCode($code)
    {
        return $this->info(['code' => $code]);
    }
}