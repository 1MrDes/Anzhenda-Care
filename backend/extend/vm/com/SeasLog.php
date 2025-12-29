<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/11 21:32
 * @description
 */

namespace vm\com;


use think\App;
use think\contract\LogHandlerInterface;
use think\Log;

class SeasLog implements LogHandlerInterface
{
    protected $config = [
        'time_format' => ' c ',
        'single'      => false,
        'file_size'   => 2097152,
        'path'        => '',
        'apart_level' => [],
        'max_files'   => 0,
        'json'        => false,
    ];

    protected $app;
    // 日志
    public static $log = [];

    public function __construct(App $app, $config = [])
    {
        $this->app = $app;
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }

//        \SeasLog::setBasePath($this->app->getRuntimePath() . 'log' . DIRECTORY_SEPARATOR);
        \SeasLog::setBasePath($this->config['path']);
        \SeasLog::setRequestID(uniqid('requestId_'));
    }

    /**
     * 日志写入接口
     * @access public
     * @param  array    $log    日志信息
     * @return bool
     */
    public function save(array $log): bool
    {
//        $info = [];
//        foreach ($log as $type => $val) {
//            foreach ($val as $msg) {
//                if (!is_string($msg)) {
//                    $msg = var_export($msg, true);
//                }
//                if($type == Log::SQL) {
//                    $type == SEASLOG_INFO;
//                } else {
//                    $type = strtoupper($type);
//                }
//                $info[$type][] = $this->config['json'] ? $msg : '[ ' . $type . ' ] ' . $msg;
//            }
//        }
//        if (!empty($info)) {
//            return $this->write($info);
//        }
        if (!empty(self::$log)) {
            return $this->write(self::$log);
        }

        return true;
    }

    /**
     * 日志写入
     * @access protected
     * @param  array     $message 日志信息
     * @return bool
     */
    protected function write($message)
    {
        // 日志信息封装
        $info['timestamp'] = date($this->config['time_format']);

        foreach ($message as $msg) {
            $info[] = is_array($msg) ? implode("\r\n", $msg) : $msg;
        }

        if (PHP_SAPI == 'cli') {
            $message = $this->parseCliLog($info);
        } else {
            // 添加调试日志
            $this->getDebugLog($info);

            $message = $this->parseLog($info);
        }
        $module = '';
        if(defined('MODULE_NAME')) {
            $module = MODULE_NAME;
        }
        \SeasLog::log(SEASLOG_INFO, "\r\n" . $message, [], $module);
        return true;
    }

    /**
     * CLI日志解析
     * @access protected
     * @param  array     $info 日志信息
     * @return string
     */
    protected function parseCliLog($info)
    {
        if ($this->config['json']) {
            $message = json_encode($info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\r\n";
        } else {
            $now = $info['timestamp'];
            unset($info['timestamp']);

            $message = implode("\r\n", $info);

            $message = "[{$now}]" . $message . "\r\n";
        }

        return $message;
    }

    /**
     * 解析日志
     * @access protected
     * @param  array     $info 日志信息
     * @return string
     */
    protected function parseLog($info)
    {
        $requestInfo = [
            'ip'     => $this->app['request']->ip(),
            'method' => $this->app['request']->method(),
            'host'   => $this->app['request']->host(),
            'uri'    => $this->app['request']->url(),
        ];

        if ($this->config['json']) {
            $info = $requestInfo + $info;
            return json_encode($info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\r\n";
        }

        array_unshift($info, "------REQUEST------\r\n[{$info['timestamp']}] {$requestInfo['ip']} {$requestInfo['method']} {$requestInfo['host']}{$requestInfo['uri']}");
        unset($info['timestamp']);

        return implode("\r\n", $info) . "\r\n";
    }

    protected function getDebugLog(&$info)
    {
        if ($this->app->isDebug()) {
            array_unshift($info, '[info] [PARAM] ' . var_export(request()->param(), true));
            array_unshift($info, '[info] [HEADER] ' . var_export(request()->header(), true));

            // 增加额外的调试信息
            $runtime = round(microtime(true) - $this->app->getBeginTime(), 10);
            $reqs    = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';

            $memory_use = number_format((memory_get_usage() - $this->app->getBeginMem()) / 1024, 2);

            $time_str   = '[RunTime:' . number_format($runtime, 6) . 's] [IO:' . $reqs . 'req/s]';
            $memory_str = ' [MEM:' . $memory_use . 'kb]';
            $file_load  = ' [FILES:' . count(get_included_files()) . ']';
            array_unshift($info, $time_str . $memory_str . $file_load);
        }
    }
}