<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/31 22:59
 * @description
 */

namespace apps\file\app\api\controller;


use apps\file\core\service\UploadedFileService;
use think\Exception;
use think\File;
use think\helper\Str;
use think\Request;
use think\response\Json;

class FileController extends BaseFileApiController
{
    /**
     * @var UploadedFileService
     */
    protected $uploadedFileService;

    protected function init()
    {
        parent::init();
        $this->uploadedFileService = service('UploadedFile', SERVICE_NAMESPACE);
    }

    public function gen_token(Request $request)
    {
        $ext = $request->param('ext', '');
        $token = $this->uploadedFileService->genToken($ext);
        return $this->success($token);
    }

    public function info(Request $request)
    {
        $scheme = $request->param('scheme', '');
        $host = $request->param('host', '');
        $id = $request->param('id', 0, 'intval');
        $file = $this->uploadedFileService->file($id, $scheme, $host);
        $file = $file ? arrayExcept($file, ['path']) : null;
        return $this->success($file);
    }

    public function file(Request $request)
    {
        $scheme = $request->param('scheme', '');
        $host = $request->param('host', '');
        $id = $request->param('id', 0, 'intval');
        $file = $this->uploadedFileService->file($id, $scheme, $host);
        $file = $file ? arrayExcept($file, ['path']) : null;
        return $this->success($file);
    }

    public function batch_info(Request $request)
    {
        $scheme = $request->param('scheme', '');
        $host = $request->param('host', '');
        $ids = $request->param('ids', '');
        $files = [];
        $idArr = explode('|', $ids);
        foreach ($idArr as $id) {
            $file = $this->uploadedFileService->file($id, $scheme, $host);
            $file = $file ? arrayExcept($file, ['path']) : null;
            $files['id_' . $id] = $file;
        }
        return $this->success($files);
    }

    public function valid(Request $request)
    {
        $ids = $request->param('ids', '');
        if($ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $id) {
                $data = [
                    'id' => $id,
                    'is_deleted' => 0
                ];
                $this->uploadedFileService->updateByPk($data);
            }
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $this->uploadedFileService->deleteByPk($id);
        return $this->success();
    }

    public function batch_delete(Request $request)
    {
        $ids = $request->param('ids', '');
        if($ids) {
            $ids = explode('|', $ids);
            foreach ($ids as $id) {
                $this->uploadedFileService->deleteByPk($id);
            }
        }
        return $this->success();
    }

    public function upload(Request $request)
    {
        $fileData = $request->param('fileData', '');
        if(empty($fileData)) {
            throw new Exception('请上传图片');
        }
        $ext = '.png';
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $fileData, $result)){
            $ext = '.' .$result[2];
            $fileData = str_replace($result[1], '', $fileData);
        }

        $saveDir = WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
        $filename = time() . Str::random(10) . $ext;
        if(!is_dir($saveDir)){
            makeDir($saveDir, 0755);
        }
        $filePath = $saveDir . $filename;
        $scheme = request()->scheme();
        $urlPrefix = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/' . getContextPath() . 'uploads/';
        $path = str_replace(WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR , '', $filePath);

        if(file_put_contents($filePath, base64_decode($fileData))) {
            if(filesize($filePath) > 1024*1024*10) {
                $ret = [
                    'code' => -1,
                    'msg' => '超出大小限制',
                    'data' => ''
                ];
                @unlink($filePath);
            } else {
                $f = new File($filePath);
                $f->savename = $path;
                $result = $this->uploadedFileService->submit($f);
                $ret =  array(
                    'code' => 0,
                    "msg" => "SUCCESS",
                    'data' => [
                        'url' => $result['url'],
                        "title" => $filename,
                        "original" => $filename,
                        "type" => 'png',
                        "size" => filesize($filePath),
                        'file_id' => $result['id']
                    ]
                );
            }
        } else {
            $ret = [
                'code' => -1,
                'msg' => '上传失败',
                'data' => ''
            ];
        }
        return json($ret);
    }
}