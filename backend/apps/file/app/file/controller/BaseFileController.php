<?php
namespace apps\file\app\file\controller;

use apps\file\core\service\UploadedFileService;
use think\Exception;
use vm\com\RestController;

class BaseFileController extends RestController
{
    /**
     * @var UploadedFileService
     */
    protected $uploadedFileService;

    protected function init()
    {
        if(!defined('IN_UPLOAD')) {
            die('hack attemping');
        }
        $this->uploadedFileService = service('UploadedFile', SERVICE_NAMESPACE);
        parent::init();
    }

    protected function auth()
    {
        $token = request()->header('upload-token', '');
        if(empty($token)) {
            $token = request()->param('upload_token', '');
        }
        if(empty($token)) {
            throw new Exception('上传令牌无效');
        }
        $exists = $this->uploadedFileService->isTokenExists($token);
        if(!$exists) {
            throw new Exception('上传令牌无效');
        }
    }
}