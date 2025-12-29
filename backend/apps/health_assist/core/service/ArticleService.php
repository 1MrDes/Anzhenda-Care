<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\Article;
use vm\com\BaseService;
use vm\com\logic\FileLogic;

class ArticleService extends BaseService
{
    const CACHE_PREFIX_ID = 'article:id:';
    const CACHE_PREFIX_CODE = 'article:code:';

    /**
     * @return Article
     */
    protected function getModel()
    {
        return new Article();
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

    public function create(array $data)
    {
        $data['add_time'] = time();
        return parent::create($data);
    }

    public function getByPk($id)
    {
        if($data = cache(self::CACHE_PREFIX_ID . $id)) {
            return  $data;
        }
        $data = parent::getByPk($id);
        if($data) {
            cache(self::CACHE_PREFIX_ID . $id, $data, 3600*24*365);
        }
        return $data;
    }

    public function updateByPk(array $data)
    {
        cache(self::CACHE_PREFIX_ID . $data['id'], null);
        $data['update_time'] = time();
        return parent::updateByPk($data);
    }

    public function deleteByPk($id)
    {
        $data = $this->getByPk($id);
        cache(self::CACHE_PREFIX_ID . $id, null);
        $result = parent::deleteByPk($id);
        if($result) {
            if($data['img_id'] > 0) {
                $this->getFileLogic()->delete($data['img_id']);
            }
        }
        return $result;
    }

    public function getByCode($code)
    {
        if($id = cache(self::CACHE_PREFIX_CODE . $code)) {
            return  $this->getByPk($id);
        }
        $data = parent::info(['code' => $code]);
        if($data) {
            cache(self::CACHE_PREFIX_CODE . $code, $data['id'], 3600*24*365);
        }
        return $data;
    }

    public function format($data)
    {
        $data['img_url'] = '';
        if($data['img_id'] > 0) {
            $file = $this->getFileLogic()->file($data['img_id']);
            if($file) {
                $data['img_url'] = $file['url'];
            }
        }
        return $data;
    }
}