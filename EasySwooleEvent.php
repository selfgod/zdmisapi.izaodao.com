<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午1:04
 */

namespace EasySwoole;

use Base\ServerRegister;
use \EasySwoole\Core\AbstractInterface\EventInterface;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\EventHelper;
use \EasySwoole\Core\Swoole\ServerManager;
use \EasySwoole\Core\Swoole\EventRegister;
use \EasySwoole\Core\Http\Request;
use \EasySwoole\Core\Http\Response;

Class EasySwooleEvent implements EventInterface
{

    public static function frameInitialize(): void
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
        Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER, \Base\ExceptionHandler::class);
        Di::getInstance()->set(SysConst::LOGGER_WRITER, \Base\LoggerWriter::class);
    }

    /**
     * @param ServerManager $server
     * @param EventRegister $register
     */
    public static function mainServerCreate(ServerManager $server, EventRegister $register): void
    {
        $register->add($register::onWorkerStart, function (\swoole_server $server, $workerId) {
            echo "worker {$workerId} start\n";
            ServerRegister::workerStart();
        });
        $register->add($register::onManagerStart, function (\swoole_server $server) {
            ServerRegister::managerStart();
        });
        EventHelper::registerDefaultOnMessage($register, \Base\WsParser::class);
        $register->add($register::onClose, function (\swoole_server $server, $fd) {
            ServerRegister::close($fd);
        });
    }

    public static function onRequest(Request $request, Response $response): void
    {
        // TODO: Implement onRequest() method.
    }

    public static function afterAction(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}