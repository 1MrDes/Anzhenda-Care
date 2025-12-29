<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/13 15:01
 * @description
 */

namespace apps\health_assist\core\service;


use apps\health_assist\core\model\UserPlatform;
use vm\com\BaseModel;
use vm\com\BaseService;

class UserPlatformService extends BaseService
{

    /**
     * @return UserPlatform|BaseModel
     */
    protected function getModel()
    {
        return new UserPlatform();
    }

    public function getByOpenid($openid)
    {
        return $this->info([
            'open_id' => $openid
        ]);
    }

    public function getByUnionid($unionid)
    {
        return $this->getAll([
            'union_id' => $unionid
        ]);
    }

    public function getByUserId($userId)
    {
        return $this->getAll([
            'user_id' => $userId
        ]);
    }

    public function getByUserIdWithAppid($appid, $userId)
    {
        return $this->info([
            'appid' => $appid,
            'user_id' => $userId
        ]);
    }

    public function save(array $data)
    {
        if($data['id'] == 0) {
            return $this->create($data);
        } else {
            return $this->updateByPk($data);
        }
    }
}