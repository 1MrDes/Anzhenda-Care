<?php
/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-24 16:46
 * Description:
 */

namespace apps\file\core\service;


use apps\file\core\model\UploadedFile;
use think\Exception;
use think\File;
use think\helper\Str;
use vm\com\BaseService;

class UploadedFileService extends BaseService
{

    private $tokenCachePrefix = 'upload_token:';
    protected $fileAuthKeyCachePrefix = 'file_auth_key:';

    /**
     * @var CacheService
     */
    protected $cacheService;

    protected $cachePrefix = 'file:';

    protected function init()
    {
        parent::init();
        $this->cacheService = service('Cache', SERVICE_NAMESPACE);
    }

    /**
     * @return UploadedFile
     */
    protected function getModel()
    {
        return new UploadedFile();
    }

    public function deleteByPk($id)
    {
        $file = $this->getByPk($id);
        $result = parent::deleteByPk($id);
        if($result) {
            if(is_file(WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR . $file['path'])) {
                @unlink(WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR . $file['path']);
            }
            cache($this->cachePrefix . $id, null);
        }
        return $result;
    }

    public function batchDelete(array $ids)
    {
        foreach ($ids as $id) {
            $file = $this->getByPk($id);
            $result = parent::deleteByPk($id);
            if($result) {
                if(is_file(WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR . $file['path'])) {
                    @unlink(WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR . $file['path']);
                }
                cache($this->cachePrefix . $id, null);
            }
        }
        return true;
    }

    public function getByPk($id)
    {
        if($file = cache($this->cachePrefix . $id)) {
            return $file;
        }
        $file = parent::getByPk($id);
        if($file) {
            cache($this->cachePrefix . $id, $file, 3600*24*365);
        }
        return $file;
    }

    public function submit(File $file, $host = '', $scheme = '')
    {
        $data = [
            'name' => $file->getBasename(),
            'type' => $file->getMime(),
            'size' => $file->getSize(),
            'path' => $file->savename,
            'dateline' => time()
        ];
        $id = parent::create($data);
        if($id) {
            $data['id'] = $id;
            // $protocol = request()->param('protocol', 'http');
            // $data['path'] = $protocol . '://' . $_SERVER['HTTP_HOST'] . getContextPath() . 'uploads/' . $data['path'] . '?__id=' . $id;
            if(empty($scheme)) {
                $scheme = request()->scheme();
            }
            if(empty($host)) {
                if(ENV == 'release') {
                    $host = env('UPLOAD_HOST') . '/';
                } else {
                    $host = request()->host() . '/' . getContextPath();
                }
            } else {
                $host = $host . '/' . getContextPath();
            }
            $data['url'] = $scheme . '://' . $host . 'uploads/' . $data['path'] . '?__id=' . $id;
            return $data;
        }
        throw new Exception('上传失败');
    }

    public function save(array $file, $host = '', $scheme = '')
    {
        $data = [
            'name' => $file['name'],
            'type' => $file['mime'],
            'size' => $file['size'],
            'path' => $file['path'],
            'dateline' => time()
        ];
        $id = parent::create($data);
        if($id) {
            $data['id'] = $id;
            // $protocol = request()->param('protocol', 'http');
            // $data['path'] = $protocol . '://' . $_SERVER['HTTP_HOST'] . getContextPath() . 'uploads/' . $data['path'] . '?__id=' . $id;
            if(empty($scheme)) {
                $scheme = request()->scheme();
            }
            if(empty($host)) {
                if(ENV == 'release') {
                    $host = env('UPLOAD_HOST') . '/';
                } else {
                    $host = request()->host() . '/' . getContextPath();
                }
            } else {
                $host = $host . '/' . getContextPath();
            }
            $data['url'] = $scheme . '://' . $host . 'uploads/' . $data['path'] . '?__id=' . $id;
            return $data;
        }
        throw new Exception('上传失败');
    }

    public function getByAuthKey($authKey, $scheme = '', $host = '')
    {
        $fileId = cache($this->fileAuthKeyCachePrefix . $authKey);
        if(!$fileId) {
            throw new Exception('文件不存在');
        }
//        cache($this->fileAuthKeyCachePrefix . $authKey, null);
        $file = $this->getByPk($fileId);
        return $this->format($file, $scheme, $host);
    }

    /**
     * 获取文件信息
     * @param $id
     * @param $scheme
     * @param $host
     * @throws
     * @return null|static
     */
    public function file($id, $scheme = '', $host = '')
    {
        $file = $this->getByPk($id);
        if($file) {
            if(empty($scheme)) {
                $scheme = request()->scheme();
            }
            if(empty($host)) {
                if(ENV == 'release') {
                    $host = env('UPLOAD_HOST') . '/';
                } else {
                    $host = request()->host() . '/' . getContextPath();
                }
            } else {
                $host = $host . '/' . getContextPath();
            }
//            $ext = ImageUtil::getExt($file['path']);
//            if($ext == '.mp3') {
//                $authKey = md5($file['id'] . rand_string(20));
//                $url = $scheme . '://' . ($host ? $host : $_SERVER['HTTP_HOST']) . '/' . getContextPath() . 'upload.php/file/download?auth_key=' . $authKey;
//                $file['url'] = $url;
//                cache($this->fileAuthKeyCachePrefix . $authKey, $file['id'], 30);
//            } else {
//                $file['url'] = $scheme . '://' . $host . '/' . getContextPath() . 'uploads/' . $file['path'];
//                $file = $this->format($file, $scheme, $host);
//            }
            $file['url'] = $scheme . '://' . $host . 'uploads/' . $file['path'];
            $file = $this->format($file, $scheme, $host);
        }
        return $file;
    }

    public function format($file, $scheme = '', $host = '')
    {
        return $file;
    }

    /**
     * 生成上传令牌
     * @param $ext
     * @return string
     */
    public function genToken($ext = '')
    {
        if(empty($ext)) {
            $ext = uniqid();
        }
        $lastTokenCacheKey = 'user_upload_token:' . empty($ext) ? '' : md5($ext);
        $lastToken = $this->cacheService->get($lastTokenCacheKey);
        if(!empty($lastToken)) {
            $this->cacheService->rm($this->tokenCachePrefix . $lastToken);
        }

        $token = md5(uniqid() . $ext . time() . Str::random(10));
        $this->cacheService->set($this->tokenCachePrefix . $token, $token, 0);
        $this->cacheService->set($lastTokenCacheKey, $token, 0);
        return $token;
    }

    /**
     * 验证上传令牌是否存在
     * @param $token
     * @return mixed
     */
    public function isTokenExists($token)
    {
        return $this->cacheService->get($this->tokenCachePrefix . $token);
    }

}