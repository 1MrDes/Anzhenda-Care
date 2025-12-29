<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/13 14:59
 * @description
 */

namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class User extends BaseModel
{
    const VIRTUAL_MOBILE_PHONE_PREFIX = '000';

    public function increaseBalance($id, $amount)
    {
        $this->where('id', '=', $id)->inc('balance', $amount)->update();
        return $this->getNumRows() > 0;
    }

    public function decreaseBalance($id, $amount)
    {
        $this->where('id', '=', $id)
                    ->where('balance', ">=", $amount)
                    ->dec('balance', $amount)
                    ->update();
        return $this->getNumRows() > 0;
    }

    public function increaseWithdrawBalance($id, $amount)
    {
        $result1 = $this->where('id', '=', $id)
                        ->inc('balance', $amount)
                        ->inc('withdraw_balance', $amount)
                        ->update();
        return $this->getNumRows() > 0;
    }

    public function decreaseWithdrawBalance($id, $amount)
    {
        $result1 = $this->where('id', '=', $id)
                        ->where('balance', ">=", $amount)
                        ->where('withdraw_balance', ">=", $amount)
                        ->dec('balance', $amount)
                        ->dec('withdraw_balance', $amount)
                        ->update();
        return $this->getNumRows() > 0;
    }

    public function applyWithdraw($id, $amount)
    {
        $result1 = $this->where('id', '=', $id)
                        ->where('balance', ">=", $amount)
                        ->where('withdraw_balance', ">=", $amount)
                        ->dec('balance', $amount)
                        ->dec('withdraw_balance', $amount)
                        ->inc('withdrawing_balance', $amount)
                        ->update();
        return $this->getNumRows() > 0;
    }

    public function rejectWithdraw($id, $amount)
    {
        $result1 = $this->where('id', '=', $id)
                        ->where('withdrawing_balance', ">=", $amount)
                        ->inc('balance', $amount)
                        ->inc('withdraw_balance', $amount)
                        ->dec('withdrawing_balance', $amount)
                        ->update();
        return $this->getNumRows() > 0;
    }

    public function finishWithdraw($id, $amount)
    {
        $result1 = $this->where('id', '=', $id)
                        ->where('withdrawing_balance', ">=", $amount)
                        ->dec('withdrawing_balance', $amount)
                        ->update();
        return $this->getNumRows() > 0;
    }
}