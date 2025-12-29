<?php
/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-25 12:17
 * Description:
 */

namespace apps\base\core\logic;

use think\Exception;
use vm\org\queue\QueueFactory;

class SmsCaptchaLogic
{
    protected $_cachePrefix = 'sms_captcha:';

    public function send($phone, $type)
    {
        if(!check_mobile($phone)) {
            throw new Exception('手机号错误');
        }
        $lastCacheKey = $this->_cachePrefix . 'last_sms:' . $phone;
        if (cache($lastCacheKey)) {
            throw new Exception('1分钟内只能发送一次');
        }
        $captcha = rand_string(6, 1);
        $sms = array(
            'code' => 'verify-code',
            'phone' => $phone,
            'platform' => '',
            'data' => array('code' => $captcha, 'time' => 5)
        );
        try {
            $config = config('queue');
            $queueHandler = QueueFactory::instance();
            $queueHandler->set($config['queues']['sms'], $sms);
            //缓存验证码
            $cacheName = $this->_cachePrefix . $type . ':' . $phone;
            cache($cacheName, $captcha, 60 * 5);    // 5分钟内有效
            cache($lastCacheKey, $captcha, 60);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 验证手机短信验证码
     * 验证成功后将删除缓存
     * @author              c.k xiao <jihaoju@qq.com>
     * @time                2015-3-3 下午4:06:02
     * @param    string $phone 手机号
     * @param    string $type 验证码类型
     * @param    string $captcha 验证码
     * @return    bool
     * @throws  Exception
     */
    public function verify($phone, $type, $captcha)
    {
        $cacheName = $this->_cachePrefix . $type . ':' . $phone;
        $cap = cache($cacheName);
        if ($captcha && $cap == $captcha) {
            cache($cacheName, null);
            return true;
        }
        throw new Exception('验证码错误');
    }


    /**
     * 验证手机短信验证码
     * 验证成功后不会删除缓存
     * @author              c.k xiao <jihaoju@qq.com>
     * @time                2015-3-3 下午4:06:02
     * @param    string $phone 手机号
     * @param    string $type 验证码类型
     * @param    string $captcha 验证码
     * @throws    Exception
     * @return    bool
     */
    public function check($phone, $type, $captcha)
    {
        $cacheName = $this->_cachePrefix . $type . ':' . $phone;
        $cap = cache($cacheName);
        if ($captcha && $cap == $captcha) {
            return true;
        } else {
            throw new Exception('验证码错误');
        }
    }

}