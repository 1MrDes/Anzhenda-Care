<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\UnionHealthAssistant;
use vm\com\BaseModel;
use vm\com\BaseService;
use vm\com\logic\FileLogic;
use vm\com\logic\RegionLogic;

class UnionHealthAssistantService extends BaseService
{
    /**
     * @var RegionLogic
     */
    private $regionLogic;

    /**
     * @var FileLogic
     */
    protected $fileLogic;

    /**
     * @return UnionHealthAssistant
     */
    protected function getModel()
    {
        return new UnionHealthAssistant();
    }

    private function getRegionLogic()
    {
        if($this->regionLogic !== null) {
            return $this->regionLogic;
        }
        $this->regionLogic = logic('Region', '\vm\com\logic\\');
        $this->regionLogic->init([
            'rpc_server' => env('rpc_base.host') . '/region',
            'rpc_provider' => env('rpc_base.provider'),
            'rpc_token' => env('rpc_base.token'),
        ]);
        return $this->regionLogic;
    }

    private function getFileLogic()
    {
        if($this->fileLogic !== null) {
            return $this->fileLogic;
        }
        $this->fileLogic = logic('File', '\vm\com\logic\\');
        $this->fileLogic->init([
            'rpc_server' => env('rpc_file.host') . '/file',
            'rpc_provider' => env('rpc_file.provider'),
            'rpc_token' => env('rpc_file.token'),
        ]);
        return $this->fileLogic;
    }

    private function genSn()
    {
        do {
            $sn = rand_string(6, 1);
            $isExists = $this->info([
                'sn' => $sn
            ]);
        } while($isExists);
        return $sn;
    }

    public function create(array $data)
    {
        $data['sn'] = 's' . $this->genSn();
        return parent::create($data);
    }

    public function getBySn($sn)
    {
        return $this->info([
            'sn' => $sn
        ]);
    }

    public function save(array $data)
    {
        if($rs = $this->info(['user_id' => $data['user_id']])) {
            $data['id'] = $rs['id'];
            return $this->updateByPk($data);
        } else {
            return $this->create($data);
        }
    }

    public function getByUserId($userId)
    {
        return $this->info(['user_id' => $userId]);
    }

    public function format(array $data)
    {
        $data['region'] = '';
        $regions = $this->getRegionLogic()->parents($data['city_id']);
        foreach ($regions as $region) {
            $data['region'] .= $region['region_name'];
        }
        $data['avatar_url'] = '';
        if($data['avatar'] > 0) {
            $file = $this->getFileLogic()->file($data['avatar']);
            if($file) {
                $data['avatar_url'] = $file['url'];
            }
        }

        $data['contact_wx_qrcode_url'] = '';
        if($data['contact_wx_qrcode'] > 0) {
            $file = $this->getFileLogic()->file($data['contact_wx_qrcode']);
            if($file) {
                $data['contact_wx_qrcode_url'] = $file['url'];
            }
        }

        $data['paramedic_card_file_url'] = '';
        if($data['paramedic_card_file_id'] > 0) {
            $file = $this->getFileLogic()->file($data['paramedic_card_file_id']);
            if($file) {
                $data['paramedic_card_file_url'] = $file['url'];
            }
        }

        $data['dietician_card_file_url'] = '';
        if($data['dietician_card_file_id'] > 0) {
            $file = $this->getFileLogic()->file($data['dietician_card_file_id']);
            if($file) {
                $data['dietician_card_file_url'] = $file['url'];
            }
        }

        $data['physician_card_file_url'] = '';
        if($data['physician_card_file_id'] > 0) {
            $file = $this->getFileLogic()->file($data['physician_card_file_id']);
            if($file) {
                $data['physician_card_file_url'] = $file['url'];
            }
        }

        $data['nurse_card_file_url'] = '';
        if($data['nurse_card_file_id'] > 0) {
            $file = $this->getFileLogic()->file($data['nurse_card_file_id']);
            if($file) {
                $data['nurse_card_file_url'] = $file['url'];
            }
        }

        $data['apothecary_card_file_url'] = '';
        if($data['apothecary_card_file_id'] > 0) {
            $file = $this->getFileLogic()->file($data['apothecary_card_file_id']);
            if($file) {
                $data['apothecary_card_file_url'] = $file['url'];
            }
        }

        $data['other_card_file_url'] = '';
        if($data['other_card_file_id'] > 0) {
            $file = $this->getFileLogic()->file($data['other_card_file_id']);
            if($file) {
                $data['other_card_file_url'] = $file['url'];
            }
        }
        
        return $data;
    }
}