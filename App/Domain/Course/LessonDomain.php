<?php

namespace App\Domain\Course;

use App\Model\Common\User;
use App\Model\Common\Lesson;
use App\Model\Course\LessonModel;
use Base\BaseDomain;
use Base\Thrift;
use EasySwoole\Core\Utility\Sort;

class LessonDomain extends BaseDomain
{
    /**
     * 获取录播课件列表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getRecordLessonList(array $params)
    {
        $nowDate = date('Y-m-d H:i:s');
        $bindValues = [];
        $where = 'ssln.is_record = 1';
        if ($params['scheduleId'] > 0) {
            $where .= ' and ss.id = :schedule_id';
            $bindValues['schedule_id'] = $params['scheduleId'];
        }
        if (!empty($params['name'])) {
            $where .= " and ss.name like :name";
            $bindValues['name'] = '%' . $params['name'] . '%';
        }
        if ($params['teacherUid'] > 0) {
            $where .= ' and ssln.teacher_id = :teacher_id';
            $bindValues['teacher_id'] = $params['teacherUid'];
        }
        if (!empty($params['startTime'])) {
            $where .= ' and ssln.class_mode = 1 and ssln.start_time >= :start and ssln.start_time <= :end and ssln.end_time < :endTime';
            $bindValues['start'] = date('Y-m-d 00:00:00', strtotime($params['startTime']));
            $bindValues['end'] = date('Y-m-d 23:59:59', strtotime($params['startTime']));
            $bindValues['endTime'] = $nowDate;
        } else {
            $where .= ' and ((ssln.class_mode = 1 and ssln.end_time < :endTime) or ssln.class_mode = 2)';
            $bindValues['endTime'] = $nowDate;
        }
        if (intval($params['setRecord']) === 1) {
            $where .= " and ssln.record_link IS NOT NULL and ssln.record_link != ''";
        } else {
            $where .= " and (ssln.record_link IS NULL or ssln.record_link = '')";
        }
        $where .= ' and ss.is_del = 0 and ssln.is_del = 0';
        $lessonModel = new LessonModel();
        $count = $lessonModel->getLessonJoinScheduleCount($where, $bindValues);
        if ($params['type'] === 'list') {
            $lessonList = [];
            if ($count > 0) {
                $lessonCommon = new Lesson();
                $userCommon = new User();
                $lessonId = $lessonModel->getLessonJoinScheduleIds($where, $bindValues, $params['page'], 20);
                $lessons = $lessonCommon->getLessonByIds($lessonId);
                $scheduleIds = array_unique(array_column($lessons, 'schedule_id'));
                $schedules = $lessonCommon->getScheduleByIds($scheduleIds);
                $teacherIds = array_unique(array_column($lessons, 'teacher_id'));
                $teachers = empty($teacherIds) ? [] : $userCommon->getTeacherByIds($teacherIds);
                $uploaderIds = array_unique(array_column($lessons, 'record_uploader'));
                $uploaderList = empty($uploaderIds) ? [] : $userCommon->getUsersByUids($uploaderIds);
                $uploaders = empty($uploaderList) ? [] : array_reduce($uploaderList, function ($list, $val) {
                    $list[$val['uid']] = $val;
                    return $list;
                });
                $i = 0;
                $lessons = Sort::multiArraySort($lessons, 'start_time', SORT_DESC);
                foreach ($lessons as $lesson) {
                    $lessonList[$i]['lesson_id'] = $lesson['id'];
                    $lessonList[$i]['lesson_name'] = $lesson['name'];
                    $lessonList[$i]['schedule_id'] = $lesson['schedule_id'];
                    $lessonList[$i]['schedule_name'] = isset($schedules[$lesson['schedule_id']]) ? $schedules[$lesson['schedule_id']]['name'] : '';
                    $lessonList[$i]['schedule_remark'] = isset($schedules[$lesson['schedule_id']]) ? $schedules[$lesson['schedule_id']]['remark'] : '';
                    $lessonList[$i]['teacher_name'] = $lesson['teacher_id'] && isset($teachers[$lesson['teacher_id']]) ? $teachers[$lesson['teacher_id']]['name'] : '';
                    $lessonList[$i]['class_time'] = '';
                    if (intval($lesson['class_mode']) === 1) {
                        $lessonList[$i]['class_time'] = date('Y-m-d', strtotime($lesson['start_time'])) . ' ' . date('H:i', strtotime($lesson['start_time'])) . '-' . date('H:i', strtotime($lesson['end_time']));
                    }
                    $lessonList[$i]['record_download'] = $lesson['record_download'];
                    $lessonList[$i]['record_link'] = $lesson['record_link'] ?: '';
                    $lessonList[$i]['uploader'] = $lesson['record_uploader'] && isset($uploaders[$lesson['record_uploader']]) ? $uploaders[$lesson['record_uploader']]['username'] : '';
                    $i++;
                }
            }
            $data['lessonList'] = $lessonList;
        } else {
            $data['lessonCount'] = $count;
        }
        return $data;
    }

    /**
     * 更新录播课件地址
     * @param $lessonId
     * @param $link
     * @return bool
     */
    public function saveLessonRecordLink($params)
    {
        $lessonCommon = new Lesson();
        $lesson = $lessonCommon->getLessonByIds([$params['lessonId']]);
        if (!empty($lesson)) {
            $res = $lessonCommon->updateLesson('id = :lesson_id and is_del = 0', [
                'lesson_id' => $params['lessonId']
            ], [
                'record_link' => $params['link'],
                'record_time' => gmstrftime('%H:%M:%S', $params['timeLen']),
                'record_uploader' => $params['uid'],
                'modify_user' => $params['uid'],
                'modify_time' => date('Y-m-d H:i:s')
            ]);
            Thrift::getInstance()->service('Admin')->delCache('lesson', $params['lessonId']);
            return $res;
        }
        return FALSE;
    }

    /**
     * 删除课件录播地址
     * @param array $params
     * @return bool
     */
    public function delLessonRecordLink(array $params): bool
    {
        $lessonCommon = new Lesson();
        $lesson = $lessonCommon->getLessonByIds([$params['lessonId']]);
        if (!empty($lesson)) {
            $res = $lessonCommon->updateLesson('id = :lesson_id and is_del = 0', [
                'lesson_id' => $params['lessonId']
            ], [
                'record_link' => NULL,
                'record_time' => '00:00:00',
                'record_uploader' => 0,
                'modify_user' => $params['uid'],
                'modify_time' => date('Y-m-d H:i:s')
            ]);
            Thrift::getInstance()->service('Admin')->delCache('lesson', $params['lessonId']);
            return $res;
        }
        return FALSE;
    }

    /**
     * 获取课程录播地址
     * @param $lessonId
     * @return mixed
     */
    public function getLessonRecordUrl($lessonId)
    {
        return (new Lesson())->getLessonRecordUrl($lessonId);
    }

    /**
     * 公开课列表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getOpenLessonList(array $params): array
    {
        $teacherId = (int)$params['teacherId'];
        $startDate = $params['startDate'] ?? '';
        $endDate = $params['endDate'] ?? '';
        $name = $params['name'] ?? '';

        $where = 'ss.business_type = :business_type';
        $bindValues['business_type'] = 5;
        if ($teacherId > 0) {
            $where .= ' AND ssln.teacher_id = :teacherId';
            $bindValues['teacherId'] = $teacherId;
        }
        if (!empty($startDate)) {
            $start = date('Y-m-d 00:00:00', strtotime($startDate));
            $where .= ' AND ssln.start_time >= :start';
            $bindValues['start'] = $start;
        }
        if (!empty($endDate)) {
            $end = date('Y-m-d 23:59:59', strtotime($endDate));
            $where .= ' AND ssln.start_time <= :end';
            $bindValues['end'] = $end;
        }
        if (!empty($name)) {
            $where .= ' AND ssln.name LIKE :name';
            $bindValues['name'] = '%' . addslashes($name) . '%';
        }
        $where .= ' AND ssln.is_del = 0 AND ss.is_del = 0';
        $lessonModel = new LessonModel();
        $lessons = [];
        $count = $lessonModel->getLessonJoinScheduleCount($where, $bindValues);
        if ($count > 0) {
            $lessonIds = $lessonModel->getLessonJoinScheduleIds($where, $bindValues, $params['page'], $params['limit']);
            if (!empty($lessonIds)) {
                $lessonList = (new Lesson())->getLessonByIds($lessonIds);
                //老师信息
                $teacherIds = array_values(array_unique(array_column($lessonList, 'teacher_id')));
                $teachers = empty($teacherIds) ? [] : (new User())->getTeacherByIds($teacherIds);
                foreach ($lessonList as $value) {
                    $item['lesson_id'] = $value['id'];
                    $item['name'] = $value['name'];
                    $item['teacher'] = (int)$value['teacher_id'] && isset($teachers[$value['teacher_id']]) ? $teachers[$value['teacher_id']]['name'] : '';
                    if (empty($value['start_time']) || empty($value['end_time'])) {
                        $item['start_time'] = $item['end_time'] = $item['start_data'] = $item['startTime'] = $item['endTime'] = '';
                    } else {
                        $item['start_time'] = $value['start_time'];
                        $item['end_time'] = $value['end_time'];
                        $item['start_data'] = date('Y-m-d', strtotime($value['start_time']));
                        $item['startTime'] = date('H:i', strtotime($value['start_time']));
                        $item['endTime'] = date('H:i', strtotime($value['end_time']));
                    }
                    $lessons[] = $item;
                }
                $lessons = Sort::multiArraySort($lessons, 'start_time', SORT_ASC);
            }
        }
        return ['count' => $count, 'lessons' => $lessons];
    }

    /**
     * 旁听课列表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getListenLessonList(array $params): array
    {
        $teacherId = (int)$params['teacherId'];
        $startDate = $params['startDate'] ?? '';
        $endDate = $params['endDate'] ?? '';
        $name = $params['name'] ?? '';

        $lessons = [];
        $where = 'sol.`status` = :status';
        $bindValues['status'] = 1;
        if ($teacherId > 0) {
            $where .= ' AND ssln.teacher_id = :teacherId';
            $bindValues['teacherId'] = $teacherId;
        }
        if (!empty($startDate)) {
            $start = date('Y-m-d 00:00:00', strtotime($startDate));
            $where .= ' AND ssln.start_time >= :start';
            $bindValues['start'] = $start;
        }
        if (!empty($endDate)) {
            $end = date('Y-m-d 23:59:59', strtotime($endDate));
            $where .= ' AND ssln.start_time <= :end';
            $bindValues['end'] = $end;
        }
        if (!empty($name)) {
            $where .= ' AND ss.name LIKE :name';
            $bindValues['name'] = '%' . addslashes($name) . '%';
        }
        $where .= ' AND sol.is_del = 0 AND ssln.is_del = 0 AND ss.is_del = 0';
        $lessonModel = new LessonModel();
        $count = $lessonModel->getObserveLessonCount($where, $bindValues);
        if ($count > 0) {
            $lessonList = $lessonModel->getObserveLesson($where, $bindValues, $params['page'], $params['limit'], 'ssln.id,ssln.schedule_id,ss.name as schedule_name,ss.remark,ssln.name,ssln.teacher_id,ssln.start_time,ssln.end_time,sol.ob_name');
            if (!empty($lessonList)) {
                //老师信息
                $teacherIds = array_values(array_unique(array_column($lessonList, 'teacher_id')));
                $teachers = empty($teacherIds) ? [] : (new User())->getTeacherByIds($teacherIds);
                foreach ($lessonList as $value) {
                    $item['schedule_id'] = $value['schedule_id'];
                    $item['lesson_id'] = $value['id'];
                    $item['schedule_name'] = $value['schedule_name'];
                    $item['remark'] = $value['remark'];
                    $item['name'] = $value['ob_name'] ?: $value['name'];
                    $item['teacher'] = (int)$value['teacher_id'] && isset($teachers[$value['teacher_id']]) ? $teachers[$value['teacher_id']]['name'] : '';
                    if (empty($value['start_time']) || empty($value['end_time'])) {
                        $item['start_time'] = $item['end_time'] = $item['start_data'] = $item['startTime'] = $item['endTime'] = '';
                    } else {
                        $item['start_time'] = $value['start_time'];
                        $item['end_time'] = $value['end_time'];
                        $item['start_data'] = date('Y-m-d', strtotime($value['start_time']));
                        $item['startTime'] = date('H:i', strtotime($value['start_time']));
                        $item['endTime'] = date('H:i', strtotime($value['end_time']));
                    }
                    $lessons[] = $item;
                }
                $lessons = Sort::multiArraySort($lessons, 'start_time', SORT_ASC);
            }
        }
        return ['count' => $count, 'lessons' => $lessons];
    }
}