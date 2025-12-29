<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/31 22:59
 * @description
 */

namespace apps\file\app\file\controller;

use think\Exception;
use think\facade\Filesystem;
use think\File;
use think\file\UploadedFile;
use think\helper\Str;
use think\Request;
use vm\org\ImageUtil;

class UploadController extends BaseFileController
{
    public function img2base64(Request $request)
    {
        if($request->isOptions()) {

        } else {
            // 获取表单上传文件 例如上传了001.jpg
            $file = request()->file('file');

            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>1024*1024*2,'ext'=>'jpg,jpeg,png,gif'])
                ->move(WWW_PATH . 'uploads/temp');
            if($info){
                $result = base64EncodeImage(WWW_PATH . 'uploads/temp/' . $info->getSaveName());
                return $this->success($result);
            }else{
                // 上传失败获取错误信息
                throw new Exception($file->getError());
            }
        }
    }

    public function form(Request $request)
    {
        if($request->isOptions()) {

        } else {
            // 获取表单上传文件 例如上传了001.jpg
            $file = request()->file('file');
            if(!$this->checkSize($file, 1024*1024*100)) {       //  100MB
                throw new Exception('文件大小超出最大值');
            }
            if(!$this->checkExt($file, 'jpg,jpeg,png,gif,mp4,mp3,m4a,wav,wma,aac,xls,xlsx,doc,docx')) {
                throw new Exception('文件类型错误');
            }
            $savename = Filesystem::disk('public')->putFile( 'files', $file);
            $file->savename = $savename;

            $result = $this->uploadedFileService->submit($file);
            $result['extra'] = request()->param('extra', '');
            $base64encode = request()->param('base64encode', '');
            if($base64encode) {
                $base64Image = base64EncodeImage(WWW_PATH . 'uploads/' . $savename);
                $result['base64'] = $base64Image['base64'];
            }
            return $this->success($result);
        }
    }

    public function base64(Request $request)
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
                    'msg' => '超出大小限制'
                ];
                @unlink($filePath);
            } else {
                $f = new File($filePath);
                $f->savename = $path;
                $result = $this->uploadedFileService->submit($f);
                $ret =  array(
                    'code' => 0,
                    "msg" => "SUCCESS",
//                    "url" => $urlPrefix . $path . '?__id=' . $result['id'],
                    'url' => $result['url'],
                    "title" => $filename,
                    "original" => $filename,
                    "type" => 'png',
                    "size" => filesize($filePath),
                    'file_id' => $result['id']
                );
            }
        } else {
            $ret = [
                'code' => -1,
                'msg' => '上传失败'
            ];
        }
        return $ret;
    }

    public function ue(Request $request)
    {
        header("Access-Control-Allow-Headers: X-Requested-With,X_Requested_With");
        if($request->isOptions()) {

        } else {
            $action = $request->param('action', '');
            switch ($action) {
                case 'config':
                    $result = $this->getUeConfig();
                    break;
                case 'uploadimage':
                    $result = $this->ueUploadFile();
                    break;
                case 'uploadfile':
                    $result = $this->ueUploadFile();
                    break;
                case 'uploadscrawl':
                    $result = $this->ueUploadScrawl();
                    break;
                case 'catchimage':
                    $result = $this->ueCatchimage();
                    break;
                default:
                    $result = '请求错误';
                    break;
            }
            $callback = $request->param('callback', '');
            /* 输出结果 */
            if (!empty($callback)) {
                if (preg_match("/^[\w_]+$/", $callback)) {
                    echo htmlspecialchars($callback) . '(' . (is_array($result) ? json_encode($result) : $result) . ')';
                } else {
                    echo json_encode(array(
                        'state'=> 'callback参数不合法'
                    ));
                }
            } else {
                echo is_array($result) ? json_encode($result) : $result;
            }
//            exit();
        }
    }

    private function getUeConfig()
    {
        date_default_timezone_set("PRC");
        $protocol = request()->scheme();
        $urlPrefix = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . getContextPath() . 'uploads/';
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(DOC_PATH . "data/ue_config.json")), true);
//        $CONFIG['imageUrlPrefix'] = $urlPrefix;
        $CONFIG['imageUrlPrefix'] = '';
        return json_encode($CONFIG);
    }

    private function ueUploadFile()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        if(!$this->checkSize($file, 1024*1024*10)) {
            throw new Exception('文件大小超出最大值');
        }
        if(!$this->checkExt($file, 'jpg,jpeg,png,gif,mp3,m4a,aac,xls,xlsx,doc,docx')) {
            throw new Exception('文件类型错误');
        }
        $savename = Filesystem::disk('public')->putFile( 'files', $file);
        $file->savename = $savename;

        $result = $this->uploadedFileService->submit($file);

        $ret =  array(
            "state" => "SUCCESS",          //上传状态，上传成功时必须返回"SUCCESS"
            'url' => $result['url'],
            "title" => $result['name'],          //新文件名
            "original" => $file->getOriginalName(),       //原始文件名
            "type" => $result['type'],            //文件类型
            "size" => $result['size']          //文件大小
        );
        return $ret;
    }

    private function ueUploadScrawl()
    {
        $file = request()->param('file', '');
        if(empty($file)) {
            throw new Exception('没有文件');
        }
        $saveDir = WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
        $filename = time() . Str::random(10) . '.png';
        if(!is_dir($saveDir)){
            makeDir($saveDir, 0755);
        }
        $filePath = $saveDir . $filename;
        $scheme = request()->scheme();
        $urlPrefix = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/' . getContextPath() . 'uploads/';
        $path = str_replace(WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR , '', $filePath);

        if(file_put_contents($filePath, base64_decode($file))) {
            $f = new File($filePath);
            $f->savename = $path;
            if(!$this->checkSize($f, 1024*1024*5)) {
                $ret = [
                    'state' => '超出大小限制'
                ];
                @unlink($filePath);
            } else {
                $result = $this->uploadedFileService->submit($f);
                $ret =  array(
                    "state" => "SUCCESS",
                    'url' => $result['url'],
                    "title" => $filename,
                    "original" => $filename,
                    "type" => 'png',
                    "size" => filesize($filePath)
                );
            }
        } else {
            $ret = [
                'state' => '上传失败'
            ];
        }
        return $ret;
    }

    public function ueCatchimage()
    {
        $source = request()->param('source');
        $result = [];
        if($source) {
            $dir = WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
            if(!is_dir($dir)) {
                makeDir($dir, 0777);
            }
            foreach ($source as $item) {
                $filename = ImageUtil::grabImage($item, $dir);
                $file = $this->uploadedFileService->save([
                    'name' => $filename,
                    'mime' => '',
                    'size' => filesize($dir . $filename),
                    'path' => str_replace(WWW_PATH . 'uploads' . DIRECTORY_SEPARATOR, '', $dir . $filename)
                ]);
                $result[] = [
                    'source' => $item,
                    'state' => 'SUCCESS',
                    'url' => $file['url']
                ];
            }
        }
        return [
            'state' => 'SUCCESS',
            'list' => $result
        ];
    }

    public function sharding_upload(Request $request)
    {
        $ret = [
            'code' => 0,
            "status" => "SUCCESS",
            "url" => '',
            "title" => '',
            "original" => '',
            "type" => '',
            'file_id' => 0
        ];
        $identifier = $request->param('identifier', '');
        // 第几个片段
        $chunkNumber = $request->param('chunk_number', 1, 'intval');
        // 是否最后一个片段
        $isLast = $request->param('is_last', 'no');
        // 文件总大小
        $totalSize = $request->param('total_size', 0, 'intval');
        // 文件名
        $fileName = $request->param('filename', '');

        $dir = WWW_PATH . 'uploads/tmp/' . $identifier;
        if (!is_dir($dir)) {
            makeDir($dir, 0777);
        }
        $path = $dir . "/" . $chunkNumber;
        $postData = @file_get_contents('php://input');
        file_put_contents($path, $postData);

        // 已上传大小
        $uploadedSize = 0;
        // 统计文件
        $files = [];
        $dirFp = opendir($dir); //返回一个资源类型
        while ($f = readdir($dirFp)) {
            $file = $dir . "/" . $f;
            if ($f != "." && $f != ".." && is_file($file)) {
                $files[] = $f;
                $uploadedSize += filesize($file);
            }
        }
        closedir($dirFp);

        if ($isLast == 'yes') { // 上传完成
            if((ceil($totalSize / 1024 / 1024)) > 500) { // 不超过500M
                delDir($dir);
                $ret = [
                    'code' => -1,
                    "status" => "FAILED",
                    "url" => '',
                    "title" => '',
                    "original" => '',
                    "type" => '',
                    'file_id' => 0,
                    'size' => $totalSize
                ];
                return json($ret);
            }

            $suffix = substr(strrchr($fileName, '.'), 1);
            $filePath = WWW_PATH . 'uploads/files/' . date('Ymd') . '/';
            if(!is_dir($filePath)) {
                makeDir($filePath);
            }
            $filePath .= md5(uniqid() . microtime() . rand_string(10)) . '.' . $suffix;
            $fp = fopen($filePath, "abw");
            sort($files, SORT_NUMERIC);
            for ($i = 0; $i < count($files); $i++) {
                $handle = fopen($dir . "/" . $files[$i], "rb");
                fwrite($fp, fread($handle, filesize($dir . "/" . $files[$i])));
                fclose($handle);
            }
            fclose($fp);

            delDir($dir); // 删除临时文件

            $f = new File($filePath);
            $saveName = str_replace(WWW_PATH . 'uploads/', '', $filePath);
            $f->savename = $saveName;
            $result = $this->uploadedFileService->submit($f);

            $scheme = request()->scheme();
            $urlPrefix = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/' . getContextPath() . 'uploads/';
            $ret = [
                'code' => 0,
                "status" => "FINISHED",
                "url" => $urlPrefix . $saveName . '?__id=' . $result['id'],
                "title" => $fileName,
                "original" => $fileName,
                "type" => $suffix,
                'file_id' => $result['id'],
                'size' => $totalSize
            ];
        }
        return json($ret);
    }

    public function chunk_upload(Request $request)
    {
        $ret = [
            'code' => 0,
            "status" => "SUCCESS",
            "url" => '',
            "title" => '',
            "original" => '',
            "type" => '',
            'file_id' => 0
        ];
        $fileName = $request->param('filename');
        $totalChunks = $request->param('totalChunks');
        $dir = WWW_PATH . 'uploads/tmp/' . md5($request->param('identifier') . $fileName);
        if (!is_dir($dir)) {
            makeDir($dir, 0777);
        }
        $path = $dir . "/" . $request->param('chunkNumber');
        move_uploaded_file($_FILES["file"]["tmp_name"], $path);
        // 文件总大小
        $totalSize = $request->param('totalSize', 0);
        // 已上传大小
        $uploadedSize = 0;
        // 统计文件
        $files = [];
        $dirFp = opendir($dir); //返回一个资源类型
        while ($f = readdir($dirFp)) {
            $file = $dir . "/" . $f;
            if ($f != "." && $f != ".." && is_file($file)) {
                $files[] = $f;
                $uploadedSize += filesize($file);
            }
        }
        closedir($dirFp);

        if ($totalSize == $uploadedSize) { // 上传完成
            if((ceil($totalSize / 1024 / 1024)) > 500) { // 不超过500M
                delDir($dir);
                $ret = [
                    'code' => -1,
                    "status" => "FAILED",
                    "url" => '',
                    "title" => '',
                    "original" => '',
                    "type" => '',
                    'file_id' => 0,
                    'size' => $totalSize
                ];
                return $ret;
            }

            $suffix = substr(strrchr($fileName, '.'), 1);
            $filePath = WWW_PATH . 'uploads/' . date('Ymd') . '/';
            if(!is_dir($filePath)) {
                makeDir($filePath);
            }
            $filePath .= date('YmdHis') . rand_string(10) . '.' . $suffix;
            $fp = fopen($filePath, "abw");
            for ($i = 1; $i <= $totalChunks; $i++) {
                $handle = fopen($dir . "/" . $i, "rb");
                fwrite($fp, fread($handle, filesize($dir . "/" . $i)));
                fclose($handle);
            }
            fclose($fp);

            delDir($dir); // 删除临时文件

            $f = new File($filePath);
            $saveName = str_replace(WWW_PATH . 'uploads/', '', $filePath);
            $f->savename = $saveName;
            $result = $this->uploadedFileService->submit($f);

            $scheme = request()->scheme();
            $urlPrefix = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/' . getContextPath() . 'uploads/';
            $ret = [
                'code' => 0,
                "status" => "FINISHED",
                "url" => $urlPrefix . $saveName . '?__id=' . $result['id'],
                "title" => $fileName,
                "original" => $fileName,
                "type" => $suffix,
                'file_id' => $result['id'],
                'size' => $totalSize
            ];
        }
        return $ret;
    }

    /**
     * 检测上传文件后缀
     * @param UploadedFile $file
     * @param  array|string   $ext    允许后缀
     * @return bool
     */
    private function checkExt(UploadedFile $file, $ext)
    {
        if (is_string($ext)) {
            $ext = explode(',', $ext);
        }

        $extension = strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION));

        if (!in_array($extension, $ext)) {
            return false;
        }
        return true;
    }

    /**
     * 检测上传文件大小
     * @param UploadedFile $file
     * @param  integer   $size    最大大小
     * @return bool
     */
    private function checkSize(UploadedFile $file, $size)
    {
        if ($file->getSize() > (int) $size) {
            return false;
        }

        return true;
    }

    /**
     * 检测上传文件类型
     * @param UploadedFile $file
     * @param  array|string   $mime    允许类型
     * @return bool
     */
    private function checkMime(UploadedFile $file, $mime)
    {
        if (is_string($mime)) {
            $mime = explode(',', $mime);
        }

        if (!in_array(strtolower($file->getMime()), $mime)) {
            $this->error = 'mimetype to upload is not allowed';
            return false;
        }

        return true;
    }
}