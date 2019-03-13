<?php
namespace Base;
require_once __DIR__ . '/Thrift/Clients/ThriftClient.php';

use EasySwoole\Config;
use ThriftClient\ThriftClient;

class Thrift
{
    /**
     * 是否初始化完成
     * @var bool
     */
    protected static $_initialized = FALSE;
    /**
     * 当前类实例
     * @var self
     */
    private static $_instance;

    /**
     * 首先获取远程RPC的所有接口配置
     * Thrift constructor.
     */
    private function __construct()
    {
        $rpc = Config::getInstance()->getConf('RPC');
        $addrs = explode('|', $rpc['host']);
        $confArr = [];
        foreach ($addrs as $index => $addr) {
            $confArr['Config']['addresses'][] = $addr . ':' . $rpc['port'];
        }
        ThriftClient::config($confArr);
        $confArr = [];
        $services = ThriftClient::instance('Config')->getServices();
        foreach ($services as $name => $port) {
            foreach ($addrs as $addr) {
                $confArr[$name]['addresses'][] = $addr . ':' . $port;
            }
        }

        ThriftClient::config($confArr);
    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 获取thrift service实例
     * @param $name
     * @return object
     */
    public function service($name)
    {
        return ThriftClient::instance($name);
    }
}