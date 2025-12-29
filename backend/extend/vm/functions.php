<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2017/7/5
 * Time: 22:40
 */
/**
 * @param $name
 * @return vm\com\BaseService
 */
function service($name, $namespace = '\\app\\common\\service\\')
{
    static $clazz = [];
    $name = $namespace . $name . 'Service';
    $namHash = md5($name);
    if (!isset($clazz[$namHash])) {
        $clazz[$namHash] = new \vm\com\Aop(new $name());
    }
    return $clazz[$namHash];
}

function logic($name, $namespace = '\\app\\common\\logic\\')
{
    static $clazz = [];
    $name = $namespace . $name . 'Logic';
    $namHash = md5($name);
    if (!isset($clazz[$namHash])) {
        $clazz[$namHash] = new \vm\com\Aop(new $name());
    }
    return $clazz[$namHash];
}

function api_request($app, $path, $method = 'GET', array $data = null)
{
    $cfg = config('api.' . $app);
    $uri = $cfg['host'] . $cfg['paths'][$path];
    $appid = $cfg['app_id'];
    $appKey = $cfg['app_key'];
    $params = [
        'appid' => $appid,
        'timestamp' => time(),
        'nonce_str' => rand_string(15),
    ];
    if ($method == \vm\org\HttpUtil::REQUEST_METHOD_GET && !empty($data)) {
        $params = array_merge($params, $data);
    }
    $signature = genApiSignature($params, $appKey);
    $url = $uri . '?' . http_build_query($params) . '&signature=' . $signature;
    $httpUtil = new \vm\org\HttpUtil();
    $response = $httpUtil->curl($url, $method, $data);
    $response = json_decode($response, true);
    if ($response['code'] != 0) {
        \think\facade\Log::error('接口访问出错,msg:' . $response['msg']);
    }
    $result = $response['data'];
    return $result;
}

/**
 * 获取图片格式
 * @param string $string 字符串格式：data:image/png;base64,xxxxxx
 * @return array
 */
function getImageFormat($string)
{
    preg_match('/data:(\S+);base64,(\S+)/', $string, $matches);
    return $matches;
}

/**
 * 将本地图片转成base64字符串
 * @param $imageFile    本地文件路径
 * @return array
 */
function base64EncodeImage($imageFile)
{
    $imageInfo = getimagesize($imageFile);
    $imageData = fread(fopen($imageFile, 'r'), filesize($imageFile));
//    $base64Image = 'data:' . $imageInfo['mime'] . ';base64,' . chunk_split(base64_encode($imageData));
    return [
        'mime' => $imageInfo['mime'],
        'base64' => chunk_split(base64_encode($imageData))
    ];
}

/**
 * 剪切图片为圆形
 * @param  $picture 图片数据流 比如file_get_contents(imageurl)返回的东东
 * @return 图片数据流
 */
function yuanImg($picture)
{
    $src_img = imagecreatefromstring($picture);
    $w = imagesx($src_img);
    $h = imagesy($src_img);
    $w = min($w, $h);
    $h = $w;
    $img = imagecreatetruecolor($w, $h);
    //这一句一定要有
    imagesavealpha($img, true);
    //拾取一个完全透明的颜色,最后一个参数127为全透明
    $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
    imagefill($img, 0, 0, $bg);
    $r = $w / 2; //圆半径
    $y_x = $r; //圆心X坐标
    $y_y = $r; //圆心Y坐标
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $rgbColor = imagecolorat($src_img, $x, $y);
            if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                imagesetpixel($img, $x, $y, $rgbColor);
            }
        }
    }
    /**
     * 如果想要直接输出图片，应该先设header。header("Content-Type: image/png; charset=utf-8");
     * 并且去掉缓存区函数
     */
    //获取输出缓存，否则imagepng会把图片输出到浏览器
    ob_start();
    imagepng($img);
    imagedestroy($img);
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
}

/**
 * 计算.星座
 *
 * @param int $month 月份
 * @param int $day 日期
 * @return int
 */
function getConstellation($month, $day){
    $signs = array(
        array('20'=>'aquarius'), array('19'=>'pisces'),

        array('21'=>'aries'), array('20'=>'taurus'),

        array('21'=>'gemini'), array('22'=>'cancer'),

        array('23'=>'leo'), array('23'=>'virgo'),

        array('23'=>'libra'), array('24'=>'scorpio'),

        array('22'=>'sagittarius'), array('22'=>'capricornus')
    );

    $key = (int)$month - 1;
//    list($startSign, $signName) = each($signs[$key]);
    $startSign = key($signs[$key]);
    $signName = current($signs[$key]);
    if( $day < $startSign ){
        $key = $month - 2 < 0 ? $month = 11 : $month -= 2;
//        list($startSign, $signName) = each($signs[$key]);
        $startSign = key($signs[$key]);
        $signName = current($signs[$key]);
    }
    return $signName;
}

function constellations()
{
    return [
        'aquarius'=>'水瓶座',
        'pisces'=>'双鱼座',
        'aries'=>'白羊座',
        'taurus'=>'金牛座',
        'gemini'=>'双子座',
        'cancer'=>'巨蟹座',
        'leo'=>'狮子座',
        'virgo'=>'处女座',
        'libra'=>'天秤座',
        'scorpio'=>'天蝎座',
        'sagittarius'=>'射手座',
        'capricornus'=>'摩羯座',
    ];
}

/**
 * 计算.生肖
 *
 * @param int $year 年份
 * @return int
 */
function getZodiac($year){
//    $animals = array(
//        '鼠', '牛', '虎', '兔', '龙', '蛇',
//        '马', '羊', '猴', '鸡', '狗', '猪'
//    );
//    $key = ($year - 1900) % 12;
//    return $animals[$key];
    return ($year - 1900) % 12;
}

function zodiacs()
{
    return array(
        '鼠', '牛', '虎', '兔', '龙', '蛇',
        '马', '羊', '猴', '鸡', '狗', '猪'
    );
}

/**
 * 通过字符串获取当月第一天和最后一天时间戳
 * @param string $string 字符串格式：2018-01
 * @return array
 */
function getFirstAndLastDayOfMonth($string)
{
    $firstDay = date('Y-m-01 00:00:00', strtotime($string));
    $lastDay = date('Y-m-d 23:59:59', strtotime("$firstDay +1 month -1 day"));
    return [strtotime($firstDay), strtotime($lastDay)];
}

/**
 * 通过字符串获取当年第一天和最后一天时间戳
 * @param string $string 字符串格式：2018
 * @return array
 */
function getFirstAndLastDayOfYear($string)
{
    $firstDay = date('Y-01-01 00:00:00', strtotime($string . '-01-01'));
    $lastDay = date('Y-m-d 23:59:59', strtotime("$firstDay +12 month -1 day"));
    return [strtotime($firstDay), strtotime($lastDay)];
}

function writeLog($log = '', $file = 'logs.txt')
{
    if (!is_dir(dirname($file))) {
        makeDir(dirname($file));
    }
    //检测日志文件大小，超过配置大小则备份日志文件重新生成
    if (is_file($file) && floor(2 * 1024 * 1024) <= filesize($file)) {
        rename($file, dirname($file) . DIRECTORY_SEPARATOR . time() . '-' . basename($file));
    }

    $fp = fopen($file, "a");
    flock($fp, LOCK_EX);
    fwrite($fp, "[" . date('Y-m-d H:i:s') . "]\r\n" . $log . "\r\n\r\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * 获取项目相对路径
 * @return string
 */
function getContextPath()
{
    $contextPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $contextPath = ltrim($contextPath, '/');
    return $contextPath = (empty($contextPath) ? '' : rtrim($contextPath, '/')) . '/';
}

/**
 * 判断是否为命令行模式
 * @return bool
 */
function isCli()
{
    return preg_match("/cli/i", php_sapi_name()) ? true : false;
}

/**
 * 生成分享海报
 * @param array  参数,包括图片和文字
 * @param string $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
 * @return [type] [description]
 */
function createPoster($config = array(), $filename = "")
{
    //如果要看报什么错，可以先注释调这个header
    if (empty($filename)) {
        header("content-type: image/png");
    }
    $imageDefault = array(
        'left' => 0,
        'top' => 0,
        'right' => 0,
        'bottom' => 0,
        'width' => 100,
        'height' => 100,
        'opacity' => 100
    );
    $textDefault = array(
        'text' => '',
        'left' => 0,
        'top' => 0,
        'fontSize' => 32,       //字号
        'fontColor' => '255,255,255', //字体颜色
        'angle' => 0,
    );
    $background = $config['background'];//海报最底层得背景
    //背景方法
    $backgroundInfo = getimagesize($background);
    $backgroundFun = 'imagecreatefrom' . image_type_to_extension($backgroundInfo[2], false);
    $background = $backgroundFun($background);
    $backgroundWidth = imagesx($background);  //背景宽度
    $backgroundHeight = imagesy($background);  //背景高度
    $imageRes = imageCreatetruecolor($backgroundWidth, $backgroundHeight);
    $color = imagecolorallocate($imageRes, 0, 0, 0);
    imagefill($imageRes, 0, 0, $color);
    // imageColorTransparent($imageRes, $color);  //颜色透明
    imagecopyresampled($imageRes, $background, 0, 0, 0, 0, imagesx($background), imagesy($background), imagesx($background), imagesy($background));
    //处理了图片
    if (!empty($config['image'])) {
        foreach ($config['image'] as $key => $val) {
            $val = array_merge($imageDefault, $val);
            $info = getimagesize($val['url']);
            $function = 'imagecreatefrom' . image_type_to_extension($info[2], false);
            if ($val['stream']) {   //如果传的是字符串图像流
                $info = getimagesizefromstring($val['url']);
                $function = 'imagecreatefromstring';
            }
            $res = $function($val['url']);
            $resWidth = $info[0];
            $resHeight = $info[1];
            //建立画板 ，缩放图片至指定尺寸
            $canvas = imagecreatetruecolor($val['width'], $val['height']);
            imagefill($canvas, 0, 0, $color);
            //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
            imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'], $resWidth, $resHeight);
            $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) - $val['width'] : $val['left'];
            $val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) - $val['height'] : $val['top'];
            //放置图像
            imagecopymerge($imageRes, $canvas, $val['left'], $val['top'], $val['right'], $val['bottom'], $val['width'], $val['height'], $val['opacity']);//左，上，右，下，宽度，高度，透明度
        }
    }
    //处理文字
    if (!empty($config['text'])) {
        foreach ($config['text'] as $key => $val) {
            $val = array_merge($textDefault, $val);
            list($R, $G, $B) = explode(',', $val['fontColor']);
            $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
            $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) : $val['left'];
            $val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) : $val['top'];
            imagettftext($imageRes, $val['fontSize'], $val['angle'], $val['left'], $val['top'], $fontColor, $val['fontPath'], $val['text']);
        }
    }
    //生成图片
    if (!empty($filename)) {
        $res = imagejpeg($imageRes, $filename, 90); //保存到本地
        imagedestroy($imageRes);
        if (!$res) return false;
        return $filename;
    } else {
        imagejpeg($imageRes);     //在浏览器上显示
        imagedestroy($imageRes);
    }
}

if(!function_exists('imagecopymerge_alpha')) {
    function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $opacity=$pct;
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);

        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
        //$opacity = 100 - $opacity;

        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
    }
}
/*--------------- 工具函数 -----------------*/
function getRealClientIp()
{
    //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $res = preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : '';
    return $res;
}

/**
 * 检查手机号码格式
 * @param $mobile 手机号码
 */
function check_mobile($mobile)
{
    if (preg_match('/1[3456789]\d{9}$/', $mobile)
        || preg_match('/000\d{8}$/', $mobile))
        return true;
    return false;
}

//自定义函数手机号隐藏中间四位
function hidePhoneNumber($phone)
{
    return substr_replace($phone, '****', 3, 4);
}

//姓名隐藏中间一位
function hideRealName($realName)
{
    return substr_replace($realName, '*', 1, 1);
}

function check_email($email)
{
    if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $email)) {
        return false;
    }
    return true;
}

function check_url($url)
{
    if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%
=~_|]/i", $url)) {
        return false;
    }
    return true;
}

/**
 * 作用：统计字符长度包括中文、英文、数字
 * 参数：需要进行统计的字符串、编码格式目前系统统一使用UTF-8
 * @param $str      字符串
 * @param $charset  字符编码
 * @return int
 */
function sstrlen($str, $charset = 'utf-8')
{
    $n = 0;
    $p = 0;
    $c = '';
    $len = strlen($str);
    if ($charset == 'utf-8') {
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str{$i});
            if ($c > 252) {
                $p = 5;
            } elseif ($c > 248) {
                $p = 4;
            } elseif ($c > 240) {
                $p = 3;
            } elseif ($c > 224) {
                $p = 2;
            } elseif ($c > 192) {
                $p = 1;
            } else {
                $p = 0;
            }
            $i += $p;
            $n++;
        }
    } else {
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str{$i});
            if ($c > 127) {
                $p = 1;
            } else {
                $p = 0;
            }
            $i += $p;
            $n++;
        }
    }
    return $n;
}

/**
 * 分行连续截取字符串
 * @param $str              需要截取的字符串,UTF-8
 * @param int $row 截取的行数
 * @param int $number 每行截取的字数，中文长度
 * @param bool $suffix 最后行是否添加‘…’后缀
 * @return array            返回数组共$row个元素，下标1到$row
 */
function rowSubstr($str, $row = 1, $number = 10, $suffix = true)
{
    $result = array();
    for ($r = 1; $r <= $row; $r++) {
        $result[$r] = '';
    }

    $str = trim($str);
    if (!$str) return $result;

    $theStrlen = strlen($str);

    //每行实际字节长度
    $oneRowNum = $number * 3;
    for ($r = 1; $r <= $row; $r++) {
        if ($r == $row and $theStrlen > $r * $oneRowNum and $suffix) {
            $result[$r] = mg_cn_substr($str, $oneRowNum - 6, ($r - 1) * $oneRowNum) . '...';
        } else {
            $result[$r] = mg_cn_substr($str, $oneRowNum, ($r - 1) * $oneRowNum);
        }
        if ($theStrlen < $r * $oneRowNum) break;
    }

    return $result;
}

/**
 * 按字节截取utf-8字符串
 * 识别汉字全角符号，全角中文3个字节，半角英文1个字节
 * @param $str  需要切取的字符串
 * @param $len  截取长度[字节]
 * @param int $start 截取开始位置，默认0
 * @return string
 */
function mg_cn_substr($str, $len, $start = 0)
{
    $q_str = '';
    $q_strlen = ($start + $len) > strlen($str) ? strlen($str) : ($start + $len);

    //如果start不为起始位置，若起始位置为乱码就按照UTF-8编码获取新start
    if ($start and json_encode(substr($str, $start, 1)) === false) {
        for ($a = 0; $a < 3; $a++) {
            $new_start = $start + $a;
            $m_str = substr($str, $new_start, 3);
            if (json_encode($m_str) !== false) {
                $start = $new_start;
                break;
            }
        }
    }

    //切取内容
    for ($i = $start; $i < $q_strlen; $i++) {
        //ord()函数取得substr()的第一个字符的ASCII码，如果大于0xa0的话则是中文字符
        if (ord(substr($str, $i, 1)) > 0xa0) {
            $q_str .= substr($str, $i, 3);
            $i += 2;
        } else {
            $q_str .= substr($str, $i, 1);
        }
    }
    return $q_str;
}

/**
 * +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * +----------------------------------------------------------
 * @param int $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 * +----------------------------------------------------------
 * @return string
 * +----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '')
{
    $str = '';
    switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1:
            $chars = str_repeat('0123456789', 3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) {//位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    if ($type != 4) {
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
    } else {
        // 中文随机字
        for ($i = 0; $i < $len; $i++) {
            $str .= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
        }
    }
    return $str;
}

/**
 * 生成一定范围内的随机数
 * @param int $min
 * @param int $max
 * @return int
 */
function randLimitNumber($min = 0, $max = 100)
{
    srand((double)microtime() * 1000000);
    $random = rand($min, $max);
    return $random;
}

/**
 * @desc 将数组中的null转换为空字串
 * @param array $data
 * @return array
 */
function null2str($data)
{
    if (is_array($data)) {
        array_walk_recursive($data, function (&$v) {
            $v = is_null($v) ? '' : $v;
        });
    }
    return $data;
}

/**
 * 过滤危险代码
 * @param $str
 * @param string $tag
 * @return null|string|string[]
 */
function filterDangerCode($str, $tag = 'script')
{
    $preg = "/<" . $tag . "[\s\S]*?<\/" . $tag . ">/i";
    $newstr = preg_replace($preg, "", $str);    //第四个参数中表示替换几次，默认是-1，替换全部
    return $newstr;
}

/**
 * 过滤emoji表情
 * @param $str
 * @return null|string|string[]
 */
function filterEmoji($str)
{
    $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);

    return $str;
}

/**
 * 取得某月天数,可用于任意月份
 * @param string $month 月份
 * @param string $year 年份
 * @return number
 */
function days($month, $year)
{
    switch ($month) {
        case 4 :
        case 6 :
        case 9 :
        case 11 :
            $days = 30;
            break;

        case 2 :
            if ($year % 4 == 0) {
                if ($year % 100 == 0) {
                    $days = $year % 400 == 0 ? 29 : 28;
                } else {
                    $days = 29;
                }
            } else {
                $days = 28;
            }
            break;

        default :
            $days = 31;
            break;
    }
    return $days;
}

/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $date1
 * @param string $date2
 * @return int
 */
function dateDiffs($date1, $date2)
{
    if ($date1 < $date2) {
        $tmp = $date2;
        $date2 = $date1;
        $date1 = $tmp;
    }
    return round(($date1 - $date2) / 86400);
}

/**
 * 计算两个时间戳相差的月份
 * @param $beginTime
 * @param $endTime
 * @return int
 */
function monthDiffs($beginTime, $endTime)
{
    $beginYear = date('Y', $beginTime);
    $endYear = date('Y', $endTime);
    $beginMonth = date('m', $beginTime);
    $endMonth = date('m', $endTime);
    return ($endYear - $beginYear) * 12 + $endMonth - $beginMonth;
}

/**
 * 阳历增减天数、月数、周数、年数
 *  @author http://blog.alipay168.cn
 * @param $express string 表达式
 * @param $lunarYmd string 阴历日期
 * @return array
 */
function cal_lunar($express, $lunarYmd)
{
    //拆分年月日时分秒
    list($ly, $lm, $ld) = explode('-', date('Y-n-j', strtotime($lunarYmd)));
    $express = trim($express);
    if (stripos($express, 'd') !== false) {
        //取出符号,"+" 或者 "-"
        $symbol = substr($express, 0, 1);
        $num = intval(trim(str_replace(['day', 'days', 'd', '+', '-'], '', $express)));
        $nextLunar = \com\nlf\calendar\Lunar::fromYmd($ly, $lm, $ld);
        $nextLunar = $symbol == '+' ? $nextLunar->next($num) : $nextLunar->next(-$num);
        $lunar = $nextLunar->getYear() . '-' . $nextLunar->getMonth() . '-' . $nextLunar->getDay();
        $nextLunarStr = $nextLunar->toString();
        $nextSolar = $nextLunar->getSolar()->toString();
    } elseif (stripos($express, 'w') !== false) {
        //取出符号,"+" 或者 "-"
        $symbol = substr($express, 0, 1);
        $num = intval(trim(str_replace(['week', 'w', 'weeks', '+', '-'], '', $express)));
        //转换为周的天数
        $num = 7 * $num;
        $nextLunar = \com\nlf\calendar\Lunar::fromYmd($ly, $lm, $ld);
        $nextLunar = $symbol == '+' ? $nextLunar->next($num) : $nextLunar->next(-$num);
        $lunar = $nextLunar->getYear() . '-' . $nextLunar->getMonth() . '-' . $nextLunar->getDay();
        $nextLunarStr = $nextLunar->toString();
        $nextSolar = $nextLunar->getSolar()->toString();
    } elseif (stripos($express, 'm') !== false) {
        //取出符号,"+" 或者 "-"
        $symbol = substr($express, 0, 1);
        $num = intval(trim(str_replace(['month', 'months', 'm', '+', '-'], '', $express)));

        if ($symbol == '+') {
            $addNum = $lm + $num;
            if ($addNum <= 12) {
                $lm = $addNum;
            } else {
                $ly = $ly + intval($addNum / 12);
                $lm = $addNum % 12;
            }
        } else {
            if ($num < $lm) {
                $lm = $lm - $num;
            } else {
                $lm = 12 - ($num - $lm) % 12;
                $ly = $ly - intvaL(($num + $lm) / 12);
            }
        }
        $nextLunar = \com\nlf\calendar\Lunar::fromYmd($ly, $lm, $ld);
        $nextLunarStr = $nextLunar->toString();
        $lunar = $nextLunar->getYear() . '-' . $nextLunar->getMonth() . '-' . $nextLunar->getDay();
        $nextSolar = $nextLunar->getSolar()->toString();

    } elseif (stripos($express, 'y') !== false) {
        //取出符号,"+" 或者 "-"
        $symbol = substr($express, 0, 1);
        $num = intval(trim(str_replace(['year', 'years', 'y', '+', '-'], '', $express)));
        $nextLunar = \com\nlf\calendar\Lunar::fromYmd($symbol == '+' ? $ly + $num : $ly - $num, $lm, $ld);
        $nextLunarStr = $nextLunar->toString();
        $lunar = $nextLunar->getYear() . '-' . $nextLunar->getMonth() . '-' . $nextLunar->getDay();
        $nextSolar = $nextLunar->getSolar()->toString();
    } else {
        $nextLunar = \com\nlf\calendar\Lunar::fromYmd($ly, $lm, $ld);
        $nextLunarStr = $nextLunar->toString();
        $lunar = $ly . '-' . $lm . '-' . $ld;
        $nextSolar = $nextLunar->getSolar()->toString();
    }

    return [
        'lunar' => $lunar,
        'lunarStr' => $nextLunarStr,
        'solar' => $nextSolar,
    ];
}

/**
 * 二维数组排序  根据某个值排序
 * @param array $arr 数组
 * @param string $keys 用来排序的值
 * @param string $type 升序和降序
 * @return    array|bool
 *
 */
function arraySort($arr, $keys, $type = 'asc')
{
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v) {
        $keysvalue[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 取数组中指定键值的元素
 * @param array $array
 * @param array $keys
 * @return array
 * @example $ages  = ['lb'=>27,'cnn'=>23,'mm'=>'22','cc'=>21];
 * $filters = array_only($ages,['cnn']);
 * print_r 将会是 ['cnn']
 */
function arrayOnly($array, $keys)
{
    return array_intersect_key($array, array_flip((array)$keys));
}

/**
 * 取数组中除去指定键值的元素
 * @param array $array
 * @param array $keys
 * @return array
 * @example $ages  = ['lb'=>27,'cnn'=>23,'mm'=>'22','cc'=>21];
 * $filters = array_except($ages,['cnn']);
 * print_r 将会是 ['lb','mm','cc']
 */
function arrayExcept($array, $keys)
{
    return array_diff_key($array, array_flip((array)$keys));
}

/**
 * 取数组中的元素 如果不存在可指定默认值 键支持 ' world.country.province.city'格式
 * @param $array
 * @param $key
 * @param $default
 * @return mixed
 * @example  $city = array_get($address,'world.contry.province.city','shanghai');
 */
function arrayGet($array, $key, $default = null)
{
    if (is_null($key)) {

        return $array[$key];
    }
    if (isset($array[$key])) {

        return $array[$key];
    }
    foreach (explode('.', $key) as $segment) {

        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];

        return $array;
    }
}

/**
 * 递归方式的对变量中的特殊字符进行转义
 * @param mix $value
 * @return  mix
 */
function addslashesDeep($value)
{
    if (empty($value)) {
        return $value;
    } else {
        return is_array($value) ? array_map('addslashesDeep', $value) : addslashes(trim($value));
    }
}

/**
 * 将对象成员变量或者数组的特殊字符进行转义
 * @param mix $obj 对象或者数组
 * @return   mix                  对象或者数组
 */
function addslashesDeepObj($obj)
{
    if (is_object($obj) == true) {
        foreach ($obj as $key => $val) {
            $obj->$key = addslashesDeep($val);
        }
    } else {
        $obj = addslashesDeep($obj);
    }

    return $obj;
}

/**
 * 递归方式的对变量中的特殊字符去除转义
 * @param mix $value
 * @return  mix
 */
function stripslashesDeep($value)
{
    if (empty($value)) {
        return $value;
    } else {
        return is_array($value) ? array_map('stripslashesDeep', $value) : stripslashes($value);
    }
}

/**
 * 去除危险字符，防止SQL攻击
 * @param string $string ;
 * @return    string
 */
function safe4search($string)
{

    $string = str_replace('%20', '', $string);
    $string = str_replace('%27', '', $string);
    $string = str_replace('%2527', '', $string);
    $string = str_replace('*', '', $string);
    $string = str_replace('"', '&quot;', $string);
    $string = str_replace("'", '', $string);
    $string = str_replace('"', '', $string);
    $string = str_replace(';', '', $string);
    $string = str_replace('<', '&lt;', $string);
    $string = str_replace('>', '&gt;', $string);
    $string = str_replace("{", '', $string);
    $string = str_replace('}', '', $string);
    $string = str_replace('\\', '', $string);
    $string = str_replace('%', '\%', $string);
    return htmlspecialchars($string, ENT_QUOTES);

}

/**
 *  将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
 *
 * @access  public
 * @param string $str 待转换字串
 *
 * @return  string       $str         处理后字串
 */
function makeSemiangle($str)
{
    $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
        '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
        'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
        'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
        'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
        'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
        'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
        'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
        'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
        'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
        'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
        'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
        'ｙ' => 'y', 'ｚ' => 'z',
        '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',
        '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']',
        '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',
        '》' => '>',
        '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
        '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.',
        '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',
        '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',
        '　' => ' ');

    return strtr($str, $arr);
}

/**
 * 加密解密函数
 * 该函数来源于DISCUZ
 * @param string $string 欲加密、解密的字符串
 * @param string $operation DECODE表示解密,其它表示加密
 * @param string $key 密匙
 * @param int $expiry 密文有效期
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;

    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 加密
 * @param $password
 * @param $salt
 * @return string
 */
function encryptWithSalt($str, $salt)
{
    return md5(md5($str) . $salt);
}

/**
 * 加密函数
 *
 * @param string $str 加密前的字符串
 * @param string $key 密钥
 * @return string 加密后的字符串
 */
function dz_encrypt($str, $key = '')
{
    $coded = '';
    $keylength = strlen($key);

    for ($i = 0, $count = strlen($str); $i < $count; $i += $keylength) {
        $coded .= substr($str, $i, $keylength) ^ $key;
    }

    return str_replace('=', '', base64_encode($coded));
}

/**
 * 解密函数
 *
 * @param string $str 加密后的字符串
 * @param string $key 密钥
 * @return string 加密前的字符串
 */
function dz_decrypt($str, $key = '')
{
    $coded = '';
    $keylength = strlen($key);
    $str = base64_decode($str);

    for ($i = 0, $count = strlen($str); $i < $count; $i += $keylength) {
        $coded .= substr($str, $i, $keylength) ^ $key;
    }

    return $coded;
}

function crc32_encode($val){
    $checksum = crc32($val);
    if($checksum < 0) $checksum += 4294967296;
    return $checksum;
}

/**
 * 生成API接口参数签名
 * @param array $params
 * @param $authKey
 * @return string
 */
function genApiSignature(array $params, $authKey)
{
    ksort($params);
    $str = '';
    foreach ($params as $key => $val) {
        if ($val === '') {
            continue;
        }
        $str .= empty($str) ? $key . '=' . $val : '&' . $key . '=' . $val;
    }
    return md5($str . $authKey);
}

/**
 * 判断字符串是否为中文
 * @param $str
 * @return false|int
 */
function isChinese($str)
{
    //[\u4E00-\u9FFF]+
    $pattern = '#[\x{4e00}-\x{9fa5}]+#u';
    return preg_match($pattern, $str);
}

/*----------------------IP、地理位置相关函数---------------------------------------*/
define('EARTH_RADIUS', 6371);//地球半径，平均半径为6371km
/**
 *计算某个经纬度的周围某段距离的正方形的四个点
 *
 * @param float lng 经度
 * @param float lat 纬度
 * @param float distance 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
 * @return array 正方形的四个点的经纬度坐标
 */
function squarePoint($lng, $lat, $distance = 0.5)
{

    $dlng = 2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
    $dlng = rad2deg($dlng);

    $dlat = $distance / EARTH_RADIUS;
    $dlat = rad2deg($dlat);

    return array(
        'left-top' => array(
            'lat' => $lat + $dlat,
            'lng' => $lng - $dlng
        ),
        'right-top' => array(
            'lat' => $lat + $dlat,
            'lng' => $lng + $dlng
        ),
        'left-bottom' => array(
            'lat' => $lat - $dlat,
            'lng' => $lng - $dlng
        ),
        'right-bottom' => array(
            'lat' => $lat - $dlat,
            'lng' => $lng + $dlng
        )
    );

}

/**
 * 根据地址返回经纬度坐标
 * @param string $addr 地址，例：广州市天河区体育西广利路77号东洲大厦B座
 * @param string $city 城市，广州
 * @return    array|bool
 *
 */
function geocoder($addr, $city)
{

    $result = array(
        'lon' => 0,
        'lat' => 0
    );
    if (empty($addr)) {
        return $result;
    }
    $addr = urlencode($addr);
    $url = 'http://api.map.baidu.com/geocoder?address=' . $addr . '&output=json&key=' . C('BAIDU_MAP_KEY');
    if (!empty($city)) {
        $url .= '&city=' . urlencode($city);
    }
    $result = file_get_contents($url);
    $result = json_decode($result, 1);
    if (is_array($result) && strtoupper($result['status']) == 'OK') {
        $result = array(
            'lon' => floatval($result['result']['location']['lng']),
            'lat' => floatval($result['result']['location']['lat'])
        );
    } else {
        $result = false;
    }
    return $result;

}

/**
 * 根据地球上任意两点的经纬度计算两点间的距离 单位 m
 * @return    float
 *
 */
function pointDistance($lat1, $lng1, $lat2, $lng2)
{

    $lat1 = ($lat1 * pi()) / 180;
    $lng1 = ($lng1 * pi()) / 180;

    $lat2 = ($lat2 * pi()) / 180;
    $lng2 = ($lng2 * pi()) / 180;

    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = EARTH_RADIUS * $stepTwo * 1000;
    return round($calculatedDistance);
}

if (!function_exists('isMobile')) {
    function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('isWeixin')) {
    function isWeixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        } else {
            return false;
        }
    }
}
//===================================
//
// 功能：IP地址获取真实地址函数
// 参数：$ip - IP地址
//
//===================================
define('__QQWRY__', DOC_PATH . "./data/qqwry.dat");
function ipfrom($ip)
{
    //检查IP地址
    if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
        return 'IP Address Error';
    }
    //打开IP数据文件
    if (!$fd = @fopen(__QQWRY__, 'rb')) {
        return 'IP date file not exists or access denied';
    }

    //分解IP进行运算，得出整形数
    $ip = explode('.', $ip);
    $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

    //获取IP数据索引开始和结束位置
    $DataBegin = fread($fd, 4);
    $DataEnd = fread($fd, 4);
    $ipbegin = implode('', unpack('L', $DataBegin));
    if ($ipbegin < 0) $ipbegin += pow(2, 32);
    $ipend = implode('', unpack('L', $DataEnd));
    if ($ipend < 0) $ipend += pow(2, 32);
    $ipAllNum = ($ipend - $ipbegin) / 7 + 1;

    $BeginNum = 0;
    $EndNum = $ipAllNum;

    //使用二分查找法从索引记录中搜索匹配的IP记录
    while ($ip1num > $ipNum || $ip2num < $ipNum) {
        $Middle = intval(($EndNum + $BeginNum) / 2);

        //偏移指针到索引位置读取4个字节
        fseek($fd, $ipbegin + 7 * $Middle);
        $ipData1 = fread($fd, 4);
        if (strlen($ipData1) < 4) {
            fclose($fd);
            return 'System Error';
        }
        //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
        $ip1num = implode('', unpack('L', $ipData1));
        if ($ip1num < 0) $ip1num += pow(2, 32);

        //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
        if ($ip1num > $ipNum) {
            $EndNum = $Middle;
            continue;
        }

        //取完上一个索引后取下一个索引
        $DataSeek = fread($fd, 3);
        if (strlen($DataSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
        fseek($fd, $DataSeek);
        $ipData2 = fread($fd, 4);
        if (strlen($ipData2) < 4) {
            fclose($fd);
            return 'System Error';
        }
        $ip2num = implode('', unpack('L', $ipData2));
        if ($ip2num < 0) $ip2num += pow(2, 32);

        //没找到提示未知
        if ($ip2num < $ipNum) {
            if ($Middle == $BeginNum) {
                fclose($fd);
                return 'Unknown';
            }
            $BeginNum = $Middle;
        }
    }

    //下面的代码读晕了，没读明白，有兴趣的慢慢读
    $ipFlag = fread($fd, 1);
    if ($ipFlag == chr(1)) {
        $ipSeek = fread($fd, 3);
        if (strlen($ipSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
        fseek($fd, $ipSeek);
        $ipFlag = fread($fd, 1);
    }

    if ($ipFlag == chr(2)) {
        $AddrSeek = fread($fd, 3);
        if (strlen($AddrSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if (strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }

        while (($char = fread($fd, 1)) != chr(0))
            $ipAddr2 .= $char;

        $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
        fseek($fd, $AddrSeek);

        while (($char = fread($fd, 1)) != chr(0))
            $ipAddr1 .= $char;
    } else {
        fseek($fd, -1, SEEK_CUR);
        while (($char = fread($fd, 1)) != chr(0))
            $ipAddr1 .= $char;

        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if (strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }
        while (($char = fread($fd, 1)) != chr(0)) {
            $ipAddr2 .= $char;
        }
    }
    fclose($fd);

    //最后做相应的替换操作后返回结果
    if (preg_match('/http/i', $ipAddr2)) {
        $ipAddr2 = '';
    }
    $ipaddr = "$ipAddr1 $ipAddr2";
    $ipaddr = preg_replace('/CZ88.Net/is', '', $ipaddr);
    $ipaddr = preg_replace('/^s*/is', '', $ipaddr);
    $ipaddr = preg_replace('/s*$/is', '', $ipaddr);
    if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
        $ipaddr = 'Unknown';
    }

    return $ipaddr;
}

/**
 * 保存远程文件到本地
 * @param $url
 * @param $dir
 * @param $filename
 * @param array $allowExts
 * @return bool|string
 */
function grabRemoteFile($url, $dir, $filename = '', array $allowExts = [])
{
    if ($url == "") {
        return false;
    }
    if ($filename == "") {
        $urlParse = parse_url($url);
        $ext = strrchr($urlParse['path'], ".");
        if (!empty($allowExts) && !in_array($ext, $allowExts)) {
            return false;
        }
        $filename = time() . rand_string(10) . $ext;
    }
    ob_start();
    readfile($url);
    $file = ob_get_contents();
    ob_end_clean();
//    $size = strlen($file);
    $fp2 = @fopen($dir . $filename, "a");
    fwrite($fp2, $file);
    fclose($fp2);
    return $filename;
}

/*------------------------目录相关函数------------------------------------------*/
// 循环创建目录
if (!function_exists('makeDir')) {
    function makeDir($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode))
            return true;
        if (!makeDir(dirname($dir), $mode))
            return false;
        return @mkdir($dir, $mode);
    }
}

/**
 * 修正目录分隔符
 * @param string $path
 * @return string
 */
function dirPath($path)
{

    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;

}

/**
 * 递归删除目录
 *
 * @param string $dir
 * @return bool
 */
function delDir($dir)
{
    $dir = dirPath($dir);
    if (!is_dir($dir)) {
        return FALSE;
    }
    //系统目录，防止误删
    $systemdirs = array();
    if (in_array($dir, $systemdirs)) {
        exit("Cannot remove system dir $dir !");
    }
    $list = glob($dir . '*');
    foreach ($list as $v) {
        is_dir($v) ? delDir($v) : @unlink($v);
    }
    return @rmdir($dir);
}

/**
 * 列出目录的子目录
 * @param string $dir
 */
function listDir($dir)
{
    $temp_list = array();
    // BY 南极村民 < yinpoo@126.com > 2016-10-19 10:40 判断严谨，避免因文件路径不存在造成奔溃
    if (!file_exists($dir)) {
        return array();
    }

    $dirhandle = opendir($dir);
    while (($file = readdir($dirhandle)) !== false) {
        if (($file != ".") && ($file != "..") && ($file != ".svn") && is_dir($dir . $file)) {
            $temp_list[$file] = $file;
        }
    }
    @closedir($dirhandle);
    return $temp_list;

}

/**
 * 列出目录中的文件
 * @param string $dir
 * @param string $pattern
 * @return array
 */
function listFiles($dir, $pattern = '', $ext = '')
{
    $files = array();
    $dirhandle = opendir($dir);
    while (($file = readdir($dirhandle)) !== false) {
        if (($file != ".") && ($file != "..") && is_file($dir . $file)) {
            $fname = $ext ? basename($file, $ext) : basename($file);
            if (!empty($pattern)) {
                if (preg_match($pattern, $fname)) {
                    $files[] = $fname;
                } else {
                    continue;
                }
            } else {
                $files[] = $fname;
            }
        }
    }
    @closedir($dirhandle);
    return $files;
}

/*------------------------目录相关函数END-----------------------------------*/

/**
 * 为SQL查询创建LIMIT条件
 * @param int $page
 * @param int $limit
 * @return array
 */
function build_limit($page = 1, $limit = 10)
{
    $page = $page >= 1 ? $page : 1;
    $return = array(
        'begin' => ($page - 1) * $limit,
        'offset' => $limit
    );
    return $return;

}

/**
 * 将xml转换为数组
 * @param string $xml :xml文件或字符串
 * @return array
 */
function xmlToArray($xml)
{
    //考虑到xml文档中可能会包含<![CDATA[]]>标签，第三个参数设置为LIBXML_NOCDATA
    if (file_exists($xml)) {
        libxml_disable_entity_loader(false);
        $xml_string = simplexml_load_file($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    } else {
        libxml_disable_entity_loader(true);
        $xml_string = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    }
    $result = json_decode(json_encode($xml_string), true);
    return $result;
}

/**
 * 将数组转换为xml
 * @param array $arr :数组
 * @param object $dom :Document对象，默认null即可
 * @param object $node :节点对象，默认null即可
 * @param string $root :根节点名称
 * @param string $cdata :是否加入CDATA标签，默认为false
 * @return string
 */
function arrayToXml($arr, $dom = null, $node = null, $root = 'xml', $cdata = false)
{
    if (!$dom) {
        $dom = new DOMDocument('1.0', 'utf-8');
    }
    if (!$node) {
        $node = $dom->createElement($root);
        $dom->appendChild($node);
    }
    foreach ($arr as $key => $value) {
        $child_node = $dom->createElement(is_string($key) ? $key : 'node');
        $node->appendChild($child_node);
        if (!is_array($value)) {
            if (!$cdata) {
                $data = $dom->createTextNode($value);
            } else {
                $data = $dom->createCDATASection($value);
            }
            $child_node->appendChild($data);
        } else {
            arrayToXml($value, $dom, $child_node, $root, $cdata);
        }
    }
    return $dom->saveXML();
}

/**
 * 读取CSV文件中的某几行数据
 * @param $csvfile csv文件路径
 * @param $lines 读取行数
 * @param $offset 起始行数
 * @return array
 * */
function csvGetLines($csvfile, $lines, $offset = 0)
{
    if (!$fp = fopen($csvfile, 'r')) {
        return false;
    }
    $i = $j = 0;
    while (false !== ($line = fgets($fp))) {
        if ($i++ < $offset) {
            continue;
        }
        break;
    }
    $data = array();
    while (($j++ < $lines) && !feof($fp)) {
        $data[] = fgetcsv($fp);
    }
    fclose($fp);
    return $data;
}
