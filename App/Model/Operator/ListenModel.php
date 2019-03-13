<?php

namespace App\Model\Operator;

use Base\BaseModel;
use Base\Db;

class ListenModel extends BaseModel
{
    /**
     * 获取旁听课信息
     * @param $lessonId
     * @return array
     */
    public function getListenByLesson($lessonId)
    {
        $data = Db::slave('zd_netschool')->select('ob_name,sit_num,ob_info')
            ->from('sty_observing_lesson')
            ->where('lesson_id = :lesson_id AND status = 1 AND is_del = 0')
            ->bindValue('lesson_id', $lessonId)->row();
        return $data ?: [];
    }

    /**
     * 旁听课状态
     * @param $lessonId
     * @return array
     */
    public function getListenStatusByLesson($lessonId)
    {
        $data = Db::slave('zd_netschool')->select('status,is_del')
            ->from('sty_observing_lesson')
            ->where('lesson_id = :lesson_id')
            ->bindValue('lesson_id', $lessonId)->row();
        return $data ?: [];
    }

    /**
     * 新增旁听课
     * @param array $save
     */
    public function insertListenLesson(array $save)
    {
        Db::master('zd_netschool')->insert('sty_observing_lesson')->cols($save)->query();
    }

    /**
     * 更新旁听课
     * @param $where
     * @param array $bindValues
     * @param array $save
     */
    public function updateListenLesson($where, array $bindValues, array $save)
    {
        Db::master('zd_netschool')->update('sty_observing_lesson')
            ->where($where)->bindValues($bindValues)
            ->cols($save)->query();
    }
}