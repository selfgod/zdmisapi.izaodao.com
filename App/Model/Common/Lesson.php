<?php

namespace App\Model\Common;

use Base\BaseModel;
use Base\Db;
use Base\Thrift;

class Lesson extends BaseModel
{
    /**
     * 根据lessonIds获取lesson数据
     * @param $lessonIds
     * @return mixed
     */
    public function getLessonByIds($lessonIds)
    {
        return Thrift::getInstance()->service('Schedule')->getLessonByIds($lessonIds);
    }

    /**
     * 根据scheduleIds获取schedule数据
     * @param $scheduleIds
     * @return mixed
     */
    public function getScheduleByIds($scheduleIds)
    {
        return Thrift::getInstance()->service('Schedule')->getScheduleByIds($scheduleIds);
    }

    /**
     * 更新lesson
     * @param $where
     * @param array $bindValues
     * @param array $data
     * @return bool
     */
    public function updateLesson($where, array $bindValues, array $data)
    {
        $res = Db::master('zd_netschool')->update('sty_schedule_lesson')
            ->where($where)->bindValues($bindValues)
            ->cols($data)->query();
        if($res !== FALSE)
            return TRUE;
        return FALSE;
    }

    /**
     * 获取课件录播地址
     * @param $lessonId
     * @return mixed
     */
    public function getLessonRecordUrl($lessonId)
    {
        return Thrift::getInstance()->service('Schedule')->getLessonRecordUrl($lessonId);
    }
}