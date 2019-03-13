<?php
/**
 * 排课
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/9/25
 * Time: 09:50
 */

namespace App\Domain\Teach\Course;

use App\Model\Teach\Course\CourseModel;
use App\Model\Teach\Teacher\TeacherModel;
use Base\BaseDomain;

class CourseDomain extends BaseDomain
{
    /**
     * 排课 - 每周课表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryCourseWeekList(array $params)
    {
        $where_sql = '1=1 ';
        $bindValues = [];
        $start_time = $params['start_time'] ? $params['start_time'] : '';
        $end_time = $params['end_time'] ? $params['end_time'] : '';
        $week = date("w",time());
        if($start_time){
            $start_time .= ' 00:00:00';
        }else{
            $start_time = date('Y-m-01 00:00:00');
        }
        if($end_time){
            $end_time .= ' 23:59:59';
        }else{
            $end_time = date('Y-m-01 00:00:00', strtotime('+1 months'));
        }
        $action = $params['action'] ? $params['action'] : '';
        if($action == 'week_all'){
            $where_sql .= ' and work_type = 0 ';
            $where_sql .= ' and work_type_group = :work_type_group ';
            $bindValues['work_type_group'] = $params['group'];
        }else{
            $where_sql .= ' and work_type = 1 ';
        }
        if($params['teacher_id'] > 0){
            $where_sql .= ' and uid = :uid ';
            $bindValues['uid'] = $params['teacher_id'];
        }

        $teacher = new TeacherModel();
        $count = $teacher->queryTeacherList($where_sql, $bindValues, TRUE);
        if ($params['type'] === 'list') {
            $courseWeekList = [];
            if ($count > 0) {
                $courseWeekList = $teacher->queryTeacherList($where_sql, $bindValues, ((intval($params['page']) > 0) ? $params['page'] : 1), $params['limit']);
                if($courseWeekList){
                    foreach ($courseWeekList as $key => $val) {
                        //用户名真名
                        $course = new CourseModel();
                        $course_list = $course->getCourseList($val['uid'], $start_time, $end_time);
                        $courseWeekList[$key]['neight_135'] = $course_list['neight_135'] ?: [];
                        $courseWeekList[$key]['neight_2467'] = $course_list['neight_2467'] ?: [];
                        $courseWeekList[$key]['bai_67'] = $course_list['bai_67'] ?: '';
                        $courseWeekList[$key]['bai_other'] = $course_list['bai_other'] ?: [];
                    }
                }
            }
            $data['courseWeekList'] = $courseWeekList;
        } else {
            $data['courseWeekCount'] = $count;
        }
        return $data;
    }

}