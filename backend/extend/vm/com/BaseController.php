<?php


namespace vm\com;


class BaseController
{
    /**
     * @var \TpcTemplate
     */
    protected $TpcTemplate;
    /**
     * @var array 模板变量
     */
    private $_vars = [];

    public function __construct()
    {
        $this->initialize();
    }

    protected function initialize()
    {
        require_once EXTEND_PATH . 'vm/org/TpcTemplate/TpcTemplate.php';
        $this->TpcTemplate = \TpcTemplate::getInstance();
    }

    /**
     * 模板变量赋值
     *
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    protected function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->_vars = array_merge($this->_vars, $name);
        } else {
            $this->_vars[$name] = $value;
        }
    }

    protected function out($templateFile)
    {
        ob_start();
        ob_implicit_flush(0);
        extract($this->_vars, EXTR_OVERWRITE);
        include $this->TpcTemplate->getfile($templateFile);
        // 获取并清空缓存
        return ob_get_clean();
    }
}