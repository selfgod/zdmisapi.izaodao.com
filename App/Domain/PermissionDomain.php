<?php
namespace App\Domain;

use App\Model\Common\Permission;
use Base\BaseDomain;

class PermissionDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new Permission();
    }

    /**
     * 用户是否有某功能权限
     * @param $key
     * @param $uid
     * @return bool
     * @throws \Exception
     */
    public function hasFuncPermission($key, $uid)
    {
        if ($this->baseModel->isFounder($uid)) {
            return TRUE;
        }
        $fun = $this->baseModel->getFuncPermission($key);
        if (!empty($fun)) {
            if ($uid_arr_str = $fun['set_user']) {
                $uid_arr = explode(',', $uid_arr_str);
                if (in_array($uid, $uid_arr)) {
                    return TRUE;
                }
            }

            $user_group = $this->baseModel->getMemberRole($uid);
            if (($rid_arr_str = $fun['set_role']) && $user_group) {
                $rid_arr = explode(',', $rid_arr_str);
                foreach ($user_group as $n) {
                    if (in_array($n['role_id'], $rid_arr)) {
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }
}