<?php

namespace App\Domain\Goods;

use App\Model\Common\User;
use App\Model\Course\LessonModel;
use App\Model\Goods\GoodsModel;
use Base\BaseDomain;
use Base\Db;
use Base\Exception\BadRequestException;
use Base\Thrift;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Utility\Sort;

class GoodsDomain extends BaseDomain
{
    /**
     * 刪除商品
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function delUserGoods(array $params): bool
    {
        $userId = $params['userOpenId'] ? Thrift::getInstance()->service('User')->getUidByOpenId($params['userOpenId']) : 0;
        if (!$userId) throw new BadRequestException('此用户不存在', 1);
        $goodsId = $params['goodsId'] ?: 0;
        $model = new GoodsModel();
        $userGoods = $model->getUserGoods('sug.uid = :uid AND sug.goods_id = :goodsId AND sug.is_del = 0 AND sg.is_del = 0', ['uid' => $userId, 'goodsId' => $goodsId]);
        if (empty($userGoods)) throw new BadRequestException('用户商品不存在', 2);
        $nowDate = date('Y-m-d H:i:s');
        $modify_user = $params['uid'] ?: 0;
        $taskGoods = [];
        //用户剩余商品
        $remainGoods = $model->getUserGoods('sug.uid = :uid AND sug.goods_id <> :goodsId AND sg.business_type NOT IN(2,5) AND sug.is_del = 0 AND sg.is_del = 0', ['uid' => $userId, 'goodsId' => $goodsId]);
        if (!empty($remainGoods)) {
            //当前是否存在未处理的学管师任务
            $exist = (new User())->userIsExistUnAssignTeachTask($userId);
            if (!$exist) {
                foreach ($remainGoods as $item) {
                    if ((int)$item['is_activate'] === 1 && (int)$item['business_type'] === 1 && ((int)$item['unlimit_expire'] === 1 || $item['expire'] > $nowDate)) {
                        $taskGoods[] = $item;
                    }
                }
            }
        }
        //商品特权
        $goodsPrivilege = $model->getGoodsPrivilege([$goodsId]);
        $goodsPrivilegeIds = empty($goodsPrivilege) ? [] : array_values(array_column($goodsPrivilege, 'privilege_id'));
        $diffPrivilegeIds = [];
        if (!empty($goodsPrivilegeIds)) {
            $otherGoods = $model->getUserGoods('sug.uid = :uid AND sug.goods_id <> :goodsId AND sug.is_del = 0 AND sg.is_del = 0', ['uid' => $userId, 'goodsId' => $goodsId]);
            $otherGoodsIds = empty($otherGoods) ? [] : array_column($otherGoods, 'goods_id');
            $otherPrivilege = $model->getGoodsPrivilege($otherGoodsIds);
            $otherPrivilegeIds = empty($otherPrivilege) ? [] : array_values(array_unique(array_column($otherPrivilege, 'privilege_id')));
            $diffPrivilegeIds = array_diff($goodsPrivilegeIds, $otherPrivilegeIds);
        }
        $status = FALSE;
        //开启事务
        Db::master('zd_netschool')->beginTrans();
        try {
            //删除用户商品
            $model->updateTable('sty_user_goods', 'uid = :uid AND goods_id = :goodsId AND is_del = 0', ['uid' => $userId, 'goodsId' => $goodsId], [
                'is_del' => 1,
                'modify_user' => $modify_user,
                'modify_time' => $nowDate
            ]);
            //删除商品Info
            $model->updateTable('sty_user_goods_info', 'uid = :uid AND goods_id = :goodsId AND is_del = 0', ['uid' => $userId, 'goodsId' => $goodsId], [
                'is_del' => 1,
                'modify_time' => $nowDate
            ]);
            //删除用户签订的商品协议
            $model->updateTable('sty_user_protocol', 'uid = :uid AND goods_id = :goodsId AND checkActive = 1', ['uid' => $userId, 'goodsId' => $goodsId], ['checkActive' => 0]);
            //用户特权更新
            if (!empty($diffPrivilegeIds)) {
                $model->updateTable('sty_user_privilege', 'uid = :uid AND ' . $model->whereIn('privilege_id', $diffPrivilegeIds) . ' AND is_del = 0', ['uid' => $userId], [
                    'is_del' => 1,
                    'modify_time' => $nowDate
                ]);
            }
            if (!empty($taskGoods)) {
                //添加学管师任务
                $taskGoods = Sort::multiArraySort($taskGoods, 'activate_time');
                $add = [
                    'uid' => $userId,
                    'goods_id' => (int)$taskGoods[0]['goods_id'],
                    'activate_time' => $taskGoods[0]['activate_time'],
                    'expire' => $taskGoods[0]['expire'],
                    'unlimit_expire' => $taskGoods[0]['unlimit_expire'],
                    'longest_schedule_id' => 0,
                    'create_time' => $nowDate
                ];
                $getUserLastEndSchedule = (new LessonModel())->getUserLastEndSchedule($userId);
                $add['longest_schedule_id'] = empty($getUserLastEndSchedule) ? 0 : (int)$getUserLastEndSchedule['schedule_id'];
                $model->insertTable('sty_teach_task_class', $add);
            }
            if (empty($remainGoods)) {
                //删除首课 删除学管师任务
                $model->deleteTable('jh_common_member_profile_wx', 'uid = :uid', ['uid' => $userId], 'zd_class');
                $model->deleteTable('sty_teach_task_class', 'uid = :uid', ['uid' => $userId]);
            }
            //加入删除日志
            $typeMap = [1 => '换商品', 2 => '退费', 3 => '内部员工', 4 => '离职', 5 => '测试'];
            $delType = $params['options']['del_type'] ?? 0;
            $remark = $params['options']['remark'] ?? '';
            $type = $typeMap[$delType] ?? '';
            $content = '&nbsp;<strong>' . $userGoods[0]['name'] . '(id:' . $goodsId . ')' . '</strong>';
            if (!empty($type)) $content .= '&nbsp;<strong>类型:</strong>' . $type;
            if (!empty($remark)) $content .= '&nbsp;<strong>备注:</strong>' . $remark;
            $model->insertTable('zd_info_opt_log', [
                'uid' => $userId,
                'opt_uid' => $modify_user,
                'dateline' => time(),
                'flag' => 2,//来源：后台
                'type' => 14,
                'vip_course_id' => $goodsId,
                'content' => $content
            ], 'zd_class');
            //提交事务
            Db::master('zd_netschool')->commitTrans();
            $status = TRUE;
        } catch (\Exception $e) {
            //事务回滚
            Db::master('zd_netschool')->rollBackTrans();
            Logger::getInstance()->log('GoodsDomain\delUserGoods ERROR:' . $e->getMessage() . ' params:' . \GuzzleHttp\json_encode($params));
        }
        if ($status) {
            Thrift::getInstance()->service('User')->delGoodsUpdate($userId);
            $learn_status = Thrift::getInstance()->service('User')->getUserNowLearnStatus($userId, 6);
            Thrift::getInstance()->service('User')->updateUserLearnInfo($userId, ['learn_status' => $learn_status]);
            return TRUE;
        }
        return FALSE;
    }
}