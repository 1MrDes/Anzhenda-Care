<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\Adv;
use think\Exception;
use vm\com\BaseService;
use vm\com\logic\FileLogic;

class AdvService extends BaseService
{
    const TYPE_IMG = 10;    // 图片
    const TYPE_VIDEO = 20;  // 视频
    const TYPE_TEXT = 30;  // 文本

    const LINK_TYPE_WEB_PAGE = 10;  // 网页链接
    const LINK_TYPE_WEAPP_PAGE = 20;  // 小程序页面

    /**
     * @inheritDoc
     * @return Adv
     */
    protected function getModel()
    {
        return new Adv();
    }

    /**
     * @return AdvPositionService
     */
    private function getAdvPositionService()
    {
        return service('AdvPosition', SERVICE_NAMESPACE);
    }

    /**
     * @return FileLogic
     */
    private function getFileLogic()
    {
        $logic = logic('File', '\vm\com\logic\\');
        $logic->init([
            'rpc_server' => env('rpc_file.host') . '/file',
            'rpc_provider' => env('rpc_file.provider'),
            'rpc_token' => env('rpc_file.token'),
        ]);
        return $logic;
    }

    public function deleteByPositionId($positionId)
    {
        return $this->delete([
            'position_id' => $positionId
        ]);
    }

    public function getByPk($id)
    {
        $data = parent::getByPk($id);
        if($data) {
            if($data['type'] == self::TYPE_IMG || $data['type'] == self::TYPE_VIDEO) {
                $file = $this->getFileLogic()->file($data['file_id']);
                $data['file_url'] = $file['url'];
            }
        }
        return $data;
    }

    public function getByPosition($position)
    {
        $advPosition = $this->getAdvPositionService()->getByCode($position);
        if(empty($advPosition)) {
            throw new Exception('广告位无效');
        }
        $res = $this->getModel()->getByPositionId($advPosition['id'], 1);
        if($res) {
            $fileLogic = $this->getFileLogic();
            foreach ($res as &$rs) {
                if($rs['type'] == self::TYPE_IMG || $rs['type'] == self::TYPE_VIDEO) {
                    $file = $fileLogic->file($rs['file_id']);
                    $rs['file_url'] = $file['url'];
                }
            }
        }
        return $res;
    }
}