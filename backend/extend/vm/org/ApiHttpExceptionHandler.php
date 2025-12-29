<?php
/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-16 15:07
 * Description:
 */

namespace vm\org;


use Exception;
use think\exception\Handle;
use think\facade\Log;

class ApiHttpExceptionHandler extends Handle
{
    public function render(Exception $e)
    {
        $code = $e->getCode() === 0 ? -1 : $e->getCode();
//        $code = $e->getCode();
        if(ENV == 'release') {
            $msg = $e->getMessage();
        } else {
            Log::error("File:" .$e->getFile() . "\r\n" . "LINE:" . $e->getLine() . "\r\n" . $e->getMessage());
            $msg = $e->getMessage();
        }
        $data = [
            'code' => $code,
            'msg' => $msg,
            'data' => ''
        ];
        ob_end_clean();
        response($data, 200, [], 'json')->send();
        exit();
    }
}