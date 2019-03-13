<?php

namespace App\Domain\Employees;

use App\Domain\Permission\ManageDomain;
use App\Model\Common\User;
use App\Model\Employees\EmployeesModel;
use App\Model\Common\Permission;
use Base\BaseDomain;
use Base\HttpClient;
use Base\WorkWxApi;
use EasySwoole\Config;
use EasySwoole\Core\Component\Logger;
use PhpParser\Error;

class EmployeesDomain extends BaseDomain
{
    /**
     * 获取内部员工列表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryEmployeesData(array $params)
    {
        $bindValues = [];
        $where = '1 = 1';
        if ($params['e_uid'] > 0) {
            $where .= ' and uid = :uid';
            $bindValues['uid'] = $params['e_uid'];
        }
        if (!empty($params['name'])) {
            $where .= " and ( username like :name1 or realname like :name2 ) ";
            $bindValues['name1'] = '%' . $params['name'] . '%';
            $bindValues['name2'] = '%' . $params['name'] . '%';
        }
        if (!empty($params['mobile'])) {
            $where .= ' and mobile = :mobile';
            $bindValues['mobile'] = $params['mobile'];
        }
        if (!empty($params['wx_name'])) {
            $where .= " and ( qy_name like :wx_name ) ";
            $bindValues['wx_name'] = '%' . $params['wx_name'] . '%';
        }
        if (!empty($params['department'])) {
            $workWxApi = new WorkWxApi();
            $res = $workWxApi->queryUsersByDepartmentId($params['department']);
            $res_arr = json_decode($res,true);
            if(in_array($res_arr['errcode'], ['40014', '42001'])){
                $res = $workWxApi->queryUsersByDepartmentId($params['department']);
                $res_arr = json_decode($res,true);
            }
            if($res_arr['userlist']){
                $where .= " and qy_userid in ( '0' ";
                foreach ($res_arr['userlist'] as $n){
                    $where .= ",'" . $n['userid'] . "' ";
                }
                $where .= ') ';
            }
        }
        if (!empty($params['create_time'])) {
            $where .= ' and create_time >= :start and create_time <= :end ';
            $bindValues['start'] = date('Y-m-d 00:00:00', strtotime($params['create_time']));
            $bindValues['end'] = date('Y-m-d 23:59:59', strtotime($params['create_time']));
        }
        if ($params['wx_userid'] > 0) {
            $where .= ' and qy_userid = :wx_userid';
            $bindValues['wx_userid'] = $params['wx_userid'];
        }
        if(in_array($params['gender'], ['1','2'])){
            $where .= ' and gender = :gender';
            $bindValues['gender'] = $params['gender'];
        }
        if(in_array($params['status'], ['0','1','2'])){
            if(intval($params['status']) > 0){
                $where .= ' and enable = :status';
                $bindValues['status'] = intval($params['status']);
            }else{
                $where .= ' and enable < 1 ';
            }
        }
        $where .= ' and is_del = 0 ';
        $employeesModel = new EmployeesModel();
        $count = $employeesModel->getEmployeesCount($where, $bindValues);
        if ($params['type'] === 'list') {
            $employeesList = [];
            if ($count > 0) {
                $employeesList = $employeesModel->getEmployeesList($where, $bindValues, $params['page'], 20);
            }
            $data['employeesList'] = $employeesList;
        } else {
            $data['employeesCount'] = $count;
        }
        return $data;
    }

    /**
     * 获取内部员工信息
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryEmployeesInfo(array $params)
    {
        $info = [];
        if ($params['e_uid'] > 0) {
            $employeesModel = new EmployeesModel();
            $info =  $employeesModel->getEmployeesInfo($params['e_uid']);
        }
        $data['employeesInfo'] = $info;
        return $data;
    }

    /**
     * 获取内部员工信息
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryZDEmployeesInfo(array $params)
    {
        $info = [];
        if ($params['mobile']) {
            $employeesModel = new EmployeesModel();
            $info =  $employeesModel->getZDEmployeesInfo($params['mobile']);
        }
        $data['zdEmployeesInfo'] = $info;
        return $data;
    }

    /**
     * 保存内部员工信息
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function saveEmployees(array $params, array $cookie)
    {
        $res_s = false;
        if ($params['e_uid']) {
            if($params['create_type'] == 1){
                $data["name"] = $params['wx_name'];
                $data["userid"] = $params['wx_userid'];
                $data["mobile"] = $params['wx_mobile'];
                $data["department"] = json_decode($params['department'], true);
                $data["position"] = $params['position']?:'';
                $data["gender"] = $params['gender']?:0;
                $data["email"] = $params['email']?:'';
                $data["enable"] = $params['enable'] = 1;
                $res_wx_user_json = $this->queryQyWxUser($params['wx_userid']);
                if(isset($res_wx_user_json['userid']) && $res_wx_user_json['userid']){
                    $res = $this->updateQyWxUser($data);
                }else{
                    $res = $this->createQyWxUser($data);
                }
            }

            if(intval($params['create_type']) === 0 || intval($res['errcode']) === 0){
                $employeesModel = new EmployeesModel();
                try {
                    $employeesModel->saveCompanyUserInfoUid($params);
                    $res_s =  true;
                } catch (Error $error) {
                    $res = $error->getRawMessage();
                    $res_s =  false;
                }

                //更新jh_common_member_profile 企业微信信息
                if(isset($params['wx_userid']) && $params['wx_userid']){
                    $employeesModel->updateZdMemberProfile($params['e_uid'], $params['wx_userid'], $params['wx_name'], $params['wx_mobile']);
                }
            }

//            $permission = new Permission();
//            $info =  $permission->getUserGroup($params['e_uid']);
//            if(intval($info['cpgroupid']) !== intval($params['role_id'])){
//                if(intval($params['role_id']) > 0){
//                    if(!empty($info) && intval($info['cpgroupid']) > 0){
//                        $permission->delUserGroup($params['e_uid'], $info['cpgroupname'], ($params['userInfo']['user_name']?:''));
//                    }
//                    $permission->addUserGroup($params['e_uid'], $params['role_id'], ($params['userInfo']['user_name']?:''));
//                }else{
//                    $permission->delUserGroup($params['e_uid'], $info['cpgroupname'], $params['userInfo']['user_name']?:'');
//                }
//            }
            $role_ids = json_decode($params['role_id'], true);

            if( !empty($role_ids)){
//                if($params['create_type'] == 1){
//                    $user = new User();
//                    $userInfo = $user->getUserInfo($params['e_uid']);
//                    //调用JAVA入职接口  暂时关闭
//                    $res = HttpClient::post(
//                        Config::getInstance()->getConf('LINK_HOST_KEEPERAPI') . 'rest/v1/sysUsers',
//                        '',[
//                        'headers' => ['Content-Type' => 'application/json'],
//                        'body' => \GuzzleHttp\json_encode([
//                                'sysUserId' =>$userInfo['open_id'],
//                                'workEmail' => !empty($params["email"]) ? $params["email"] : $params["e_uid"] . '@166.com',
//                                'sysRoleIdList' => $role_ids
//                            ], JSON_UNESCAPED_UNICODE),
//                            'timeout' => '6.0',
//                            'cookie' => $cookie
//                        ]
//                    );
//                }

                (new ManageDomain())->addZdmisMember('', $params['uid'], $params['email'], 1,  0, $role_ids, $params['e_uid']);
            }

        }
        return $res_s ? ['addType' => true, 'data' => $res] : ['addType' => $res_s, 'data' => $res];
    }

    /**
     * 删除 - 内部员工
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function deleteEmployees(array $params)
    {
        $employeesModel = new EmployeesModel();
        $res_s =  $employeesModel->delCompanyUserInfo($params['e_uid']);
        return ["delType" => $res_s];
    }

    /**
     * 离职 - 内部员工
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function leaveEmployees(array $params, array $cookie)
    {
        $res = ['leaveType' => false];
        if($params['e_uid']){
            $employeesModel = new EmployeesModel();
            $userInfo = $employeesModel->getEmployeesInfo($params['e_uid']);
            if($userInfo['qy_userid']){
                $res_1 = $this->deleteQyWxUser($userInfo['qy_userid']);
                $employeesModel->leaveCompanyUserInfo($params['e_uid']);
            }
            if($params['authClear']){
//                $permission = new Permission();
//                $info =  $permission->getUserGroup($params['e_uid']);
//                if($info && intval($info['cpgroupid']) > 0){
//                    $permission->delUserGroup($params['e_uid'], $info['cpgroupname'], ($params['userInfo']['user_name']?:''));
//                }

                //调用JAVA入职接口  暂时关闭
//                $user = new User();
//                $userInfo = $user->getUserInfo($params['e_uid']);
//                HttpClient::post(
//                    Config::getInstance()->getConf('LINK_HOST_KEEPERAPI') . 'rest/v1/sysUsers/dimission',
//                    ['sysUserId' =>$userInfo['open_id']],[
//                        'timeout' => '6.0',
//                        'cookie' => $cookie
//                    ]
//                );

                //调用zdmisapi接口 清除用户全部权限
                $permission = new Permission();
                $permission->delRoleToMember($params['e_uid'], 0, true);
            }
            if($params['goodsClear']){
                $employeesModel->goodsClear($params['e_uid']);
            }
            if($params['zaoyuanClear']){
                $employeesModel->zaoyuanClear($params['e_uid']);
            }
            if($params['xuefenClear']){
                $employeesModel->xuefenClear($params['e_uid']);
            }

            $res = $res_1 ? ['leaveType' => true] : ['leaveType' => false];
        }
        return $res;
    }

    /**
     * 企业微信 - 获取员工详细信息
     * @param $userId
     * @return array
     */
    public function queryQyWxUser($userId)
    {
        $workWxApi = new WorkWxApi();
        $res = $workWxApi->queryUserById($userId);
        $res_arr = json_decode($res,true);
        if(in_array($res_arr['errcode'], ['40014', '42001'])){
            $res = $workWxApi->queryUserById($userId);
            $res_arr = json_decode($res,true);
        }
        return $res_arr;
    }

    /**
     * 企业微信 - 创建员工
     * @param $data
     * @return bool
     */
    public function createQyWxUser(array $params)
    {
        $workWxApi = new WorkWxApi();
        $res = $workWxApi->createUser($params);
        $res_arr = json_decode($res,true);
        if(in_array($res_arr['errcode'], ['40014', '42001'])){
            $res = $workWxApi->createUser($params);
            $res_arr = json_decode($res,true);
        }
        return $res_arr;
    }

    /**
     * 企业微信 - 更新员工
     * @param $data
     * @return bool
     */
    public function updateQyWxUser($data)
    {
        $workWxApi = new WorkWxApi();
        $res = $workWxApi->updateUser($data);
        $res_arr = json_decode($res,true);
        if(in_array($res_arr['errcode'], ['40014', '42001'])){
            $res = $workWxApi->updateUser($data);
            $res_arr = json_decode($res,true);
        }
        return $res_arr;
    }

    /**
     * 企业微信 - 删除员工
     * @param $userId
     * @return bool
     */
    public function deleteQyWxUser($userId)
    {
        $workWxApi = new WorkWxApi();
        $res = $workWxApi->deleteUserById($userId);
        $res_arr = json_decode($res,true);
        if(in_array($res_arr['errcode'], ['40014', '42001'])){
            $res = $workWxApi->deleteUserById($userId);
            $res_arr = json_decode($res,true);
        }
        if(intval($res_arr['errcode']) === 0){
            return true;
        }
        return false;
    }

    /**
     * 组织架构一件更新
     * @return bool
     */
    public function updateDepartmentAll(){
        $workWxApi = new WorkWxApi();
        $res = $workWxApi->getDepartmentList();
        $b_arr = json_decode($res,true);
        if(in_array($b_arr['errcode'], ['40014', '42001'])){
            $res = $workWxApi->getDepartmentList();
            $b_arr = json_decode($res,true);
        }
        if($b_arr['department']){
            $employeesModel = new EmployeesModel();
            foreach ($b_arr['department'] as $n){
                $employeesModel->saveDepartmentInfo($n);
            }
            return true;
        }
        return false;
    }

    /**
     * 用户企业微信信息一件更新
     * @return bool
     */
    public function updateUserAll(){
        $workWxApi = new WorkWxApi();
        $res = $workWxApi->queryUsersByDepartmentId(1, 1, 2);
        $b_arr = json_decode($res,true);
        if(in_array($b_arr['errcode'], ['40014', '42001'])){
            $res = $workWxApi->queryUsersByDepartmentId(1, 1, 2);
            $b_arr = json_decode($res,true);
        }
        if($b_arr['userlist']){
            $employeesModel = new EmployeesModel();
            $ret = [];
            foreach ($b_arr['userlist'] as $n){
                $res = $employeesModel->saveCompanyUserInfo([
                    'mobile' => $n['mobile'],
                    'qy_userid' => $n['userid'],
                    'qy_name' => $n['name'],
                    'department' => $n['department']?json_encode($n['department']):'',
                    'position' => $n['position']?:'',
                    'gender' => $n['gender']?intval($n['gender']):0,
                    'email' => $n['email'] ?:'',
                    'avatar' => $n['avatar'] ?:'',
                    'enable' => intval($n['enable'])
                ]);
                if($res === FALSE){
                    $ret[] = $n;
                }
            }
            return $ret;
        }
        return false;
    }

    /**
     * 组织架构一件更新
     * @return array
     */
    public function queryDepartmentData(){
        $employeesModel = new EmployeesModel();
        return $employeesModel->getDepartmentAll();
    }
}