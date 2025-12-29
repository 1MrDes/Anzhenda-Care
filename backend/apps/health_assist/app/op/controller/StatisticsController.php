<?php


namespace apps\health_assist\app\op\controller;

use apps\health_assist\core\service\UserService;

class StatisticsController extends BaseHealthAssistOpController
{
    /**
     * @var UserService
     */
    private $userService;

    protected function init()
    {
        parent::init();
        $this->userService = service('User', SERVICE_NAMESPACE);
    }

    public function user()
    {
        $todayBeginTime = strtotime(date('Ymd 00:00:00'));
        $todayEndTime = strtotime(date('Ymd 23:59:59'));
        $yesterdayBeginTime = $todayBeginTime - 24*3600;
        $yesterdayEndTime = $todayEndTime - 24*3600;
        $register = [
            'all' => $this->userService->count(),
            'today' => $this->userService->count(['register_begin_time' => $todayBeginTime, 'register_end_time' => $todayEndTime]),
            'yesterday' => $this->userService->count(['register_begin_time' => $yesterdayBeginTime, 'register_end_time' => $yesterdayEndTime]),
        ];
        $login = [
            'today' => $this->userService->count(['login_begin_time' => $todayBeginTime, 'login_end_time' => $todayEndTime]),
            'yesterday' => $this->userService->count(['login_begin_time' => $yesterdayBeginTime, 'login_end_time' => $yesterdayEndTime]),
        ];
        return $this->success([
            'register' => $register,
            'login' => $login
        ]);
    }

}