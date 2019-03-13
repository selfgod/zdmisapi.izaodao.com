<?php
namespace Base;

use Base\Exception\Exception;
use EasySwoole\Config;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Http\AbstractInterface\ExceptionHandlerInterface;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

class ExceptionHandler implements ExceptionHandlerInterface
{

    public function handle(\Throwable $throwable, Request $request, Response $response)
    {
        $msg = $throwable->getMessage();
        $file = $throwable->getFile();
        $line = $throwable->getLine();
        if ($throwable instanceof Exception) {
            $code = $throwable->getCode();
            $jmsg = $msg;
        } else {
            $code = 500;
            $isDebug = Config::getInstance()->getConf('DEBUG');
            $detail = "{$msg} file[{$file}] line[{$line}]";
            $jmsg = $isDebug ? $detail : '服务器出错了';
        }

        Logger::getInstance()->log("{$msg} file[{$file}] line[{$line}]", 'error');

        if(!$response->isEndResponse()){
            $return = ['msg' => $jmsg];
            $response->write(json_encode($return, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $response->withHeader('Content-type','application/json;charset=utf-8');
            $response->withStatus($code);
        }
    }
}