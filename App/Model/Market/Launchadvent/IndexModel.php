<?php
namespace App\Model\Market\Launchadvent;

use Base\BaseModel;
use Base\Db;

class IndexModel extends BaseModel
{
    /**
     * 增加广告
     * @param $businessType
     * @param $ads
     * @param $point
     * @param $content
     * @param $tag
     * @param $pushdate
     * @param $userCount
     * @param $cost
     * @param $expect_resources
     * @param $actual_resources
     * @param $uid
     * @return mixed
     */
    public function addAd($businessType, $ads, $point, $content, $tag, $pushdate, $userCount, $cost,
                          $expect_resources, $actual_resources, $uid)
    {
        $id = Db::master('zd_class')->insert('zd_ad_launchadvent')->cols([
            'business_type' => $businessType,
            'ads' => $ads,
            'point' => $point,
            'content' => $content,
            'tag' => $tag,
            'pushdate' => $pushdate,
            'user_count' => $userCount,
            'cost' => $cost,
            'expect_resources' => $expect_resources,
            'actual_resources' => $actual_resources,
            'create_user' => $uid
        ])->query();
        return $id;
    }

    /**
     * 获取总数量
     * @param $businessType
     * @param $platform
     * @param $ads
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $tag
     * @param $content
     * @param $contact
     * @return int
     * @throws \Exception
     */
    public function getAdListNum($businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag, $content, $contact)
    {
        list($where, $binds) = $this->genWhereBinds($businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag, $content, $contact);
        $total = Db::slave('zd_class')->select('count(*)')->from('zd_ad_launchadvent as zal')
            ->leftJoin('zd_class.zd_ad_ads as zaa', 'zaa.id=zal.ads')
            ->where($where)
            ->bindValues($binds)->single();
        return intval($total);
    }

    protected function genWhereBinds($businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag, $content, $contact)
    {
        $where = '';
        $binds = [];
        if (!empty($businessType)) {
            $where .= 'zal.business_type = :business_type and ';
            $binds['business_type'] = $businessType;
        }
        if (!empty($platform)) {
            $where .= 'zaa.platform = :platform and ';
            $binds['platform'] = $platform;
        }
        if (!empty($ads)) {
            $where .= 'zal.ads = :ads and ';
            $binds['ads'] = $ads;
        }
        if (!empty($pushDateStart)) {
            $where .= 'zal.pushdate >= :datestart and ';
            $binds['datestart'] = $pushDateStart;
        }
        if (!empty($pushDateEnd)) {
            $where .= 'zal.pushdate <= :dateend and ';
            $binds['dateend'] = $pushDateEnd;
        }
        if (!empty($tag)) {
            $where .= 'zal.tag like :tag and ';
            $binds['tag'] = '%' . $tag . '%';
        }
        if (!empty($content)) {
            $where .= 'zal.content like :content and ';
            $binds['content'] = '%' . $content . '%';
        }
        if (!empty($contact)) {
            $where .= 'zaa.lianxiren like :contact and ';
            $binds['contact'] = '%' . $contact . '%';
        }
        $where .= 'zal.is_del=0';
        return [$where, $binds];
    }

    /**
     * 获取广告列表数据
     * @param $page
     * @param $limit
     * @param $businessType
     * @param $platform
     * @param $ads
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $tag
     * @param $content
     * @param $contact
     * @param $orderby
     * @return mixed
     * @throws \Exception
     */
    public function getAdList($page, $limit, $businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag,
                              $content, $contact, $orderby)
    {
        if (!empty($orderby)) {
            list($key, $order) = explode(':', $orderby);
        } else {
            $key = 'pushdate';
            $order = 'desc';
        }
        $offset = $page * $limit;
        list($where, $binds) = $this->genWhereBinds($businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag, $content, $contact);
        $db = Db::slave('zd_class')->select('zal.id,zal.business_type,zaa.name,zal.ads,zal.point,zal.content,
            zal.pushdate,zal.user_count,zal.tag,zal.cost,zal.income,zal.expect_resources,zal.actual_resources_auto,
            zal.actual_resources,zaa.platform,zal.buy_count,zal.unit_price,zal.roi,zal.conversion_rate')
            ->from('zd_ad_launchadvent as zal')
            ->leftJoin('zd_class.zd_ad_ads as zaa', 'zaa.id=zal.ads')
            ->where($where)
            ->bindValues($binds)
            ->offset($offset)->limit($limit);
        if (strtolower($order) === 'asc') {
            $list = $db->orderByASC(['zal.' . $key])->query();
        } else {
            $list = $db->orderByDESC(['zal.' . $key])->query();
        }
        return $list;
    }

    /**
     * 获取广告详情
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function getAdInfo($id)
    {
        $info = Db::slave('zd_class')->select('zal.business_type,zaa.platform,zal.ads,zal.point,zal.content,zal.tag,
        zal.pushdate,zal.user_count,zal.cost,zal.expect_resources,zal.actual_resources,zal.actual_resources_auto')
            ->from('zd_ad_launchadvent as zal')
            ->leftJoin('zd_class.zd_ad_ads as zaa', 'zaa.id=zal.ads')
            ->where('zal.id = :id and zal.is_del = 0')
            ->bindValue('id', $id)
            ->row();
        return $info;
    }

    /**
     * 更新广告信息
     * @param $id
     * @param $businessType
     * @param $ads
     * @param $point
     * @param $content
     * @param $tag
     * @param $pushdate
     * @param $userCount
     * @param $cost
     * @param $expect_resources
     * @param $actual_resources
     * @param $uid
     * @return bool
     */
    public function updateAd($id, $businessType, $ads, $point, $content, $tag, $pushdate, $userCount, $cost,
                             $expect_resources, $actual_resources, $uid)
    {
        $ret = Db::master('zd_class')->update('zd_ad_launchadvent')
            ->cols([
                'business_type' => $businessType,
                'ads' => $ads,
                'point' => $point,
                'content' => $content,
                'tag' => $tag,
                'pushdate' => $pushdate,
                'user_count' => $userCount,
                'cost' => $cost,
                'expect_resources' => $expect_resources,
                'actual_resources' => $actual_resources,
                'modify_user' => $uid,
                'modify_time' => date('Y-m-d H:i:s')
            ])
            ->where('id = :id and is_del = 0')
            ->bindValue('id', $id)->query();
        return $ret;
    }

    /**
     * 删除广告
     * @param $id
     * @return bool
     */
    public function deleteAd($id)
    {
        $ret = Db::master('zd_class')->update('zd_ad_launchadvent')->cols(['is_del' => 1])->where('id = :id')
            ->bindValue('id', $id)->query();
        return $ret > 0;
    }

    /**
     * 通过广告商和tag获取广告投放信息
     * @param $ads
     * @param $tag
     * @return mixed
     */
    public function getAdByAdsTag($ads, $tag)
    {
        $list = Db::slave('zd_class')->select('id')->from('zd_ad_launchadvent')->where('tag = :tag and ads=:ads and is_del=0')
            ->bindValues([
                'tag' => $tag,
                'ads' => $ads
            ])->query();
        return $list;
    }

    /**
     * 获取导出列表
     * @param $page
     * @param $limit
     * @param $businessType
     * @param $platform
     * @param $ads
     * @param $pushDateStart
     * @param $pushDateEnd
     * @param $tag
     * @param $content
     * @param $contact
     * @return mixed
     * @throws \Exception
     */
    public function getAdExportList($page, $limit, $businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag,
                              $content, $contact)
    {
        $offset = $page * $limit;
        list($where, $binds) = $this->genWhereBinds($businessType, $platform, $ads, $pushDateStart, $pushDateEnd, $tag, $content, $contact);
        $list = Db::slave('zd_class')->select('zal.tag,zal.pushdate,zal.cost')
            ->from('zd_ad_launchadvent as zal')
            ->leftJoin('zd_class.zd_ad_ads as zaa', 'zaa.id=zal.ads')
            ->where($where)
            ->bindValues($binds)
            ->offset($offset)->limit($limit)->orderByDESC(['zal.id'])->query();
        return $list;
    }

    /**
     * 广告投放更新数据
     * @param $sql
     */
    public function update_data($sql)
    {
        Db::master('zd_class')->query($sql);
    }
}