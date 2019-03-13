<?php
namespace Base;

use Workerman\MySQL\Connection;

class Db {
    protected static $_inited = FALSE;
    protected static $_instances = [];
    protected static $_masterConf = [];
    protected static $_slaveConf = [];


    public static function init($config = []): void
    {
        $masters = $config['master'];
        $slaves = $config['slave'];

        foreach ($masters as $master) {
            $dbs = $master['db'];
            foreach ($dbs as $db) {
                self::$_masterConf[$db] = ['host' => $master['host'], 'user' => $master['user'], 'pwd' => $master['pwd']];
            }
        }

        foreach ($slaves as $slave) {
            $dbs = $slave['db'];
            foreach ($dbs as $db) {
                self::$_slaveConf[$db][] = ['host' => $slave['host'], 'user' => $slave['user'], 'pwd' => $slave['pwd']];
            }
        }
    }

    /**
     * 获取写实例
     * @param $name string 数据库名
     * @return Connection
     */
    public static function master($name)
    {
        if (!isset(self::$_instances['master'][$name])) {
            self::$_instances['master'][$name] = new ZdMysqlConnection(self::$_masterConf[$name]['host'],
                '3306', self::$_masterConf[$name]['user'], self::$_masterConf[$name]['pwd'], $name);
        }
        return self::$_instances['master'][$name];
    }

    /**
     * 获取读实例
     * @param $name string 数据库名
     * @return Connection
     */
    public static function slave($name)
    {
        if (empty(self::$_instances['slave']) || !isset(self::$_instances['slave'][$name])) {
            if (!isset(self::$_slaveConf[$name])) {
                return self::master($name);
            } else {
                foreach (self::$_slaveConf[$name] as $slave) {
                    self::$_instances['slave'][$name][] = new ZdMysqlConnection($slave['host'], '3306', $slave['user'], $slave['pwd'], $name);
                }
            }
        }
        $num = count(self::$_instances['slave'][$name]);
        $index = $num === 1 ? 0 :mt_rand(0, $num - 1);
        return self::$_instances['slave'][$name][$index];
    }
}