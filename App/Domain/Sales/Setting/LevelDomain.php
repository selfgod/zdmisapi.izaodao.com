<?php
namespace App\Domain\Sales\Setting;


use App\Model\Common\User;
use App\Model\Sales\Setting\SalesLevelModel;
use Base\BaseDomain;

/**
 * 职级配置
 * Class LevelDomain
 * @package App\Domain\Sales\Manage\Setting
 */
class LevelDomain extends BaseDomain
{

    public function __construct()
    {
        $this->baseModel = new SalesLevelModel();
    }

    /**
     * 创建销售职级
     * @param $uid
     * @param $type
     * @param $level
     * @param $code
     * @param $color
     * @param $order
     * @return int|null
     */
    public function createSalesLevel($uid, $type, $level, $code, $color, $order)
    {
        if ($this->hasDupLevel($type, $level)) {
            return -1;
        }
        $ret = $this->baseModel->create($uid, $type, $level, $code, $color, $order);
        return $ret;
    }

    /**
     * 销售类型
     * @return array
     */
    public function getSalesType()
    {
        return SalesLevelModel::getSalesType();
    }

    /**
     * 获取列表
     * @return mixed
     */
    public function getList()
    {
        $types = $this->getSalesType();
        $list = $this->baseModel->getList();
        $data = [];
        if (!empty($list)) {
            $uids = array_column($list, 'create_user');
            $users = (new User())->getUsersByUids($uids);
            foreach ($users as $user) {
                $data[$user['uid']] = $user['username'];
            }
        }

        foreach ($list as $index => $value) {
            $list[$index]['sales_type'] = $types[$value['sales_type']];
            $list[$index]['create_user'] = $data[$value['create_user']];
        }
        return $list;
    }

    /**
     * 通过人员类型获取配置信息
     * @param $type
     * @return mixed
     */
    public function getListByType($type)
    {
        $list = $this->baseModel->getList($type);
        return $list;
    }

    /**
     * 获取某个职级信息
     * @param $id
     * @return array
     */
    public function getLevelInfo($id)
    {
        return $this->baseModel->getOne($id);
    }

    /**
     * 验证是否有重复的职级配置
     * @param $type
     * @param $level
     * @param string $id
     * @return bool
     */
    protected function hasDupLevel($type, $level, $id = '')
    {
        $list = $this->baseModel->getLevelByTypeLevel($type, $level);
        $ids = array_column($list, 'id');
        if ($id !== '') {
            return !empty($list) && !in_array($id, $ids);
        } else {
            return !empty($list);
        }
    }

    /**
     * 更新职级
     * @param $id
     * @param $uid
     * @param $type
     * @param $level
     * @param $code
     * @param $color
     * @param $order
     * @return int
     */
    public function updateLevel($id, $uid, $type, $level, $code, $color, $order)
    {
        if ($this->hasDupLevel($type, $level, $id)) {
            return -1;
        }
        $this->baseModel->addAuditLog($this->baseModel->_leveltb, $id, $uid, [
            'sales_type' => $type,
            'sales_level' => $level,
            'level_order' => $order,
            'code' => $code,
            'color' => $color
        ]);
        return $this->baseModel->modify($id, $uid, $type, $level, $code, $color, $order);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAudit($id)
    {
        $info = $this->baseModel->getAuditInfo($this->baseModel->_leveltb, $id);
        return $info;
    }

    /**
     * 获取用户对应的职级信息
     * @param $uid
     * @param $month Y-m
     * @return array
     */
    public function getLevelInfoByUid($uid, $month='')
    {
        return $this->baseModel->getLevelInfoByUid($uid, $month);
    }

}