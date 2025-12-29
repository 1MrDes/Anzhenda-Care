<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-3
 * Time: 10:27
 */

namespace vm\com;


use think\Exception;

abstract class RestController
{

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        try {
            $method = request()->method();
            if($method == 'OPTIONS') {
                $response = response(null, 200, [], 'json');
                $response->send();
                exit();
            }
            $this->auth();
        } catch (Exception $e) {
            $data = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'data' => ''
            ];
            $response = response($data, 200, [], 'json');
            $response->send();
            exit();
        }
    }

    protected function auth()
    {

    }

    protected function response($code, $msg, $data)
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    protected function success($data = null)
    {
        return $this->response(0, '', $data);
    }

    protected function error($code, $msg)
    {
        return $this->response($code, $msg, '');
    }

}