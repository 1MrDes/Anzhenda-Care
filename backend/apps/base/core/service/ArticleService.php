<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/1/2 12:10
 * @description
 */

namespace apps\base\core\service;

use apps\base\core\logic\FileLogic;
use apps\base\core\model\Article;
use vm\com\BaseService;

class ArticleService extends BaseService
{

    /**
     * @return Article
     */
    protected function getModel()
    {
        return new Article();
    }

    /**
     * @return FileLogic
     */
    private function getFileLogic()
    {
        return logic('File', LOGIC_NAMESPACE);
    }

    public function create(array $data)
    {
        $nowtime = time();
        $data['add_time'] = $nowtime;
        $data['update_time'] = $nowtime;
        return parent::create($data);
    }

    public function update(array $data, array $where = [])
    {
        $nowtime = time();
        $data['update_time'] = $nowtime;
        return parent::updateByPk($data);
    }

    public function info($params)
    {
        $data = parent::info($params);
        if($data && $data['img_id'] > 0) {
            $file = $this->getFileLogic()->file($data['img_id']);
            $data['img_url'] = $file['url'];
        }
        return $data;
    }

    public function getByCateId($cateId, $isShow = -1, $pageSize = 10)
    {
        return $this->getModel()->getByCateId($cateId, $isShow, $pageSize);
    }

    public function getByCode($code)
    {
        $data = $this->getModel()->getByCode($code);
        if($data && $data['img_id'] > 0) {
            $file = $this->getFileLogic()->file($data['img_id']);
            $data['img_url'] = $file['url'];
        }
        return $data;
    }
}