<?php

namespace App\Model\Teach\Suspend;

use Base\BaseModel;
use Base\Db;

class SuspendModel extends BaseModel
{
    /**
     * 获取休学列表
     * @param $where
     * @param array $bindValues
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function getSuspendList($where, array $bindValues, $page = 1, $limit = 0, $order = ['a.end_time asc'])
    {
        $order[] = 'a.id';
        $query = Db::slave('zd_netschool')->select('a.*,b.stuffname')
            ->from('sty_user_suspend as a')
            ->leftJoin('zd_class.jh_common_member_profile_stuff as b', 'on a.uid=b.uid and b.type="sa"')
            ->where($where)->bindValues($bindValues)->orderBy($order);
        if ($limit > 0) $query->setPaging($limit)->page($page);
        $res = $query->query();
        return $res ?: [];
    }

    /**
     * 获取休学数量
     * @param $where
     * @param array $bindValues
     * @return int
     * @throws \Exception
     */
    public function getSuspendCount($where, array $bindValues)
    {
        $count = Db::slave('zd_netschool')->select('COUNT(*)')
                ->from('sty_user_suspend as a')
                ->leftJoin('zd_class.jh_common_member_profile_stuff as b', 'on a.uid=b.uid and b.type="sa"')
                ->where($where)->bindValues($bindValues)->single();
        return intval($count);
    }

    /**
     * 删除休学信息
     * @param int $id
     * @return bool
     */
    public function delUserSuspend($id = 0)
    {
        $res = FALSE;
        if($id)
        {
            $where = ' id = ' . $id;
            $data['is_del'] = 1;
            $data['modify_time'] = date('Y-m-d H:i:s');
            $res = Db::master('zd_netschool')->update('sty_user_suspend')
                ->where($where)
                ->cols($data)->query();
        }
        return $res;
    }

}