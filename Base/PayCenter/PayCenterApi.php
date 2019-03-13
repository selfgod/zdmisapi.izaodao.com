<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/15
 * Time: 下午5:10
 */
namespace Base\PayCenter;


use Base\HttpClient;
use EasySwoole\Config;
use EasySwoole\Core\Component\Logger;

class PayCenterApi
{
    private $config;
    protected $appIdArr = [
        'de'=>'9058f61b5e814c2ebaa4e062df162235',
        'kr'=>'06fedeb667084d0f9b6666e8baccc9e1',
        'bp'=>'887b86de893d476fb83d6d0f9a7fa834',
    ];

    function __construct()
    {
        $this->config = Config::getInstance()->getConf('PAY_API');

    }

    /**
     * @param $url
     * @param array $param
     * @return mixed
     */
    function get($url, $param=[]){
        $res_string = HttpClient::get($this->config['payApiHost'].$url, $apiParam = $this->apiParam($param));
        try{
            $res = json_decode($res_string, true);
            if(!$res['state']) Logger::getInstance()->log('接口错误：'.json_encode($apiParam));
            return $res['objData'];
        }catch (\Exception $e){
            Logger::getInstance()->log('网络错误：'.json_encode($e));
        }
        return [];
    }

    /**
     * @param $url
     * @param $data
     * @return string
     */
    function post($url, $data){
        $res_string = HttpClient::post($this->config['payApiHost'].$url, $apiParam = $this->apiParam($data));
        try{
            $res = json_decode($res_string, true);
            if(!$res['state']) Logger::getInstance()->log('接口错误：'.json_encode($apiParam));
            return $res['objData'];
        }catch (\Exception $e){
            Logger::getInstance()->log('网络错误：'.json_encode($e));
        }
        return [];
    }

    /**
     * @param array $data
     * @return array
     */
    private function apiParam($data=[]){
        $data['appId'] = $this->config['payAppId'];
        $data['st'] = time();
        $sign_data = [];
        foreach($data as $k=>$v){
            if(in_array($k, ['appId','st','queryAppId'])) $sign_data[$k] = $v;
        }
        $data['sign'] = $this->getSignature($this->config['paySecretKey'], $sign_data);

        return $data;
    }

    /**
     * 生成签名字符串
     * @param $key
     * @param $params
     * @return string
     */
    protected function getSignature($key, $params)
    {
        if (empty($params)) {
            return '';
        }
        $str = '';
        ksort($params);
        foreach ($params as $k => $v) {
            if (!is_array($v)) {
                $str .= "$k=$v&";
            }
        }
        $str .= 'key=' . $key;
        return md5($str);
    }
}