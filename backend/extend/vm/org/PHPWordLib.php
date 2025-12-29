<?php
/**
 *  +----------------------------------------------------------------------
 *  | ThinkPHP [ WE CAN DO IT JUST THINK ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2020 ahai574 All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed ( ++++ahai574++++ )
 *  +----------------------------------------------------------------------
 *  | Author: 阿海 <764882431@qq.com>
 *  +----------------------------------------------------------------------
 *  处理导入excel 导入csv  导出xls xlsx csv
 */
namespace vm\org;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Writer\Html as WriteHtml;

class PHPWordLib
{
    /**
     * 文件保存名称 不用写后缀 ，默认会使用下载驱动作为后缀
     */
    private $fileName = 'docx';
    /**
     * 下载文件的驱动类
     *  如Xls,Xlsx
     */
    private $downloadClass = 'Word2007';
    /**
     * 是下载还是保存至本地  默认是下载文件
     */
    private $isDownload = true;
    /**
     * 保存至服务器的路径
     */
    private $filePath = "";
    /**
     * 保存至服务器的路径+文件名称 -- 这个不需要设置 ---自动使用 $filePath+$fileName+时间
     */
    private $saveFilePath = "";
    /**
     * @param string html内容
     */
    private $wordData = '';

    /**
     * 虽然说支持多种格式，但是我只要用来读写word即可
     * 写入的文件类型 , 'ODText' => 'odt', 'RTF' => 'rtf', 'HTML' => 'html'
     */
    private $writers = array('Word2007' => 'docx', 'HTML' => 'html');


    public function __construct($config = [])
    {
        isset($config['fileName']) && $this->fileName = mb_convert_encoding($config['fileName'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        isset($config['downloadClass']) && $this->downloadClass = $config['downloadClass'];
        isset($config['wordData']) && $this->wordData = $config['wordData'];
        isset($config['isDownload']) && $this->isDownload = (bool)$config['isDownload'];
        $this->filePath = isset($config['filePath']) ? $config['filePath'] : "runtime/uploads/files/" . date("Y-m-d");
        //文件名去除后缀
        if (strripos($this->fileName, ".") !== false) {
            $this->fileName = substr($this->fileName, 0, strripos($this->fileName, "."));
        }
        //如果是保存至本地 则设置保存的文件路径及名称，同样由于可能存在同名称 所以给加了一个随机数给这个文件名称,一般够用
        $this->saveFilePath = !($this->isDownload) ? $this->filePath . "/" . $this->fileName . "_" . time() . rand(0, 1000) . "." . strtolower($this->writers[$this->downloadClass]) : '';
        if (!file_exists($this->filePath)) {
            // @mkdir($this->filePath, 0777, true);
            @mkdir(iconv("UTF-8", "GBK", $this->filePath), 0777, true);
        }

        if (isset($config['downloadClass']) && !array_key_exists($config['downloadClass'], $this->writers)) {
            throw new \Exception("下载的驱动类,必须在writers中");
        }
    }

    /**
     * 下载时的header头
     */
    private function header()
    {
        if ($this->isDownload) {
            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $this->fileName . "." . strtolower($this->writers[$this->downloadClass]) . '"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
        } else {
            // 确保文件没有缓存,在ios上可能会出现问题
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
    }

    /**
     * @param String $data html内容
     */
    public function SetWordData($data)
    {
        $this->wordData = $data;
        return $this;
    }

    /**
     * 使用这个方法 downloadClass ===
     * 下载文件支持： docx
     * 保存文件至服务器支持 .html,docx,...【如果需要更多格式，比如odt，记得在writers里面添加--必须是phpword里面支持的 pdf的话，我】
     */
    public function createServer()
    {
        //只用来下载docx 文件
        if ($this->downloadClass !== 'Word2007' && $this->isDownload) {
            throw new \Exception("当前只支持docx文件下载");
        }

        $this->header();
        //设置允许的请求时间
        @set_time_limit(5 * 60);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $html = new Html();
        $html::addHtml($section, $this->wordData, false, false);

        if ($this->isDownload) {
            $phpWord->save('php://output');
            exit;
        } else {
            $phpWord->save($this->saveFilePath, $this->downloadClass);
            //返回文件路径
            return $this->saveFilePath;
        }
    }

    /**
     * 将word文件转html代码
     * @param string $filePath docx 文件路径
     * @param boolean $saveHtml 是否保存html（依赖于isDownload，但是不会下载html页面） 否则输出html实体内容
     * @return string html|path|document
     */
    public function wordToHtml($filePath, $saveHtml = false)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("文件不存在,请检查文件路径");
        }
        //第二个参数是默认值，可以不填写
        $phpWord = IOFactory::load($filePath, "Word2007");
        $writer = IOFactory::createWriter($phpWord, "HTML");
        if ((bool)$saveHtml) {
            //返回html实体内容
            return $writer->getContent();
        } else {
            if ($this->isDownload) {
                //直接显示 html
                $phpWord->save('php://output', 'HTML');
                exit;
            } else {
                //保存至服务器
                $phpWord->save($this->saveFilePath, 'HTML');
                //返回文件路径
                return $this->saveFilePath;
            }
        }
    }
}

