<?php
namespace App\Model\Sales\Team;


use Base\BaseModel;
use Base\Db;

class WorkingDaysModel extends BaseModel
{
    public $baseTb = 'sales_kpi_working_day';

    /**
     * 获取工作日
     * @param $businessType
     * @param $region
     * @param $date
     * @return array
     */
    public function get($businessType, $region, $date)
    {
        $info = Db::slave('zd_sales')->select('id,business_type,region,data_date,working_days')
            ->from($this->baseTb)
            ->where('business_type = :businessType and region = :region and data_date = :date and is_del=0')
            ->bindValues([
                'businessType' => $businessType,
                'region' => $region,
                'date' => $date,
            ])
            ->row();
        return $info;
    }

    /**
     * 获取工作日天数
     * @param $businessType
     * @param $region
     * @param $date
     * @return int
     */
    public function getNum($businessType, $region, $date)
    {
        $workday = Db::slave('zd_sales')->select('working_days')
            ->from($this->baseTb)
            ->where('business_type = :businessType and region = :region and data_date = :date and is_del=0')
            ->bindValues([
                'businessType' => $businessType,
                'region' => $region,
                'date' => $date,
            ])
            ->single();
        $num = count(explode(',', $workday));
        return $num;
    }

    public function getOne($id)
    {
        $info = Db::slave('zd_sales')->select('id,business_type,region,data_date,working_days')
            ->from($this->baseTb)
            ->where('id = :id')
            ->bindValues([
                'id' => $id,
            ])
            ->row();
        return $info;
    }

    /**
     * 更新工作日
     * @param $id
     * @param $uid
     * @param $workingDays
     * @return mixed
     */
    public function update($id, $uid, $workingDays)
    {
        $ret = Db::master('zd_sales')->update($this->baseTb)
            ->cols([
                'working_days' => $workingDays,
                'modify_user' => $uid,
                'modify_time' => date('Y-m-d H:i:s')
            ])
            ->where('id = :id')
            ->bindValue('id', $id)->query();
        return $ret;
    }

    /**
     * 创建工作日
     * @param $uid
     * @param $businessType
     * @param $region
     * @param $date
     * @param $workingDays
     * @return mixed
     */
    public function create($uid, $businessType, $region, $date, $workingDays)
    {
        $id = Db::master('zd_sales')->insert($this->baseTb)->cols([
            'business_type' => $businessType,
            'region' => $region,
            'data_date' => $date,
            'working_days' => $workingDays,
            'create_user' => $uid,
            'modify_user' => $uid
        ])->query();
        return $id;
    }

}