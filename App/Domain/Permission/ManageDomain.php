<?php

namespace App\Domain\Permission;

use App\Model\Common\Manage;
use App\Model\Common\Permission;
use App\Model\Common\User;
use Base\BaseDomain;
use Base\Exception\BadRequestException;
use Psr\Log\NullLogger;

class ManageDomain extends BaseDomain
{

    /**
     * 获取角色数据
     * @return array
     */
    public function getRoleALL()
    {
        $project = (new Manage())->getRoleALL();
        return $project ?: [];
    }

    /**
     * 根据ID获取角色数据
     * @return array
     */
    public function getRole(array $params)
    {
        $project = (new Manage())->getRole($params['id']);
        return $project ?: [];
    }

    /**
     * 获取角色数据
     * @return array
     */
    public function getManagerList(array $params)
    {
        $project = (new Manage())->getManagerList($params['user_name'], $params['limit'], $params['page']);
        return $project ?: [];
    }

    /**
     * 获取角色数据
     * @return array
     */
    public function getManagerCount(array $params)
    {
        $project = (new Manage())->getManagerCount($params['user_name']);
        return $project ?: 0;
    }


    /**
     * 根据ID获取角色数据
     * @return array
     */
    public function getManagerInfo($open_id)
    {
        $project = (new Manage())->getManagerInfo($open_id);
        if (empty($project)) {
            return [];
        }
        if (!empty($project)) {
            $result = [];
            foreach ($project as $key=>$val) {
                $result['uid'] = $val['uid'];
                $result['open_id'] = $val['open_id'];
                $result['user_name'] = $val['user_name'];
                $result['real_name'] = $val['real_name'];
                $result['email'] = $val['email'];
                $result['org_sale'] = $val['org_sale'];
                $result['role'][$key]['id'] = $val['role_id'];
                $result['role'][$key]['role_name'] = $val['role_name'];
                $result['role'][$key]['remark'] = $val['remark'];
            }
        }
        return $result ?: [];
    }

    /**
     * 更新管理员修改增加
     * @param $open_id
     * @param $uid
     * @param $email
     * @param $status
     * @param $org_sale
     * @param $role_ids
     * @param $zd_uid
     * @return bool
     */
    public function addZdmisMember($open_id, $uid, $email, $status, $org_sale, $role_ids, $zd_uid)
    {
        $model = new Manage();
        $user = new User();
        if (!empty($zd_uid)) {
            $res = $user->getUserInfo($zd_uid);
            $open_id = $res ? $res['open_id'] : '';
            $email = $res ? $res['email'] : '';
            $realName = $res ? $res['real_name'] : '';
        } else {
            $res = $user->getUserForOpenId($open_id);
            $realName = $res ? $res['real_name'] : '';
        }
        if (empty($res)) {
            return 0;
        }
        if ($model->getZdmisMemberId($open_id, FALSE)) {
            $result = $model->updateZdmisMember($open_id, [
                'user_name'=> $res['username'],
                'real_name'=> $realName,
                'email'=> $email,
                'status'=> $status,
                'mobile'=> $res['mobile'],
                'org_sale'=> $org_sale,
                'modify_time'=> date('Y-m-d H:i:s'),
                'modify_user'=> $uid,
                'is_del'=>0
            ]);
        }else{
            $result = $model->insertZdmisMember(['uid'=> $res['uid'],
                'open_id'=> $open_id,
                'user_name'=> $res['username'],
                'real_name'=> $realName,
                'email'=> $email,
                'status'=> $status,
                'mobile'=> $res['mobile'],
                'org_sale'=> $org_sale,
                'create_time'=> date('Y-m-d H:i:s'),
                'create_user'=> $uid
            ]);
        }
        if (!empty($role_ids)) {
            $this->updateMemberRole($open_id, $role_ids);
        }
        return $result;
    }

    /**
     * 更新管理员修改增加
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function updateZdmisMember(array $params)
    {
        $model = new Manage();
        $uid = intval($params['uid']);
        $open_id = intval($params['open_id']);
        $row = $model->getZdmisMemberId($open_id);
        if (!empty($row)) {
            if (!empty($params['user_name'])) {
                $data['user_name'] = $params['user_name'];
            }
            if (!empty($params['real_name'])) {
                $data['real_name'] = $params['real_name'];
            }
            if (!empty($params['email'])) {
                $data['email'] = $params['email'];
            }
            if (!empty($params['mobile'])) {
                $data['mobile'] = $params['mobile'];
            }
            if (!empty($params['org_sale'])) {
                $data['org_sale'] = $params['org_sale'];
            }
            if (!empty($params['role_ids'])) {
                $this->updateMemberRole($open_id, $params['role_ids']);
            }
            $data['modify_time'] = date('Y-m-d H:i:s');
            $data['modify_user'] = $uid;
            $res = $model->updateZdmisMember($open_id, $data);
        } else {
            return FALSE;
        }
        return $res;
    }

    /**
     * 删除管理员
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function delZdmisMember(array $params)
    {
        $model = new Manage();
        $open_id = intval($params['open_id']);
        if ($open_id) {
            $row = $model->getZdmisMemberId($open_id);
            if (empty($row)) {
                throw new BadRequestException('数据异常');
            }
            return $model->delZdmisMember($open_id, [
                'is_del' => 1,
                'modify_user' => intval($params['uid']),
                'modify_time' => date('Y-m-d H:i:s')
            ]);
        }
        return FALSE;
    }

    /**
     * 更新管理员修改增加
     * @param array $params
     * @return bool
     * @throws BadRequestException
     */
    public function updateOrgSale(array $params)
    {
        $model = new Manage();
        $open_id = intval($params['open_id']);
        $org_sale = $params['org_sale'];
        $row = $model->getZdmisMemberId($open_id);
        if (!empty($row)) {
            $data['org_sale'] = $params['org_sale'] ? $params['org_sale'] : 0;
            $data['modify_time'] = date('Y-m-d H:i:s');
            $data['modify_user'] = intval($params['uid']);
            $res = $model->updateOrgSale($open_id, $data);
        } else {
            return FALSE;
        }
        return $res;
    }

    /**
     * 设置管理员角色
     * @param $openId
     * @param $roleIds
     * @return bool
     */
    public function updateMemberRole($openId, $roleIds)
    {
        if (count($roleIds) === 1 && $roleIds[0] === '') {
            $roleIds = [];
        }
        $uid = (new User())->getUidByOpenId($openId);
        $model = new Permission();
        $model->delRoleToMember($uid, 0, TRUE);
        if (!empty($roleIds)) {
            foreach ($roleIds as $roleId) {
                $model->addRoleToMember($uid, $openId, $roleId);
            }
        }
        return TRUE;
    }

}