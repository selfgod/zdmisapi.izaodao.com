<?php
namespace App\Model;

use Base\BaseModel;
use Base\Db;

class SysCategory extends BaseModel
{
    /**
     * 获取指定key的配置列表
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function getValues($key)
    {
        $values = Db::slave('zd_netschool')->select('ssci.order,ssci.name')->from('sty_sys_category as ssc')
            ->leftJoin('sty_sys_category_item as ssci', 'ssci.category_id = ssc.id')
            ->where('ssc.key = :key and ssc.is_del=0 and ssci.is_del=0')
            ->bindValue('key', $key)
            ->orderByASC(['ssci.order'])
            ->query();
        return $values;
    }
}