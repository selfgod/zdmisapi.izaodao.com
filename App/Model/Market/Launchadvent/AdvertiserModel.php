<?php
namespace App\Model\Market\Launchadvent;

use Base\BaseModel;
use Base\Db;

class AdvertiserModel extends BaseModel
{
    /**
     * 获取广告商列表数据
     * @param $page
     * @param $limit
     * @param $platform
     * @param $ader
     * @param $contact
     * @return mixed
     * @throws \Exception
     */
    public function getAdvertiserList($page, $limit, $platform ='', $ader = '', $contact = '')
    {
        $where = '';
        $binds = [];
        $offset = $page * $limit;
        if (!empty($platform)) {
            $where .= 'zaa.platform = :platform and ';
            $binds['platform'] = $platform;
        }
        if (!empty($ader)) {
            $where .= 'zaa.name like :name and ';
            $binds['name'] = '%' . $ader . '%';
        }
        if (!empty($contact)) {
            $where .= 'zaa.lianxiren like %:contact% and ';
            $binds['contact'] = '%' . $contact . '%';
        }
        $where .= '1=1';
        $list = Db::slave('zd_class')->select('zaa.id,zaa.platform,zaa.name,zaa.lianxiren,zaa.mobile,zaa.qq,zaa.url,jcm.username,zaa.create_time')
            ->from('zd_ad_ads as zaa')
            ->leftJoin('zd_class.jh_common_member as jcm', 'jcm.uid=zaa.create_user')
            ->where($where)
            ->bindValues($binds)
            ->offset($offset)->limit($limit)->orderByASC(['zaa.id'])->query();
        return $list;
    }

    /**
     * 获取总数量
     * @param $platform
     * @param $ader
     * @param $contact
     * @return int
     */
    public function getAdvertiserNum($platform, $ader, $contact)
    {
        $where = '';
        $binds = [];
        if (!empty($platform)) {
            $where .= 'platform = :platform and ';
            $binds['platform'] = $platform;
        }
        if (!empty($ader)) {
            $where .= 'name like :name and ';
            $binds['name'] = '%' . $ader . '%';
        }
        if (!empty($contact)) {
            $where .= 'lianxiren like %:contact% and ';
            $binds['contact'] = '%' . $contact . '%';
        }
        $where .= '1=1';
        $total = Db::slave('zd_class')->select('count(*)')->from('zd_ad_ads')
            ->where($where)
            ->bindValues($binds)->single();
        return intval($total);
    }

    /**
     * 增加广告商
     * @param $platform
     * @param $name
     * @param $contact
     * @param $mobile
     * @param $qq
     * @param $url
     * @param $uid
     * @return mixed
     */
    public function addAdvertiser($platform, $name, $contact, $mobile, $qq, $url, $uid)
    {
        $id = Db::master('zd_class')->insert('zd_ad_ads')->cols([
            'platform' => $platform,
            'name' => $name,
            'lianxiren' => $contact,
            'mobile' => $mobile,
            'qq' => $qq,
            'url' => $url,
            'create_user' => $uid
        ])->query();
        return $id;
    }

    /**
     * 获取广告商详情
     * @param $id
     * @return array
     */
    public function getAdvertiserInfo($id)
    {
        $info = Db::slave('zd_class')->select('platform,name,lianxiren,mobile,qq,url')
            ->from('zd_ad_ads')
            ->where('id = :id')
            ->bindValue('id', $id)
            ->row();
        return $info;
    }

    /**
     * 更新广告商信息
     * @param $id
     * @param $platform
     * @param $name
     * @param $contact
     * @param $mobile
     * @param $qq
     * @param $url
     * @param $uid
     * @return bool
     */
    public function updateAdvertiser($id, $platform, $name, $contact, $mobile, $qq, $url, $uid)
    {
        $ret = Db::master('zd_class')->update('zd_ad_ads')
            ->cols([
                'platform' => $platform,
                'name' => $name,
                'lianxiren' => $contact,
                'mobile' => $mobile,
                'qq' => $qq,
                'url' => $url,
                'modify_user' => $uid,
                'modify_time' => date('Y-m-d H:i:s')
            ])
            ->where('id = :id')
            ->bindValue('id', $id)->query();
        return $ret > 0;
    }
}