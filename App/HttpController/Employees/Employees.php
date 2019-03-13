<?php

namespace App\HttpController\Employees;

use App\Domain\Employees\EmployeesDomain;
use App\Model\Common\User;
use Base\PassportApi;

class Employees extends PassportApi
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'queryEmployeesData' => [
                'e_uid' => [
                    'type' => 'int',
                    'default' => 0,
                    'desc' => 'UID'
                ],
                'mobile' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '手机号码'
                ],
                'name' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '用户名真名'
                ],
                'wx_name' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '企业微信名'
                ],
                'wx_userid' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '企业微信ID'
                ],
                'department' => [
                    'type' => 'int',
                    'range' => NULL,
                    'desc' => '部门ID'
                ],
                'gender' => [
                    'type' => 'enum',
                    'range' => [0, 1, 2],
                    'desc' => '性别 0:全部 1:男 2:女'
                ],
                'status' => [
                    'type' => 'enum',
                    'range' => [0, 1, 2],
                    'desc' => '状态'
                ],
                'create_time' => [
                    'type' => 'date',
                    'range' => '',
                    'desc' => '创建时间'
                ],
                'page' => [
                    'type' => 'int',
                    'default' => 1,
                    'min' => 1,
                    'desc' => '页数'
                ],
                'type' => [
                    'type' => 'enum',
                    'require' => TRUE,
                    'range' => ['list', 'count'],
                    'default' => 'list',
                    'desc' => '数据返回类型 list:列表 count:数量'
                ],
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'queryEmployeesInfo' => [
                'e_uid' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'default' => 0,
                    'desc' => 'UID'
                ],
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'queryZDEmployeesInfo' => [
                'mobile' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'default' => '',
                    'desc' => 'UID'
                ],
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'saveEmployees' => [
                'e_uid' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'default' => 0,
                    'desc' => 'UID'
                ],
                'username' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'default' => '',
                    'desc' => '用户名'
                ],
                'realname' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'default' => '',
                    'desc' => '真实姓名'
                ],
                'mobile' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'default' => '',
                    'desc' => '电话'
                ],
                'create_type' => [
                    'type' => 'enum',
                    'range' => [0, 1],
                    'require' => TRUE,
                    'default' => 1,
                    'desc' => '创建方式 1同步企业微信'
                ],
                'wx_name' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '企业微信用户名'
                ],
                'wx_userid' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '企业微信ID'
                ],
                'wx_mobile' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '企业微信电话'
                ],
                'department' => [
                    'type' => 'string',
                    'default' => 0,
                    'desc' => 'UID'
                ],
                'position' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '企业微信职位'
                ],
                'gender' => [
                    'type' => 'enum',
                    'range' => [0, 1, 2],
                    'default' => 0,
                    'desc' => '性别'
                ],
                'email' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '邮件'
                ],
                'role_id' => [
                    'type' => 'string',
                    'default' => '',
                    'desc' => '权限组'
                ]
            ],
            'deleteEmployees' => [
                'e_uid' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'default' => 0,
                    'desc' => 'UID'
                ]
            ],
            'leaveEmployees' => [
                'e_uid' => [
                    'type' => 'int',
                    'require' => TRUE,
                    'default' => 0,
                    'desc' => 'UID'
                ],
                'authClear' => [
                    'type' => 'enum',
                    'range' => [0, 1],
                    'default' => 0,
                    'desc' => '权限清理'
                ],
                'goodsClear' => [
                    'type' => 'enum',
                    'range' => [0, 1],
                    'default' => 0,
                    'desc' => '商品清理'
                ],
                'zaoyuanClear' => [
                    'type' => 'enum',
                    'range' => [0, 1],
                    'default' => 0,
                    'desc' => '早元清理'
                ],
                'xuefenClear' => [
                    'type' => 'enum',
                    'range' => [0, 1],
                    'default' => 0,
                    'desc' => '学分清理'
                ],
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'updateDepartmentAll' => [
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'updateUserAll' => [
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'queryDepartmentData' => [
                'ignore_sign' => true,
                'ignore_auth' => true
            ],
            'regUser' => [
                'mobile' => [
                    'type' => 'string',
                    'require' => TRUE,
                    'default' => '',
                    'desc' => '电话'
                ]
            ]
        ]);
    }

    /**
     * 获取员工列表
     * @throws \Exception
     */
    public function queryEmployeesData()
    {
        $result = (new EmployeesDomain())->queryEmployeesData($this->params);
        $this->returnJson($result);
    }

    /**
     * 获取内部员工信息
     * @throws \Exception
     */
    public function queryEmployeesInfo()
    {
        $result = (new EmployeesDomain())->queryEmployeesInfo($this->params);
        $this->returnJson($result);
    }

    /**
     * 获取内部员工信息
     * @throws \Exception
     */
    public function queryZDEmployeesInfo()
    {
        $result = (new EmployeesDomain())->queryZDEmployeesInfo($this->params);
        $this->returnJson($result);
    }

    /**
     * 保存 - 内部员工信息
     * @throws \Exception
     */
    public function saveEmployees()
    {
        $result = (new EmployeesDomain())->saveEmployees($this->params, $this->request()->getCookieParams());
        $this->returnJson($result);
    }

    /**
     * 删除 - 内部员工
     * @throws \Exception
     */
    public function deleteEmployees()
    {
        $result = (new EmployeesDomain())->deleteEmployees($this->params);
        $this->returnJson($result);
    }

    /**
     * 离职 - 内部员工
     * @throws \Exception
     */
    public function leaveEmployees()
    {
        $result = (new EmployeesDomain())->leaveEmployees($this->params, $this->request()->getCookieParams());
        $this->returnJson($result);
    }

    /**
     * 组织架构一件更新
     * @throws \Exception
     */
    public function updateDepartmentAll(){
        $result = (new EmployeesDomain())->updateDepartmentAll();
        $this->returnJson($result);
    }

    /**
     * 用户企业微信信息一件更新
     * @throws \Exception
     */
    public function updateUserAll(){
        $result = (new EmployeesDomain())->updateUserAll();
        $this->returnJson($result);
    }

    /**
     * 获取组织架构列表
     * @throws \Exception
     */
    public function queryDepartmentData()
    {
        $result = (new EmployeesDomain())->queryDepartmentData();
        $this->returnJson($result);
    }

    /**
     * 获得所有组
     * @throws \Exception
     */
    public function regUser()
    {
        $mobile = $this->params['mobile'];
        if($mobile){
            $result = (new User())->regUser($mobile);
            $this->returnJson($result);
        }
    }
}