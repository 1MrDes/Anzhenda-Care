<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/31 15:04
 * @description
 */

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\Admin;
use think\Exception;
use think\helper\Str;
use think\Validate;
use vm\com\BaseService;

class AdminService extends BaseService
{
    private $authCachePrefix = 'u:';
    private $cacheExpire = 3600*24*30;

    /**
     * @return Admin
     */
    protected function getModel()
    {
        return new Admin();
    }

    /**
     * 登录
     * @param $username
     * @param $password
     * @return array
     * @throws Exception
     */
    public function login($username, $password)
    {
        $validate = new Validate([
            'username'  => 'require',
            'password' => 'require'
        ]);
        if(!$validate->check(['username' => $username, 'password' => $password])) {
            throw new Exception('参数错误');
        }
        $admin = $this->getModel()->getByUsername($username);
        if(empty($admin)) {
            throw new Exception("帐号不存在");
        } else {
            if($admin['password'] != md5($password . $admin['salt'])) {
                throw new Exception('密码不正确');
            } elseif($admin['is_locked'] == 1) {
                throw new Exception('帐号已被锁定');
            } else {
                $token = md5($admin['id'] . $admin['username'] . $password . time() . Str::random(6));
                $data = [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'real_name' => $admin['real_name'],
                    'is_super' => $admin['is_super'],
                    'access_token' => $token
                ];
                $cacheName = $this->authCachePrefix . $token;
                cache($cacheName, $data, $this->cacheExpire);

                $data['resources'] = $this->getResources($admin['id']);

                $rs = [
                    'id' => $admin['id'],
                    'last_login_ip' => getRealClientIp(),
                    'last_login_time' => time()
                ];
                $this->updateByPk($rs);

                return $data;
            }
        }
    }

    public function loginByToken($token)
    {
        $data = $this->getAuth($token);
        if($data) {
            $admin = $this->getByPk($data['id']);
            if($admin['is_locked'] == 1) {
                throw new Exception('帐号已被锁定');
            }
            $data['resources'] = $this->getResources($data['id']);
            $rs = [
                'id' => $data['id'],
                'last_login_ip' => getRealClientIp(),
                'last_login_time' => time()
            ];
            $this->updateByPk($rs);
            return $data;
        } else {
            throw new Exception('用户不存在');
        }
    }

    public function logout($token)
    {
        $cacheName = $this->authCachePrefix . $token;
        cache($cacheName, null);
    }

    public function getResources($userId)
    {
        /** @var AdminBelongRoleService $adminRoleService */
        $adminRoleService = service('AdminBelongRole', SERVICE_NAMESPACE);
        /** @var RoleBelongResourceService $roleResourceService */
        $roleResourceService = service('RoleBelongResource', SERVICE_NAMESPACE);
        /** @var AdminResourceService $resourceService */
        $resourceService = service('AdminResource', SERVICE_NAMESPACE);
        $resources = array();
        $userRoles = $adminRoleService->getByUserId($userId);
        if(!empty($userRoles)) {
            foreach ($userRoles as $userRole) {
                $roleResources = $roleResourceService->getByRoleId($userRole['role_id']);
                if(!empty($roleResources)) {
                    foreach ($roleResources as $roleResource) {
                        $resource = $resourceService->getByPk($roleResource['resource_id']);
                        if(stripos($resource['url'], ',') === false) {
                            if(!in_array($resource['url'], $resources)) {
                                $resources[] = $resource['url'];
                            }
                        } else {
                            $urls = explode(',', $resource['url']);
                            foreach ($urls as $url) {
                                if(!in_array($url, $resources)) {
                                    $resources[] = $url;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $resources;
    }

    /**
     * 获取认证信息
     * @param $token
     * @return mixed
     * @throws Exception
     */
    public function getAuth($token)
    {
        $cacheName = $this->authCachePrefix . $token;
        if($data = cache($cacheName)) {
            return $data;
        } else {
            return null;
        }
    }

    /**
     * 添加账号
     * @param array $data
     * @param array $roleIds      角色
     * @throws Exception
     */
    public function addAdmin(array $data, array $roleIds)
    {
        $validate = new Validate([
            'username'  => 'require',
            'password' => 'require',
            'mobile' => 'require',
            'real_name' => 'require'
        ]);
        if(!$validate->check($data)) {
            throw new Exception('参数错误');
        }
        if(empty($roleIds)) {
            throw new Exception('请选择角色');
        }
        $oldAccount = $this->getModel()->getByUsername($data['username']);
        if(!empty($oldAccount)) {
            throw new Exception('账号已存在');
        }
        if(isset($data['is_super'])) {
            unset($data['is_super']);
        }
        $dateline = time();
        $data['salt'] = Str::random(6);
        $data['password'] = md5($data['password'] . $data['salt']);
        $data['create_time'] = $dateline;
        $data['update_time'] = $dateline;
        $id = $this->create($data);
        // 处理角色
        if($id && !empty($roleIds)) {
            $backendUserRoleService = service('BackendUserRole', SERVICE_NAMESPACE);
            foreach ($roleIds as $roleId) {
                $rs = [
                    'user_id' => $id,
                    'role_id' => $roleId
                ];
                $backendUserRoleService->create($rs);
            }
        }
    }

    /**
     * 编辑
     * @param array $data
     * @param array $roleIds
     */
    public function renew(array $data, array $roleIds)
    {
        if(!empty($data['password'])) {
            $data['salt'] = Str::random(6);
            $data['password'] = md5($data['password'] . $data['salt']);
        } else {
            unset($data['password']);
        }
        if(isset($data['is_super'])) {
            unset($data['is_super']);
        }
        $dateline = time();
        $data['update_time'] = $dateline;
        unset($data['username']);   // 不允许修改用户名
        unset($data['real_name']);   // 不允许修改真实姓名
        $result = $this->updateByPk($data);
        // 处理用户角色
        if($result){
            $backendUserRoleService = service('AdminRole', SERVICE_NAMESPACE);
            $backendUserRoleService->deleteByUserId($data['id']);
            if(!empty($roleIds)) {
                foreach ($roleIds as $roleId) {
                    $rs = [
                        'user_id' => $data['id'],
                        'role_id' => $roleId
                    ];
                    $backendUserRoleService->create($rs);
                }
            }
        } else {
            throw new Exception('操作失败');
        }
    }

    /**
     * 锁定
     * @param $id
     * @return false|int
     * @throws Exception
     */
    public function lock($id)
    {
        $admin = $this->getByPk($id);
        if($admin == null) {
            throw new Exception('帐号不存在');
        }
        if($admin['is_super'] == 1) {
            throw new Exception('超级管理员不允许锁定');
        }

        $data = [
            'id' => $id,
            'is_locked' => 1
        ];
        $result = $this->updateByPk($data);
        if($result) {
            return $result;
        }
        throw new Exception('操作失败');
    }

    /**
     * 解锁
     * @param $id
     * @return bool|false|int
     * @throws Exception
     */
    public function unlock($id)
    {
        $admin = $this->getByPk($id);
        if($admin == null) {
            throw new Exception('帐号不存在');
        }
        if($admin['is_super'] == 1) {
            throw new Exception('超级管理员不允许锁定');
        }

        $data = [
            'id' => $id,
            'is_locked' => 0
        ];
        $result = $this->updateByPk($data);
        if($result) {
            return $result;
        }
        throw new Exception('操作失败');
    }

    /**
     * 设置密码
     * @param $id
     * @param $password
     * @return false|int
     * @throws Exception
     */
    public function setPassword($id, $password)
    {
        $admin = $this->getByPk($id);
        if($admin == null) {
            throw new Exception('帐号不存在');
        }
        $data['id'] = $id;
        $data['salt'] = Str::random(6);
        $data['password'] = md5($password . $data['salt']);
        $result = $this->updateByPk($data);
        return $result;
    }

}