<?php
/**
 * 数据罗盘 - 结课未选课列表
 * Created by PhpStorm.
 * User: wuheng
 * Date: 2018/9/25
 * Time: 09:50
 */

namespace App\Domain\Teach\DataCompass;

use App\Model\Common\User;
use App\Model\Teach\Schedule\ClassEndModel;
use App\Model\Teach\Schedule\JoinScheduleLogModel;
use Base\BaseDomain;
use Lib\XLSXWriter;

class JoinScheduleLogDomain extends BaseDomain
{
    /**
     * 数据罗盘 - 获取结课未选课列表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryJoinScheduleLogList(array $params)
    {
        $where_sql = ' 1 = 1 ';
        $start_time = $params['start_time'] ? $params['start_time'] : '';
        $end_time = $params['end_time'] ? $params['end_time'] : '';
        if ($params['time_type'] == 1) {
            if (!empty($start_time) && !empty($end_time)) {
                $where_sql .= " AND suli.first_time >='" . $start_time . " 00:00:00' and suli.first_time <= '" . $end_time . " 23:59:59' ";
            }
        }elseif ($params['time_type'] == 2) {
            if (!empty($start_time) && !empty($end_time)) {
                $where_sql .= " AND suli.last_expire >='" . $start_time . " 00:00:00' and suli.last_expire <= '" . $end_time . " 23:59:59' ";
            }
        }else{
            if (!empty($start_time) && !empty($end_time)) {
                $where_sql .= " AND iol.uid in (select uid from zd_netschool.sty_user_goods as sug left join zd_netschool.sty_goods as sg on sug.goods_id = sg.id 
                where sug.is_del = 0 and sg.is_active = 1 AND sug.activate_time >='" . $start_time . " 00:00:00' and sug.activate_time <= '" . $end_time . " 23:59:59' ) ";
            }
        }

        if(isset($params['grade_id']) && intval($params['grade_id']) >=0)
        {
            $where_sql .= ' AND suli.grade_id = ' . $params['grade_id'];
        }

        if (isset($params['flag']) && intval($params['flag']) > 0) {
            $where_sql .= ' AND iol.flag = ' . $params['flag'];
        }

        $user = new User();
        if (isset($params['uid_name']) && !empty($params['uid_name'])) {
            if (is_numeric($params['uid_name']) && !preg_match('/^1[3456789][0-9]{9}$/', $params['uid_name'])) {
                $where_sql .= ' AND iol.uid = ' . $params['uid_name'];
            }else{
                $userInfo = $user->getUserUidFromUsername(['username' => $params['uid_name']]);
                if($userInfo){
                    $where_sql .= ' AND iol.uid in (0 ';
                    foreach ($userInfo as $n){
                        $where_sql .= ', ' . $n;
                    }
                    $where_sql .= ') ';
                }
            }
        }
        if (!empty($params['opt_uid_name'])) {
            if (is_numeric($params['opt_uid_name']) && !preg_match('/^1[3456789][0-9]{9}$/', $params['uid_name'])) {
                $where_sql .= ' AND iol.opt_uid = ' . $params['opt_uid_name'];
            }else{
                $userInfo = $user->getUserUidFromUsername(['username' => $params['opt_uid_name']]);
                if($userInfo){
                    $where_sql .= ' AND iol.opt_uid in (0 ';
                    foreach ($userInfo as $n){
                        $where_sql .= ', ' . $n;
                    }
                    $where_sql .= ') ';
                }
            }
        }
        $where_sql .= ' AND iol.type IN (1,2,3,8,9) ';

        $joinScheduleLog = new JoinScheduleLogModel();
        $count = $joinScheduleLog->getJoinScheduleLogCount($where_sql);
        if ($params['type'] === 'list') {
            $joinScheduleLogList = [];
            if ($count > 0) {
                $joinScheduleLogList = $joinScheduleLog->getJoinScheduleLogList($where_sql, ((intval($params['page']) > 0) ? $params['page'] : 1), $params['limit']);
                if($joinScheduleLogList){
                    $log_come_from = array('1' => '前台', '2' => '后台');
                    $log_type = array('1' => ' 添加一对一课程', '2' => '删除一对一课程', '3' => '修改一对一课程',
                        '4' => '修改基本信息', '5' => '修改口语信息', '6'=>'修改结课日期', '7'=>'退费信息',
                        '8'=>'添加阶段课程', '9'=>'删除阶段课程', '10'=>'解锁阶段', '11'=>'添加商品', '12'=>'次数调整',
                        '13'=>'取消回访任务', '14'=>'删除商品', '15'=>'逾期停课', '16'=>'停课恢复', '17'=>'修改发票状态');
                    $describe_arr = ['1'=>'学员操作', '2'=>'后台操作'];
                    $classEnd = new ClassEndModel();
                    $result = $classEnd->resultConf();
                    $conf = $result['conf'];
                    foreach ($joinScheduleLogList as $key => $val) {
                        //用户名真名
                        $user_info = $user->getUserNameInfo($val['uid']);
                        $joinScheduleLogList[$key]['username'] = isset($user_info['user_real_name_text']) ? $user_info['user_real_name_text'] : '';
                        $opt_user_info = $user->getUserNameInfo($val['opt_uid']);
                        $joinScheduleLogList[$key]['opt_username'] = isset($opt_user_info['username']) ? $opt_user_info['username'] : '';
                        $user_activate = $user->getUserLastActiveTime($val['uid']);
                        $joinScheduleLogList[$key]['activate_time'] = isset($user_activate['activate_time']) ? $user_activate['activate_time'] : '';
                        $joinScheduleLogList[$key]['type'] = $log_type[$val['type']];
                        $joinScheduleLogList[$key]['describe'] = $describe_arr[$val['flag']];
                        $joinScheduleLogList[$key]['flag'] = $log_come_from[$val['flag']];
                        $joinScheduleLogList[$key]['dateline_date'] = date('Y-m-d H:i:s', $val['dateline']);
                        if($val['user_identity']>0){
                            $identity_describe = $conf['identity'][$val['user_identity']].'-';
                            if ($val['sub_identity'] > 0) {
                                $sub_identity_describe = $conf['sub_identity_' . $val['user_identity'] . ''][$val['sub_identity']];
                            } else {
                                $sub_identity_describe = '无';
                            }
                            $joinScheduleLogList[$key]['identity_describe'] = $identity_describe.$sub_identity_describe;
                        }else{
                            $joinScheduleLogList[$key]['identity_describe'] = '无';
                        }
                    }
                }
            }
            $data['joinScheduleLogList'] = $joinScheduleLogList;
        } else {
            $data['joinScheduleLogCount'] = $count;
        }
        return $data;
    }

    /**
     * 数据罗盘 - 结课未选课列表 - 导出
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function outputJoinScheduleLog(array $data)
    {
        $res = [];
        if (!empty($data)) {
            $writer = new XLSXWriter();
            $dir = EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'Export';
            $filename = '加删课记录_' . date('Y-m-d') . '_' . time() . '.xlsx';
            $sheet1 = 'sheet1';
            $title = ['操作时间', '学员UID', '学员用户名/真名', '操作人UID', '操作人用户名', '身份标识', '等级', '激活日期', '报名日期', '毕业日期', '内容', '动作', '操作类型', '记录来源'];
            $title_type = ['string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string'];
            $width = [25, 12, 35, 12, 25, 25, 12, 20, 20, 20, 80, 20, 15, 15];
            $writer->writeSheetHeader($sheet1, $title_type, ['widths' => $width, 'suppress_row'=>true]);
            $writer->writeSheetRow($sheet1, [$filename, '', '', '', '', '', '', '', '', '', '', '', '', ''], ['font-size' => 20, 'height'=>35, 'font-style'=>'bold', 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
            $writer->markMergedCell($sheet1, $start_row=0, $start_col=0, $end_row=0, $end_col=13);
            $writer->writeSheetRow($sheet1, $title, ['font'=>'黑体', 'font-size' => 14, 'height'=>35, 'font-style'=>'bold', 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
            foreach ($data as $n) {
                $writer->writeSheetRow($sheet1, [
                    date('Y-m-d H:i:s',$n['dateline']),
                    $n['uid'],
                    $n['username'],
                    $n['opt_uid'],
                    $n['opt_username'],
                    $n['identity_describe'],
                    ($n['grade_id'] ? ('L' . $n['grade_id']) : '未定级'),
                    ($n['activate_time'] ? (date('Y-m-d', strtotime($n['activate_time']))) : ''),
                    ($n['first_time'] ? (date('Y-m-d', strtotime($n['first_time']))) : ''),
                    ($n['last_expire'] ? (date('Y-m-d', strtotime($n['last_expire']))) : ''),
                    str_replace(array("&nbsp;","&amp;","\t","\r\n","\r","\n"),array("","","","","",""), strip_tags($n['content'])),
                    $n['type'],
                    $n['describe'],
                    $n['flag']
                ], ['font-size' => 12, 'height'=>35, 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
            }
            $writer->writeToFile($dir . DIRECTORY_SEPARATOR . $filename);
            $res = ['downUrl' => $filename];
        }

        return $res;
    }

}