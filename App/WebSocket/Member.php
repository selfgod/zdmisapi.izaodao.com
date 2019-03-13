<?php

namespace App\WebSocket;

use App\Domain\Member\MemberDomain;
use Base\Thrift;
use Base\WsController;

class Member extends WsController
{
    public function checkOnline()
    {
        $isOnline = FALSE;
        $openId = $this->getParams('openId');
        $zdSessionId = $this->getParams('zdSessionId');
        if ($zdSessionId && $openId) {
            $userInfo = Thrift::getInstance()->service('User')->getUserBySessionId($zdSessionId);
            if (!empty($userInfo) && $openId === $userInfo['open_id'])
                $isOnline = TRUE;
        }
        if (!$isOnline)
            $this->raw('logout');
        else
            $this->raw('success');
    }

    /**
     * 绑定用户
     */
    public function bindUid()
    {
        $openId = $this->getParams('openId');
        $uid = Thrift::getInstance()->service('User')->getUidByOpenId($openId);
        $fd = $this->getFd();
        if ((new MemberDomain())->bindUid($uid, $fd)) {
            $this->raw("uid:{$uid} bind success");
        } else {
            $this->raw("uid:{$uid} bind fail");
        }
    }

    /**
     * 用户待处理任务
     */
    public function userWaitTask()
    {
        $openId = $this->getParams('openId');
        $uid = Thrift::getInstance()->service('User')->getUidByOpenId($openId);
        $res = (new MemberDomain())->getUserWaitTask($uid);
        $this->json($res);
    }

    /**
     * 用户通知任务
     */
    public function userTaskNotice()
    {
        $openId = $this->getParams('openId');
        $uid = Thrift::getInstance()->service('User')->getUidByOpenId($openId);
        $res = (new MemberDomain())->getUserTaskNotice($uid);
        empty($res) ? $this->raw('Not Task Notice') : $this->json($res);
    }

    /**
     * 清除用户任务提醒
     */
    public function clearUserTaskNotice()
    {
        $params = $this->getParams();
        $params['uid'] = Thrift::getInstance()->service('User')->getUidByOpenId($params['openId']);
        (new MemberDomain())->clearUserTaskNotice($params);
        $this->raw('Clear Success');
    }
}