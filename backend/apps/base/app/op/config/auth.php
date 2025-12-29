<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/12 10:56
 * @description
 */
return [
    'user/login' => 'non', //  不用验证权限
    'user/logout' => 'login',   // 需要登录
    'admin/profile' => 'login',   // 需要登录
    'admin/password' => 'login'
];