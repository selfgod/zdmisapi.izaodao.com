<?php

namespace Base;


use Base\Exception\BadRequestException;
use EasySwoole\Config;
use EasySwoole\Core\Component\Logger;

class SignFilter
{
    /**
     * 超过多少秒，当前请求为失败
     * @var int 时间戳时间控制
     */
    protected $timeDiff;

    /**
     * SignFilter constructor.
     * @param int $timeDiff
     */
    public function __construct($timeDiff = 600)
    {
        $this->timeDiff = $timeDiff;
    }


    public function check($params)
    {
        if (empty($params)) return;
        $signKey = Config::getInstance()->getConf('SIGN_KEY');
        $signature = strtolower($params['sign']);
        $timestamp = intval($params['st']);
        if (abs($timestamp - time()) > $this->timeDiff) {
            throw new BadRequestException('时间戳校验失败', 6);
        }
        unset($params['sign']);
        $expectSign = $this->getSignature($signKey, $params);

        if ($expectSign !== $signature) {
            Logger::getInstance()->log('Wrong Sign, needSign:' . $expectSign, 'debug');
            throw new BadRequestException('验签失败', 6);
        }
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