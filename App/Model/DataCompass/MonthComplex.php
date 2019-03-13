<?php

namespace App\Model\DataCompass;

use Base\BaseModel;
use Base\Db;

class MonthComplex extends BaseModel
{
    /**
     * 获取月份统计记录
     * @param $start
     * @param $end
     * @return array
     */
    public function getMonthSasRecord($start, $end)
    {
        $data = Db::slave('zd_jpdata')->select('*')->from('sas_month_record')
            ->where('month >= :start AND month <= :end')
            ->bindValues(['start' => $start, 'end' => $end])
            ->orderByDESC(['month'])
            ->query();
        return $data ?: [];
    }

    /**
     * 获取等级月份统计记录
     * @param $start
     * @param $end
     * @param int $grade
     * @return array
     */
    public function getGradeMonthRecord($start, $end, $grade = -1)
    {
        $query = Db::slave('zd_jpdata')->select('*')->from('sas_grade_month_record')
            ->where('month >= :start AND month <= :end')
            ->bindValues(['start' => $start, 'end' => $end]);
        if ($grade >= 0) $query->where('grade_id = :grade')->bindValue('grade', $grade);
        $data = $query->orderByDESC(['month'])->query();
        return $data ?: [];
    }
}