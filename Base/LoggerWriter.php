<?php
namespace Base;

use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\LoggerWriterInterface;

class LoggerWriter implements LoggerWriterInterface
{
    private $defaultDir;

    public function __construct()
    {
        $this->defaultDir = Config::getInstance()->getConf('LOG_DIR');
    }

    function writeLog($obj, $logCategory, $timeStamp)
    {
        $str = self::formatLogMessage($obj, $logCategory, $timeStamp);
        $filePath = $this->defaultDir . '/' . date('Y-m-d') . '.log';
        file_put_contents($filePath, $str, FILE_APPEND|LOCK_EX);
    }

    /**
     * 格式化一条日志信息。
     * @param string $message 消息内容
     * @param integer $level 消息等级
     * @param integer $time 时间戳
     * @return string 格式化后的消息
     */
    protected static function formatLogMessage($message, $level, $time)
    {
        return @date('Y/m/d H:i:s', $time) . " [$level] $message\n";
    }
}