<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2018/11/16
 * Time: 上午8:41
 */
namespace App\HttpController\Sales\Setting;

use App\Domain\Sales\Setting\TeamStructureDomain;
use Base\BaseController;

class TeamStructure extends BaseController
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [

            'getRegionForBusinessType' => [
                'business_type' => ['type' => 'string', 'require' => true, 'desc' => '业务类型'],
            ],
            'getSalesmanTypeForBusinessType'=>[
                'business_type'=>['type'=>'string', 'require'=>true, 'desc'=>'业务类型'],
            ],
            'getDept' => [
                'year_month'=>['type'=>'string', 'require'=>true, 'desc'=>'年月，例如2018-11'],
                'business_type' => ['type' => 'string', 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'desc' => '区域'],
            ],
            'getTeam' => [
                'year_month'=>['type'=>'string', 'require'=>true, 'desc'=>'年月，例如2018-11'],
                'business_type' => ['type' => 'string', 'desc' => '业务类型'],
                'region' => ['type' => 'string', 'desc' => '区域'],
                'parent_id' => ['type' => 'int', 'desc' => '部门id'],
            ],
            'getSalesman' => [
                'year_month'=>['type'=>'string', 'require'=>true, 'desc'=>'年月，例如2018-11'],
                'parent_id' => ['type' => 'int', 'desc' => '团队id'],
                'top_id' => ['type' => 'int', 'desc' => '部门id'],
            ],
            'addStructure' => [
                'uid' => ['type' => 'string', 'require' => true, 'desc' => 'uid'],
                'id' => ['type' => 'string', 'require' => true, 'desc' => '组织结构id'],
                'code' => ['type' => 'string', 'default' => '', 'desc' => '编码'],
                'parent_id' => ['type' => 'string', 'require' => true, 'desc' => '父菜单id'],
                'parent_code' => ['type' => 'string', 'default' => '', 'desc' => '父菜单编码'],
                'name' => ['type' => 'string', 'require' => true, 'desc' => '名称'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'leaf' => ['type' => 'int', 'require' => true, 'desc' => ''],
            ],
            'updateStructure' => [
                'uid' => ['type' => 'string', 'require' => true, 'desc' => 'uid'],
                'id' => ['type' => 'string', 'require' => true, 'desc' => '组织结构id'],
                'name' => ['type' => 'string', 'require' => true, 'desc' => '名称'],
                'region' => ['type' => 'string', 'require' => true, 'desc' => '区域'],
                'leaf' => ['type' => 'int', 'require' => true, 'desc' => ''],
            ],
            'delStructure' => [
                'uid' => ['type' => 'string', 'require' => true, 'desc' => 'uid'],
                'id' => ['type' => 'string', 'require' => true, 'desc' => '组织结构id'],
            ]
        ]);
    }

    public function getRegionForBusinessType()
    {
        $result = (new TeamStructureDomain())->getRegionForBusinessType($this->params['business_type']);
        $this->returnJson($result);
    }

    public function getSalesmanTypeForBusinessType()
    {
        $result = (new TeamStructureDomain())->getSalesmanTypeForBusinessType($this->params['business_type']);
        $this->returnJson($result);
    }

    public function getDept()
    {
        $result = (new TeamStructureDomain())->getDept($this->params);
        $this->returnJson($result);
    }

    public function getTeam()
    {
        $result = (new TeamStructureDomain())->getTeam($this->params);
        $this->returnJson($result);
    }

    public function getSalesman()
    {
        $result = (new TeamStructureDomain())->getSalesman($this->params);
        $this->returnJson($result);
    }

    /**
     * 增加销售架构
     */
    public function addStructure()
    {
        $id = (new TeamStructureDomain())->addStructure($this->params['id'], $this->params['uid'], $this->params['code'],
            $this->params['parent_id'], $this->params['parent_code'], $this->params['name'], $this->params['region'], $this->params['leaf']);
        if ($id === 0) {
            return $this->returnJson($id, '插入失败', 500);
        } else {
            return $this->returnJson($id);
        }
    }

    /**
     * 更新销售架构
     */
    public function updateStructure()
    {
        $ret = (new TeamStructureDomain())->updateStructure($this->params['id'], $this->params['uid'], $this->params['name'],
            $this->params['region'], $this->params['leaf']);
        return $this->returnJson($ret);
    }

    /**
     * 删除销售架构
     */
    public function delStructure()
    {
        $ret = (new TeamStructureDomain())->delStructure($this->params['id'], $this->params['uid']);
        return $this->returnJson($ret);
    }
}
