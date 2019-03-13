<?php

namespace App\Domain\Teach\DataCompass;

use App\Model\DataCompass\MonthComplex;
use Base\BaseDomain;

class ComplexDomain extends BaseDomain
{
    public function getMonthComplexSas(array $params)
    {
        $action = $params['action'];
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $grade = intval($params['grade']);
        if (!empty($startTime) && !empty($endTime) && $endTime >= $startTime) {
            $start = date('Ym', strtotime($startTime));
            $end = date('Ym', strtotime($endTime));
        } else {
            $start = date('Y') . '01';
            $end = date('Y') . '12';
        }
        $model = new MonthComplex();
        if (in_array($action, ['chooseCourse', 'endCourse'])) {
            if ($grade < 0) $grade = 0;
            $list = $model->getGradeMonthRecord($start, $end, $grade);
        } else {
            $list = $model->getMonthSasRecord($start, $end);
        }
        $data = [];
        if (!empty($list)) {
            foreach ($list as $val) {
                $item = $this->formatComplexVal($val);
                $data[] = $item;
            }
        }
        return $data;
    }

    /**
     * 格式化Complex Val
     * @param $item
     * @return mixed
     */
    private function formatComplexVal($item)
    {
        unset($item['create_time']);
        unset($item['modify_time']);
        $item['month'] = substr_replace($item['month'], '-', 4, 0);
        $item['learn_rate'] = isset($item['learn_rate']) ? ($item['learn_rate'] / 100) . '%' : '0%';
        $item['new_activate_rate'] = isset($item['new_activate_rate']) ? ($item['new_activate_rate'] / 100) . '%' : '0%';
        $item['new_choose_rate'] = isset($item['new_choose_rate']) ? ($item['new_choose_rate'] / 100) . '%' : '0%';
        $item['new_choose_live_rate'] = isset($item['new_choose_live_rate']) ? ($item['new_choose_live_rate'] / 100) . '%' : '0%';
        $item['attendance_rate'] = isset($item['attendance_rate']) ? ($item['attendance_rate'] / 100) . '%' : '0%';
        $item['attendance_live_rate'] = isset($item['attendance_live_rate']) ? ($item['attendance_live_rate'] / 100) . '%' : '0%';
        $item['complete_course_rate'] = isset($item['complete_course_rate']) ? ($item['complete_course_rate'] / 100) . '%' : '0%';
        $item['transition_rate'] = isset($item['transition_rate']) ? ($item['transition_rate'] / 100) . '%' : '0%';
        $item['next_grade_choose_rate'] = isset($item['next_grade_choose_rate']) ? ($item['next_grade_choose_rate'] / 100) . '%' : '0%';
        return $item;
    }
}