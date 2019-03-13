<?php

namespace App\HttpController\Market\Source;

use App\Domain\Market\Source\LevelOneSourceDomain;
use Base\BaseController;

/**
 * 入库
 * Class GenerateSource
 * @package App\HttpController\Market\Launchadvent
 */
class LevelOne extends BaseController
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'setLevelOneSource' => [
                'ignore_sign' => true,
                'ignore_auth' => true,
                'cat' => ['type'=> 'string','default' => ''],
                'source' => ['type'=> 'string','default' => ''],
                'tag' => ['type'=> 'string','default' => ''],
                'lang_type' => ['type'=> 'string','default' => ''],
                'dateline' => ['type'=> 'string','default' => ''],
                'uid' => ['type'=> 'string','default' => 0],
                'name' => ['type'=> 'string','default' => ''],
                'tel' => ['type'=> 'string','default' => ''],
                'platform' => ['type'=> 'string','default' => ''],
            ]
        ]);
    }


    /**
     * 获取v2数据
     * @throws \Exception
     */
    public function setLevelOneSource(){
        $domain = new LevelOneSourceDomain();
        $data = [
            'uid'=>$this->params['uid'],
            'name'=>$this->params['name'],
            'tel'=>$this->params['tel'],
            'platform'=>$this->params['platform'],
            'cat'=>$this->params['cat'],
            'source'=>$this->params['source'],
            'tag'=>$this->params['tag'],
            'lang_type'=>$this->params['lang_type'],
            'dateline'=>$this->params['dateline'],
        ];
        $match_info = $domain->setLevelOne($data['lang_type'],$data['platform'],$data['cat'],$data['uid'],
            $data['name'],$data['tel'],$data['source'],$data['tag'],$data['dateline']);
        $this->returnJson(isset($match_info['lang_type'])?$match_info['lang_type']:'');
    }
}