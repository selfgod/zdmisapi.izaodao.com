<?php
namespace App\Model\Sales\Team;

use App\Traits\SalesManageAuditLog;
use Base\BaseModel;
use Base\Db;

class LevelListModel extends BaseModel
{
    use SalesManageAuditLog;
    public $listTb = 'sales_kpi_level_list';

    public function getKeyMap()
    {
        return [
            'salary_base' => '基本工资',
            'salary_kpi_base' => '绩效工资基数',
            'perform_target' => '指标产能',
            'perform_weight' => '指标产能权重',
            'perform_weight_team'=>'团队业绩权重',
            'trans_rate_target' => '转化率指标',
            'trans_rate_weight' => '转化率权重',
            'call_sec_target' => '通时指标',
            'call_sec_weight' => '通时权重',
            'resign_target' => '流失率指标',
            'resign_weight' => '流失率权重'
        ];
    }

    /**
     * 获取一条数据
     * @param $id
     * @return array
     */
    public function getOne($id)
    {
        $info = Db::slave('zd_sales')->select('id,data_date,salary_base,salary_kpi_base,perform_target,perform_weight,
        trans_rate_target,trans_rate_weight,perform_weight_team,call_sec_target,call_sec_weight,resign_target,resign_weight')
            ->from($this->listTb)
            ->where('id = :id and is_del=0')
            ->bindValue('id', $id)
            ->row();
        return $info;
    }

    /**
     * 获取一条数据
     * @param $businessType
     * @param $region
     * @param $date
     * @param $levelId
     * @return array
     */
    public function getOneByLevel($businessType, $region, $date, $levelId)
    {
        $info = Db::slave('zd_sales')->select('id,salary_base,salary_kpi_base,perform_target,perform_weight,
        trans_rate_target,trans_rate_weight,call_sec_target,perform_weight_team,call_sec_weight,resign_target,resign_weight')
            ->from($this->listTb)
            ->where('business_type=:business_type and region=:region and level_id=:level_id and data_date=:date and is_del=0')
            ->bindValues([
                'business_type' => $businessType,
                'region' => $region,
                'level_id' => $levelId,
                'date' => $date
            ])
            ->row();
        return $info;
    }

    /**
     * 获取列表信息
     * @param $businessType
     * @param $region
     * @param $date
     * @return mixed
     * @throws \Exception
     */
    public function getList($businessType, $region, $date)
    {
        $list = Db::slave('zd_sales')->select('skll.id,skll.data_date,skll.salary_base,skll.salary_kpi_base,skll.perform_target,
        skll.perform_weight,skll.perform_weight_team,skll.trans_rate_target,skll.trans_rate_weight,skll.call_sec_target,skll.call_sec_weight,
        skll.resign_target,skll.resign_weight,skl.id as level_id,skl.sales_type,skl.sales_level,skl.level_order,skl.code,skl.color')
            ->from('sales_kpi_level as skl')
            ->leftJoin($this->listTb . ' as skll', "skl.id=skll.level_id and skll.business_type = '{$businessType}' and 
            skll.region = '{$region}' and skll.data_date = '{$date}' and skll.is_del=0")
            ->orderByASC(['skl.level_order'])
            ->query();
        return $list;


//        $list = Db::slave('zd_sales')->select('skll.id,skll.data_date,skll.salary_base,skll.salary_kpi_base,skll.perform_target,
//        skll.perform_weight,skll.trans_rate_target,skll.trans_rate_weight,skll.call_sec_target,skll.call_sec_weight,
//        skll.resign_target,skll.resign_weight,skl.sales_type,skl.sales_level,skl.level_order,skl.code,skl.color')
//            ->from($this->listTb . ' as skll')
//            ->leftJoin('sales_kpi_level as skl', 'skl.id=skll.level_id')
//            ->where('skll.business_type = :businessType and skll.region = :region and skll.data_date = :data_date and skll.is_del=0')
//            ->bindValues([
//                'businessType' => $businessType,
//                'region' => $region,
//                'data_date' => $date
//            ])
//            ->orderByASC(['skl.level_order'])
//            ->query();
//        return $list;
    }

    /**
     * 增加级别数据
     * @param $uid
     * @param $data
     * @return int|mixed
     */
    public function addLevel($uid, $data)
    {
        $data['modify_user'] = $uid;
        $data['modify_time'] = date('Y-m-d H:i:s');
        $id = Db::master('zd_sales')->insert($this->listTb)->cols($data)->query();
        return $id ? $id : 0;
    }

    /**
     * 编辑
     * @param $id
     * @param $uid
     * @param $cols
     * @return mixed
     */
    public function update($id, $uid, $cols)
    {
        $keyMap = $this->getKeyMap();
        $data = [
            'modify_user' => $uid,
            'modify_time' => date('Y-m-d H:i:s')
        ];
        foreach ($cols as $key => $val) {
            if($key=='perform_weight_team') $val = floatval($val);
            if($key=='perform_target') $val = floatval($val);
            if (isset($keyMap[$key])) {
                $data[$key] = $val;
            }
        }
        $ret = Db::master('zd_sales')->update($this->listTb)
            ->cols($data)
            ->where('id = :id')
            ->bindValue('id', $id)->query();
        return $ret;
    }
}