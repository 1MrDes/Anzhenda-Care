<?php


namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class SystemNotice extends BaseModel
{
    const TYPE_SINGLE = 10;         //  单个用户
    const TYPE_ALL = 20;            //  全部用户
    const TYPE_VIP = 30;            //  VIP用户

    const STATUS_PULLED = 1;            // 已拉取
    const STATUS_WAIT_PULL = 0;         // 未拉取
}