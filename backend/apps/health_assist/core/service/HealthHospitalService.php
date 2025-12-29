<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\HealthHospital;
use vm\com\BaseModel;
use vm\com\BaseService;
use vm\com\logic\FileLogic;

class HealthHospitalService extends BaseService
{
    /**
     * @var FileLogic
     */
    private $fileLogic;

    /**
     * @return HealthHospital
     */
    protected function getModel()
    {
        return new HealthHospital();
    }

    /**
     * @return FileLogic
     */
    private function getFileLogic()
    {
        if($this->fileLogic !== null) {
            return $this->fileLogic;
        }
        $this->fileLogic = logic('File', 'vm\com\logic\\');
        $this->fileLogic->init([
            'rpc_server' => env('rpc_file.host') . '/file',
            'rpc_provider' => env('rpc_file.provider'),
            'rpc_token' => env('rpc_file.token'),
        ]);
        return $this->fileLogic;
    }

    public function search(array $params, $pageSize, array $sortOrder = [])
    {
        return $this->getModel()->pageListByParams($params, $pageSize, [], $sortOrder);
    }

    public function format(array $data)
    {
        $data['logo_url'] = '';
        if (is_numeric($data['logo']) && $data['logo'] > 0) {
            $file = $this->getFileLogic()->file($data['logo']);
            $data['logo_url'] = $file['url'];
        } else {
            if(strtolower(substr($data['logo'], 0, 4)) == 'http') {
                $suffix = substr(strrchr($data['logo'], '.'), 1);
                if(!in_array($suffix, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $suffix = 'jpe';
                }
                $fileData = 'data:image/'.$suffix.';base64,' . base64_encode(file_get_contents($data['logo']));
                $file = $this->getFileLogic()->upload($fileData);
                $data['logo_url'] = $file['url'];
                $this->updateByPk([
                    'id' => $data['id'],
                    'logo' => $file['id']
                ]);
            } else {
                $data['logo_url'] = $data['logo'];
            }
        }
        return $data;
    }
}