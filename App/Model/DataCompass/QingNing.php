<?php

namespace App\Model\DataCompass;

use Base\BaseModel;
use Base\Cache\ZdRedis;
use Base\Db;

class QingNing extends BaseModel
{
    /**
     * 获取uid openId 映射
     * @param array $openIds
     * @return array
     */
    public function getUidOpenIdMap(array $openIds): array
    {
        if (empty($openIds)) return [];
        $key_pre = 'map_openId_';
        $data = $msOpenIds = [];
        foreach ($openIds as $openId) {
            $uid = ZdRedis::instance(FALSE)->get($key_pre . $openId);
            if ($uid !== FALSE) {
                $data[$uid] = $openId;
            } else {
                $msOpenIds[] = $openId;
            }
        }
        if (!empty($msOpenIds)) {
            $query = Db::slave('zd_class')->select('uid,open_id')->from('jh_common_member')
                ->where($this->whereIn('open_id', $msOpenIds))->query();
            if (!empty($query)) {
                foreach ($query as $item) {
                    $data[$item['uid']] = $item['open_id'];
                    ZdRedis::instance(FALSE)->setEx($key_pre . $item['open_id'], ZdRedis::TTL, $item['uid']);
                }
            }
        }
        return $data;
    }

    /**
     * uid 首tag 映射
     * @param array $uids
     * @return array
     */
    public function uidFirstTagMap(array $uids): array
    {
        if (empty($uids)) return [];
        $key_pre = 'map_first_tag_uid_';
        $data = $msUids = [];
        foreach ($uids as $uid) {
            $firstTag = ZdRedis::instance(FALSE)->get($key_pre . $uid);
            if ($firstTag !== FALSE) {
                $data[$uid] = $firstTag;
            } else {
                $msUids[] = $uid;
            }
            unset($uid);
        }
        if (!empty($msUids)) {
            $query = Db::slave('zd_class')->select('uid,first_tag')->from('zd_zixun')->where($this->whereIn('uid', $msUids))->where('first_tag<>""')->query();
            if (!empty($query)) {
                foreach ($query as $item) {
                    if (!empty($item['first_tag'])) {
                        $data[$item['uid']] = $item['first_tag'];
                        ZdRedis::instance(FALSE)->setEx($key_pre . $item['uid'], ZdRedis::TTL, $item['first_tag']);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * uid yhq 时间 映射
     * @param array $uids
     * @return array
     */
    public function uidYhqTimeMap(array $uids): array
    {
        if (empty($uids)) return [];
        $key_pre = 'map_yhq_time_uid_';
        $data = $msUids = [];
        foreach ($uids as $uid) {
            $yhqTime = ZdRedis::instance(FALSE)->get($key_pre . $uid);
            if ($yhqTime !== FALSE) {
                $data[$uid] = $yhqTime;
            } else {
                $msUids[] = $uid;
            }
            unset($uid);
        }
        if (!empty($msUids)) {
            $uidTelMap = $this->uidTelMap($msUids);
            if (!empty($uidTelMap)) {
                $telUidMap = array_flip($uidTelMap);//k v 交换
                $tels = array_keys($telUidMap);
                $query = Db::slave('zd_class')->select('tel,dateline')->from('zd_crm')
                    ->where($this->whereIn('tel', $tels))->where("ctag3='yhq_ad'")->groupBy(['tel'])->query();
                if (!empty($query)) {
                    foreach ($query as $item) {
                        if (!empty($item['dateline'])) {
                            $date = date('Y-m-d H:i:s', $item['dateline']);
                            $uid = $telUidMap[$item['tel']];
                            $data[$uid] = $date;
                            ZdRedis::instance(FALSE)->setEx($key_pre . $uid, ZdRedis::TTL, $date);
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * uid 再跟cc map
     * @param array $uids
     * @return array
     * @throws \Exception
     */
    public function uidTrackCCMap(array $uids): array
    {
        if (empty($uids)) return [];
        $data = [];
        $query = Db::slave('zd_class')->select('zcf.salesman,zzu.uid')->from('zd_customer_follow as zcf')
            ->leftJoin('zd_customer as zc', 'on zcf.cid = zc.cid')
            ->leftJoin('zd_zixun_uid as zzu', 'on zc.zid = zzu.zid')
            ->where($this->whereIn('zzu.uid', $uids))->orderByDESC(['zcf.cc_updatedate'])->query();
        if (!empty($query)) {
            foreach ($query as $item) {
                if (isset($data[$item['uid']])) continue;
                $data[$item['uid']] = $item['salesman'];
            }
        }
        return $data;
    }

    /**
     * uid 商品信息 map
     * @param array $uids
     * @return array
     * @throws \Exception
     */
    public function uidGoodsMap(array $uids): array
    {
        if (empty($uids)) return [];
        $data = [];
        $query = Db::slave('zd_netschool')->select('sug.uid,sug.goods_name,sug.create_time')->from('sty_user_goods as sug')
            ->leftJoin('sty_goods as sg', 'on sug.goods_id = sg.id')
            ->where($this->whereIn('sug.uid', $uids))
            ->where('sg.is_active=1 and sug.is_del=0')
            ->orderByDESC(['sug.create_time'])->query();
        if (!empty($query)) {
            foreach ($query as $item) {
                if (isset($data[$item['uid']])) continue;
                $data[$item['uid']] = ['goods_name' => $item['goods_name'], 'create_time' => $item['create_time']];
            }
        }
        return $data;
    }

    /**
     * uid 手机号 map
     * @param array $uids
     * @return array
     */
    public function uidTelMap(array $uids): array
    {
        if (empty($uids)) return [];
        $key_pre = 'map_mobile_uid_';
        $data = $msUids = [];
        foreach ($uids as $uid) {
            $mobile = ZdRedis::instance(FALSE)->get($key_pre . $uid);
            if ($mobile !== FALSE) {
                $data[$uid] = $mobile;
            } else {
                $msUids[] = $uid;
            }
            unset($uid);
        }
        if (!empty($msUids)) {
            $query = Db::slave('zd_class')->select('uid,mobile')->from('jh_common_member')
                ->where($this->whereIn('uid', $msUids))->query();
            if (!empty($query)) {
                foreach ($query as $item) {
                    if (!empty($item['mobile'])) {
                        $data[$item['uid']] = $item['mobile'];
                        ZdRedis::instance(FALSE)->setEx($key_pre . $item['uid'], ZdRedis::TTL, $item['mobile']);
                    }
                }
            }
        }
        return $data;
    }
}