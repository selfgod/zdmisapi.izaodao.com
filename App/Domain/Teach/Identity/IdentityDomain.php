<?php
/**
 * 教学 - 身份标识
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/9/25
 * Time: 09:50
 */

namespace App\Domain\Teach\Identity;

use App\Model\Common\Category;
use Base\BaseDomain;

class IdentityDomain extends BaseDomain
{
    /**
     * 获取标签列表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryIdentityList(array $params)
    {
        $model_sys = new Category();
        $conf['identity'] = $model_sys->getConf('svip_key2');
        $conf['sub_identity_1'] = $model_sys->getConf('identity_ordinary');
        $conf['sub_identity_2'] = $model_sys->getConf('identity_vip');
        $conf['sub_identity_3'] = $model_sys->getConf('identity_svip');
        $conf['sub_identity_4'] = $model_sys->getConf('identity_lifelong');
        $conf['sub_identity_5'] = $model_sys->getConf('identity_talent');
        $conf['sub_identity_6'] = $model_sys->getConf('identity_visitor');
        if ($params['user_identity']){
            $res = ['identity' => $conf['sub_identity_' . $params['user_identity']]];
        }else{
            $res = ['identity' => $conf['identity']];
        }
        return $res;
    }

}