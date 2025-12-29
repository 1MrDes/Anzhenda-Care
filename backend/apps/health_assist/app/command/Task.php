<?php
namespace apps\health_assist\app\command;

use EasyTask\Helper;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

/**
 * Class Task
 * @package apps\health_assist\app\command
 * 启动：  /usr/local/php/bin/php -c /usr/local/php/etc/php.ini ./think task_health_assist start --daemon
 * 停止： /usr/local/php/bin/php -c /usr/local/php/etc/php.ini ./think task_health_assist stop
 * 强制停止： /usr/local/php/bin/php -c /usr/local/php/etc/php.ini ./think task_health_assist stop --force
 */
class Task extends Command
{
    private $logFile;

    public function __construct()
    {
        parent::__construct();
        $this->logFile = DOC_PATH . 'runtime/log/daemon/task_health_assist/'.date('Ymd').'.log';
    }

    protected function configure()
    {
        //设置名称为task
        $this->setName('task_health_assist')
            //增加一个命令参数
            ->addArgument('action', Argument::OPTIONAL, "action", '')
            ->addOption('force', 'f', Option::VALUE_NONE, '强制退出')
            ->addOption('daemon', 'd', Option::VALUE_NONE, '设置为守护进程');
    }

    protected function execute(Input $input, Output $output)
    {
        //获取输入参数
        $action = trim($input->getArgument('action'));
        $force = $input->getOption('force') ? true : false;
        $daemon = $input->getOption('daemon') ? true : false;

        // 配置任务
        $task = new \EasyTask\Task();
        $task->setPrefix('task_health_assist');
        $task->setTimeZone('Asia/Shanghai');
        $task->setDaemon($daemon);
        $task->setAutoRecover(true);
        $runtimePath = app()->getRuntimePath() . 'task' . DIRECTORY_SEPARATOR . 'health' . DIRECTORY_SEPARATOR;
        if(!is_dir($runtimePath)) {
            makeDir($runtimePath);
        }
        $task->setRunTimePath($runtimePath);

        $timerInterval = Helper::canUseEvent() ? 1 : 1;

        $logFile = $this->logFile;



        // 根据命令执行
        if ($action == 'start') {
            $task->start();
        } elseif ($action == 'status') {
            $task->status();
        } elseif ($action == 'stop') {
            //是否强制停止
            $task->stop($force);
        } else {
            exit('Command is not exist');
        }
    }
}