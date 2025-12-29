<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/2 16:40
 * @description
 */

// 事务管理配置

return [
    'PayTradeService/query',
    'PayTradeService/paySuccess',

    'UserService/registerByEmail',
    'UserService/loginByEmail',
    'UserService/loginById',
    'UserService/registerByWxAuthWithMobile',
    'UserService/modifyPasswordByEmail',
    'UserService/loginByOpenid',
    'UserService/logout',
    'VipLevelOrderService/payConfirm',
    'UserChatMessageService/send',
    'PlatformAccountBookService/record',
    'UserAccountBookService/record',

];