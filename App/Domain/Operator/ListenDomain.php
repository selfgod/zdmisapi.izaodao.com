<?php

namespace App\Domain\Operator;

use App\Model\Operator\ListenModel;
use Base\BaseDomain;
use Base\Exception\BadRequestException;
use Base\Helper\OptionsHelper;
use Base\Thrift;
use EasySwoole\Core\Component\Logger;

class ListenDomain extends BaseDomain
{
    /**
     * 获取旁听课信息
     * @param $lessonId
     * @return array
     * @throws BadRequestException
     */
    public function getListenCourse($lessonId)
    {
        $lesson = Thrift::getInstance()->service('Schedule')->getLessonInfo($lessonId);
        if (empty($lesson)) throw new BadRequestException("课件不存在");
        $schedule = Thrift::getInstance()->service('Schedule')->getScheduleInfo($lesson['schedule_id']);
        if (empty($schedule)) throw new BadRequestException("课件阶段课程不存在");
        $curricularZH = OptionsHelper::get_options('curricular_system_zh', $schedule['curricular_system']);
        $data = [
            'lesson_id' => $lessonId,
            'schedule_name' => $schedule['name'],
            'remark' => $schedule['remark'],//备注
            'lesson_name' => $lesson['name'],
            'start_time' => $lesson['start_time'] ?: '',
            'end_time' => $lesson['end_time'] ?: '',
            'curricular_zh' => $curricularZH ?: '',//课程体系
            'teacher' => '',
            'ob_status' => 0,
            'ob_name' => '',//旁听名称
            'ob_info' => '',//旁听介绍
            'ob_num' => 0,//旁听人数
        ];
        //教师信息
        if ($teacherId = intval($lesson['teacher_id'])) {
            $teacher = Thrift::getInstance()->service('User')->getTeacherInfo($teacherId);
            $data['teacher'] = $teacher['name'] ?: '';
        }
        //旁听信息
        $listen = (new ListenModel())->getListenByLesson($lessonId);
        if (!empty($listen)) {
            $data['ob_status'] = 1;
            $data['ob_name'] = $listen['ob_name'] ?: '';
            $data['ob_info'] = $listen['ob_info'] ?: '';
            $data['ob_num'] = intval($listen['sit_num']);
        }
        return $data;
    }

    /**
     * 设置旁听课件
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function setListenCourse(array $params)
    {
        $nowDate = date('Y-m-d H:i:s');
        $lessonId = intval($params['lessonId']);
        $lesson = Thrift::getInstance()->service('Schedule')->getLessonInfo($lessonId);
        if (empty($lesson)) throw new BadRequestException("课件不存在");
        $status = intval($params['ob_status']);
        $save = ['status' => 0];
        if ($status === 1) {
            $save['status'] = 1;
            $save['ob_name'] = addslashes($params['ob_name']);
            $save['ob_info'] = addslashes($params['ob_info']);
            $save['sit_num'] = intval($params['ob_num']);
        }
        $req = TRUE;
        try {
            $listenModel = new ListenModel();
            $listen = $listenModel->getListenStatusByLesson($lessonId);
            if (!empty($listen)) {
                if (intval($listen['is_del']) === 1) $save['is_del'] = 0;
                $save['modify_time'] = $nowDate;
                $save['modify_user'] = $params['uid'];
                $listenModel->updateListenLesson('lesson_id = :lesson_id', ['lesson_id' => $lessonId], $save);
            } else {
                $save['lesson_id'] = $lessonId;
                $save['create_user'] = $params['uid'];
                $listenModel->insertListenLesson($save);
            }
            if ((int)$lesson['channel_id'])
                Thrift::getInstance()->service('Kafka')->producer('lesson', 'addLessonChannel', [
                    'lessonId' => $lessonId,
                    'action' => 'edit'
                ]);
        } catch (\Exception $e) {
            $req = FALSE;
            Logger::getInstance()->log('setListenCourse ERROR:' . $e->getMessage() . ' params:' . \GuzzleHttp\json_encode($params), 'error');
        }
        return $req;
    }
}