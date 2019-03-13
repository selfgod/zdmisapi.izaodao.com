<?php

namespace App\Model\Teach\Label;

use Base\BaseModel;
use Base\Db;

class LabelModel extends BaseModel
{

    /**
     * 获取部分标签下全部uid
     * @param $label_ids
     * @return array
     */
    public function getUidFromLabels($label_ids)
    {
        $search_ids = ' ( -1 ';
        foreach ($label_ids as $n){
            if($n > 0){
                $search_ids .= ',' . $n;
            }
        }
        $label_p = Db::slave('zd_netschool')->from('sty_label')->select('id')
            ->where('p_id IN ' . $search_ids . ' ) ')
            ->query();
        if($label_p){
            foreach($label_p as $n){
                $search_ids .= ',' . $n['id'];
            }
        }
        $search_ids .= ' ) ';
        $list = Db::slave('zd_netschool')->from('sty_user_label')
            ->select('uid')
            ->where('label_id IN ' . $search_ids)
            ->groupBy(['uid'])->query();
        return !empty($list)?$list:[];
    }

    /**
     * 获取用户标签
     */
    public function getUserLabelInfo($uid)
    {
        $list = Db::slave('zd_netschool')->from('sty_user_label as sul')
            ->select('sul.*,sl.name')
            ->leftJoin('sty_label as sl', 'ON sl.id = sul.label_id')
            ->where('sul.uid = ' . $uid . ' ')->query();
        return !empty($list)?$list:[];
    }

    /**
     * 获取全部子标签
     */
    public function getLabelList($id)
    {
        $list = Db::slave('zd_netschool')->from('sty_label')
            ->select('*')->where('p_id = ' . $id . ' and is_del = 0 ')->query();
        return !empty($list)?$list:[];
    }
}