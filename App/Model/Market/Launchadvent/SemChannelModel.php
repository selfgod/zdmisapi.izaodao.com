<?php
namespace App\Model\Market\Launchadvent;

use Base\BaseModel;
use Base\Db;

class SemChannelModel extends BaseModel
{
    /**
     * 增加sem渠道
     * @param $name
     * @param $uid
     * @return mixed
     */
    public function addSemChannel($name, $uid)
    {
        $id = Db::master('zd_class')->insert('zd_ad_sem_channel')->cols([
            'name' => $name,
            'create_user' => $uid
        ])->query();
        return $id;
    }

    /**
     * 获取总数量
     * @return int
     */
    public function getSemChannelNum()
    {
        $total = Db::slave('zd_class')->select('count(*)')->from('zd_ad_sem_channel')->single();
        return intval($total);
    }

    /**
     * 获取sem渠道列表数据
     * @param $page
     * @param $limit
     * @return mixed
     * @throws \Exception
     */
    public function getSemChannelList($page, $limit)
    {
        $offset = $page * $limit;
        $list = Db::slave('zd_class')->select('zasc.id,zasc.name,jcm.username,zasc.create_time')
            ->from('zd_ad_sem_channel as zasc')
            ->leftJoin('zd_class.jh_common_member as jcm', 'jcm.uid=zasc.create_user')
            ->offset($offset)->limit($limit)->orderByASC(['zasc.id'])->query();
        return $list;
    }

    /**
     * 更新sem渠道信息
     * @param $id
     * @param $name
     * @param $uid
     * @return bool
     */
    public function updateSemChannel($id, $name, $uid)
    {
        $ret = Db::master('zd_class')->update('zd_ad_sem_channel')
            ->cols([
                'name' => $name,
                'modify_user' => $uid,
                'modify_time' => date('Y-m-d H:i:s')
            ])
            ->where('id = :id')
            ->bindValue('id', $id)->query();
        return $ret > 0;
    }

    /**
     * 获取sem渠道信息
     * @param $id
     * @return array
     */
    public function getSemChannelInfo($id)
    {
        $info = Db::slave('zd_class')->select('name')
            ->from('zd_ad_sem_channel')
            ->where('id = :id')
            ->bindValue('id', $id)
            ->row();
        return $info;
    }
}