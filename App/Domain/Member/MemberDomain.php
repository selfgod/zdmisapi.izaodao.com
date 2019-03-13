<?php

namespace App\Domain\Member;

use App\Model\Common\User;
use App\Model\Setting\WaitproModel;
use Base\BaseDomain;
use Base\Cache\ZdRedis;
use Base\Exception\BadRequestException;
use EasySwoole\Config;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Utility\Sort;

class MemberDomain extends BaseDomain
{
    protected static $redisKey = [];

    public function __construct()
    {
        $redisKey = Config::getInstance()->getConf('REDIS_KEY');
        self::$redisKey['bindUidKey'] = $redisKey['bindUid'];
        self::$redisKey['bindFdKey'] = $redisKey['bindFdByUid'];
        self::$redisKey['processTaskKey'] = $redisKey['waitProcessTaskByUid'];
        self::$redisKey['noticeTaskKey'] = $redisKey['noticeTaskByUid'];
    }

    /**
     * 客户端绑定uid
     * @param $uid
     * @param $fd
     * @return bool
     */
    public function bindUid($uid, $fd): bool
    {
        if (!$uid || !$fd) return FALSE;
        $uidKey = 'uid_' . $uid;
        $fdKey = 'fd_' . $fd;
        ZdRedis::instance()->hSet(self::$redisKey['bindUidKey'], $fdKey, $uid);
        $fds = ZdRedis::instance()->hGet(self::$redisKey['bindFdKey'], $uidKey);
        $hVal = ($fds ?: '-') . $fd . '-';
        ZdRedis::instance()->hSet(self::$redisKey['bindFdKey'], $uidKey, $hVal);
        return TRUE;
    }

    /**
     * 解绑
     * @param $fd
     * @return bool
     */
    public function unBindUid($fd)
    {
        if (!$fd) return FALSE;
        $fdKey = 'fd_' . $fd;
        $uid = ZdRedis::instance()->hGet(self::$redisKey['bindUidKey'], $fdKey);
        if ($uid !== FALSE) {
            $uidKey = 'uid_' . $uid;
            ZdRedis::instance()->hDel(self::$redisKey['bindUidKey'], $fdKey);
            $fds = ZdRedis::instance()->hGet(self::$redisKey['bindFdKey'], $uidKey);
            if ($fds !== FALSE) {
                if ($fds === "-{$fd}-") {
                    ZdRedis::instance()->hDel(self::$redisKey['bindFdKey'], $uidKey);
                } else {
                    $hVal = str_replace("-{$fd}-", '-', $fds);
                    ZdRedis::instance()->hSet(self::$redisKey['bindFdKey'], $uidKey, $hVal);
                }
            }
        }
        return TRUE;
    }

    /**
     * 删除全部绑定用户
     */
    public function delAllBind()
    {
        ZdRedis::instance()->delete(self::$redisKey['bindUidKey']);
        ZdRedis::instance()->delete(self::$redisKey['bindFdKey']);
    }

    /**
     * 获取全部绑定的uid
     * @return array
     */
    public function getBindUids()
    {
        $res = ZdRedis::instance()->hVals(self::$redisKey['bindUidKey']);
        return empty($res) ? [] : array_unique($res);
    }

    /**
     * 获取uid对应的fd
     * @param $uid
     * @return array
     */
    public function getFdByUid($uid)
    {
        $res = ZdRedis::instance()->hGet(self::$redisKey['bindFdKey'], "uid_{$uid}");
        return empty($res) ? [] : explode('-', trim($res, '-'));
    }

    /**
     * 向uid推送消息
     * @param $uid
     * @param array $data
     * @return bool
     */
    public function pushMegByUid($uid, array $data)
    {
        $fdArr = $this->getFdByUid($uid);
        if (!empty($fdArr)) {
            //向客户端推送消息
            $server = ServerManager::getInstance()->getServer();
            foreach ($fdArr as $fd) {
                $fd = intval($fd);
                if ($server->exist($fd)) {
                    $server->push($fd, \GuzzleHttp\json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 获取当前在线用户
     * @param $self
     * @return array|bool
     */
    public function getOnlineUsers($self)
    {
        $data = [];
        $uids = $this->getBindUids();
        if (!empty($uids)) {
            $users = (new User())->getUsersByUids($uids);
            if (!empty($users)) {
                foreach ($users as $user) {
                    if ($user['uid'] === $self['uid']) continue;
                    $info['uid'] = $user['uid'];
                    $info['username'] = $user['username'];
                    $data[] = $info;
                }
            }
            $data = empty($data) ? [] : Sort::multiArraySort($data, 'uid');
        }
        array_unshift($data, ['uid' => $self['uid'], 'username' => $self['user_name']]);
        return $data;
    }

    /**
     * 发送用户任务提醒
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function sendUserTaskRemind(array $params)
    {
        $uid = $params['uid'];
        $key = $params['task_key'];
        $num = $params['num'] > 0 ? intval($params['num']) : 1;
        $task = (new WaitproModel())->getWaitProjectByKey([$key]);
        if (empty($task))
            throw new BadRequestException('task_key 不存在');
        $task = $task[0];
        //任务数递增
        ZdRedis::instance()->hIncrBy(self::$redisKey['processTaskKey'] . $uid, $key, $num);
        $data = ['key' => $task['key'], 'link' => $params['link'], 'num' => $num];
        $msg = ['event' => 'taskNotice', 'data' => array_merge($data, [
            'title' => $params['msg'] ?: str_replace('#num#', $num, $task['name']),
            'icon' => $task['icon']
        ])];
        if (!$this->pushMegByUid($uid, $msg)) {//向用户推送消息
            $data['title'] = $params['msg'];
            //预存用户任务消息
            ZdRedis::instance()->lPush(self::$redisKey['noticeTaskKey'] . $uid, $data);
        }
        return TRUE;
    }

    /**
     * 获取用户待处理任务
     * @param $uid
     * @return array
     */
    public function getUserWaitTask($uid)
    {
        $data = [];
        $tasks = ZdRedis::instance()->hGetAll(self::$redisKey['processTaskKey'] . $uid);
        if (!empty($tasks)) {
            $taskList = (new WaitproModel())->getWaitProjectByKey(array_keys($tasks));
            if (!empty($taskList)) {
                foreach ($taskList as $i => $task) {
                    $task_key = $task['key'];
                    $num = intval($tasks[$task_key]);
                    $data[$i]['title'] = str_replace('#num#', $num, $task['name']);
                    $data[$i]['key'] = $task_key;
                    $data[$i]['num'] = $num;
                    $data[$i]['link'] = $task['link'];
                    $data[$i]['icon'] = $task['icon'];
                }
            }
        }
        return ['event' => 'waitTaskList', 'data' => $data];
    }

    /**
     * 获取通知任务
     * @param $uid
     * @return array
     */
    public function getUserTaskNotice($uid)
    {
        $data = [];
        $rKey = self::$redisKey['noticeTaskKey'] . $uid;
        $notices = ZdRedis::instance()->lRange($rKey, 0, -1);
        if (!empty($notices)) {
            $keys = array_column($notices, 'key');
            $taskAll = (new WaitproModel())->getWaitProjectByKey(array_values($keys));
            $tasks = array_reduce($taskAll, function ($taskList, $val) {
                $taskList[$val['key']] = $val;
                return $taskList;
            });
            foreach ($notices as $notice) {
                if (!isset($tasks[$notice['key']])) continue;
                $val = $notice;
                $val['num'] = intval($notice['num']);
                if (isset($data[$notice['key']])) {
                    $val['num'] += $data[$notice['key']]['num'];
                    $val['link'] = $tasks[$notice['key']]['link'];
                    $val['title'] = '';
                }
                $val['title'] = $val['title'] ?: str_replace('#num#', $val['num'], $tasks[$notice['key']]['name']);
                $val['icon'] = $tasks[$notice['key']]['icon'];
                $data[$notice['key']] = $val;
            }
            ZdRedis::instance()->delete($rKey);
        }
        return empty($data) ? [] : [
            'event' => 'taskRemainNotice',
            'data' => array_values($data)
        ];
    }

    /**
     * 清除用户任务提醒
     * @param array $params
     * @return bool
     */
    public function clearUserTaskNotice(array $params)
    {
        $uid = $params['uid'];
        $taskKey = $params['task_key'] ?? '';
        $type = $params['type'] ?? '';
        $num = isset($params['num']) ? (intval($params['num']) > 0 ? intval($params['num']) : 1) : 1;
        if (empty($taskKey) || empty($type)) return FALSE;
        $rKey = self::$redisKey['processTaskKey'] . $uid;
        $count = intval(ZdRedis::instance()->hGet($rKey, $taskKey));
        if ($type !== 'all' && $count > $num) {
            $res = ZdRedis::instance()->hIncrBy($rKey, $taskKey, "-{$num}");
        } else {
            $res = ZdRedis::instance()->hDel($rKey, $taskKey);
        }
        if ($res) {
            $msg = ['event' => 'clearTaskNotice', 'data' => []];
            $this->pushMegByUid($uid, $msg);//向用户推送消息
        }
        return !!$res;
    }

    /**
     * 推送事件消息
     * @param array $params
     * @return bool
     */
    public function userEventPush(array $params)
    {
        return $this->pushMegByUid($params['uid'], [
            'event' => $params['event'],
            'data' => $params['data'] ?? []
        ]);
    }
}