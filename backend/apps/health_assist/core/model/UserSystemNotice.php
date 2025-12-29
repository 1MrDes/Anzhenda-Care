<?php

namespace apps\health_assist\core\model;

use vm\com\BaseModel;

class UserSystemNotice extends BaseModel
{
    const STATUS_READ = 1;          // 已读
    const STATUS_WAIT_READ = 0;     // 未读
}