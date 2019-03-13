<?php

namespace App\HttpController\Market\Source;

use App\Domain\Market\Source\GenerateSourceDomain;
use Base\BaseController;

/**
 * 入库
 * Class GenerateSource
 * @package App\HttpController\Market\Launchadvent
 */
class Generate extends BaseController
{
    protected function getRules()
    {
        $rules = parent::getRules();
        return array_merge($rules, [
            'setGenerateSource' => [
                'ignore_sign' => true,
                'ignore_auth' => true,
                'id' => ['type' => 'int', 'default' => 0],
                'cat' => ['type'=> 'string','default' => ''],
                'source' => ['type'=> 'string','default' => ''],
                'tag' => ['type'=> 'string','default' => ''],
                'lang_type' => ['type'=> 'string','default' => ''],
                'uid' => ['type'=> 'string','default' => ''],
                'name' => ['type'=> 'string','default' => ''],
                'tel' => ['type'=> 'string','default' => ''],
                'platform' => ['type'=> 'string','default' => ''],
                'ip' => ['type'=> 'string','default' => ''],
                'category'=>['type'=> 'string','default' => ''],
                'is_callin'=>['type'=> 'string','default' => ''],
                'empty_callin'=>['type'=> 'string','default' => ''],
            ]
        ]);
    }


    /**
     * 获取入库数据
     * @throws \Exception
     */
    public function setGenerateSource(){
        $domain = new GenerateSourceDomain();
        $data = [
            'id'=>$this->params['id'],
            'uid'=>$this->params['uid'],
            'name'=>$this->params['name'],
            'tel'=>$this->params['tel'],
            'platform'=>$this->params['platform'],
            'cat'=>$this->params['cat'],
            'source'=>$this->params['source'],
            'tag'=>$this->params['tag'],
            'lang_type'=>$this->params['lang_type'],
            'ip'=>$this->params['ip'],
            'is_action'=>$this->params['category'],
            'is_callin'=>$this->params['is_callin'],
            'category'=>$this->params['category'],
            'empty_callin'=>$this->params['empty_callin'],
        ];
        $match_info = $domain->setGenerateSource($data);
        $this->returnJson($match_info);
    }
}