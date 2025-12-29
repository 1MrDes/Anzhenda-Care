<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthServiceImage;
use vm\com\BaseService;
use vm\com\logic\FileLogic;

class HealthServiceImageService extends BaseService
{

    /**
     * @inheritDoc
     * @return HealthServiceImage
     */
    protected function getModel()
    {
        return new HealthServiceImage();
    }

    private $fileLogic = null;
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

    public function getByHealthServiceId($healthServiceId)
    {
        $res = $this->getModel()->getAll(['health_service_id' => $healthServiceId]);
        if(!empty($res)) {
            $ids = [];
            foreach ($res as $rs) {
                $ids[] = $rs['image_id'];
            }
            $files = $this->getFileLogic()->batchInfo($ids);
            foreach ($res as &$rs) {
                foreach ($files as $file) {
                    if($rs['image_id'] == $file['id']) {
                        $rs['image_url'] = $file['url'];
                        break;
                    }
                }
                if(!isset($rs['image_url'])) {
                    $rs['image_url'] = '';
                }
            }
        }
        return $res;
    }

    public function deleteByHealthServiceId($healthServiceId)
    {
        return $this->delete(['health_service_id' => $healthServiceId]);
    }
}