<?php
/**
 * 入库规则
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2019/1/17
 * Time: 11:00 AM
 */

namespace App\Model\Market\Setting;

use Base\BaseModel;
use Base\Db;
use Base\Helper\ArrayHelper;

class IntoRuleModel extends BaseModel
{
    protected $rule_table = 'zd_channel_into_set';

    function getIntoSetLockBusinessType($all_col = false)
    {
        $res = Db::slave('zd_class')->select('*')
            ->from($this->rule_table)
            ->where('is_lock=1')
            ->query();
        if($all_col) return $res;
        return ArrayHelper::array_value_recursive('business_type', $res);
    }

    function getOpenRule()
    {
        $this->sWhereClean()->setSqlWhereAnd(['is_open'=>1]);
        return $this->selectData($this->rule_table)->query();
    }

    function getItem($business_type)
    {
        $this->sWhereClean()->setSqlWhereAnd(['business_type'=>$business_type]);
        return $this->selectData($this->rule_table, '*')->row();
    }
}