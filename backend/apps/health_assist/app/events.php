<?php


\think\facade\Event::listen('UserRegister', function($userId) {
    /** @var \apps\health_assist\core\service\UserService $userService */
    $userService = service('User', SERVICE_NAMESPACE);
    /** @var \apps\health_assist\core\service\UserRegisterRecordService $userRegisterRecordService */
    $userRegisterRecordService = service('UserRegisterRecord', SERVICE_NAMESPACE);
    $user = $userService->getByPk($userId);
    $mobile = $userService->getMobile($userId);
    // 添加注册记录
    $userRegisterRecordService->create([
        'user_id' => $userId,
        'mobile' => $mobile
    ]);

//    if($user['fromuid'] > 0 && $userRegisterRecordService->count(['mobile' => $mobile]) == 1) {
//        $userService->onSetVipLevel($user['fromuid'], 1, '3d');   // 给推荐人赠送3天VIP会员
//
//        // 给用户发送通知
//        /** @var \apps\health_assist\core\service\SystemNoticeService $systemNoticeService */
//        $systemNoticeService = service('SystemNotice', SERVICE_NAMESPACE);
//        $systemNoticeId = $systemNoticeService->create([
//            'title' => '恭喜您免费获得3天VIP',
//            'content' => '您已免费获得3天vip会员权益，请点击查看。',
//            'type' => \apps\health_assist\core\model\SystemNotice::TYPE_SINGLE,
//            'status' => \apps\health_assist\core\model\SystemNotice::STATUS_WAIT_PULL,
//            'recipient_id' => $user['fromuid'],
//            'manager_id' => 0,
//            'url' => json_encode([
//                'weapp' => '/my/pages/vip/index',
//                'web' => '/vip/index',
//                'app' => '/my/pages/vip/index'
//            ])
//        ]);
//    }
});

\think\facade\Event::listen('UserLogin', function($userId) {
    /** @var \apps\health_assist\core\service\UserLoggingService $userLoggingService */
    $userLoggingService = service('UserLogging', SERVICE_NAMESPACE);
    $userLoggingService->create([
        'user_id' => $userId,
        'login_time' => time(),
        'login_date' => strtotime(date('Ymd 00:00:00')),
        'login_ip' => getRealClientIp()
    ]);
});

\think\facade\Event::listen('UserLogout', function($userId) {

});

\think\facade\Event::listen('UserDelete', function($userId) {
    \think\facade\Log::info('UserDelete:' . $userId);
});