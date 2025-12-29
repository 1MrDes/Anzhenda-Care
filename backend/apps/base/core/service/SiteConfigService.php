<?php

namespace apps\base\core\service;

use apps\base\core\model\SiteConfig;
use vm\com\BaseService;

class SiteConfigService extends BaseService
{
    protected $cacheName = 'site_configs';

    /**
     * @return SiteConfig
     */
    protected function getModel()
    {
        return new SiteConfig();
    }

    public function getAllConfigs()
    {
        if($data = cache($this->cacheName)) {
            return $data;
        }
        $data = array();
        $configs = parent::getAll();
        if($configs !== null) {
            foreach ($configs as $config) {
                if($config['value_type'] == 'json') {
                    $config['value'] = empty($config['value']) ? null : json_decode($config['value'], true);
                } else if($config['value_type'] == 'ini') {
                    $config['value'] = empty($config['value']) ? null : parse_ini_string($config['value'], true);
                }
                $data[$config['code']] = $config;
            }
        }
        cache($this->cacheName, $data, 3600*24*30);
        return $data;
    }

    public function getByCode($code)
    {
        $configs = $this->getAllConfigs();
        if(isset($configs[$code])) {
            return $configs[$code];
        }
        return null;
    }

    public function getValueByCode($code)
    {
        $configs = $this->getAllConfigs();
        if(isset($configs[$code])) {
            return $configs[$code]['value'];
        }
        return null;
    }

    public function getNonHiddenItems()
    {
        $data = array();
        $res = $this->getModel()->where('type', 'group')->order('sort_order asc')->select()->toArray();
        foreach ($res as $rs) {
            $items = $this->getModel()
                ->where('parent_id', $rs['id'])
                ->where('type', '<>', 'hidden')
                ->order('sort_order asc')
                ->select()->toArray();
            foreach ($items as &$item) {
                $displayOptions = array();
                if(!empty($item['store_range']) && !empty($item['store_options'])) {
                    $storeOptions = explode("\r\n", $item['store_options']);
                    foreach ($storeOptions as $v) {
                        list($key, $val) = explode("=", $v);
                        $displayOptions[$key] = $val;
                    }
                }
                $item['display_options'] = $displayOptions;
            }
            $rs['vars'] = $items;
            $data[$rs['id']] = $rs;
        }
        return $data;
    }

    public function batchUpdate(array $keys, array $values)
    {
        foreach ($keys as $key) {
            $this->getModel()->updateByCode($key, ['value' => $values[$key]]);
        }
        cache($this->cacheName, null);
        return true;
    }
}