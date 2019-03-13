<?php
namespace App\Model\Sales\Setting;

use App\Traits\SalesManageAuditLog;
use Base\BaseModel;
use Base\Cache\ZdRedis;
use Base\Db;

class SalesLevelModel extends BaseModel
{
    use SalesManageAuditLog;
    public $_leveltb = 'sales_kpi_level';

    /**
     * 销售类型
     * @return array
     */
    public static function getSalesType()
    {
        return [
            1 => '销售顾问',
            2 => '销售主管',
            3 => '销售经理'
        ];
    }

    public function getKeyMap()
    {
        return [
            'sales_type' => '人员类别',
            'sales_level' => '职级',
            'level_order' => '序号',
            'code' => '岗位代码',
            'color' => '颜色'
        ];
    }

    /**
     * 创建销售职级
     * @param $uid
     * @param $type
     * @param $level
     * @param $code
     * @param $color
     * @param $order
     * @return null|int
     */
    public function create($uid, $type, $level, $code, $color, $order)
    {
        $id = Db::master('zd_sales')->insert($this->_leveltb)->cols([
            'sales_type' => $type,
            'sales_level' => $level,
            'level_order' => $order,
            'code' => $code,
            'color' => $color,
            'create_user' => $uid,
            'modify_user' => $uid
        ])->query();
        return $id;
    }

    /**
     * 获取职级配置列表
     * @param string $type
     * @return mixed
     */
    public function getList($type = '')
    {
        $query = Db::slave('zd_sales')->select('id,sales_type,sales_level,level_order,code,color,create_user,create_time')
            ->from($this->_leveltb)
            ->where('is_del=0')
            ->orderByASC(['sales_type', 'level_order']);
        if (empty($type)) {
            return $query->where('is_del=0')->query();
        } else {
            return $query->where('sales_type = :type and is_del=0')
                ->bindValue('type', $type)
                ->query();
        }
    }

    /**
     * 获取配置的销售职级
     * @param $id
     * @return array
     */
    public function getOne($id)
    {
        $info = Db::slave('zd_sales')->select('id,sales_type,sales_level,level_order,code,color')->from($this->_leveltb)
            ->where('id = :id and is_del=0')
            ->bindValue('id', $id)
            ->row();
        return $info;
    }

    /**
     * 根据uid获取对应的职级信息
     * @param $uid
     * @param $month Y-m
     * @return array
     */
    public function getLevelInfoByUid($uid, $month='')
    {
        $key = 'SALES_LEVEL_INFO:' . $uid;
        if(empty($month)) {
            $date = date('Y-m');
            $info = ZdRedis::instance()->get($key);
            if ($info !== FALSE) {
                return $info;
            }
        }else $date = $month;

        $levelId = Db::slave('zd_sales')->select('level')->from('sales_kpi_salary')
            ->where('salesman_id = :uid and data_date = :date and is_delete = 0')
            ->bindValues([
                'uid' => $uid,
                'date' => $date
            ])->single();
        if (empty($levelId)) {
            return [];
        } else {
            $info = $this->getOne($levelId);
            if (!empty($info)) {
                if(empty($month))
                    ZdRedis::instance()->set($key, $info, 3600 * 24);
                return $info;
            } else {
                return [];
            }
        }
    }

    /**
     * 通过人员类别和职级获取信息
     * @param $type
     * @param $level
     * @return mixed
     */
    public function getLevelByTypeLevel($type, $level)
    {
        $list = Db::slave('zd_sales')->select('id')->from($this->_leveltb)
            ->where('sales_type = :sales_type and sales_level=:sales_level and is_del=0')
            ->bindValues([
                'sales_type' => $type,
                'sales_level' => $level
            ])->query();
        return $list;
    }

    /**
     * 修改销售职级
     * @param $id
     * @param $uid
     * @param $type
     * @param $level
     * @param $code
     * @param $color
     * @param $order
     * @return bool
     */
    public function modify($id, $uid, $type, $level, $code, $color, $order)
    {
        $ret = Db::master('zd_sales')->update($this->_leveltb)
            ->cols([
                'sales_type' => $type,
                'sales_level' => $level,
                'level_order' => $order,
                'code' => $code,
                'color' => $color,
                'modify_user' => $uid,
                'modify_time' => date('Y-m-d H:i:s')
            ])
            ->where('id = :id')
            ->bindValue('id', $id)->query();
        return $ret;
    }
}