<?php

namespace Base\Cache;

/**
 * Class RedisConnector
 * @package Base\Cache
 */
class RedisConnector
{
    /**
     * @var \Redis
     */
    protected $_redis;
    protected $_host = '127.0.0.1';
    protected $_port = 6379;
    protected $_password = '';
    protected $_autoSerialize = TRUE;
    /**
     * 连接池
     * @var array
     */
    protected static $instances = [];
    /**
     * 不进行自动序列化连接
     * @var array
     */
    protected static $noSerInstances = [];

    private function __construct($host, $port, $password, $autoSerialize = TRUE)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_password = $password;
        $this->_autoSerialize = $autoSerialize;
        $this->connect();
    }

    public static function getInstance($host, $port, $password)
    {
        $key = $host . $port;
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($host, $port, $password);
        }
        return self::$instances[$key];
    }

    public static function getNoSerInstance($host, $port, $password)
    {
        $key = $host . $port;
        if (!isset(self::$noSerInstances[$key])) {
            self::$noSerInstances[$key] = new self($host, $port, $password, FALSE);
        }
        return self::$noSerInstances[$key];
    }

    /**
     * 截获redis调用, 如果抛出异常则进行redis重连
     * @param $redisFunc
     * @param $args
     * @return mixed
     */
    public function __call($redisFunc, $args)
    {
        try {
            $ret = call_user_func_array([$this->_redis, $redisFunc], $args);
        } catch (\RedisException $e) {
            $this->connect();
            $ret = call_user_func_array([$this->_redis, $redisFunc], $args);
        }
        return $ret;
    }


    /**
     * 连接redis
     * @return void
     * @throws \Exception
     */
    protected function connect()
    {
        $this->_redis = new \Redis();
        $success = $this->_redis->connect($this->_host, $this->_port, 5);
        if (!$success) {
            throw new \Exception('could not connect to redis');
        }
        if (!empty($this->_password) && !$this->_redis->auth($this->_password)) {
            throw new \Exception('auth redis error');
        }
        if ($this->_autoSerialize) {
            $this->_redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
        }
    }
}
