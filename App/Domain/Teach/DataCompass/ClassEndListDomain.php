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
use App\Model\Sales\Consult\SaMemoModel;
use App\Model\Teach\Schedule\ClassEndModel;
use App\Model\Teach\Label\LabelModel;
use Base\BaseDomain;
use Lib\XLSXWriter;

class ClassEndListDomain extends BaseDomain
{
    /**
     * 数据罗盘 - 获取结课未选课列表
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryClassEndList(array $params)
    {
        $where_sql1 = ' AND 1 = 1 ';
        $start_time = $params['start_time'] ? $params['start_time'] : '';
        $end_time = $params['end_time'] ? $params['end_time'] : '';
        if ($params['time_type'] == 1) {
            if (!empty($start_time) && !empty($end_time)) {
                $where_sql1 .= " AND sus.uid in (select uid from zd_netschool.sty_user_goods as sug left join zd_netschool.sty_goods as sg on sug.goods_id = sg.id 
                where sug.is_del = 0 and sg.is_active = 1 and sug.create_time >='" . $start_time . " 00:00:00' and sug.create_time <= '" . $end_time . " 23:59:59' ) ";
            }
        }elseif ($params['time_type'] == 2) {
            if (!empty($start_time) && !empty($end_time)) {
                $where_sql1 .= " AND suli.last_expire >='" . $start_time . " 00:00:00' and suli.last_expire <= '" . $end_time . " 23:59:59' ";
            }
        }else{
            if (!empty($start_time) && !empty($end_time)) {
                $where_sql1 .= " AND sus.uid in (select uid from zd_netschool.sty_user_goods as sug left join zd_netschool.sty_goods as sg on sug.goods_id = sg.id 
                where sug.is_del = 0 and sg.is_active = 1 AND sug.activate_time >='" . $start_time . " 00:00:00' and sug.activate_time <= '" . $end_time . " 23:59:59' ) ";
            }
        }

        if(isset($params['user_identity']) && intval($params['user_identity']) > 0)
        {
            $where_sql1 .= ' AND suli.user_identity = ' . $params['user_identity'];
        }

        if($params['user_identity'] > 0 && $params['sub_identity'] > 0 )
        {
            $where_sql1 .= ' AND suli.sub_identity = ' . $params['sub_identity'];
        }

        if(isset($params['grade_id']) && intval($params['grade_id']) >=0)
        {
            $where_sql1 .= ' AND suli.grade_id = ' . $params['grade_id'];
        }

        $sa_uid = $params['sa_uid'];
        if (!empty($sa_uid)) {
            $where_sql1 .= ' AND sus.uid in ( select uid from zd_netschool.message_user where sa_uid = ' . $sa_uid . ') ';
        }

        $where_sql = ' AND end_time < NOW() ';
        $date_start = $date_end = date('Y-m-d');
        if (intval($params['end_days_start']) > 0 ) {
            $date_start = date('Y-m-d', strtotime("-" . intval($params['end_days_start']) . " days"));
        }
        if (intval($params['end_days_end']) > 0 ) {
            $date_end = date('Y-m-d', strtotime("-" . intval($params['end_days_end']) . " days"));
        }
        if(($params['end_days_start'] >= 0) && ($params['end_days_end']  >= 0 ) && $date_end <= $date_start) {
            $where_sql .= " and t1.end_time <= '" . $date_start . " 23:59:59' and t1.end_time >= '" . $date_end . " 00:00:00' ";
        }
        if($params['label']){
            $where_sql .= " and t1.uid in (0";
            $styLabelM = new LabelModel();
            $label_ids = $styLabelM->getUidFromLabels(explode(',', $params['label']));
            foreach ($label_ids as $n){
                $where_sql .= "," . $n['uid'];
            }
            $where_sql .= ')';
        }

        $classEnd = new ClassEndModel();
        $count = $classEnd->getClassEndCount($where_sql, $where_sql1);
        if ($params['type'] === 'list') {
            $endClassList = [];
            if ($count > 0) {
                $endClassList = $classEnd->getClassEndList($where_sql, $where_sql1, ((intval($params['page']) > 0) ? $params['page'] : 1), $params['limit']);
                if($endClassList){
                    $user = new User();
                    $label = new LabelModel();
                    $consult = new SaMemoModel();
                    $result = $classEnd->resultConf();
                    $conf = $result['conf'];
                    foreach ($endClassList as $key => $val) {
                        //用户名真名
                        $user_info = $user->getUserNameInfo($val['uid']);
                        $endClassList[$key]['username'] = isset($user_info['user_real_name_text']) ? $user_info['user_real_name_text'] : '';
                        $teacher = $user->getMyTeacher($val['uid']);
                        $endClassList[$key]['teacher'] = isset($teacher['stuffname']) ? $teacher['stuffname'] : '';
                        $rc_cc = $user->getMyXzCc($val['uid']);
                        $endClassList[$key]['rc_cc'] = isset($rc_cc['stuffname']) ? $rc_cc['stuffname'] : '';
                        $visit = $consult->getSaLastMemo($val['uid']);
                        $endClassList[$key]['visit_stuff'] = isset($visit['stuff_sa']) ? $visit['stuff_sa'] : '';
                        $endClassList[$key]['visit_time'] = isset($visit['adddate_sa']) ? $visit['adddate_sa'] : '';
                        $endClassList[$key]['visit_info'] = isset($visit['memo_sa']) ? $visit['memo_sa'] : '';
                        $userLabel = $label->getUserLabelInfo($val['uid']);
                        $userLabel_info = '';
                        if($userLabel){
                            foreach ($userLabel as $n){
                                $userLabel_info .= $n['name'] . ' ';
                            }
                        }
                        $endClassList[$key]['label_info'] = $userLabel_info;

                        if($val['user_identity']>0){
                            $identity_describe = $conf['identity'][$val['user_identity']].'-';
                            if ($val['sub_identity'] > 0) {
                                $sub_identity_describe = $conf['sub_identity_' . $val['user_identity'] . ''][$val['sub_identity']];
                            } else {
                                $sub_identity_describe = '无';
                            }
                            $endClassList[$key]['identity_describe'] = $identity_describe.$sub_identity_describe;
                        }else{
                            $endClassList[$key]['identity_describe'] = '无';
                        }
                    }
                }
            }
            $data['endClassList'] = $endClassList;
        } else {
            $data['endClassCount'] = $count;
        }
        return $data;
    }

    /**
     * 数据罗盘 - 结课未选课列表 - 导出
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function outputClassEndInfo(array $data)
    {
        $res = [];
        if (!empty($data)) {
            $writer = new XLSXWriter();
            $dir = EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'Export';
            $filename = '结课未选课_' . date('Y-m-d') . '_' . time() . '.xlsx';
            $sheet1 = 'sheet1';
            $title = ['用户UID', '用户名/真名', '身份标识', '标签', '等级', '班主任', '到期时间', '最新结课时间', '结课时长', '最新回访时间', '最新回访人', '最新回访内容'];
            $title_type = ['string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string'];
            $width = [15, 30, 20, 25, 15, 15, 17, 17, 15, 25, 15, 55];
            $writer->writeSheetHeader($sheet1, $title_type, ['widths' => $width, 'suppress_row'=>true]);
            $writer->writeSheetRow($sheet1, [$filename, '', '', '', '', '', '', '', '', '', '', ''], ['font-size' => 20, 'height'=>35, 'font-style'=>'bold', 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
            $writer->markMergedCell($sheet1, $start_row=0, $start_col=0, $end_row=0, $end_col=11);
            $writer->writeSheetRow($sheet1, $title, ['font'=>'黑体', 'font-size' => 14, 'height'=>35, 'font-style'=>'bold', 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
            foreach ($data as $n) {
                $start = strtotime (date("y-m-d 00:00:00")); //当前时间
                $end = strtotime ((substr($n['end_time'], 0, 10) . " 00:00:00"));
                $days = ceil(($start - $end) / 86400); //60s*60min*24h
                $writer->writeSheetRow($sheet1, [
                    $n['uid'],
                    $n['username'],
                    $n['identity_describe'],
                    $n['label_info'],
                    ($n['grade_id'] ? ('L' . $n['grade_id']) : '未定级'),
                    $n['teacher'],
                    (isset($n['unlimit_expire']) && $n['unlimit_expire'] == 1 ) ? "无限期" : substr($n['expire'], 0, 10),
                    substr($n['end_time'], 0, 10),
                    $days . "天",
                    $n['visit_time'],
                    $n['visit_stuff'],
                    $n['visit_info']
                ], ['font-size' => 12, 'height'=>35, 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
            }
            $writer->writeToFile($dir . DIRECTORY_SEPARATOR . $filename);
            $res = ['downUrl' => $filename];
        }

        return $res;
    }

}