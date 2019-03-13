<?php

namespace App\Model\Course;

use Base\BaseModel;
use Base\Db;

class LessonModel extends BaseModel
{
    /**
     * 获取lessonIds
     * @param $where
     * @param array $bindValues
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function getLessonJoinScheduleIds($where, array $bindValues, $page = 1, $limit = 0)
    {
        $query = Db::slave('zd_netschool')->select('ssln.id')
            ->from('sty_schedule_lesson as ssln')
            ->leftJoin('sty_schedule as ss', 'ssln.schedule_id = ss.id')
            ->where($where)->bindValues($bindValues)->orderByDESC(['ssln.start_time']);
        if ($limit > 0) $query->setPaging($limit)->page($page);
        $lessonIds = $query->column();
        return $lessonIds ?: [];
    }

    /**
     * 获取lesson数量
     * @param $where
     * @param array $bindValues
     * @return int
     * @throws \Exception
     */
    public function getLessonJoinScheduleCount($where, array $bindValues)
    {
        $count = Db::slave('zd_netschool')->select('COUNT(*)')
            ->from('sty_schedule_lesson as ssln')
            ->leftJoin('sty_schedule as ss', 'ssln.schedule_id = ss.id')
            ->where($where)->bindValues($bindValues)->single();
        return intval($count);
    }

    /**
     * 旁听课信息
     * @param $where
     * @param array $bindValues
     * @param int $page
     * @param int $limit
     * @param string $fields
     * @return array
     * @throws \Exception
     */
    public function getObserveLesson($where, array $bindValues, $page = 1, $limit = 0, $fields = '')
    {
        $fields = $fields ?: 'ssln.id';
        $data = Db::slave('zd_netschool')->select($fields)
            ->from('sty_observing_lesson as sol')
            ->leftJoin('sty_schedule_lesson as ssln', 'sol.lesson_id = ssln.id')
            ->leftJoin('sty_schedule as ss', 'ssln.schedule_id = ss.id')
            ->where($where)->bindValues($bindValues)->setPaging($limit)->page($page)->query();
        return $data ?: [];
    }

    /**
     * 获取旁听课总数
     * @param $where
     * @param array $bindValues
     * @return int
     * @throws \Exception
     */
    public function getObserveLessonCount($where, array $bindValues)
    {
        $count = Db::slave('zd_netschool')->select('COUNT(*)')
            ->from('sty_observing_lesson as sol')
            ->leftJoin('sty_schedule_lesson as ssln', 'sol.lesson_id = ssln.id')
            ->leftJoin('sty_schedule as ss', 'ssln.schedule_id = ss.id')
            ->where($where)->bindValues($bindValues)->single();
        return intval($count);
    }

    /**
     * 获取用户当前最后结课的课程
     * @param $uid
     * @return array
     * @throws \Exception
     */
    public function getUserLastEndSchedule($uid): array
    {
        $schedule = Db::slave('zd_netschool')->select('sus.schedule_id')->from('sty_user_schedule AS sus')
            ->leftJoin('sty_schedule AS ss', 'sus.schedule_id = ss.id')
            ->where('uid = :uid AND class_mode = 1 AND sus.is_del = 0 AND ss.is_del = 0')->bindValue('uid', $uid)->row();
        return $schedule ?: [];
    }
}