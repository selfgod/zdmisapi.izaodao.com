<?php

namespace App\Domain\Sales\Memo;

use App\Model\Sales\Consult\MemoModel;
use App\Model\Sales\Consult\SaMemoModel;
use Base\BaseDomain;
use Base\Thrift;

class MemoDomain extends BaseDomain
{
    /**
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getCCLastMemo(array $params)
    {
        if($params['user_uid'] || $params['user_openid']){
            if(!$params['user_uid']){
                $userUid = Thrift::getInstance()->service('User')->getUidByOpenId($params['user_openid']);
                if(!$userUid){
                    return [];
                }
                $params['user_uid'] = $userUid;
            }
            return (new MemoModel())->getLastMemo($params['user_uid'], $params['cc_username']) ? : [];
        } else {
            return [];
        }
    }

    /**
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getSALastMemo(array $params)
    {
        if($params['user_uid'] || $params['user_openid']){
            if(!$params['user_uid']){
                $userUid = Thrift::getInstance()->service('User')->getUidByOpenId($params['user_openid']);
                if(!$userUid){
                    return [];
                }
                $params['user_uid'] = $userUid;
            }
            return (new SaMemoModel())->getSALastMemo($params['user_uid'], $params['sa_username']) ? : [];
        } else {
            return [];
        }
    }
}