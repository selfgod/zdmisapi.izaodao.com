<?php

namespace App\Model\Setting;

use Base\BaseModel;
use Base\Db;

class WaitproModel extends BaseModel
{
    /**
     * 获取待处理项目
     * @return array
     */
    public function getWaitProject()
    {
        $project = Db::slave('zd_netschool')
            ->select('id,`key`,name,link,info,icon')
            ->from('sty_setting_wait_process')
            ->where('is_del = 0')
            ->orderByASC(['id'])->query();
        return $project ?: [];
    }

    /**
     * 通过id获取内容
     * @param $id
     * @return array
     */
    public function getWaitProjectById($id)
    {
        $row = Db::slave('zd_netschool')
            ->select('id,`key`,name,link,info,icon')
            ->from('sty_setting_wait_process')
            ->where('id = :id and is_del = 0')
            ->bindValue('id', $id)
            ->orderByASC(['id'])->limit(1)->row();
        return $row ?: [];
    }

    /**
     * 通过key获取内容
     * @param $keys
     * @return array
     */
    public function getWaitProjectByKey(array $keys)
    {
        $data = Db::slave('zd_netschool')
            ->select('id,`key`,name,link,info,icon')
            ->from('sty_setting_wait_process')
            ->where($this->whereIn('`key`', $keys))
            ->where('is_del = 0')
            ->orderByASC(['id'])
            ->query();
        return $data ?: [];
    }

    /**
     * 更新
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateWaitProjectById($id, $data)
    {
        $res = Db::master('zd_netschool')->update('sty_setting_wait_process')
            ->cols($data)->where('id = :id and is_del = 0')
            ->bindValue('id', $id)->query();
        return !!$res;
    }

    /**
     * 新增
     * @param $data
     * @return bool
     */
    public function insertWaitProject($data)
    {
        $res = Db::master('zd_netschool')->insert('sty_setting_wait_process')
            ->cols($data)->query();
        return !!$res;
    }
}