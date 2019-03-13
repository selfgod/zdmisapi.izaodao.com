<?php
namespace App\Model\Market\Launchadvent;

use Base\BaseModel;
use Base\Db;

class SemModel extends BaseModel
{
    /**
     * 增加sem
     * @param $businessType
     * @param $channel
     * @param $tag
     * @param $pushdate
     * @param $cost
     * @param $uid
     * @return mixed
     */
    public function addSem($businessType, $channel, $tag, $pushdate, $cost, $uid)
    {
        $tagArr = explode('-', $tag);
        $id = Db::master('zd_class')->insert('zd_ad_sem')->cols([
            'business_type' => $businessType,
            'channel' => $channel,
            'tag' => $tag,
            'tag0' => $tagArr[0],
            'tag1' => $tagArr[1],
            'pushdate' => $pushdate,
            'cost' => $cost,
            'create_user' => $uid
        ])->query();
        return $id;
    }

    /**
     * 获取sem导出数据
     * @param $page
     * @param $limit
     * @param $businessType
     * @param $channel
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $costMin
     * @param $costMax
     * @return mixed
     * @throws \Exception
     */
    public function getSemExportList($page, $limit, $businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax)
    {
        $offset = $page * $limit;
        list($where, $binds) = $this->genWhereBinds($businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax);
        $list = Db::slave('zd_class')->select('tag,pushdate,cost')
            ->from('zd_ad_sem')
            ->where($where)
            ->bindValues($binds)
            ->offset($offset)->limit($limit)->orderByDESC(['id'])->query();
        return $list;
    }

    /**
     * 获取总数量
     * @param $businessType
     * @param $channel
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $costMin
     * @param $costMax
     * @return int
     */
    public function getSemListNum($businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax)
    {
        list($where, $binds) = $this->genWhereBinds($businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax);
        $total = Db::slave('zd_class')->select('count(*)')->from('zd_ad_sem')
            ->where($where)
            ->bindValues($binds)->single();
        return intval($total);
    }

    protected function genWhereBinds($businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax)
    {
        $where = '';
        $binds = [];
        if (!empty($businessType)) {
            $where .= 'business_type = :business_type and ';
            $binds['business_type'] = $businessType;
        }
        if (!empty($channel)) {
            $where .= 'channel = :channel and ';
            $binds['channel'] = $channel;
        }
        if (!empty($pushDateStart)) {
            $where .= 'pushdate >= :datestart and ';
            $binds['datestart'] = $pushDateStart;
        }
        if (!empty($pushDateEnd)) {
            $where .= 'pushdate <= :dateend and ';
            $binds['dateend'] = $pushDateEnd;
        }
        if (!empty($costMin)) {
            $where .= 'cost >= :costmin and ';
            $binds['costmin'] = $costMin;
        }
        if (!empty($costMax)) {
            $where .= 'cost <= :costmax and ';
            $binds['costmax'] = $costMax;
        }
        $where .= 'is_del=0';
        return [$where, $binds];
    }

    /**
     * 获取sem列表数据
     * @param $page
     * @param $limit
     * @param $businessType
     * @param $channel
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $costMin
     * @param $costMax
     * @return mixed
     * @throws \Exception
     */
    public function getSemList($page, $limit, $businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax)
    {
        $offset = $page * $limit;
        list($where, $binds) = $this->genWhereBinds($businessType, $channel, $pushDateStart, $pushDateEnd, $costMin, $costMax);
        $list = Db::slave('zd_class')->select('zas.id,zas.business_type,zasc.name,zas.tag,zas.pushdate,zas.cost,jcm.username,zas.create_time')
            ->from('zd_ad_sem as zas')
            ->leftJoin('zd_class.jh_common_member as jcm', 'jcm.uid=zas.create_user')
            ->leftJoin('zd_class.zd_ad_sem_channel as zasc', 'zasc.id=zas.channel')
            ->where($where)
            ->bindValues($binds)
            ->offset($offset)->limit($limit)->orderByDESC(['zas.id'])->query();
        return $list;
    }

    /**
     * 获取sem详情
     * @param $id
     * @return array
     */
    public function getSemInfo($id)
    {
        $info = Db::slave('zd_class')->select('business_type,channel,tag,pushdate,cost')
            ->from('zd_ad_sem')
            ->where('id = :id and is_del=0')
            ->bindValue('id', $id)
            ->row();
        return $info;
    }

    /**
     * 更新sem信息
     * @param $id
     * @param $businessType
     * @param $channel
     * @param $tag
     * @param $pushdate
     * @param $cost
     * @param $uid
     * @return bool
     */
    public function updateSem($id, $businessType, $channel, $tag, $pushdate, $cost, $uid)
    {
        $tagArr = explode('-', $tag);
        $ret = Db::master('zd_class')->update('zd_ad_sem')
            ->cols([
                'business_type' => $businessType,
                'channel' => $channel,
                'tag' => $tag,
                'tag0' => $tagArr[0],
                'tag1' => $tagArr[1],
                'pushdate' => $pushdate,
                'cost' => $cost,
                'modify_user' => $uid,
                'modify_time' => date('Y-m-d H:i:s')
            ])
            ->where('id = :id and is_del=0')
            ->bindValue('id', $id)->query();
        return $ret > 0;
    }

    /**
     * 删除sem
     * @param $id
     * @return bool
     */
    public function deleteSem($id)
    {
        $ret = Db::master('zd_class')->update('zd_ad_sem')->cols(['is_del' => 1])->where('id = :id')
            ->bindValue('id', $id)->query();
        return $ret > 0;
    }
}