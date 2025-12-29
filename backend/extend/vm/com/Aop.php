<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-7-5
 * Time: 17:57
 */

namespace vm\com;

use think\facade\Db;
use think\facade\Log;

class Aop
{
    private $instance;

    private $transactions = [ // 以以下值开头的方法都将开启事务管理
        'add',
        'edit',
        'delete',
        'create',
        'update',
        'insert',
        'modify',
        'set',
        'unset',
        'save',
        'submit',
        'cancel',
        'audit',
        'register',
        'login',
        'logout',
        'agree',
        'refuse',
        'inc',
        'dec',
        'increase',
        'decrease',
        'on',
        'bind',
        'unbind',
        'apply',
        'join',
        'pay',
        'reject',
        'finish'
    ];

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function __call($method, $argument)
    {
        if (!method_exists($this->instance, $method)) {
            throw new \Exception('未定义的方法：' . $method);
        }
        $tag = get_class($this->instance) . '::' . $method;
        $beginTime = microtime(true);
        Log::info($tag.' BEGIN');
        $transactionEnable = $this->isTransactionEnable($method);
        try {
            //前置
            if($transactionEnable) {
                // 启动事务
                if($this->instance instanceof BaseService) {
                    $this->instance->getDb()->startTrans();
                } else {
                    Db::startTrans();
                }
            }

            $callBack = array($this->instance, $method);
            $return = call_user_func_array($callBack, $argument);

            // 后置
            // 提交事务
            if($transactionEnable) {
                if($this->instance instanceof BaseService) {
                    $this->instance->getDb()->commit();
                } else {
                    Db::commit();
                }
            }
            return $return;
        } catch (\Exception $e) {
            // 回滚事务
            if($transactionEnable) {
                if($this->instance instanceof BaseService) {
                    $this->instance->getDb()->rollback();
                } else {
                    Db::rollback();
                }
            }
            throw $e;
        } finally {
            $runtime = (microtime(true) - $beginTime);
            Log::info($tag.' END [ RunTime:'.$runtime.'s ]');
        }
    }

    /**
     * 是否需要开启事务
     * @param $method   方法名
     * @return bool
     */
    private function isTransactionEnable($method)
    {
        static $enable = null;
        if($enable === true ) {
            return $enable;
        }
        foreach ($this->transactions as $val) {
            if(preg_match('/^'.$val.'/i', $method)) {
                $enable = true;
                return $enable;
            }
        }

        $transactionConfig = config('transaction');
        $className = get_class($this->instance);
        if(stripos($className, '\\') !== false) {
            $temp = explode('\\', $className);
            $className = $temp[count($temp) - 1];
        }
        $enable = in_array($className . '/' . $method, $transactionConfig);
        return $enable;
    }
}