<?php
namespace App\Domain\Sales\Manage\Team;


use App\Model\Sales\Team\SalesKpiModel;
use App\Model\Sales\Team\WorkingDaysModel;
use Base\BaseDomain;

class WorkingDaysDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new WorkingDaysModel();
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
        return $this->baseModel->create($uid, $businessType, $region, $date, $workingDays);
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
        $res = $this->baseModel->update($id, $uid, $workingDays);
        if($res){
            $item = $this->baseModel->getOne($id);
            if(!empty($item)){
                $days = count(explode(',', $item['working_days']));
                (new SalesKpiModel())->_setSalaryData([
                    'business_type'=>$item['business_type'],
                    'region'=>$item['region'],
                    'data_date'=>$item['data_date'],
                    'structure_type'=>'salesman',
                    'online_time'=>['>'=>0],
                    'is_share'=>1
                ], ['share_day'=>$days]);
            }
        }
        return $res;
    }

    /**
     * 获取工作日
     * @param $businessType
     * @param $region
     * @param $date
     * @return array
     */
    public function get($businessType, $region, $date)
    {
        return $this->baseModel->get($businessType, $region, $date);
    }

    /**
     * 获取工作日数量
     * @param $businessType
     * @param $region
     * @param $date
     * @return int
     */
    public function getNum($businessType, $region, $date)
    {
        return $this->baseModel->getNum($businessType, $region, $date);
    }

    /**
     * 判断是否是工作日
     * @param $businessType
     * @param $region
     * @param $date
     * @return bool
     */
    public function isWorkingDay($businessType, $region, $date)
    {
        $st = explode('-', date('Y-m-d', $date));
        $dataDate = $st[0] . '-' . $st[1];
        $day = $st[2];
        $workingDays = $this->baseModel->get($businessType, $region, $dataDate);
        if (!empty($workingDays)) {
            return in_array($day, explode(',', $workingDays['working_days']));
        } else if (date('w', $date) === '6' || date('w', $date) === '0') {//所在月未配置根据节假日计算
            return FALSE;
        } else {
            return TRUE;
        }
    }
}