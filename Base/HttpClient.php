<?php

namespace Base;

use EasySwoole\Core\Component\Logger;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

class HttpClient
{
    /**
     * 发送post请求
     * @param $url
     * @param $data
     * @param array $options
     * @return string
     */
    public static function post($url, $data, array $options = [])
    {
        $timeout = $options['timeout'] ?? 3.0;
        unset($options['timeout']);
        $payload = [];
        if (!empty($data)) $payload = ['form_params' => $data];
        if (isset($options['cookie'])) {
            $domain = parse_url($url)['host'];
            $payload['cookies'] = CookieJar::fromArray($options['cookie'], $domain);
            unset($options['cookie']);
        }
        $payload = array_merge($payload, $options);
        $client = new Client(['timeout' => $timeout]);
        try {
            $response = $client->request('POST', $url, $payload);
            return (string)$response->getBody();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = Psr7\str($e->getResponse());
            } else {
                $response = $e->getMessage();
            }
            Logger::getInstance()->log('http post error: Request-' . Psr7\str($e->getRequest()) . ' --- Response-' . $response, 'error');
            return FALSE;
        }
    }

    /**
     * 发送get请求
     * @param $url
     * @param $data
     * @param array $options
     * @return string
     */
    public static function get($url, $data, array $options = [])
    {
        $payload = ['query' => $data];
        if (isset($options['cookie'])) {
            $domain = parse_url($url)['host'];
            $payload['cookies'] = CookieJar::fromArray($options['cookie'], $domain);
            unset($options['cookie']);
        }
        if(isset($options['timeout'])){
            $time_out = $options['timeout'];
            unset($options['timeout']);
        }else{
            $time_out = 3.0;
        }
        $payload = array_merge($payload, $options);
        $client = new Client(['timeout' => $time_out]);
        try {
            $response = $client->request('GET', $url, $payload);
            return (string)$response->getBody();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = Psr7\str($e->getResponse());
            } else {
                $response = $e->getMessage();
            }
            Logger::getInstance()->log('http get error: Request-' . Psr7\str($e->getRequest()) . ' --- Response-' . $response, 'error');
            return FALSE;
        }
    }
}