<?php

namespace Base\Cache;

/**
 * Redis操作类
 * Class ZdRedis
 * @package Base\Cache
 */
class ZdRedis
{

    /**
     * 过期时间 1天
     */
    const TTL = 86400;

    /**
     * 获取redis连接实例
     * @param bool $autoSerialize 是否进行自动序列化
     * @return mixed
     */
    public static function instance($autoSerialize = TRUE)
    {
        $redisConf = \EasySwoole\Config::getInstance()->getConf('REDIS');
        if ($autoSerialize) {
            return RedisConnector::getInstance($redisConf['host'], $redisConf['port'], $redisConf['pwd']);
        } else {
            return RedisConnector::getNoSerInstance($redisConf['host'], $redisConf['port'], $redisConf['pwd']);
        }
    }

    /**
     * 开启管道操作
     * @param bool $autoSerialize
     * @return \Redis
     */
    public static function pipeline($autoSerialize = TRUE)
    {
        return self::instance($autoSerialize)->multi(\Redis::PIPELINE);
    }

    /**
     * 关闭管道同步数据
     * @param bool $autoSerialize
     * @return mixed
     */
    public static function sync($autoSerialize = TRUE)
    {
        return self::instance($autoSerialize)->exec();
    }

}
