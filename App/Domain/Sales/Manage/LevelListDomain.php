<?php
namespace App\Domain\Sales\Manage;

use App\Model\Sales\Team\LevelListModel;
use App\Model\Sales\Team\SalesKpiModel;
use Base\BaseDomain;

class LevelListDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new LevelListModel();
    }

    /**
     * 获取级别列表
     * @param $businessType
     * @param $region
     * @param $date
     * @return array
     * @throws \Exception
     */
    public function getList($businessType, $region, $date)
    {
        $ccList = [];
        $tlList = [];
        $manList = [];
        $list = $this->baseModel->getList($businessType, $region, $date);
        foreach ($list as $item) {
            $item = $this->formatItem($item);
            $type = $item['sales_type'];
            if ($type === 1) {
                $ccList[] = $item;
            } elseif ($type === 2) {
                $tlList[] = $item;
            } else {
                $manList[] = $item;
            }
        }
        return ['cc_list' => $ccList, 'tl_list' => $tlList, 'man_list' => $manList];
    }

    /**
     * 转化null
     * @param $item
     * @return mixed
     */
    public function formatItem($item)
    {
        foreach ($item as $key => $value) {
            if ($value === null) {
                $item[$key] = '';
            }
        }
        return $item;
    }

    /**
     * 更新
     * @param $id
     * @param $uid
     * @param $data
     * @return mixed
     */
    public function update($id, $uid, $data)
    {
        $nowMonth = date('Y-m');
        $updateData = [];
        $keyMap = $this->baseModel->getKeyMap();
        foreach ($keyMap as $key => $desc) {
            $updateData[$key] = $data[$key];
        }
        if ($id === 0) {
            $level = $this->baseModel->getOneByLevel($data['business_type'], $data['region'], $nowMonth, $data['level_id']);
            if (empty($level)) {
                $ret = $this->baseModel->addLevel($uid, array_merge($updateData, [
                    'business_type' => $data['business_type'],
                    'region' => $data['region'],
                    'level_id' => $data['level_id'],
                    'data_date' => $nowMonth
                ]));
                if ($ret) {
                    $this->baseModel->addAuditLog($this->baseModel->listTb, $ret, $uid, $updateData, array_map(function ($n) {
                        return '';
                    }, $keyMap));
                }
                return $ret;
            }
            return 0;
        }
        $info = $this->baseModel->getOne($id);
        if (!empty($info) && $info['data_date'] !== $nowMonth) {
            return 0;
        }
        if($id){
            $updateKpiData = [];
            foreach($updateData as $field=>$value){
                if($info[$field]!=$value){
                    $updateKpiData[$field] = $updateData[$field];
                }
            }
            if(!empty($updateKpiData)){
                (new SalesKpiModel())->_setSalaryData([
                    'business_type'=>$data['business_type'],
                    'region' => $data['region'],
                    'level' => $data['level_id'],
                ], $updateKpiData);
            }
        }

        $this->baseModel->addAuditLog($this->baseModel->listTb, $id, $uid, $updateData);
        return $this->baseModel->update($id, $uid, $updateData);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAudit($id)
    {
        $info = $this->baseModel->getAuditInfo($this->baseModel->listTb, $id);
        return $info;
    }

    /**
     * @param $businessType
     * @param $region
     * @param $date
     * @param $levelId
     * @return array
     */
    public function getOneByLevel($businessType, $region, $date, $levelId)
    {
        $info = $this->baseModel->getOneByLevel($businessType, $region, $date, $levelId);
        return $info;
    }

}