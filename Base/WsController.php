<?php

namespace Base;

use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Socket\AbstractInterface\WebSocketController;

class WsController extends WebSocketController
{
    protected static $map = [];

    protected function actionNotFound(?string $actionName)
    {
        $this->raw("action call {$actionName} not found");
    }

    protected function getParams($key = '')
    {
        $res = NULL;
        $res = $this->request()->getArg('data');
        if (!empty($key))
            $res = $res[$key] ?? NULL;
        return $res;
    }

    protected function getFd()
    {
        return $this->client()->getFd();
    }

    protected function raw($str)
    {
        $this->response()->write($str);
    }

    protected function json(array $data = [])
    {
        $this->response()->write(\GuzzleHttp\json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
