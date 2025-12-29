<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/31 22:59
 * @description
 */

namespace apps\base\app\op\controller;


class IndexController extends BaseOpController
{
    public function index()
    {
        return $this->success('hello');
    }
}