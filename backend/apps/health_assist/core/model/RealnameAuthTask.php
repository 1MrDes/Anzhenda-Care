<?php

namespace apps\health_assist\core\model;

use vm\com\BaseModel;

class RealnameAuthTask extends BaseModel
{
    const VERIFY_RESULT_SUCCESS = 1;           //  匹配
    const VERIFY_RESULT_FAIL = 0;              //  不匹配

    const PAY_STATUS_WAIT_PAY = 0;
    const PAY_STATUS_PAIED = 1;
}