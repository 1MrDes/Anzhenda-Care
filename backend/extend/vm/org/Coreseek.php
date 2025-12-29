<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2018/11/2
 * Time: 16:20
 */

namespace vm\org;


class Coreseek
{
    protected $_cfg = array();
    /**
     * @var SphinxClient
     */
    protected $_sphinx = null;

    public function __construct(array $config = null)
    {
        if($config === null) {
            $this->_cfg = config('sphinx');
        } else {
            $this->_cfg = $config;
        }
    }

    private function connect()
    {
        if(class_exists('SphinxClient')) {
            $this->_sphinx = new \SphinxClient();
        } else {
            $this->_sphinx = new \vm\org\SphinxClient();
        }
        $this->_sphinx->SetServer($this->_cfg['host'], intval($this->_cfg['port']));
        $this->_sphinx->SetConnectTimeout(3);
    }

    /**
     * 初始化sphinx接口
     *
     * @param int $mode
     * @param int $ranker
     * @return SphinxClient
     */
    public function init($mode = null, $ranker = null)
    {
        if ($mode === null) {
            $mode = SPH_MATCH_EXTENDED2;
        }
        if ($ranker === null) {
            $ranker = SPH_RANK_PROXIMITY_BM25;
        }

        $this->connect();
        $this->_sphinx->SetArrayResult(true);
        $this->_sphinx->SetMatchMode($mode);
        // 设置评分模式
        $this->_sphinx->SetRankingMode($ranker);
        return $this->_sphinx;
    }
}