<?php
/**
 * Created by PhpStorm.
 * User: songwenyao
 * Date: 2019/1/23
 * Time: 9:47 AM
 */

namespace App\Domain\Market\Source;

use App\Domain\Sales\SalesmanDomain;
use App\Domain\Sales\Setting\TeamStructureDomain;
use App\Model\Market\Source\MoveLogModel;
use App\Model\Sales\Customer\CustomerModel;
use Base\BaseDomain;

class MoveLogDomain extends BaseDomain
{
    public function __construct()
    {
        $this->baseModel = new MoveLogModel();
    }

    function addLog($sales_uid, $type, $sub_type, $source_type, $cid, $tag)
    {
        list($group_dept_type, $my_team) = (new SalesmanDomain())->getSalesmanType($sales_uid);
        if(empty($my_team)) return false;
        $business_type = (new TeamStructureDomain())->getBusinessTypeForSalesmanType($group_dept_type);
        $CustomerModel = new CustomerModel();
        $follow = $CustomerModel->getCustomerFollow($cid, $business_type);
        $zid = $CustomerModel->getZidForCid($cid);
        $data = [
            'lang_type'=>$business_type,
            'type'=>$type,
            'sub_type'=>$sub_type,
            'source_type'=>$source_type,
            'dept_type'=>$group_dept_type,
            'team'=>$my_team['team'],
            'group'=>$my_team['group'],
            'oldcc'=>(!empty($follow)&&$follow['salesman']!=$my_team['cc'])?$follow['salesman']:'',
            'cc'=>$my_team['cc'],
            'zid'=>$zid?$zid[0]:0,
            'cid'=>$cid,
            'tag0'=>$tag,
            'dateline'=>date('Y-m-d H:i:s'),
            'admin'=>'系统'
        ];
        return $this->baseModel->addMoveLog($data);
    }
}