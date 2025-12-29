<?php


namespace apps\health_assist\app\api\controller;

use apps\health_assist\core\model\UserPlatform;
use apps\health_assist\core\service\CacheService;
use apps\health_assist\core\service\SiteConfigService;
use apps\health_assist\core\service\UserPlatformService;
use apps\health_assist\core\service\UserService;
use think\Exception;
use think\facade\Log;
use apps\health_assist\app\Request;
use vm\com\logic\AliPayMiniLogic;
use vm\com\logic\WechatMiniAppLogic;
use vm\org\emoji\Emoji;
use vm\org\WXBizDataCrypt;

class UserController extends BaseHealthAssistApiController
{
    /**
     * @var WechatMiniAppLogic
     */
    protected $wxMiniLogic = null;
    /**
     * @var UserService
     */
    protected $userService = null;
    /**
     * @var UserPlatformService
     */
    protected $userPlatformService = null;

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * @var SiteConfigService
     */
    private $siteConfigService;

    protected function init()
    {
        parent::init();
        $this->userService = service('User', SERVICE_NAMESPACE);
        $this->userPlatformService = service('UserPlatform', SERVICE_NAMESPACE);
        $this->cacheService = service('Cache', SERVICE_NAMESPACE);
        $this->siteConfigService = service('SiteConfig', SERVICE_NAMESPACE);
        $this->wxMiniLogic = logic('WechatMiniApp', '\vm\com\logic\\');
        $this->wxMiniLogic->init([
            'app_id' => $this->siteConfigService->getValueByCode('weapp_app_id'),
            'app_secret' => $this->siteConfigService->getValueByCode('weapp_app_secret'),
            'app_token' => $this->siteConfigService->getValueByCode('weapp_app_token'),
            'encode_aeskey' => $this->siteConfigService->getValueByCode('weapp_app_encoding_aeskey'),
        ]);
    }

    /**
     * @return AliPayMiniLogic
     */
    private function getAliPayMiniLogic()
    {
        static $logic = null;
        if(!is_null($logic)) {
            return $logic;
        }
        $site = config('site');
        /** @var AliPayMiniLogic $logic */
        $logic = logic('AliPayMini', '\vm\com\logic\\');
        $logic->init([
            'app_id' => $site['alipay']['miniapp']['app_id'],
            'app_cert_path' => $site['alipay']['miniapp']['app_cert_path'],
            'alipay_cert_path' => $site['alipay']['miniapp']['alipay_cert_path'],
            'root_cert_path' => $site['alipay']['miniapp']['root_cert_path'],
            'rsa_private_key' => trim(file_get_contents($site['alipay']['miniapp']['app_cert_private_path']))
        ]);
        return $logic;
    }

    public function save_msg_template(Request $request)
    {

    }

    public function simple_info(Request $request)
    {
        $uid = $request->param('uid', 0, 'intval');
        if ($uid == 0) {
            throw new Exception('用户不存在');
        }
        $user = $this->userService->getByPk($uid);
        $userInfo = arrayOnly($user, ['id', 'nick', 'avatar', 'avatar_url']);
        return $this->success($userInfo);
    }

    public function login_by_token(Request $request)
    {
        $accessToken = $request->param('access_token', '');
        $user = $this->userService->getAuth($accessToken);
        if(empty($user)) {
            throw new Exception('登录失败');
        }
        $user['access_token'] = $accessToken;
        $user['upload_token'] = $this->userService->getUploadToken($user['id']);
        return $this->success([
            'user' => $user
        ]);
    }

    public function weapp_decrypt_msg(Request $request)
    {
        $openid = $request->param('openid', '');
        $encryptedData = $request->param('encryptedData', '');
        $iv = $request->param('iv', '');
        if (empty($openid) || empty($encryptedData) || empty($iv)) {
            throw new Exception('参数不能为空');
        }
        $appId = $this->siteConfigService->getValueByCode('weapp_app_id');
        $userPlatform = $this->userPlatformService->getByOpenid($openid);
        if(empty($userPlatform)) {
            throw new Exception('无法解密');
        }
        $sessionKey = $userPlatform['access_token'];
        $data = '';
        $wXBizDataCrypt = new WXBizDataCrypt($appId, $sessionKey);
        $result = $wXBizDataCrypt->decryptData($encryptedData, $iv, $data);
        if ($result != WXBizDataCrypt::$OK) {
            Log::error('微信数据解密失败,errcode:' . $result);
            throw new Exception('发生错误，请重试');
        }
        $data = json_decode($data, true);
        return $this->success($data);
    }

    public function weapp_bind_phone_number(Request $request)
    {
        $encryptedData = $request->param('encrypted_data', '');
        $iv = $request->param('iv', '');
        $loginCode = $request->param('login_code', '');
        if (empty($loginCode) || empty($encryptedData) || empty($iv)) {
            throw new Exception('参数不能为空');
        }
        $ret = $this->wxMiniLogic->getOpenIdByCode($loginCode);
        $openid = $ret['openid'];
        $sessionKey = $ret['session_key'];
        $appId = $this->siteConfigService->getValueByCode('weapp_app_id');
        $data = '';
        $wXBizDataCrypt = new WXBizDataCrypt($appId, $sessionKey);
        $result = $wXBizDataCrypt->decryptData($encryptedData, $iv, $data);
        if ($result != WXBizDataCrypt::$OK) {
            Log::error('微信数据解密失败,errcode:' . $result);
            throw new Exception('发生错误，请重试');
        }
        $data = json_decode($data, true);
        $phoneNumber = $data['phoneNumber'];
        $userPlatform = $this->userPlatformService->getByOpenid($openid);
        if($userPlatform && $userPlatform['user_id'] > 0) {
            $this->userService->modifyMobilePhone($userPlatform['user_id'], $phoneNumber);
        }
        return $this->success(['mobile' => $phoneNumber]);
    }

    public function weapp_login_by_code(Request $request)
    {
        $code = $request->param('code');
        if (empty($code)) {
            throw new Exception('code为空');
        }
        $ret = $this->wxMiniLogic->getOpenIdByCode($code);
        $openid = $ret['openid'];
        try {
            $sessionKey = $ret['session_key'];
            $userPlatform = $this->userPlatformService->getByOpenid($openid);
            $this->userPlatformService->updateByPk([
                'id' => $userPlatform['id'],
                'access_token' => $sessionKey
            ]);
            $user = $this->userService->loginByOpenid($openid);
            return $this->success([
                'openid' => $openid,
                'user' => $user,
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
        return $this->error(106, '未登录');
    }

    public function weapp_login_auth(Request $request)
    {
        $code = $request->param('code');
        if (empty($code)) {
            throw new Exception('code为空');
        }
        $ret = $this->wxMiniLogic->getOpenIdByCode($code);
        $openid = $ret['openid'];
        $sessionKey = $ret['session_key'];
        $unionid = isset($ret['unionid']) ? $ret['unionid'] : '';

        $fromuid = $request->param('fromuid', 0, 'intval');
        $nick = $request->param('nick', '');
        $avatarUrl = $request->param('avatar_url', '');

        if (empty($openid) || empty($nick)) {
            throw new Exception('参数不能为空');
        }

        $userInfo = [
            'openid' => $openid,
            'session_key' => $sessionKey,
            'unionid' => $unionid,
            'nick' => $nick,
            'avatar_url' => $avatarUrl,
            'fromuid' => $fromuid,
            'mobile' => ''
        ];

        $appId = $this->siteConfigService->getValueByCode('weapp_app_id');
        $user = $this->userService->registerByWeAppAuth($appId, $userInfo, UserPlatform::PLATFORM_WX_MINI);
        $userDto = $this->userService->loginById($user['id']);
        $userDto['openid'] = $openid;
        return $this->success([
            'user' => $userDto,
            'openid' => $openid,
        ]);
    }

    public function alipay_login_auth(Request $request)
    {
        $code = $request->param('code', '');
        if (empty($code)) {
            throw new Exception('code为空');
        }
        $response = $this->getAliPayMiniLogic()->getAuthTokenInfo($code);
        $openid = $response->user_id;
        $sessionKey = $response->access_token;
        $expiresIn = $response->expires_in;
        $reExpiresIn = $response->re_expires_in;
        $refreshToken = $response->refresh_token;

        $fromuid = $request->param('fromuid', 0, 'intval');
        $nick = $request->param('nick', '');
        $avatarUrl = $request->param('avatar_url', '');

        if (empty($openid) || empty($nick)) {
            throw new Exception('参数不能为空');
        }

        $userInfo = [
            'openid' => $openid,
            'session_key' => $sessionKey,
            'expires_in' => $expiresIn,
            'refresh_token' => $refreshToken,
            'unionid' => '',
            'nick' => $nick,
            'avatar_url' => $avatarUrl,
        ];
        $site = config('site');
        $appId = $site['alipay']['miniapp']['app_id'];
        $user = $this->userService->registerByAliPayMiniAppAuth($appId, $fromuid, $userInfo, UserPlatform::PLATFORM_ALIPAY_MINI);
        $userDto = $this->userService->loginById($user['id']);
        $userDto['openid'] = $openid;
        return $this->success([
            'user' => $userDto,
            'openid' => $openid,
        ]);
    }
}