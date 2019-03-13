<?php

namespace Base;

use App\Domain\Member\MemberDomain;
use EasySwoole\Config;

class ServerRegister
{
    public static function workerStart()
    {
        $dbConf = Config::getInstance()->getConf('DATABASE');
        Db::init($dbConf);
    }

    public static function managerStart()
    {
        (new MemberDomain())->delAllBind();
    }

    public static function close($fd)
    {
        (new MemberDomain())->unBindUid($fd);
    }
}