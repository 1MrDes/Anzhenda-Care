<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/5/27 19:08
 * @description
 */

namespace vm\com;


use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Exception;
use think\facade\Env;

abstract class BaseDaemonTask extends Command
{
    protected $input;
    protected $output;
    private $ppid = 0;    // 父进程ID
    protected $invokeCount = 100;
    protected $name;
    protected $desc;
    protected $logFile;

    protected function configure()
    {
        $this->setName($this->name)->setDescription($this->desc);

        $this->ppid = posix_getppid();
        if (method_exists($this, 'init')) {
            $this->init();
        }
        $this->logFile = Env::get('runtime_path') . 'log/daemon/'.date('Ymd').'.txt';
    }

    /**
     * 检测是否应该退出进程
     * @return bool
     */
    protected function checkExit()
    {
        // 超过最大执行次数则退出当前进程
        if ($this->invokeCount !== null && $this->invokeCount <= 0) {
            // 退出运行
            writeLog('本轮作业已执行完,退出本次运行', Env::get('runtime_path') . 'log/daemon/'.date('Ymd').'.txt');
//            $GLOBALS['DAEMON_TASK_WORKER']->exit(1);
            exit(1);
        }
        $exit = false;
        $ppid = posix_getppid();
        if ($this->ppid == 0) {
            $this->ppid = $ppid;
        }
        // 父进程已退出
        if ($this->ppid != $ppid) {
            // 退出运行
//			swoole_timer_clear($this->_tick_id);
            writeLog('主进程已退出', Env::get('runtime_path') . 'log/daemon/'.date('Ymd').'.txt');
//            $GLOBALS['DAEMON_TASK_WORKER']->exit(1);
            exit(1);
        }
    }

    protected function execute(Input $input, Output $output)
    {
        $this->input = $input;
        $this->output = $output;

        while (true) {
            try {
                $this->checkExit();
                $this->invoke();
                if ($this->invokeCount !== null) {
                    $this->invokeCount--;
                }
                usleep(500000);
                //sleep(1);
            } catch (Exception $e) {
                $log = 'FILE:' . $e->getFile() . PHP_EOL . 'LINE:' . $e->getLine() . PHP_EOL . 'MSG:' . $e->getMessage();
                writeLog($log, Env::get('runtime_path') . 'log/daemon/daemon.txt');
            }
        }
    }

    abstract public function invoke();
}