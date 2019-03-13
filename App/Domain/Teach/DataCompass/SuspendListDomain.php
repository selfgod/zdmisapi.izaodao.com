<?php
/**
 * 数据罗盘 - 休学列表
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/9/25
 * Time: 09:50
 */

namespace App\Domain\Teach\DataCompass;

use App\Model\Common\User;
use App\Model\Common\Permission;
use App\Model\Teach\Suspend\SuspendModel;
use Base\BaseDomain;
use Base\Thrift;
use Lib\XLSXWriter;

class SuspendListDomain extends BaseDomain
{
    /**
     * 数据罗盘 - 获取休学列表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function querySuspendList(array $params)
    {
        $bindValues = [];
        $where = ' a.is_del = 0 ';
        if($params['sa']){
            $where .= ' and b.stuffname = :sa';
            $bindValues['sa'] = $params['sa'];
        }
        if($params['search_user']){
            if(preg_match("/^[1-9][0-9]*$/",$params['search_user'])){
                $where .= ' and a.uid = :uid';
                $bindValues['uid'] = $params['search_user'];
            }else{
                $where .= ' and a.uid in ( 0 ';
                $user = new User();
                $userIds = $user->getUserUidFromUsername(['username'=>$params['search_user']]);
                if($userIds){
                    foreach ($userIds as $n) {
                        $where .= ', ' . $n;
                    }
                }
                $where .= ' ) ';
            }
        }
        if($params['status'] == 1){
            $where .= ' and a.status = :status';
            $bindValues['status'] = $params['status'];
            $where .= ' and a.start_time < :start_time';
            $bindValues['start_time'] = date('Y-m-d H:i:s');
        }elseif($params['status'] == 2){
            $where .= ' and a.status in (2,3) ';
        }elseif($params['status'] == 3){
            $where .= ' and a.start_time > :start_time';
            $bindValues['start_time'] = date('Y-m-d H:i:s');
        }

        /* 休学开始时间 */
        if($params["search_type"] == 1){
            if ($params['suspend_start_time'] != "" && $params['suspend_end_time'] == "") {
                $start = $params['suspend_start_time'] . " 00:00:00";
                if ($params['status'] == 3) {
                    $where .= ' and a.create_time >= :start';
                    $bindValues['start'] = $start;
                }else {
                    $where .= ' and a.create_time >= :start';
                    $bindValues['start'] = $start;
                    $where .= ' and a.start_time <= :start_time2';
                    $bindValues['start_time2'] = date('Y-m-d H:i:s');
                }
            }
            if ($params['suspend_end_time'] != "" && $params['suspend_start_time'] == "") {
                $end = $params['suspend_end_time'] . " 23:59:59";
                if ($params['status'] == 3) {
                    $where .= ' and a.create_time >= :start and a.create_time <= :end';
                    $bindValues['start'] = date('Y-m-d H:i:s');
                    $bindValues['end'] = $end;
                }else {
                    $where .= ' and a.create_time >= :start';
                    $bindValues['start'] = $start;
                    $where .= ' and a.start_time <= :start_time2';
                    $bindValues['start_time2'] = date('Y-m-d H:i:s');
                }
            }
            if ($params['suspend_end_time'] != "" && $params['suspend_start_time'] != "") {
                $start = $params['suspend_start_time'] . " 00:00:00";
                $end = $params['suspend_end_time'] . " 23:59:59";
                if ($params['status'] == 3) {
                    $where .= ' and a.create_time >= :start and a.create_time <= :end';
                    $bindValues['start'] = date('Y-m-d H:i:s');
                    $bindValues['end'] = $end;
                }else {
                    $where .= ' and a.create_time >= :start and a.create_time <= :end';
                    $bindValues['start'] = $start;
                    $bindValues['end'] = $end;
                    $where .= ' and a.start_time <= :start_time2';
                    $bindValues['start_time2'] = date('Y-m-d H:i:s');
                }
            }
        }
        /* 休学结束时间 */
        if($params["search_type"] == 2){
            if ($params['suspend_start_time'] != "" && $params['suspend_end_time'] == "") {
                $start = $params['suspend_start_time'] . " 00:00:00";
                if ($params['status'] == 3) {
                    $where .= ' and a.create_time >= :start';
                    $bindValues['start'] = $start;
                }else {
                    $where .= ' and a.end_time >= :start';
                    $bindValues['start'] = $start;
                }
            }
            if ($params['suspend_end_time'] != "" && $params['suspend_start_time'] == "") {
                $end = $params['suspend_end_time'] . " 23:59:59";
                if ($params['status'] == 3) {
                    $where .= ' and a.create_time <= :end';
                    $bindValues['end'] = $end;
                }else {
                    $where .= ' and a.end_time <= :end';
                    $bindValues['end'] = $end;
                }
            }
            if ($params['suspend_end_time'] != "" && $params['suspend_start_time'] != "") {
                $start = $params['suspend_start_time'] . " 00:00:00";
                $end = $params['suspend_end_time'] . " 23:59:59";
                if ($params['status'] == 3) {
                    $where .= ' and a.create_time >= :start and a.create_time <= :end';
                    $bindValues['start'] = date('Y-m-d H:i:s');
                    $bindValues['end'] = $end;
                }else {
                    $where .= ' and a.end_time >= :start and a.end_time <= :end';
                    $bindValues['start'] = $start;
                    $bindValues['end'] = $end;
                }
            }
        }
        /* 休学申请时间 */
        if($params["search_type"] == 3){
            if ($params['suspend_start_time'] != "" && $params['suspend_end_time'] == "") {
                $start = $params['suspend_start_time'] . " 00:00:00";
                $where .= ' and a.create_time >= :start';
                $bindValues['start'] = $start;
            }
            if ($params['suspend_end_time'] != "" && $params['suspend_start_time'] == "") {
                $end = $params['suspend_end_time'] . " 23:59:59";
                $where .= ' and a.create_time <= :end';
                $bindValues['end'] = $end;
            }
            if ($params['suspend_end_time'] != "" && $params['suspend_start_time'] != "") {
                $start = $params['suspend_start_time'] . " 00:00:00";
                $end = $params['suspend_end_time'] . " 23:59:59";
                $where .= ' and a.create_time >= :start and a.create_time <= :end';
                $bindValues['start'] = $start;
                $bindValues['end'] = $end;
            }
        }

        $order = ['a.end_time asc'];
        if($params['sort_order']){
            $order = explode(',', $params['sort_order']);
        }

        $suspend = new SuspendModel();
        $count = $suspend->getSuspendCount($where, $bindValues);
        if ($params['type'] === 'list') {
            $suspendList = [];
            if ($count > 0) {
                $suspendList = $suspend->getSuspendList($where, $bindValues, $params['page'], $params['limit'], $order);
                if(!empty($suspendList)){
                    $user = new User();
                    foreach ($suspendList as $k => $v) {
                        $user_info = $user->getUserNameInfo($v['uid']);
                        $suspendList[$k]['username'] = isset($user_info['user_real_name_text']) ? $user_info['user_real_name_text'] :'';
                    }
                }
            }
            $data['suspendList'] = $suspendList;
        } else {
            $data['suspendCount'] = $count;
        }
        return $data;
    }

    /**
     * 数据罗盘 - 休学列表 - 删除
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function delSuspendInfo(array $params)
    {
        $suspend_id = $params['id'];
        $user_uid = $params['s_uid'];
        $uid = $params['uid'];
        $permission = new Permission();
        $del_button = $permission->hasFuncPermission('suspendList_manage',$uid);
        if($del_button)
        {
            if($suspend_id)
            {
                $suspend = new SuspendModel();
                $res = $suspend->delUserSuspend($suspend_id);
                if($res)
                {
                    Thrift::getInstance()->service('User')->updateUserLearnInfoSuspend($user_uid,0);
                    return ['delStatus' => 1];//成功
                }
            }
        }
        else
        {
            return ['delStatus' => 2];// 没有权限
        }

        return ['delStatus' => 0];// 失败
    }

    /**
     * 数据罗盘 - 休学列表 - 导出
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function outputSuspendInfo(array $data, $status)
    {
        $res = [];
        if (!empty($data)) {
            $writer = new XLSXWriter();
            $dir = EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'Export';
            $filename = '休学学员名单_' . date('Y-m-d') . '_' . time() . '.xlsx';
            $sheet1 = 'sheet1';
            $title = ['开始时间', '结束时间', '用户名/真名', '班主任', '申请时间', '休学状态', '理由'];
            $title_type = ['string', 'string', 'string', 'string', 'string', 'string', 'string'];
            $width = [25, 25, 25, 25, 25, 15, 65];
            $writer->writeSheetHeader($sheet1, $title_type, ['widths' => $width, 'suppress_row'=>true]);
            $writer->writeSheetRow($sheet1, [$filename, '', '', '', '', '', ''], ['font-size' => 20, 'height'=>35, 'font-style'=>'bold', 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
            $writer->markMergedCell($sheet1, $start_row=0, $start_col=0, $end_row=0, $end_col=6);
            $writer->writeSheetRow($sheet1, $title, ['font'=>'黑体', 'font-size' => 14, 'height'=>35, 'font-style'=>'bold', 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
            foreach ($data as $n) {
                $writer->writeSheetRow($sheet1, [
                    $n['start_time'],
                    $n['end_time'],
                    $n['username'],
                    $n['stuffname'],
                    $n['create_time'],
                    ($status == 1 || !$status ) ? "正在休学" :
                        (($status == 2) ? "休学结束" : (($status == 3) ? "即将开始" :'')),
                    $n['reason']
                ], ['font-size' => 12, 'height'=>35, 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
            }
            $writer->writeToFile($dir . DIRECTORY_SEPARATOR . $filename);
            $res = ['downUrl' => $filename];
        }

        return $res;
    }

}