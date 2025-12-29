<?php
namespace apps\base\app\command;

use EasyTask\Helper;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use vm\org\queue\QueueFactory;

/**
 * Class Task
 * @package apps\base\app\command
 * 启动：  /usr/local/php/bin/php -c /usr/local/php/etc/php.ini ./think task_base start --daemon
 * 停止： /usr/local/php/bin/php -c /usr/local/php/etc/php.ini ./think task_base stop
 * 强制停止： /usr/local/php/bin/php -c /usr/local/php/etc/php.ini ./think task_base stop --force
 */
class Task extends Command
{
    private $logFile;

    public function __construct()
    {
        parent::__construct();
        $this->logFile = DOC_PATH . 'runtime/log/daemon/base_'.date('Ymd').'.txt';
    }

    protected function configure()
    {
        //设置名称为task
        $this->setName('task_base')
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
        $task->setPrefix('task_base');
        $task->setTimeZone('Asia/Shanghai');
        $task->setDaemon($daemon);
        $task->setAutoRecover(true);
        $runtimePath = app()->getRuntimePath() . 'task' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR;
        if(!is_dir($runtimePath)) {
            makeDir($runtimePath);
        }
        $task->setRunTimePath($runtimePath);

        $timerInterval = Helper::canUseEvent() ? 1 : 1;

        $logFile = $this->logFile;

        $task->addFunc(function () use ($logFile) {
            static $count = 1;

            writeLog('发送短信 BEGIN count:' . $count, $logFile);

            static $config = null, $queueHandler = null, $smsLogic = null;
            try {
                if(is_null($config)) {
                    $config = config('queue');
                }
                if(is_null($queueHandler)) {
                    $queueHandler = QueueFactory::instance('Redis', [
                        'host' => $config['host'],
                        'port' => $config['port'],
                        'password' => $config['password'],
                        'prefix' => $config['prefix'],
                    ]);
                }
                if(is_null($smsLogic)) {
                    $smsLogic = logic('Sms', LOGIC_NAMESPACE);
                }

                for ($k = 1; $k <= 20; $k++) {
                    $sms = $queueHandler->get($config['queues']['sms']);
                    if($sms) {
                        $smsLogic->send($sms);
                    }
                    usleep(200);
                }
            } catch (\Exception $e) {
                writeLog('发送短信 出现错误,ERROR:', $logFile);
                writeLog($e->getFile() . "\r\n" . $e->getLine() . "\r\n" . $e->getMessage(), $logFile);
            }
            writeLog('发送短信 END count:' . $count, $logFile);

            if($count == 600) {
                exit(0);
            }
            $count++;
        }, 'sms', $timerInterval, 1);

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