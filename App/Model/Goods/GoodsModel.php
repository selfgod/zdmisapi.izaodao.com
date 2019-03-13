<?php

namespace App\Model\Goods;

use Base\BaseModel;
use Base\Db;

class GoodsModel extends BaseModel
{
    public function getUserGoods($w, array $bindValues): array
    {
        $data = Db::slave('zd_netschool')->select('sug.id,sug.goods_id,sug.is_activate,sug.activate_time,sug.expire,sug.unlimit_expire,sg.name,sg.business_type')->from('sty_user_goods AS sug')
            ->leftJoin('sty_goods AS sg', 'sug.goods_id = sg.id')
            ->where($w)->bindValues($bindValues)->query();
        return $data ?: [];
    }

    /**
     * 获取商品特权
     * @param array $goodsIds
     * @return array
     */
    public function getGoodsPrivilege(array $goodsIds): array
    {
        if (empty($goodsIds)) return [];
        $data = Db::slave('zd_netschool')->select('goods_id,privilege_id')->from('sty_goods_privilege')
            ->where($this->whereIn('goods_id', $goodsIds))->where('is_del = 0')->query();
        return $data ?: [];
    }


}