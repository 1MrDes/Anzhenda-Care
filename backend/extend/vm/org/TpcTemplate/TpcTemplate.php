<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'template.func.php';

class TpcTemplate
{

    const DIR_SEP = DIRECTORY_SEPARATOR;

    /**
     * 模板实例
     *
     * @staticvar
     * @var object Template
     */
    protected static $_instance;

    /**
     * 模板参数信息
     *
     * @var array
     */
    protected $_options = array();

    /**
     * 单件模式调用方法
     * @param $options
     * @static
     * @return object Template
     */
    public static function getInstance(array $options = [])
    {
        if (!self::$_instance instanceof self)
            self::$_instance = new self($options);
        return self::$_instance;
    }

    /**
     * 构造方法
     * @param $options
     * @return void
     */
    private function __construct(array $options = [])
    {
        if(empty($options)) {
            $this->_options = array(
                'template_dir' => TPC_TEMPLATE_DIR, //模板文件所在目录
                'cache_dir' => TPC_TEMPLATE_CACHE_DIR, //缓存文件存放目录
                'auto_update' => true, //当模板文件改动时是否重新生成缓存
                'cache_lifetime' => 0, //缓存生命周期(分钟)，为 0 表示永久
                'suffix' => '.' . TPC_TEMPLATE_SUFFIX,  //模板文件后缀
            );
        }
    }

    /**
     * 设定模板参数信息
     *
     * @param array $options 参数数组
     * @return void
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * 设定模板参数
     *
     * @param string $name 参数名称
     * @param mixed $value 参数值
     * @return void
     */
    public function set($name, $value)
    {
        switch ($name) {
            case 'template_dir':
                $value = $this->_trimpath($value);
                if (!is_dir($value)) mk_dir($value);
                if (!file_exists($value))
                    $this->_throwException("未找到指定的模板目录 \"$value\"");
                $this->_options['template_dir'] = $value;
                break;
            case 'cache_dir':
                $value = $this->_trimpath($value);
                if (!is_dir($value)) mk_dir($value);
                if (!file_exists($value))
                    $this->_throwException("未找到指定的缓存目录 \"$value\"");
                $this->_options['cache_dir'] = $value;
                break;
            case 'auto_update':
                $this->_options['auto_update'] = (boolean)$value;
                break;
            case 'cache_lifetime':
                $this->_options['cache_lifetime'] = (float)$value;
                break;
            case 'suffix':
                $this->_options['suffix'] = $value;
                break;
            default:
                $this->_throwException("未知的模板配置选项 \"$name\"");
        }
    }

    /**
     * 通过魔术方法设定模板参数
     *
     * @param string $name 参数名称
     * @param mixed $value 参数值
     * @return void
     * @see    Template::set()
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * 获取模板文件
     *
     * @param string $file 模板文件名称
     * @return string
     */
    public function getfile($file)
    {
        $file = $file . $this->_options['suffix'];   //设置模板文件后缀
        $cachefile = $this->_getCacheFile($file);
        if (!file_exists($cachefile)) {
            $this->cache($file);
        }
        return $cachefile;
    }

    /**
     * 获取模块文件
     *
     * @param string $id 文件名称
     * @return string
     */
    public function getblock($id)
    {
        $file = $this->_getBlockFile($id);
        if (!is_file($file)) {
            create_block_tpl($id);
        }
        return file_get_contents($file);
    }

    /**
     * 检测模板文件是否需要更新缓存
     *
     * @param string $file 模板文件名称
     * @param string $md5data 模板文件 md5 校验信息
     * @param integer $md5data 模板文件到期时间校验信息
     * @return void
     */
    public function check($file, $md5data, $expireTime)
    {
        if ($this->_options['auto_update'] && md5_file($this->_getTplFile($file)) != $md5data) {
            $this->cache($file);
        }

        if ($this->_options['cache_lifetime'] != 0 && (time() - $expireTime >= $this->_options['cache_lifetime'] * 60)) {
            $this->cache($file);
        }
    }

    /**
     * 对模板文件进行缓存
     *
     * @param string $file 模板文件名称
     * @return void
     */
    public function cache($file)
    {
        $tplfile = $this->_getTplFile($file);
        if (!is_readable($tplfile)) {
            $this->_throwException("模板文件 \"$tplfile\" 未找到或者无法打开");
        }
        //取得模板内容
        $tpl_content = file_get_contents($tplfile);
        $template = $this->build($tpl_content);

        //添加 md5 及过期校验
        $md5data = md5_file($tplfile);
        $expireTime = time();
        $template = "<? if (!class_exists('TpcTemplate')) die('Access Denied');"
            . "\TpcTemplate::getInstance()->check('$file', '$md5data', $expireTime);"
            . "?>$template";

        //写入缓存文件
        $cachefile = $this->_getCacheFile($file);
        $makepath = $this->_makepath($cachefile);
        if ($makepath !== true)
            $this->_throwException("无法创建缓存目录 \"$makepath\"");
        file_put_contents($cachefile, $template);
    }

    public function build($template)
    {
        //过滤 <!--{}-->
        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);

        //替换语言包变量
        //$template = preg_replace("/\{lang\s+(.+?)\}/ies", "languagevar('\\1')", $template);

        //替换 PHP 换行符
        $template = str_replace("{LF}", "<?=\"\\n\"?>", $template);

        //替换直接变量输出
        $template = preg_replace("/\{(\\\$[a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\}/s", "<?=\\1['\\2']?>", $template);
        $varRegexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)"
            . "(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
        $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
        $template = preg_replace_callback("/$varRegexp/s", function ($r) {
            return tpc_addquote($r[1]);
        }, $template);
        $template = preg_replace_callback("/\<\?\=\<\?\=$varRegexp\?\>\?\>/s", function ($r) {
            return tpc_addquote($r[1]);
        }, $template);

        //替换模板载入命令
        $template = preg_replace(
            "/[\n\r\t]*\{template\s+([a-zA-Z0-9_]+)\}[\n\r\t]*/is",
            "\r\n<? include(\TpcTemplate::getInstance()->getfile('\\1')); ?>\r\n",
            $template
        );
        $template = preg_replace(
            "/[\n\r\t]*\{template\s+(\"|\')([a-zA-Z0-9_\/\.]+)(\"|\')\}[\n\r\t]*/is",
            "\r\n<? include(\TpcTemplate::getInstance()->getfile('\\2')); ?>\r\n",
            $template
        );
        //替换子模板载入命令
        $template = preg_replace_callback(
            "/[\n\r\t]*\{subtpl\s+(\"|\')([a-zA-Z0-9_\/\.]+)(\"|\')\}[\n\r\t]*/is",
            'tpc_subtpl',
            $template
        );
        //替换模块载入命令
        $template = preg_replace_callback(
            "/[\n\r\t]*\{module\s+(\"|\')([a-zA-Z0-9_]+)(\"|\')\}[\n\r\t]*/is",
            'tpc_getblock',
            $template
        );

        //替换特定函数
        $template = preg_replace_callback("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/is", function ($r) {
            return tpc_stripvtags('<? '.$r[1].' ?>','');
        }, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/is", function ($r) {
            return tpc_stripvtags('<? echo '.$r[1].'; ?>','');
        }, $template);
        // 输出函数结果
        $template = preg_replace_callback("/[\n\r\t]*\{\:(.+?)\}[\n\r\t]*/is", function ($r) {
            return tpc_stripvtags('<? echo '.$r[1].'; ?>','');
        }, $template);
        // 执行函数
        $template = preg_replace_callback("/[\n\r\t]*\{\~(.+?)\}[\n\r\t]*/is", function ($r) {
            return tpc_stripvtags('<? '.$r[1].'; ?>','');
        }, $template);

        $template = preg_replace_callback("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/is", function ($r) {
            return tpc_stripvtags($r[1] . '<? } elseif(' . $r[2] . ') { ?>' . $r[3], '');
        }, $template);

        $template = preg_replace("/([\n\r\t]*)\{else\}([\n\r\t]*)/is", "\\1<? } else { ?>\\2", $template);

        $template = preg_replace("/`([a-zA-Z0-9_]+)`/s", "\\\$\\1", $template);

        //替换tpc标签
        $template = preg_replace_callback("/\{tpc:(\w+)\s+([^}]+)\}/i", function ($r) {
            return TpcTemplate::getInstance()->tpc_tag($r[1], $r[2], $r[0]);
        }, $template);
        //替换循环函数及条件判断语句
        $nest = 5;
        for ($i = 0; $i < $nest; $i++) {
            $template = preg_replace_callback(
                "/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/is",
                function ($r) {
                    return tpc_stripvtags('<? if(is_array('.$r[1].')) { $i=0;foreach('.$r[1].' as '.$r[2].') { ?>',$r[3].'<? $i++;} } ?>');
                },
                $template
            );

            $template = preg_replace_callback(
                "/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/is",
                function ($r) {
                    return tpc_stripvtags('<? if(is_array('.$r[1].')) { $i=0;foreach('.$r[1].' as '.$r[2].' => '.$r[3].') { ?>',$r[4].'<? $i++;} } ?>');
                },
                $template
            );

            $template = preg_replace_callback(
                "/([\n\r\t]*)\{if\s+(.+?)\}([\n\r]*)(.+?)([\n\r]*)\{\/if\}([\n\r\t]*)/is",
                function ($r) {
                    return tpc_stripvtags($r[1].'<? if('.$r[2].') { ?>'.$r[3],$r[4].$r[5].'<? } ?>'.$r[6]);
                },
                $template
            );
        }

        //常量替换
        $template = preg_replace(
            "/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/s",
            "<?=\\1?>",
            $template
        );

        //删除 PHP 代码断间多余的空格及换行
        $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

        //其他替换
        $template = preg_replace_callback(
            "/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/",
            function ($r) {
                return tpc_transamp($r[0]);
            },
            $template
        );

        $template = preg_replace_callback(
            "/\<script[^\>]*?src=\"(.+?)\".*?\>\s*\<\/script\>/is",
            function ($r) {
                return tpc_stripscriptamp($r[1]);
            },
            $template
        );

        $template = preg_replace_callback(
            "/[\n\r\t]*\{block\s+([a-zA-Z0-9_]+)\}(.+?)\{\/block\}/is",
            function ($r) {
                return tpc_stripblock($r[1], $r[2]);
            },
            $template
        );
        return $template;
    }

    /**
     * 将路径修正为适合操作系统的形式
     *
     * @param string $path 路径名称
     * @return string
     */
    protected function _trimpath($path)
    {
        return str_replace(array('/', '\\', '//', '\\\\'), self::DIR_SEP, $path);
    }

    /**
     * 获取模块文件名及路径
     *
     * @param string $id 模块ID
     * @return string
     */
    public function _getBlockFile($id)
    {
        return $file = $this->_trimpath(CACHE_PATH . 'Block' . self::DIR_SEP . $id . $this->_options['suffix']);
    }

    /**
     * 获取模板文件名及路径
     *
     * @param string $file 模板文件名称
     * @return string
     */
    public function _getTplFile($file)
    {
        return $file = $this->_trimpath($this->_options['template_dir'] . self::DIR_SEP . $file);
        //return realpath(dirname($file) . self::DIR_SEP . basename($file));
    }

    /**
     * 获取模板缓存文件名及路径
     *
     * @param string $file 模板文件名称
     * @return string
     */
    protected function _getCacheFile($file)
    {
        $file = preg_replace('/\.[a-zA-Z0-9\-_]+$/i', '.cache.php', $file);
        return $file = $this->_trimpath($this->_options['cache_dir'] . self::DIR_SEP . $file);
        //return realpath(dirname($file) . self::DIR_SEP . basename($file));
    }

    /**
     * 根据指定的路径创建不存在的文件夹
     *
     * @param string $path 路径/文件夹名称
     * @return string
     */
    protected function _makepath($path)
    {
        @dmkdir(dirname($this->_trimpath($path)));
        /*
        $dirs = explode(self::DIR_SEP, dirname($this->_trimpath($path)));
        $tmp = '';
        foreach ($dirs as $dir) {
            $tmp .= $dir . self::DIR_SEP;
            if (!file_exists($tmp) && !@mkdir($tmp, 0777))
            return $tmp;
        }
        */
        return true;
    }

    /**
     * 解析TPC标签
     * @param string $op 操作方式
     * @param string $data 参数
     * @param string $html 匹配到的所有的HTML代码
     * @return string
     */
    protected function tpc_tag($op, $data, $html)
    {
        preg_match_all("/([a-z]+)\=[\"]?([^\"]+)[\"]?/i", stripslashes($data), $matches, PREG_SET_ORDER);
        $arr = array('action', 'num', 'cache', 'page', 'pagesize', 'urlrule', 'return', 'start');
        $tools = array('json', 'xml', 'get');
        $datas = array();
        $tag_id = md5(stripslashes($html));
        foreach ($matches as $v) {
            if (in_array($v[1], $arr)) {
                $$v[1] = $v[2];
                continue;
            }
            $datas[$v[1]] = $v[2];
        }
        $str = '';
        $num = isset($num) && intval($num) ? intval($num) : 20;
        $cache = isset($cache) && intval($cache) ? intval($cache) : 0;
        $return = isset($return) && trim($return) ? trim($return) : 'data';
        if (!isset($urlrule)) $urlrule = '';
        if (!empty($cache) && !isset($page)) {
            $str .= '$tag_cache_name = md5(implode(\'&\',' . self::arr_to_html($datas) . ').\'' . $tag_id . '\');if(!$' . $return . ' = S($tag_cache_name)){';
        }
        if (in_array($op, $tools)) {
            switch ($op) {
                case 'json':
                    if (isset($datas['url']) && !empty($datas['url'])) {
                        $str .= '$json = @file_get_contents(\'' . $datas['url'] . '\');';
                        $str .= '$' . $return . ' = json_decode($json, true);';
                    }
                    break;

                case 'xml':
                    $str .= "import('@.ORG.Xml');";
                    $str .= '$xml_data = @file_get_contents(\'' . $datas['url'] . '\');';
                    $str .= '$' . $return . ' = XML_unserialize($xml_data);';
                    break;

                case 'get':
                    if ($datas['dbprefix']) {
                        $dbprefix = $datas['dbprefix'];
                    } else {
                        $dbprefix = null;
                    }
                    if ($datas['dbsource']) {
                        $str .= '$get_db = M("", "' . $dbprefix . '", "' . $datas['dbsource'] . '");';
                    } else {
                        $str .= '$get_db = M("", "' . $dbprefix . '");';
                    }
                    $num = isset($num) && intval($num) > 0 ? intval($num) : 20;
                    if (isset($start) && intval($start)) {
                        $limit = intval($start) . ',' . $num;
                    } else {
                        $limit = $num;
                    }
                    if (isset($page)) {
                        $str .= '$pagesize = ' . $num . ';';
                        $str .= '$page = intval(' . $page . ') ? intval(' . $page . ') : 1;if($page<=0){$page=1;}';
                        $str .= '$offset = ($page - 1) * $pagesize;';
                        $limit = '$offset,$pagesize';
                        if ($sql = preg_replace('/select([^from].*)from/i', "SELECT COUNT(*) as count FROM ", $datas['sql'])) {
                            $str .= '$r = $get_db->query("' . $sql . '");$s = $r[0][\'count\'];';
                            $str .= '$pages=page($page,$pagesize,$s,"' . $urlrule . '");';
                            $str .= 'unset($r);';
                        }
                    }
                    $str .= '$r = $get_db->query("' . $datas['sql'] . ' LIMIT ' . $limit . '");$' . $return . ' = $r;unset($r);';
                    break;
            }
        }
        if (!empty($cache) && !isset($page)) {
            $str .= 'if(!empty($' . $return . ')){S($tag_cache_name, $' . $return . ', ' . $cache . ');}';
            $str .= '}';
        }
        return "<" . "?php " . $str . "?" . ">";
    }

    /**
     * 转换数据为HTML代码
     * @param array $data 数组
     * @return mixed
     */
    private static function arr_to_html($data)
    {
        if (is_array($data)) {
            $str = 'array(';
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    $str .= "'$key'=>" . self::arr_to_html($val) . ",";
                } else {
                    if (strpos($val, '$') === 0) {
                        $str .= "'$key'=>$val,";
                    } else {
                        $str .= "'$key'=>'" . tpc_addslashes_deep($val) . "',";
                    }
                }
            }
            return $str . ')';
        }
        return false;
    }

    /**
     * 抛出一个错误信息
     *
     * @param string $message
     * @return void
     */
    protected function _throwException($message)
    {
        die($message);
    }
}