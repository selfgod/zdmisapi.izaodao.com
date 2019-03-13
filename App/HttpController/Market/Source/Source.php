<?php

namespace App\HttpController\Market\Source;

use App\Domain\Market\Source\CallInDomain;
use Base\BaseController;
use App\Domain\Market\Source\SourceDomain;
use EasySwoole\Core\Component\Logger;
use App\Domain\Market\Source\LevelOneSourceDomain;

/**
 * 数据入Crm，一类v2，动作库，资源库
 * Class Source
 * @package App\HttpController\Market\Launchadvent
 */
class Source extends BaseController
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'setSource' => [
                'ignore_sign' => true,
                'ignore_auth' => true,
                'platform' => ['type' => 'string', 'default' => 0],
                'source' => ['type'=> 'string','default' => ''],
                'tel' => ['type'=> 'string','default' => ''],
                'tag' => ['type'=> 'string','default' => ''],
                'cat' => ['type'=> 'string','default' => ''],
                'openId' => ['type'=> 'string','default' => ''],
                'uid' => ['type'=> 'int','default' => 0],
                'keywords' => ['type'=> 'string','default' => ''],
                'lang_type' => ['type'=> 'string','default' => ''],
                'ip' => ['type'=> 'string','default' => ''],
                'getinfo'=>['type'=> 'string','default' => ''],
                'dateline' => ['type'=> 'string','default' => ''],
                'operate' => ['type'=> 'string','default' => ''],
                'add_res' => ['type'=> 'string','default' => ''],
                'crm_data' => ['type'=> 'array','default' => []],
                'crm_user' => ['type'=> 'array','default' => []],
            ],
            'importCrm' =>[
                'ignore_sign' => true,
                'ignore_auth' => true,
                'start_time'=>['type'=> 'string','default' => ''],
                'end_time'=>['type'=> 'string','default' => ''],
            ],
            'addCrmData' => [
                'ignore_sign' => true,
                'ignore_auth' => true,
                'platform' => ['type' => 'string', 'default' => 0],
                'source' => ['type'=> 'string','default' => ''],
                'tel' => ['type'=> 'string','default' => ''],
                'tag' => ['type'=> 'string','default' => ''],
                'cat' => ['type'=> 'string','default' => ''],
                'openId' => ['type'=> 'string','default' => ''],
                'uid' => ['type'=> 'int','default' => 0],
                'keywords' => ['type'=> 'string','default' => ''],
                'lang_type' => ['type'=> 'string','default' => ''],
                'ip' => ['type'=> 'string','default' => ''],
                'getinfo'=>['type'=> 'string','default' => ''],
                'dateline' => ['type'=> 'string','default' => ''],
                'operate' => ['type'=> 'string','default' => ''],
                'interCode' => ['type'=> 'string','default' => '']
            ],
            'assignCallIn'=>[
                'ignore_auth' => true,
                'ignore_sign' => true,
                'data_id'=>['type'=> 'int','default' => 0],
                'call_id'=>['type'=> 'int','default' => 0],
                'lang_type'=>['type'=> 'string','default' => ''],
                'getinfo'=>['type'=> 'string','default' => ''],
                'uid'=>['type'=> 'int','default' => 0],
                'mobile'=>['type'=> 'string','default' => ''],
                'qq'=>['type'=> 'string','default' => ''],
                'wechat'=>['type'=> 'string','default' => ''],
                'memo'=>['type'=> 'string','default' => ''],
                'tag'=>['type'=> 'string','default' => ''],
                'dateline'=>['type'=> 'string','default' => ''],
            ],
            'assignCallInIds'=>[
                'ignore_auth' => true,
                'ignore_sign' => true,
                'ids_str'=>['type'=>'array', 'require'=>true],
            ],
            'getConsultExpire'=>[
                'ignore_auth' => true,
                'ignore_sign' => true,
                'tel'=>['type'=> 'string','default' => ''],
                'lang_type'=>['type'=> 'string','default' => ''],
            ]
        ]);
    }

    function assignCallInIds()
    {
        $Call_in = new CallInDomain();
        list($state, $msg) = $Call_in->assignCallIn($this->params['ids_str']);
        return $this->returnJson(intval($state), $msg);
    }

    function assignCallIn()
    {
        if(!empty($this->params['mobile'])||!empty($this->params['wechat'])||!empty($this->params['qq'])) {
            $Call_in = new CallInDomain();
            $res = $Call_in->assign($this->params);
            return $this->returnJson(intval($res), $Call_in->getErrorMsg());
        }
        return $this->returnJson(0,'miss param mobile or wechat or qq');
    }

    /**
     * 收集资源
     * @throws \Exception
     */
    public function setSource(){
        $SourceDomain = new SourceDomain();
        $param = [
            'platform'=>$this->params['platform'],
            'source'=>$this->params['source'],
            'tel'=>$this->params['tel'],
            'tag'=>$this->params['tag'],
            'cat'=>$this->params['cat'],
            'openId'=>$this->params['openId'],
            'uid'=>$this->params['uid'],
            'keywords'=>$this->params['keywords'],
            'lang_type'=>$this->params['lang_type'],
            'ip'=>$this->params['ip'],
            'getinfo'=>$this->params['getinfo'],
            'dateline'=>$this->params['dateline'],
            'operate'=>$this->params['operate'],

            'add_res'=>$this->params['add_res'],
            'crm_data'=>$this->params['crm_data'],
            'crm_user'=>$this->params['crm_user'],
        ];

        $crm_res = [
            'add_res'=>$param['add_res'],
            'data'=>$param['crm_data'],
            'user'=>$param['crm_user'],
        ];
        if(!empty($crm_res['add_res'])){
//            logger::getInstance()->log('开始入库流程前的准备数据:'.json_encode($param));
            $SourceDomain->addDataIntoSource($crm_res,$param);
            $crm_res['msg'] = '插入成功';
            $crm_res['state'] = 1;
        }else{
            $crm_res['msg'] = '插入失败';
            $crm_res['state'] = 0;
        }
        $this->returnJson($crm_res['state'],$crm_res['msg']);
    }

    /**
     * 数据入库
     * @throws \Exception
     */
    public function importSource(){
        $crm_res = [
            'add_res'=>$this->params['crm_id'],
            'data'=>[
                'uid'=>$this->params['uid'],
                'name'=>$this->params['name'],
                'tel'=>$this->params['tel'],
                'source'=>$this->params['source'],
                'platform'=>$this->params['platform'],
                'lang_type'=>$this->params['lang_type'],
                'dateline'=>$this->params['dateline'],
                'cat'=>$this->params['cat'],
                'tag'=>$this->params['tag']?$this->params['tag']:'',
                'ctag0'=>$this->params['ctag0']?$this->params['ctag0']:'',
                'ctag1'=>$this->params['ctag1']?$this->params['ctag1']:'',
                'ctag2'=>$this->params['ctag2']?$this->params['ctag2']:'',
                'ctag3'=>$this->params['ctag3']?$this->params['ctag3']:''
            ],
            'user'=>['uid'=>$this->params['uid'], 'username'=>$this->params['username']]
        ];
        $param = [
            'cat'=>$this->params['cat'],
            'lang_type'=>$this->params['lang_type'],
            'platform'=>$this->params['platform'],
            'tel'=>$this->params['tel'],
            'source'=>$this->params['source'],
            'tag'=>$this->params['tag'],
        ];
        $SourceDomain = new SourceDomain();
        $SourceDomain->addDataIntoSource($crm_res,$param);
    }

    /**
     * 数据进入crm
     * @throws \Exception
     */
    public function addCrmData(){
        $SourceDomain = new SourceDomain();
        $param = [
            'platform'=>$this->params['platform'],
            'source'=>$this->params['source'],
            'tel'=>$this->params['tel'],
            'tag'=>$this->params['tag'],
            'cat'=>$this->params['cat'],
            'openId'=>$this->params['openId'],
            'uid'=>$this->params['uid'],
            'keywords'=>$this->params['keywords'],
            'lang_type'=>$this->params['lang_type'],
            'ip'=>$this->params['ip'],
            'getinfo'=>$this->params['getinfo'],
            'dateline'=>$this->params['dateline'],
            'operate'=>$this->params['operate'],
            'interCode' => $this->params['interCode']
        ];
        $crm_res = $SourceDomain->addDataIntoCrm($param);
        if(!empty($crm_res['add_res']) && !empty($crm_res['interCode'])){
//            logger::getInstance()->log('开始入库流程前的准备数据:'.json_encode($param));
            $SourceDomain->addDataIntoSource($crm_res,$param);
            $crm_res['msg'] = '插入成功';
            $crm_res['state'] = 1;
        }else{
            $crm_res['msg'] = '插入失败';
            $crm_res['state'] = 0;
        }
        $this->returnJson($crm_res['state'],$crm_res['msg']);
    }

    /**
     * 获取数据保护期
     * @throws \Exception
     */
    public function getConsultExpire(){
        $LevelOneSourceDomain = new LevelOneSourceDomain();
        $mobile = $this->params['tel'];
        $lang_type = $this->params['lang_type'];
        $res = $LevelOneSourceDomain->getConsultExpire($mobile, $lang_type);
        $this->returnJson($res);
    }
}