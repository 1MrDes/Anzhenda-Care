<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/9/4 17:34
 * @description
 */

namespace vm\org;


use think\Exception;

class HttpUtil
{
    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_POST = 'POST';

    public function curl($url, $httpMethod = "GET", $postFields = null, $headers = null)
    {
        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
        if (stripos($url, 'https://') !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        if ($httpMethod == self::REQUEST_METHOD_POST) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($postFields) ? self::getPostHttpBody($postFields) : $postFields);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);    // 连接超时时间，单位:秒
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // 请求超时时间，单位：秒
        if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        if (is_array($headers) && 0 < count($headers)) {
            $httpHeaders = self::getHttpHearders($headers);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        }

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($httpCode == "0") { //time out
            throw new Exception("Curl error number:" . $curlErrNo . " , Curl error details:" . $curlErr . "\r\n");
        } else if ($httpCode != "200") { //we did send the notifition out and got a non-200 response
            throw new Exception("http code:" . $httpCode . " details:" . $result . "\r\n");
        }
        return $result;
//    $returnData = json_decode($result, TRUE);
//    if ($returnData["ret"] == "FAIL") {
//        throw new Exception("Failed, details:" . $result . "\r\n");
//    } else {
//        return $returnData["data"];
//    }
    }

    public static function request($url, $httpMethod = "GET", $postFields = null, $headers = null, array $proxy = null, $returnHeader = true)
    {
        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
        if (stripos($url, 'https://') !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        if ($httpMethod == self::REQUEST_METHOD_POST) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($postFields) ? self::getPostHttpBody($postFields) : $postFields);
        }

        curl_setopt($ch, CURLOPT_HEADER, $returnHeader); // 要求返回响应头
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);    // 连接超时时间，单位:秒
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);  // 请求超时时间，单位：秒
        if (isset($headers['User-Agent'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $headers['User-Agent']); // 模拟用户使用的浏览器
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        if(!isset($headers) || (!isset($headers['Referer']) && !isset($headers['referer']))) {
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        }

        // 设置代理
        if($proxy) {
            //设置代理
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['host'] . ':' . $proxy['port']);
            //设置代理用户名密码
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['username'] . ':' . $proxy['password']);
        }

        if (is_array($headers) && 0 < count($headers)) {
            $httpHeaders = self::getHttpHearders($headers);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        }

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($returnHeader) {
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            // 根据头大小去获取头信息内容
            $header = substr($result, 0, $headerSize);
        } else {
            $headerSize = 0;
            $header = null;
        }

        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

//        if ($httpCode == "0") { //time out
//            throw new Exception("Curl error number:" . $curlErrNo . " , Curl error details:" . $curlErr . "\r\n");
//        } else if ($httpCode != "200") { //we did send the notifition out and got a non-200 response
//            throw new Exception("http code:" . $httpCode . " details:" . $result . "\r\n");
//        }
        return [
            'code' => $httpCode,
            'header' => $returnHeader ? explode("\r\n", $header) : null,
            'content' => $returnHeader ? substr($result, $headerSize,-1) : $result
        ];
    }

    public static function upload($url, $field, $filepath)
    {
        $cl = curl_init();
        if(stripos($url, 'https://') !== FALSE) {
            curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($cl, CURLOPT_SSLVERSION, 1);
        }
        if (class_exists('\CURLFile')) {
            $fields = array($field => new \CURLFile(realpath($filepath)));
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($cl, CURLOPT_SAFE_UPLOAD, false);
            }
            $fields = [$field => '@' . $filepath];
        }
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($cl, CURLOPT_POST, true);
        curl_setopt($cl, CURLOPT_POSTFIELDS, $fields);
        $content = curl_exec($cl);
        $status = curl_getinfo($cl);
        $curlErrNo = curl_errno($cl);
        $curlErr = curl_error($cl);
        curl_close($cl);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            return $content;
        } else {
            return FALSE;
        }
    }

    public static function getPostHttpBody($postFildes)
    {
        $content = "";
        foreach ($postFildes as $apiParamKey => $apiParamValue) {
            $content .= "$apiParamKey=" . urlencode($apiParamValue) . "&";
        }
        return substr($content, 0, -1);
    }

    public static function getHttpHearders($headers)
    {
        $httpHeader = array();
        foreach ($headers as $key => $value) {
            array_push($httpHeader, $key . ":" . $value);
        }
        return $httpHeader;
    }

    /**
     * 通过代理访问
     * @param $url
     * @param null $headers
     * @return bool|string
     * @throws Exception
     */
    public function requestByProxy($url, $headers = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 设置代理服务器
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($ch, CURLOPT_PROXY, PROXY_SERVER);
        // 设置隧道验证信息
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, PROXY_AUTH);
        // 加此请求头会让每个请求都自动切换IP
        if (is_array($headers) && 0 < count($headers)) {
            $httpHeaders = self::getHttpHearders($headers);
            $httpHeaders[] = "Proxy-Switch-Ip: yes";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Proxy-Switch-Ip: yes",
            ]);
        }
        //curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); //curl解压gzip页面内容
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        //$info = curl_getinfo($ch);
        $error = [];
        if (!$result) {
            $error["code"] = curl_errno($ch);
            $error["msg"] = curl_error($ch);
            curl_close($ch);
            throw new Exception('code:' . $error["code"] . '  msg:' . $error["msg"]);
        }
        curl_close($ch);
        return $result;
    }
}