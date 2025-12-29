<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/10/13 13:46
 * @description
 */

namespace vm\org;

require_once(VENDOR_PATH . 'topthink/think-image/src/image/Exception.php');
require_once(VENDOR_PATH . 'topthink/think-image/src/image/gif/Decoder.php');
require_once(VENDOR_PATH . 'topthink/think-image/src/image/gif/Encoder.php');
require_once(VENDOR_PATH . 'topthink/think-image/src/image/gif/Gif.php');
require_once(VENDOR_PATH . 'topthink/think-image/src/Image.php');

use think\Exception;
use think\Image;

class ImageUtil extends Image
{
    const HTTP_TYPE_CURL = 'curl';

    /**
     * @param $src_img  原始大图地址
     * @param $width    缩略图宽度
     * @param $height   缩略图高度
     * @param $dst_img    缩略图地址
     */
    public static function corpThumb($src_img, $width, $height, $dst_img)
    {
        $imgage = getimagesize($src_img); //得到原始大图片
        switch ($imgage[2]) { // 图像类型判断
            case 1:
                $im = imagecreatefromgif($src_img);
                break;
            case 2:
                $im = imagecreatefromjpeg($src_img);
                break;
            case 3:
                $im = imagecreatefrompng($src_img);
                break;
        }
        $src_W = $imgage[0]; //获取大图片宽度
        $src_H = $imgage[1]; //获取大图片高度
        $tn = imagecreatetruecolor($width, $height); //创建缩略图
        imagesavealpha($tn, true);   // 不要丢了图像的透明色;
        imagealphablending($tn, false); // 不合并颜色,直接用$img图像颜色替换,包括透明色;
        imagecopyresampled($tn, $im, 0, 0, 0, 0, $width, $height, $src_W, $src_H); //复制图像并改变大小
        imagepng($tn, $dst_img); //输出图像
    }

    /**
     * 生成缩略图
     * @param string $srcfile 源文件
     * @param int $tow 宽度
     * @param int $toh 高度
     * @param int $wforce 是否限制图片宽度，1：限制，0：不限制
     * @param int $hforce 是否限制图片高度，1：限制，0：不限制
     * @return string
     */
    public static function makeThumb($srcfile, $tow = 100, $toh = 100, $wforce = 0, $hforce = 0)
    {
        // 判断文件是否存在
        if (!is_file($srcfile)) {
            return '';
        }
        $ext = strrchr($srcfile, ".");
        $dstfile = dirname($srcfile) . '/' . basename($srcfile, $ext) . '_' . $tow . '_' . $toh . '_' . $wforce . '_' . $hforce . $ext;
        $back_dstfile = FixedUploadedFileUrl(str_replace(UPLOAD_FILE_PATH, '', $dstfile));
        if (is_file($dstfile)) {
            return $back_dstfile;
        }
        if ($tow < 60) $tow = 60;
        if ($toh < 60) $toh = 60;
        $make_max = 0;
        // 获取图片信息
        $im = '';
        if ($data = getimagesize($srcfile)) {
            if ($data[2] == 1) {
                $make_max = 0; // gif不处理
                if (function_exists("imagecreatefromgif")) {
                    $im = imagecreatefromgif($srcfile);
                }
            } elseif ($data[2] == 2) {
                if (function_exists("imagecreatefromjpeg")) {
                    $im = imagecreatefromjpeg($srcfile);
                }
            } elseif ($data[2] == 3) {
                if (function_exists("imagecreatefrompng")) {
                    $im = imagecreatefrompng($srcfile);
                }
            }
        }
        if (!$im) return '';

        $srcw = imagesx($im);
        $srch = imagesy($im);

        if ($srcw <= $tow && $srch <= $toh) {
            imagedestroy($im);
            copy($srcfile, $dstfile);
            return $back_dstfile;
        }

        // 同时限定了宽度和高度
        if ($wforce == 1 && $hforce == 1) {
            $ftow = $tow;
            $ftoh = $toh;
        } // 只限定了宽度
        else if ($wforce == 1 && $hforce == 0) {
            $ftow = $tow;
            $ftoh = $ftow * ($srch / $srcw);
        } // 只限定了高度
        else if ($wforce == 0 && $hforce == 1) {
            $ftoh = $toh;
            $towh = $tow / $toh;
            $srcwh = $srcw / $srch;
            if ($towh <= $srcwh) {
                $ftow = $tow;
            } else {
                $ftow = $ftoh * ($srcw / $srch);
            }
        } // 高度和宽度都未限定
        else {
            $towh = $tow / $toh;
            $srcwh = $srcw / $srch;
            if ($towh <= $srcwh) {
                $ftow = $tow;
                $ftoh = $ftow * ($srch / $srcw);
            } else {
                $ftoh = $toh;
                $ftow = $ftoh * ($srcw / $srch);
            }
        }

        if ($srcw > $tow || $srch > $toh) {
            if (function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && @$ni = imagecreatetruecolor($ftow, $ftoh)) {
                imagecopyresampled($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
            } elseif (function_exists("imagecreate") && function_exists("imagecopyresized") && @$ni = imagecreate($ftow, $ftoh)) {
                imagecopyresized($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
            } else {
                return '';
            }
            if (function_exists('imagejpeg')) {
                imagejpeg($ni, $dstfile);
            } elseif (function_exists('imagepng')) {
                imagepng($ni, $dstfile);
            }
            imagedestroy($ni);
        }
        imagedestroy($im);

        if (!file_exists($dstfile)) {
            return '';
        } else {
            return $back_dstfile;
        }

    }

    //获取远程图片保存到本地
    //示例：grabImage('http://static.oschina.net/uploads/user/83/166228_50.jpg', 'grabimage/');
    public static function grabImage($url, $dir = '', $filename = "", $httpType = 'curl', array $curlOptions = [])
    {
        if ($url == "") {
            return false;
        }
        if ($filename == "") {
            $urlParse = parse_url($url);
            $ext = strrchr($urlParse['path'], ".");
            if ($ext != ".gif" && $ext != ".jpg" && $ext != ".png") {
//                return false;
                $ext = '.jpg';
            }
            $filename = time() . rand_string(10) . $ext;
        }

        //获取远程文件所采用的方法
        if ($httpType == 'curl') {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
//            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
//            curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
            foreach ($curlOptions as $item) {
                curl_setopt($ch, $item['option'], $item['value']);
            }

            $img = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
        }

//        $size = strlen($img);
        $fp2 = @fopen($dir . $filename, "a");
        fwrite($fp2, $img);
        fclose($fp2);
        return $filename;
    }

    //将内容中的远程图片保存到本地
    //save_remote_picture($content,'http://127.0.0.1/', 'images/uploads/' . date('Ym/'), $ext = 'jpg|jpeg|gif|png')
    public static function saveRemotePicture($content, $base_url = '', $filepath = '', $ext = 'jpg|jpeg|gif|png')
    {
        if (!preg_match_all("/src=([\"|']?)([^ \"'>]+\.($ext))\\1/i", $content, $matches))
            return $content;
        $urls = $oldpath = $newpath = array();
        is_dir($filepath) or makeDir($filepath);

        foreach ($matches[2] as $k => $url) {
            if (in_array($url, $urls)) {
                continue;
            }
            $urls[$url] = $url;
            if (strpos($url, '://') === false) {
                continue;
            }
            if (strpos($url, $base_url) !== false) {
                continue;
            }
            $pathinfo = pathinfo($url);
            $fileext = $pathinfo['extension'];
            $filename = uniqid() . rand_string(5) . '.' . $fileext;
            $newfile = $filepath . $filename;

            // 保存图片
            if (copy($url, $newfile)) {
                if (preg_match("/^(jpg|jpeg|gif|png)$/i", $fileext)) {
                    if (!@getimagesize($newfile)) {
                        unlink($newfile);
                        continue;
                    }
                }
                $oldpath[] = $url;
                $newurl = $filepath . $filename;
                $newpath[] = $newurl;
            }
        }
        unset($matches);
        return str_replace($oldpath, $newpath, $content);
    }

    public static function getExt($url)
    {
//        $pathinfo = pathinfo($url);
//        $fileext = '.' . $pathinfo['extension'];
        $urlParse = parse_url($url);
        $ext = strrchr($urlParse['path'], ".");
        return $ext;
    }

    /**
     * PDF生成图片
     * @param string $pdf  待处理的PDF文件
     * @param string $path 待保存的图片目录
     * @param int $page 待导出的页面 -1为全部 0为第一页 1为第二页
     * @throws
     * @return array
     */
    public static function pdf2pic($pdf, $path, $page = -1)
    {
        if (!extension_loaded('imagick')) {
            throw new Exception('未安装imagick扩展');
        }
        if (!file_exists($pdf)) {
            throw new Exception('PDF文件不存在');
        }

        $im = new \Imagick();
        $im->setResolution(500, 500);//设置图像分辨率
        $im->setCompressionQuality(100);//设置默认的压缩质量
        if ($page == -1) {
            $im->readImage($pdf);
        } else {
            $im->readImage($pdf . '[' . $page . ']');
        }
        $images = [];
        foreach ($im as $Key => $Var) {
            $Var->setImageFormat('jpg');
            $filename = $path . '/' . md5($Key . time()) . '.jpg';
            if ($Var->writeImage($filename) == true) {
                $images[] = $filename;
            }
        }
        return $images;
    }

}