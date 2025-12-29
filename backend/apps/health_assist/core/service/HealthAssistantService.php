<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\HealthAssistant;
use apps\health_assist\core\model\SystemNotice;
use apps\health_assist\core\model\UserSystemNotice;
use think\Exception;
use vm\com\BaseService;

class HealthAssistantService extends BaseService
{
    /**
     * @return HealthAssistant
     */
    protected function getModel()
    {
        return new HealthAssistant();
    }

    /**
     * @return SystemNoticeService
     */
    private function getSystemNoticeService()
    {
        return service('SystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return UserSystemNoticeService
     */
    private function getUserSystemNoticeService()
    {
        return service('UserSystemNotice', SERVICE_NAMESPACE);
    }

    private function getRegions()
    {
        static $regions;
        if($regions != null) {
            return $regions;
        }
        $regions = file_get_contents(DOC_PATH . 'data/39regions.json');
        $regions = json_decode($regions, true);
        return $regions;
    }

    public function getByUserId($userId)
    {
        return $this->info([
            'user_id' => $userId
        ]);
    }

    public function format(array $data)
    {
        switch ($data['status']) {
            case HealthAssistant::STATUS_AUDIT_PASS:
                $data['status_label'] = '审核通过';
                break;
            case HealthAssistant::STATUS_WAIT_AUDIT:
                $data['status_label'] = '待审核';
                break;
            case HealthAssistant::STATUS_AUDIT_REJECT:
                $data['status_label'] = '审核未通过';
                break;
            default:
                $data['status_label'] = 'N/A';
                break;
        }

        $data['region_name'] = '';
        $regions = $this->getRegions();
        foreach ($regions as $region) {
            if($region['id'] == $data['province_id']) {
                $data['region_name'] .= $region['name'];
                foreach ($region['cities'] as $city) {
                    if($city['id'] == $data['city_id']) {
                        $data['region_name'] .= $city['name'];
                        break;
                    }
                }
                break;
            }
        }
        return $data;
    }

    public function onAudit($userId, $status, $reason)
    {
        if($this->update([
            'status' => $status,
            'audit_reason' => $reason
        ], ['user_id' => $userId])) {
            $title = '';
            $content = '';
            if($status == HealthAssistant::STATUS_AUDIT_PASS) {
                $title = '申请通过';
                $content = '您申请成为陪诊师已审核通过。';
            } else if($status == HealthAssistant::STATUS_AUDIT_REJECT) {
                $title = '申请未通过';
                $content = '您申请成为陪诊师因故未审核通过，原因如下：' . $reason;
            }

            $systemNoticeId = $this->getSystemNoticeService()->create([
                'title' => $title,
                'content' => $content,
                'type' => SystemNotice::TYPE_SINGLE,
                'status' => SystemNotice::STATUS_PULLED,
                'recipient_id' => $userId,
                'manager_id' => 0,
                'url' => json_encode([
                    'weapp' => '/pages/my/health_assistant/index',
                    'web' => '',
                    'app' => ''
                ])
            ]);
            $this->getUserSystemNoticeService()->create([
                'system_notice_id' => $systemNoticeId,
                'recipient_id' => $userId,
                'status' => UserSystemNotice::STATUS_WAIT_READ,
                'pull_time' => time()
            ]);
            return true;
        }
        throw new Exception('操作失败');
    }
}