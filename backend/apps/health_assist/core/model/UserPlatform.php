<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/13 14:59
 * @description
 */

namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class UserPlatform extends BaseModel
{
    // 平台类型
    const PLATFORM_WX_MP = 100; // 微信公众号
    const PLATFORM_WX_MINI = 200; // 微信小程序
    const PLATFORM_WX_APP = 260; // APP微信授权
    const PLATFORM_ALIPAY_MINI = 300; // 支付宝小程序
}