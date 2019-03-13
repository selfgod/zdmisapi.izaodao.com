<?php
namespace App\Traits;

use App\Model\Common\User;
use Base\Db;

trait SalesManageAuditLog
{


    /**
     * 比较差异，输出修改记录
     * @param array $before
     * @param array $after
     * @return string
     */
    protected function showDiff(array $before, array $after)
    {
        $bLog = '修改前：';
        $aLog = '修改后：';
        $keyMap = $this->getKeyMap();
        if (empty($keyMap)) {
            return;
        }
        $hasDiff = false;
        foreach ($before as $key => $value) {
            if (isset($after[$key]) && self::getValue($value) !== self::getValue($after[$key])) {
                $hasDiff = true;
                $name = $keyMap[$key];
                $bLog .= "{$name}:$value ";
                $aLog .= "{$name}:{$after[$key]} ";
            }
        }
        return $hasDiff?$bLog . '|' . $aLog:null;
    }

    /**
     * 获取字段描述映射，需要实现
     * @return array
     */
    abstract public function getKeyMap();

    /**
     * 获取单条记录，需要实现
     * @param $id
     * @return array
     */
    abstract public function getOne($id);

    /**
     * 格式化before数组, 可以实现
     * @param $before
     * @return array
     */
    public function formatAuditBefore($before)
    {
        return $before;
    }

    /**
     * 数字类型字符串返回数字
     * @param $value
     * @return int|string
     */
    protected static function getValue($value)
    {
        return is_numeric($value) ? $value + 0 : $value;
    }

    /**
     * 记录修改日志
     * @param $table
     * @param $id
     * @param $uid
     * @param $after
     * @param array $before
     */
    public function addAuditLog($table, $id, $uid, $after, $before = [])
    {
        if (empty($before)) {
            $before = $this->getOne($id);
            if (empty($before)) {
                return;
            }
        }
        $before = $this->formatAuditBefore($before);
        $content = $this->showDiff($before, $after);
        if($content){
            Db::master('zd_sales')->insert('sales_kpi_audit_log')->cols([
                'origin_id' => $id,
                'origin_table' => $table,
                'content' => $content,
                'modify_time' => date('Y-m-d H:i:s'),
                'modify_user' => $uid
            ])->query();
        }
    }

    /**
     * 获取日志信息
     * @param $table
     * @param $id
     * @return mixed
     */
    public function getAuditInfo($table, $id)
    {
        $data = [];
        $info = Db::slave('zd_sales')->select('content,modify_time,modify_user')
            ->from('sales_kpi_audit_log')
            ->where('origin_id = :origin_id and origin_table = :origin_table and is_del=0')
            ->bindValues([
                'origin_id' => $id,
                'origin_table' => $table
            ])
            ->orderByDESC(['id'])
            ->query();
        if (!empty($info)) {
            $uids = array_column($info, 'modify_user');
            $users = (new User())->getUsersByUids($uids);
            foreach ($users as $user) {
                $data[$user['uid']] = $user['username'];
            }

            foreach ($info as $i => $item) {
                $info[$i]['modify_user'] = $data[$item['modify_user']];
            }
        }
        return $info;
    }
}