<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2017/7/2
 * Time: 22:57
 */

namespace apps\base\core\service;

use apps\base\core\model\SmsPlatform;
use think\Exception;
use vm\com\BaseService;

class SmsPlatformService extends BaseService
{
    /**
     * @return SmsPlatform
     */
    protected function getModel()
    {
        return new SmsPlatform();
    }

    public function create(array $data)
    {
        if($this->getModel()->getByCode($data['sms_code'])) {
            throw new Exception('编码已存在');
        }
        $data['add_time'] = time();
        $data['last_time'] = time();
        return parent::create($data);
    }

    public function updateByPk(array $data)
    {
        $data['last_time'] = time();
        return parent::updateByPk($data);
    }

    public function checkCodeExists($id, $code)
    {
        $platform = $this->getModel()->getByCode($code);
        if(($platform && $id == 0) || ($platform && $platform['id'] != $id)) {
            return true;
        } else {
            return false;
        }
    }

    public function getByCode($code)
    {
        $platform = $this->info(['sms_code' => $code]);
        return $platform;
    }

    public function getByWeight()
    {
        return $this->getModel()->getByWeight();
    }
}