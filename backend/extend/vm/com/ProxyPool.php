<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/10/2 22:24
 * @description
 */

namespace vm\com;


class ProxyPool
{

    private $spiderPageNum = 2;

    public function __construct()
    {
        require_once __DIR__ . '/../org/requests/library/Requests.php';
        \Requests::register_autoloader();
    }

    public function spider()
    {
        $ipList = [];
        $this->getXiciIp($ipList);
        $this->getKuaidailiIp($ipList);
        return $ipList;
    }

    /**
     * 西刺代理ip
     * @param array $ipList
     * @return array
     */
    private function getXiciIp(array &$ipList)
    {
        for ($i = 1; $i <= $this->spiderPageNum; $i++) {
            $url = 'http://www.xicidaili.com/nn/' . $i;
            $response = \Requests::get($url);
            if(!$response->success || empty($response->body)) {
                continue;
            }
            $content = $response->body;
            preg_match_all('/<tr.*>[\s\S]*?<td class="country">[\s\S]*?<\/td>[\s\S]*?<td>(.*?)<\/td>[\s\S]*?<td>(.*?)<\/td>/', $content, $match);

            $hosts = $match[1];
            $ports = $match[2];
            foreach ($hosts as $key => $value) {
                if(!$this->checkIp($hosts[$key] . ':' . $ports[$key])) {
                    continue;
                }
                $ipList[] = [
                    'host' => $hosts[$key],
                    'port' => $ports[$key]
                ];
            }
        }
        return $ipList;
    }

    /**
     * 采集快代理免费IP
     * https://www.kuaidaili.com
     */
    public function getKuaidailiIp(array &$ipList)
    {
        $url = 'https://www.kuaidaili.com/free/inha/1/';
        $response = \Requests::get($url);
        if(!$response->success || empty($response->body)) {
            return;
        }
        $content = $response->body;
        preg_match_all('/<tr.*>[\s\S]*?<td data-title="IP">(.*?)<\/td>[\s\S]*?<td data-title="PORT">(.*?)<\/td>/', $content, $match);

        $hosts = $match[1];
        $ports = $match[2];
        foreach ($hosts as $key => $value) {
            if(!$this->checkIp($hosts[$key] . ':' . $ports[$key])) {
                continue;
            }
            $ipList[] = [
                'host' => $hosts[$key],
                'port' => $ports[$key]
            ];
        }
        return $ipList;
    }

    //检测IP可用性
    public function checkIp($ip)
    {
        $result = false;
        //用百度网和腾讯网测试IP地址的可用性
        $options = [
            'proxy' => $ip,
            'timeout' => 5,
            'connect_timeout' => 3
        ];
        for ($i = 0; $i < 2; $i++) {
            try {
                $response = \Requests::get('https://www.baidu.com', [], $options);
                if ($response->success && $response->status_code == 200) {
                    $result = true;
                    break;
                } else {
                    $response = \Requests::get('http://www.qq.com', [], $options);
                    if ($response->success && $response->status_code == 200) {
                        $result = true;
                        break;
                    }
                }
            } catch (\Exception $e) {

            }
        }
        return $result;
    }
}