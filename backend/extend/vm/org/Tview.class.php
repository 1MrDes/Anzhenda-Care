<?php

namespace vm\org;

use think\Exception;

class Tview
{

    protected $_vars = array();

    /**
     * @var \TpcTemplate|null
     */
    protected $_v = null;    // 视图解析器

    public function __construct()
    {
        require_once __DIR__ . '/TpcTemplate/TpcTemplate.php';
        $this->_v = \TpcTemplate::getInstance();
    }

    /**
     * 模板变量赋值
     *
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->_vars = array_merge($this->_vars, $name);
        } else {
            $this->_vars[$name] = $value;
        }
    }

    /**
     * 取得模板变量的值
     *
     * @access public
     * @param string $name
     * @return mixed
     */
    public function get($name = '')
    {
        if ('' === $name) {
            return $this->_vars;
        }
        return isset($this->_vars[$name]) ? $this->_vars[$name] : false;
    }

    /**
     * 解析和获取模板内容 用于输出
     *
     * @access public
     * @param string $tpl_file 模板文件名
     * @return string
     * @throws
     */
    public function fetch($tpl_file = '')
    {
        // 模板文件不存在直接返回
        if (!is_file($tpl_file)) {
            throw new Exception($tpl_file . '模板不存在');
        }
        $tpl_content = file_get_contents($tpl_file);
        $content = $this->_eval($tpl_content);
        // 输出模板文件
        return $content;
    }

    /**
     * 解析和获取字符串内容 用于输出
     *
     * @access public
     * @param string $str 字符串
     * @return string
     */
    public function fetch_str($str)
    {
        $content = $this->_eval($str);
        return $content;
    }

    private function _eval($tpl_content)
    {
        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
        extract($this->_vars, EXTR_OVERWRITE);
        eval('?>' . trim($this->_v->build($tpl_content)));
        // 获取并清空缓存
        $content = ob_get_clean();
        return $content;
    }

}