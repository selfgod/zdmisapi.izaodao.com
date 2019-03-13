<?php

namespace App\Model\Teach\Course;

use Base\BaseModel;
use Base\Db;

class CourseModel extends BaseModel
{

    /**
     * 获取部分标签下全部uid
     * @param $label_ids
     * @return array
     */
    public function getCourseList($teacher_id, $start_time, $end_time)
    {
        $res_s = [];
        $where = ' ssl.is_del = 0 ';
        $where .= " and ssl.teacher_id = :teacher_id ";
        $where .= " and ssl.start_time >= :start_time ";
        $where .= " and ssl.start_time <= :end_time ";
        $where .= ' and sss.class_mode = 1 ';
        $bindValues = [];
        $bindValues['teacher_id'] = $teacher_id;
        $bindValues['start_time'] = $start_time;
        $bindValues['end_time'] = $end_time;

        $res = Db::slave('zd_netschool')->from('sty_schedule_lesson as ssl')
            ->leftJoin('sty_schedule as sss', 'on ssl.schedule_id = sss.id')
            ->select('sss.id, sss.name, ssl.id as lesson_id, ssl.name as lesson_name, 
            ssl.start_time, ssl.end_time, ssl.teacher_id')
            ->where($where)->bindValues($bindValues)->query();
        $data_135w = [];
        $data_0246w = [];
        $data_06b = [];
        $data_ob = [];
        if($res){
            foreach ($res as $n) {
                $info_week = date('w', strtotime($n['start_time']));
                $day_type = (date('H:i', strtotime($n['start_time'])) >= '17:00') ? 1 : 0;
                if(in_array($info_week, [1,3,5]) && $day_type){
                    //一三五晚
                    $data_135w[$n['id']]['name'] = $n['name'];
                    $data_135w[$n['id']]['start'] = (isset($data_135w[$n['id']]['start']) && $data_135w[$n['id']]['start'] < $n['start_time']) ? $data_135w[$n['id']]['start'] : $n['start_time'];
                    $data_135w[$n['id']]['end'] = (isset($data_135w[$n['id']]['end']) && $data_135w[$n['id']]['end'] > $n['start_time']) ? $data_135w[$n['id']]['end'] : $n['start_time'];
                    if(!isset($data_135w[$n['id']]['week']) || !in_array($info_week, $data_135w[$n['id']]['week'])){
                        $data_135w[$n['id']]['week'][] = $info_week;
                    }
                }else if(in_array($info_week, [0,2,4,6]) && $day_type){
                    $data_0246w[$n['id']]['name'] = $n['name'];
                    $data_0246w[$n['id']]['start'] = (isset($data_0246w[$n['id']]['start']) && $data_0246w[$n['id']]['start'] < $n['start_time']) ? $data_0246w[$n['id']]['start'] : $n['start_time'];
                    $data_0246w[$n['id']]['end'] = (isset($data_0246w[$n['id']]['end']) && $data_0246w[$n['id']]['end'] > $n['start_time']) ? $data_0246w[$n['id']]['end'] : $n['start_time'];
                    if(!isset($data_0246w[$n['id']]['week']) || !in_array($info_week, $data_0246w[$n['id']]['week'])){
                        $data_0246w[$n['id']]['week'][] = $info_week;
                    }
                }else if(in_array($info_week, [0,6]) && !$day_type){
                    $data_06b[$n['id']]['name'] = $n['name'];
                    $data_06b[$n['id']]['start'] = (isset($data_06b[$n['id']]['start']) && $data_06b[$n['id']]['start'] < $n['start_time']) ? $data_06b[$n['id']]['start'] : $n['start_time'];
                    $data_06b[$n['id']]['end'] = (isset($data_06b[$n['id']]['end']) && $data_06b[$n['id']]['end'] > $n['start_time']) ? $data_06b[$n['id']]['end'] : $n['start_time'];
                    if(!isset($data_06b[$n['id']]['week']) || !in_array($info_week, $data_06b[$n['id']]['week'])){
                        $data_06b[$n['id']]['week'][] = $info_week;
                    }
                }else{
                    $data_ob[$n['id']]['name'] = $n['name'];
                    $data_ob[$n['id']]['start'] = (isset($data_ob[$n['id']]['start']) && $data_ob[$n['id']]['start'] < $n['start_time']) ? $data_ob[$n['id']]['start'] : $n['start_time'];
                    $data_ob[$n['id']]['end'] = (isset($data_ob[$n['id']]['end']) && $data_ob[$n['id']]['end'] > $n['start_time']) ? $data_ob[$n['id']]['end'] : $n['start_time'];
                    if(!isset($data_ob[$n['id']]['week']) || !in_array($info_week, $data_ob[$n['id']]['week'])){
                        $data_ob[$n['id']]['week'][] = $info_week;
                    }
                }
            }
        }
        $res_s = [
            'neight_135' => $data_135w,
            'neight_2467' => $data_0246w,
            'bai_67' => $data_06b,
            'bai_other' => $data_ob
        ];
        return $res_s;
    }

}