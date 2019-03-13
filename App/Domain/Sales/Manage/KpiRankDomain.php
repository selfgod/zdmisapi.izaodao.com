<?php
namespace App\Domain\Sales\Manage;

use App\Model\Sales\Team\SalesKpiModel;
use Base\BaseDomain;

class KpiRankDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new SalesKpiModel();
    }

    /**
     * 获取排行列表数量
     * @param $salesType
     * @param $businessType
     * @param $region
     * @param $date
     * @return string
     */
    public function getListNum($salesType, $businessType, $region, $date)
    {
        return $this->baseModel->getRankListNum($salesType, $businessType, $region, $date);
    }

    /**
     * 获取排行列表数据
     * @param $salesType
     * @param $businessType
     * @param $region
     * @param $date
     * @param $page
     * @param $limit
     * @param $order
     * @return mixed
     */
    public function getListData($salesType, $businessType, $region, $date, $page, $limit, $order)
    {
        $list = $this->baseModel->getRankListData($salesType, $businessType, $region, $date, $page, $limit, $order);
        foreach ($list as $i => $item) {
            $list[$i]['call_sec_avg_rate'] = $item['call_sec_avg_rate'] . '%';
            $list[$i]['perform_rate'] = $item['perform_rate'] . '%';
            $list[$i]['trans_rate_rate'] = $item['trans_rate_rate'] . '%';
            $list[$i]['salary_kpi_weight_total'] = $item['salary_kpi_weight_total'] . '%';
            if ($salesType === '2' || $salesType === '3') {
                $list[$i]['perform_actual_team_rate'] = $item['perform_actual_team_rate'] . '%';
                $list[$i]['resign_rate_rate'] = $item['resign_rate_rate'] . '%';

            }
        }
        return $list;
    }
}