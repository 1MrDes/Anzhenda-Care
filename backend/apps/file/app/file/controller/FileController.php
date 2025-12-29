<?php

namespace apps\file\app\file\controller;

use apps\file\core\service\UploadedFileService;
use think\Request;

class FileController
{
    /**
     * @var UploadedFileService
     */
    protected $uploadedFileService;

    public function __construct()
    {
        if(!defined('IN_UPLOAD')) {
            die('hack attemping');
        }
        $this->uploadedFileService = service('UploadedFile', SERVICE_NAMESPACE);
    }

    public function download(Request $request)
    {
        $authKey = $request->param('auth_key');
        $file = $this->uploadedFileService->getByAuthKey($authKey);

        $path = WWW_PATH . 'uploads/' . $file['path'];

        $fileSize = filesize($path);

        header("Content-type:audio/mpeg");
        header("Accept-Ranges:bytes");
        header("Accept-Length:$fileSize");
        header("Content-Disposition:attachment;filename=" . $file['name']);
        readfile($path);
        exit();
    }
}