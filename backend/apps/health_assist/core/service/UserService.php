<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/13 14:59
 * @description
 */
namespace apps\health_assist\core\service;

use apps\health_assist\core\model\SystemNotice;
use apps\health_assist\core\model\User;
use apps\health_assist\core\model\UserAccountBook;
use think\Exception;
use vm\com\BaseModel;
use vm\com\BaseService;
use vm\com\logic\FileLogic;
use vm\org\EmojiUtil;

class UserService extends BaseService
{
    const ACCESS_TOKEN_CACHE_PREFIX = 'access_token:';
    /**
     * @var CacheService
     */
    protected $cacheService = null;

    /**
     * @var UserPlatformService
     */
    protected $userPlatformService = null;

    /**
     * @var UserRegisterRecordService
     */
    protected $userRegisterRecordService;

    protected $cachePrefix = 'user:';

    /**
     * @var FileLogic
     */
    protected $fileLogic;

    protected function init()
    {
        parent::init();
        $this->cacheService = service('Cache', SERVICE_NAMESPACE);
        $this->userPlatformService = service('UserPlatform', SERVICE_NAMESPACE);
        $this->userRegisterRecordService = service('UserRegisterRecord', SERVICE_NAMESPACE);
        $this->fileLogic = logic('File', '\vm\com\logic\\');
        $this->fileLogic->init([
            'rpc_server' => env('rpc_file.host') . '/file',
            'rpc_provider' => env('rpc_file.provider'),
            'rpc_token' => env('rpc_file.token'),
        ]);
    }

    /**
     * @return BaseService|UserAccountBookService
     */
    private function getUserAccountService()
    {
        return service('UserAccountBook', SERVICE_NAMESPACE);
    }

    /**
     * @return BaseService|UserWithdrawCashService
     */
    private function getUserWithdrawCashService()
    {
        return service('UserWithdrawCash', SERVICE_NAMESPACE);
    }

    /**
     * @return SiteConfigService
     */
    private function getSiteConfigService()
    {
        return service('SiteConfig', SERVICE_NAMESPACE);
    }

    /**
     * @return SystemNoticeService
     */
    private function getSystemNoticeService()
    {
        return service('SystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return UserSystemNoticeService
     */
    private function getUserSystemNoticeService()
    {
        return service('UserSystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return User|BaseModel
     */
    protected function getModel()
    {
        return new User();
    }

    public function create(array $data)
    {
        if(isset($data['nick']) && !empty($data['nick']) && in_array($data['nick'], ['admin', '管理员'])) {
            throw new Exception($data['nick'] . '不允许被使用');
        }
        if(isset($data['mobile']) && !check_mobile($data['mobile'])) {
            throw new Exception('手机号格式不正确');
        }

        $nick = EmojiUtil::filter($data['nick']);
        if(empty($nick)) {
            $nick = !empty($data['mobile']) ? hidePhoneNumber($data['mobile']) : rand_string(8);
        }
        $nickname = $nick;
        $i = 1;
        while (true) {
            if(!parent::info(['nick' => $nick])) {
                break;
            }
            $nick = $nickname . $i;
            $i++;
        }
        $data['nick'] = $nick;

        if(isset($data['password'])) {
            $data['password_salt'] = rand_string(6);
            $data['password'] = md5($data['password'] . $data['password_salt']);
        }
        $data['register_time'] = time();
        $userId = parent::create($data);
        if($userId) {
            // 注册事件
            event('UserRegister', $userId);
        }
        return $userId;
    }

    public function updateByPk(array $data)
    {
        if(isset($data['mobile']) && !check_mobile($data['mobile'])) {
            throw new Exception('手机号格式不正确');
        }
        $result = parent::updateByPk($data);
        if($result) {
            cache($this->cachePrefix . $data['id'], null);
        }
        return $result;
    }

    public function getByPk($id)
    {
        $data = parent::getByPk($id);
        if(empty($data)) {
            throw new Exception('用户不存在');
        }
        $data['nick'] = strip_tags($data['nick']);
        $data = arrayExcept($data, ['password', 'password_salt', 'mobile']);
        $data['avatar_url'] = $this->getAvatarUrl($data['avatar'], $id);
        $data['uuid'] = $this->getUuid($id);
        return $data;
    }

    public function info($param)
    {
        $data = parent::info($param);
        if($data) {
            $data['nick'] = strip_tags($data['nick']);
            $data = arrayExcept($data, ['password', 'password_salt', 'mobile']);
            $data['avatar_url'] = $this->getAvatarUrl($data['avatar'], $data['id']);
            $data['uuid'] = $this->getUuid($data['id']);
        }
        return $data;
    }

    public function deleteByPk($id)
    {
        $mobile = '';
        while (empty($mobile) || parent::info(['mobile' => $mobile])) {
            $mobile = User::VIRTUAL_MOBILE_PHONE_PREFIX . rand_string(8, 1);
        }
        $data = [
            'id' => $id,
            'real_name' => '',
            'avatar' => '',
            'nick' => $mobile,
            'email' => '',
            'mobile' => $mobile,
            'fromuid' => 0,
            'is_deleted' => 1
        ];
        parent::updateByPk($data);
        $this->userPlatformService->delete(['user_id' => $id]);
        event('UserDelete', $id);
        return true;
    }

    public function updatePasswordById($id, $password)
    {
        $passwordSalt = rand_string(6);
        $data = [
            'id' => $id,
            'password_salt' => $passwordSalt,
            'password' => md5($password . $passwordSalt)
        ];
        return $this->updateByPk($data);
    }

    public function modifyPasswordByMobilePhone($mobile, $password)
    {
        if(!parent::info(['mobile' => $mobile])) {
            throw new Exception('账号不存在');
        }
        $passwordSalt = rand_string(6, 1);
        return $this->update([
            'password' => md5($password . $passwordSalt),
            'password_salt' => $passwordSalt,
        ], [
            'mobile' => $mobile
        ]);
    }

    public function modifyNick($userId, $nick)
    {
        $user = parent::info(['nick' => $nick]);
        if($user && $userId != $user['id']) {
            throw new Exception($nick . '已被人使用');
        }
        return $this->updateByPk([
            'id' => $userId,
            'nick' => $nick
        ]);
    }

    public function modifyMobilePhone($userId, $mobile)
    {
        $user = parent::info(['mobile' => $mobile]);
        if($user && $userId != $user['id']) {
            throw new Exception($mobile . '已被人使用');
        }
        return $this->updateByPk([
            'id' => $userId,
            'mobile' => $mobile
        ]);
    }

    public function getUuid($userId)
    {
        $authKey = $this->getSiteConfigService()->getValueByCode('auth_key');
        $hash = md5($userId . $authKey);
        return substr($hash, 0, 16) . $userId . substr($hash, 16);
    }

    public function getByNick($nick)
    {
        return $this->info([
            'nick' => $nick
        ]);
    }

    public function getByMobile($mobile)
    {
        return $this->info([
            'mobile' => $mobile
        ]);
    }

    public function getMobile($userId)
    {
        $user = parent::getByPk($userId);
        return $user ? $user['mobile'] : '';
    }

    /**
     * 微信小程序授权注册
     * @param string $appId         --小程序AppId
     * @param array $userInfo       --用户信息
     * @param int $platform         --第三方平台类型
     * @return array
     * @throws Exception
     */
    public function registerByWeAppAuth($appId, array $userInfo, $platform)
    {
        $openid = $userInfo['openid'];
        $unionid = $userInfo['unionid'];
        $sessionKey = $userInfo['session_key'];
        $headimgurl = $userInfo['avatar_url'];
        $nickname = $userInfo['nick'];
        $fromuid = $userInfo['fromuid'];
        $mobile = $userInfo['mobile'];

        $userPlatform = $this->userPlatformService->getByOpenid($openid);
        if(empty($userPlatform)) {
            $userPlatform = [
                'id' => 0,
                'appid' => $appId,
                'user_id' => 0,
                'union_id' => ''
            ];
        }
        $userPlatform['open_id'] = $openid;
        $userPlatform['access_token'] = $sessionKey;
        $userPlatform['refresh_token'] = '';
        $userPlatform['access_token_expire'] = 0;
        $userPlatform['platform'] = $platform;
        if(!empty($unionid)) {
            $userPlatform['union_id'] = $unionid;
        }
        $nowtime = time();
        $user = [
            'last_login_time' => $nowtime
        ];
        if($userPlatform['user_id'] == 0) {
            $registered = false;
            if($unionid) {
                $unionUsers = $this->userPlatformService->getByUnionid($unionid);
                if($unionUsers) {
                    foreach ($unionUsers as $unionUser) {
                        if($unionUser['user_id'] > 0) {
                            $userPlatform['user_id'] = $unionUser['user_id'];
                            $user['id'] = $unionUser['user_id'];
                            $registered = true;
                            break;
                        }
                    }
                }
            }

            if(!$registered) {  //  从未使用微信账号登录过
                $mobileRegistered = false;
                if(!empty($mobile)) {
                    $mobileUser = $this->info(['mobile' => $mobile]);
                    if(!empty($mobileUser)) {   //  手机号已注册过账号
                        if($this->userPlatformService->getByUserIdWithAppid($appId, $mobileUser['id'])) { // 手机号已绑定其他微信账号
                            throw new Exception('该手机号已注册');
                        }
                        $userPlatform['user_id'] = $mobileUser['id'];
                        $user = $mobileUser;
                        $mobileRegistered = true;
                    }
                }
                if(!$mobileRegistered) {
                    if(empty($mobile)) {
                        while (true) {
                            $mobile = User::VIRTUAL_MOBILE_PHONE_PREFIX . rand_string(8, 1);
                            if(!parent::info(['mobile' => $mobile])) {
                                break;
                            }
                        }
                    }
                    // 保存微信头像到本地
                    if(strtolower(substr($headimgurl, 0, 4)) == 'http') {
                        $suffix = substr(strrchr($headimgurl, '.'), 1);
                        if(!in_array($suffix, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $suffix = 'jpeg';
                        }
                        $fileData = 'data:image/'.$suffix.';base64,' . base64_encode(file_get_contents($headimgurl));
                        $file = $this->fileLogic->upload($fileData);
                        $headimgurl = $file['id'];
                    } else if(strtolower(substr($headimgurl, 0, 4)) == 'data') {
                        $file = $this->fileLogic->upload($headimgurl);
                        $headimgurl = $file['id'];
                    }
                    $user['avatar'] = $headimgurl;

                    $nick = $nickname;
                    $user['nick'] = $nick;
                    $user['real_name'] = '';
                    $user['password'] = rand_string(8);
                    $user['mobile'] = $mobile;
                    $user['last_ip'] = getRealClientIp();
                    $user['intro'] = '';
                    $user['fromuid'] = $fromuid;

                    $user['id'] = $this->create($user);
                    $userPlatform['user_id'] = $user['id'];
                }
            }
        } else {
            $user['id'] = $userPlatform['user_id'];
            $this->updateByPk($user);
            $user = $this->getByPk($user['id']);
        }

        $this->userPlatformService->save($userPlatform);
        if(!empty($userPlatform['union_id'])) {
            $this->userPlatformService->update([
                'user_id' => $userPlatform['user_id']
            ], [
                'union_id' => $userPlatform['union_id']
            ]);
        }

        $user['uuid'] = $this->getUuid($user['id']);
        return $user;
    }

    /**
     * 支付宝小程序授权注册
     * @param string $appId         --小程序AppId
     * @param int  $fromuid         --推荐用户ID
     * @param array $userInfo     --微信用户信息
     * @param int $platform         --第三方平台类型
     * @return array
     * @throws Exception
     */
    public function registerByAliPayMiniAppAuth($appId, $fromuid, array $userInfo, $platform)
    {
        $openid = $userInfo['openid'];
        $unionid = $userInfo['unionid'];
        $sessionKey = $userInfo['session_key'];
        $expiresIn = $userInfo['expires_in'];
        $refreshToken = $userInfo['refresh_token'];
        $headimgurl = $userInfo['avatar_url'];
        $nickname = $userInfo['nick'];

        if(strtolower(substr($headimgurl, 0, 4)) == 'http') {
            $suffix = substr(strrchr($headimgurl, '.'), 1);
            if(!in_array($suffix, ['jpg', 'jpeg', 'png', 'gif'])) {
                $suffix = 'jpeg';
            }
            $fileData = 'data:image/'.$suffix.';base64,' . base64_encode(file_get_contents($headimgurl));
            $file = $this->fileLogic->upload($fileData);
            $headimgurl = $file['id'];
        } else if(strtolower(substr($headimgurl, 0, 4)) == 'data') {
            $file = $this->fileLogic->upload($headimgurl);
            $headimgurl = $file['id'];
        }

        $userPlatform = $this->userPlatformService->getByOpenid($openid);
        if(empty($userPlatform)) {
            $userPlatform = [
                'id' => 0,
                'appid' => $appId,
                'user_id' => 0,
                'union_id' => ''
            ];
        }
        $userPlatform['open_id'] = $openid;
        $userPlatform['access_token'] = $sessionKey;
        $userPlatform['refresh_token'] = $refreshToken;
        $userPlatform['access_token_expire'] = $expiresIn;
        $userPlatform['platform'] = $platform;
        if(!empty($unionid)) {
            $userPlatform['union_id'] = $unionid;
        }
        $nowtime = time();
        $user = [
            'avatar' => $headimgurl,
            'last_login_time' => $nowtime
        ];
        if($userPlatform['user_id'] == 0) {
            $registered = false;
            if($unionid) {
                $unionUsers = $this->userPlatformService->getByUnionid($unionid);
                if($unionUsers) {
                    foreach ($unionUsers as $unionUser) {
                        if($unionUser['user_id'] > 0) {
                            $userPlatform['user_id'] = $unionUser['user_id'];
                            $user['id'] = $unionUser['user_id'];
                            $registered = true;
                            break;
                        }
                    }
                }
            }

            if(!$registered) {  //  从未使用微信账号登录过
                $nick = $nickname;
                $user['nick'] = $nick;
                $user['real_name'] = '';
                $user['password'] = rand_string(8);

                $mobile = '';
                while (true) {
                    $mobile = User::VIRTUAL_MOBILE_PHONE_PREFIX . rand_string(8, 1);
                    if(!parent::info(['mobile' => $mobile])) {
                        break;
                    }
                }

                $user['mobile'] = $mobile;
                $user['last_ip'] = getRealClientIp();
                $user['intro'] = '';
                $user['fromuid'] = $fromuid;

                $user['id'] = $this->create($user);
                $userPlatform['user_id'] = $user['id'];
            }
        } else {
            $user['id'] = $userPlatform['user_id'];
            $this->updateByPk($user);
            $user = $this->getByPk($user['id']);
        }

        $this->userPlatformService->save($userPlatform);
        if(!empty($userPlatform['union_id'])) {
            $this->userPlatformService->update([
                'user_id' => $userPlatform['user_id']
            ], [
                'union_id' => $userPlatform['union_id']
            ]);
        }

        $user['uuid'] = $this->getUuid($user['id']);
        return $user;
    }

    public function loginByAccount($mobile, $password)
    {
        $user = parent::info(['mobile' => $mobile]);
        if(empty($user) || $user['is_deleted'] == 1) {  // 手机号未注册过
            throw new Exception('手机号未注册');
        }
        if($user['password'] != md5($password . $user['password_salt'])) {
            throw new Exception('手机号或密码错误');
        }
        $this->updateByPk([
            'id' => $user['id'],
            'last_login_time' => time(),
            'last_ip' => getRealClientIp()
        ]);
        $user = $this->getByPk($user['id']);
        $user['access_token'] = $this->genAccessToken($user['id']);
        $user['upload_token'] = $this->getUploadToken($user['id']);

        event('UserLogin', $user['id']);
        return $user;
    }

    public function loginById($userId)
    {
        $user = $this->getByPk($userId);
        if(empty($user) || $user['is_deleted'] == 1) {
            throw new Exception('用户不存在');
        }
        $this->updateByPk([
            'id' => $userId,
            'last_login_time' => time(),
            'last_ip' => getRealClientIp()
        ]);
        $user['access_token'] = $this->genAccessToken($user['id']);
        $user['upload_token'] = $this->getUploadToken($user['id']);
        event('UserLogin', $user['id']);
        return $user;
    }

    public function loginByOpenid($openid)
    {
        $userPlatform = $this->userPlatformService->getByOpenid($openid);
        if(empty($userPlatform) || $userPlatform['user_id'] == 0) {
            throw new Exception("用户不存在");
        }
        $user = $this->getByPk($userPlatform['user_id']);
        if(empty($user) || $user['is_deleted'] == 1) {
            throw new Exception('用户不存在');
        }
        $this->updateByPk([
            'id' => $userPlatform['user_id'],
            'last_login_time' => time(),
            'last_ip' => getRealClientIp()
        ]);
        $user['access_token'] = $this->genAccessToken($user['id']);
        $user['upload_token'] = $this->getUploadToken($user['id']);
        event('UserLogin', $user['id']);
        return $user;
    }

    public function logout($accessToken)
    {
        $this->cacheService->rm(self::ACCESS_TOKEN_CACHE_PREFIX . $accessToken);
    }

    public function loginByAccessToken($accessToken)
    {
        $user = $this->getAuth($accessToken);
        if(empty($user) || $user['is_deleted'] == 1) {
            throw new Exception('用户不存在');
        }
        $this->updateByPk([
            'id' => $user['id'],
            'last_login_time' => time(),
            'last_ip' => getRealClientIp()
        ]);
        $user['access_token'] = $this->genAccessToken($user['id']);
        $user['upload_token'] = $this->getUploadToken($user['id']);
        event('UserLogin', $user['id']);
        return $user;
    }

    public function getAuth($accessToken)
    {
        $cacheName = self::ACCESS_TOKEN_CACHE_PREFIX . $accessToken;
        $userId = $this->cacheService->get($cacheName);
        if($userId) {
            return $this->getByPk($userId);
        }
        return null;
    }

    public function genAccessToken($userId, $extra = '')
    {
        $accessToken = md5($userId . $extra . uniqid() . rand_string(30) . microtime());
        $cacheName = self::ACCESS_TOKEN_CACHE_PREFIX . $accessToken;
        $this->cacheService->set($cacheName, $userId, 0);
        $lastToken = null;
        $params = request()->param();
        if(isset($params['access_token']) && !empty($params['access_token'])) {
            $lastToken = $params['access_token'];
        }
        if(!is_null($lastToken)) {
            $this->cacheService->rm(self::ACCESS_TOKEN_CACHE_PREFIX . $lastToken);
        }
        return $accessToken;
    }

    public function getUploadToken($userId)
    {
        return $this->fileLogic->genToken('health_assist_user_' . $userId);
    }

    public function getAvatarUrl($avatar, $userId = 0)
    {
        $avatarUrl = '';
        if(is_numeric($avatar)) {
            $file = $this->fileLogic->file($avatar);
            if($file) {
                $avatarUrl = $file['url'];
            }
        } else {
            if(strtolower(substr($avatar, 0, 4)) == 'http' && $userId > 0) {
                $suffix = substr(strrchr($avatar, '.'), 1);
                $fileData = 'data:image/'.$suffix.';base64,' . base64_encode(file_get_contents($avatar));
                $file = $this->fileLogic->upload($fileData);
                $avatarUrl = $file['url'];
                $this->updateByPk([
                    'id' => $userId,
                    'avatar' => $file['id']
                ]);
            } else {
                $avatarUrl = $avatar;
            }
        }
        return $avatarUrl;
    }

    /**
     * 设置vip等级
     * @param int $userId
     * @param int $vipLevel
     * @return bool
     */
    public function onSetVipLevel($userId, $vipLevel, $expireDays = null)
    {
        $vips = $this->getSiteConfigService()->getValueByCode('vips');
        $vip = $vips['level_' . $vipLevel];
        if(!is_null($expireDays)) {
            $expireUnit = substr($expireDays, -1, 1);
            $expireNum = intval(substr($expireDays, 0, -1));
        } else {
            $expireUnit = substr($vip['expire_days'], -1, 1);
            $expireNum = intval(substr($vip['expire_days'], 0, -1));
        }
        $user = $this->getByPk($userId);
        $nowtime = time();
        $expireBeginTime = 0;
        $expireEndTime = 0;
        $expireEndBaseTime = 0;
        if($user['vip_level'] == 0) {
            $expireBeginTime = time();
            $expireEndBaseTime = $expireBeginTime;
        } else {
            if($user['vip_expire_end_time'] <= $nowtime) {   // 原会员已过期
                $expireBeginTime = time();
                $expireEndBaseTime = $expireBeginTime;
            } else {
                $expireBeginTime = $user['vip_expire_begin_time'];
                $expireEndBaseTime = $user['vip_expire_end_time'];
            }
        }
        if($expireUnit == 'm') {
            $expireEndTime = strtotime("+".$expireNum." month", $expireEndBaseTime);
        } else if($expireUnit == 'y') {
            $expireEndTime = strtotime("+".$expireNum." year", $expireEndBaseTime);
        } else if($expireUnit == 'd') {
            $expireEndTime = strtotime("+".$expireNum." day", $expireEndBaseTime);
        }
        $result = $this->updateByPk([
            'id' => $userId,
            'vip_level' => $vipLevel,
            'vip_expire_begin_time' => $expireBeginTime,
            'vip_expire_end_time' => $expireEndTime
        ]);
        if($result) {
            return true;
        }
        throw new Exception('vip更新失败');
    }

    /**
     * 刷新用户等级
     * 将已过期的vip用的会员等级改为普通用户
     * @return void
     */
    public function onRefreshUserVipLevel()
    {
        $nowtime = time();
        $params = [
            'vip_level' => ['>', 0],
            'vip_expire_end_time' => ['<', $nowtime]
        ];
        $res = $this->pageListByParams($params, 20, ['page' => 1], ['vip_expire_end_time' => 'ASC']);
        if(!empty($res['data'])) {
            $systemNoticeService = $this->getSystemNoticeService();
            foreach ($res['data'] as $item) {
                $this->getModel()
                    ->where('id', $item['id'])
                    ->where('vip_level', '>', 0)
                    ->where('vip_expire_end_time', '<', $nowtime)
                    ->save([
                        'vip_level' => 0,
                        'vip_expire_begin_time' => 0,
                        'vip_expire_end_time' => 0
                    ]);
                // 给用户发送通知
                $systemNoticeId = $systemNoticeService->create([
                    'title' => '会员已过期',
                    'content' => '您的vip会员已过期，请重新购买。',
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_WAIT_PULL,
                    'recipient_id' => $item['id'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/my/pages/vip/index',
                        'web' => '/vip/index',
                        'app' => '/my/pages/vip/index',
                    ])
                ]);
            }
        }
    }

    public function increaseBalance($id, $amount, $record = true, $remark = '', $iidType = null, $iid = null, $extend = null)
    {
        if($this->getModel()->increaseBalance($id, $amount)) {
            if($record) {
                return $this->getUserAccountService()->record($id, UserAccountBook::OP_INCREASE, $amount, UserAccountBook::MONEY_TYPE_MONEY, $iidType, $iid, $extend, $remark);
            }
            return true;
        } else {
            throw new Exception('操作失败');
        }
    }

    public function decreaseBalance($id, $amount, $record = true, $remark = '', $iidType = null, $iid = null, $extend = null)
    {
        if($this->getModel()->decreaseBalance($id, $amount)) {
            if($record) {
                return $this->getUserAccountService()->record($id, UserAccountBook::OP_DECREASE, $amount, UserAccountBook::MONEY_TYPE_MONEY, $iidType, $iid, $extend, $remark);
            }
            return true;
        } else {
            throw new Exception('操作失败');
        }
    }

    public function increaseWithdrawBalance($id, $amount, $record = true, $remark = '', $iidType = null, $iid = null, $extend = null)
    {
        if($this->getModel()->increaseWithdrawBalance($id, $amount)) {
            if($record) {
                return $this->getUserAccountService()->record($id, UserAccountBook::OP_INCREASE, $amount, UserAccountBook::MONEY_TYPE_MONEY, $iidType, $iid, $extend, $remark);
            }
            return true;
        } else {
            throw new Exception('操作失败');
        }
    }

    public function decreaseWithdrawBalance($id, $amount, $record = true, $remark = '', $iidType = null, $iid = null, $extend = null)
    {
        if($this->getModel()->decreaseWithdrawBalance($id, $amount)) {
            if($record) {
                return $this->getUserAccountService()->record($id, UserAccountBook::OP_DECREASE, $amount, UserAccountBook::MONEY_TYPE_MONEY, $iidType, $iid, $extend, $remark);
            }
            return true;
        } else {
            throw new Exception('操作失败');
        }
    }

    public function applyWithdraw($id, $amount, $applyId)
    {
        if(!is_numeric($amount) || $amount <= 0) {
            throw new Exception('参数错误');
        }
        if($this->getModel()->applyWithdraw($id, $amount)) {
            $iid = $applyId;
            $iidType = UserAccountBook::IID_TYPE_WITHDRAW_CASH;
            return $this->getUserAccountService()->record($id, UserAccountBook::OP_DECREASE, $amount, UserAccountBook::MONEY_TYPE_MONEY, $iidType, $iid, null, '提现');
        } else {
            throw new Exception('操作失败');
        }
    }

    public function rejectWithdraw($id, $amount, $applyId)
    {
        if($this->getModel()->rejectWithdraw($id, $amount)) {
            return $this->getUserAccountService()->record($id, UserAccountBook::OP_INCREASE, $amount, UserAccountBook::MONEY_TYPE_MONEY, UserAccountBook::IID_TYPE_WITHDRAW_CASH, $applyId, null, '提现退回');
        } else {
            throw new Exception('操作失败');
        }
    }

    public function finishWithdraw($id, $amount)
    {
        if(!$this->getModel()->finishWithdraw($id, $amount)) {
            throw new Exception('操作失败');
        }
    }

    public function increaseGoldCoin($id, $amount, $record = true, $remark = '', $iidType = null, $iid = null, $extend = null)
    {
        if($this->getModel()->increase('gold_coin', ['id' => $id], $amount)) {
            if($record) {
                return $this->getUserAccountService()->record($id, UserAccountBook::OP_INCREASE, $amount, UserAccountBook::MONEY_TYPE_GOLD_COIN, $iidType, $iid, $extend, $remark);
            }
            return true;
        } else {
            throw new Exception('操作失败');
        }
    }

    public function decreaseGoldCoin($id, $amount, $record = true, $remark = '', $iidType = null, $iid = null, $extend = null)
    {
        if($this->getModel()->decrease('gold_coin', ['id' => $id, 'gold_coin' => ['>=', $amount]], $amount)) {
            if($record) {
                return $this->getUserAccountService()->record($id, UserAccountBook::OP_DECREASE, $amount, UserAccountBook::MONEY_TYPE_GOLD_COIN, $iidType, $iid, $extend, $remark);
            }
            return true;
        } else {
            throw new Exception('扣除金币失败');
        }
    }


    public function count(array $params = [])
    {
        $model = $this->getModel();
        foreach ($params as $key => $val) {
            if($key == 'register_begin_time') {
                $model = $model->where('register_time', '>=', $val);
            } else if($key == 'register_end_time') {
                $model = $model->where('register_time', '<=', $val);
            } else if($key == 'login_begin_time') {
                $model = $model->where('last_login_time', '>=', $val);
            } else if($key == 'login_end_time') {
                $model = $model->where('last_login_time', '<=', $val);
            } else {
                $model = $model->where($key, $val);
            }
        }
        return $model->count();
    }
}