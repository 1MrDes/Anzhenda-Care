<?php

namespace apps\health_assist\core\model;

use vm\com\BaseModel;

class UnionHealthAssistant extends BaseModel
{
    const STATUS_WAIT_AUDIT = 0;    // 待审核
    const STATUS_AUDIT_PASS = 1;    // 通过审核
    const STATUS_AUDIT_REJECT = 2;    // 申请被拒绝
}