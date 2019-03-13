<?php

namespace App\Model\Students\Challenge;

use Base\BaseModel;
use Base\Db;
use Base\Thrift;
use Lib\XLSXWriter;

/**
 * 班级挑战赛活动统计
 * Created by Seldoon.
 * User: Seldoon.
 * Date: 2019-01-24 10:42
 */
class Activity extends BaseModel
{
    /**
     * @param $params
     * @return int
     * @throws \Exception
     */
    public function getActivityListTotal($params): int {
        [$db, $where, $bind] = $this->getActivityCondition($params);
        
        $sql = $db->select('DISTINCT(challenger.id)')
            ->where($where)
            ->__toString();
        
        return $db->single("SELECT COUNT(*) FROM ({$sql})t", $bind);
        
    }
    
    /**
     * 挑战赛活动导出
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function exportActivityList($params): array {
        $filed = 'challenger.id,challenger.student_type,challenger.schedule_id,challenger.student_schedule,challenger.title,challenger.active_type,relate.time_type,relate.id AS relate_id';
        [$db, $where, $bind] = $this->getActivityCondition($params);
        $result = $db->select($filed)
            ->where($where)
            ->bindValues($bind)
            ->groupBy(['challenger.id'])
            ->orderByDESC(['relate_id'])
            ->query();
    
        $writer = new XLSXWriter();
        $DS = DIRECTORY_SEPARATOR;
        $dir = EASYSWOOLE_ROOT . "{$DS}Export";
        $filename = '挑战赛活动统计_' . date('Y-m-d') . '_' . time() . '.xlsx';
        $title = ['ID', '活动标题', '活动对象', '活动范围', '开始时间', '结束时间', '活动内容', '活动状态'];
        $title_type = ['string', 'string', 'string', 'string', 'string', 'string', 'string', 'string'];
        $width = [10, 40, 15, 23, 23, 23, 18, 18];
        $sheet_name = '挑战赛活动';
        // 设置头的样式
        $writer->writeSheetHeader($sheet_name, $title_type, ['widths' => $width, 'suppress_row'=>true]);
        $style = ['font-size' => 20, 'height' => 35, 'font-style' => 'bold', 'halign' => 'center', 'valign' => 'center', 'border' => 'left,right,top,bottom', 'border-style' => 'thin'];
        // 设置列头的样式
        $writer->writeSheetRow($sheet_name, [$filename, '', '', '', '', '', '', ''], $style);
        $writer->markMergedCell($sheet_name, $start_row=0, $start_col=0, $end_row=0, $end_col=7);
        $writer->writeSheetRow($sheet_name, $title, ['font'=>'黑体', 'font-size' => 14, 'height'=>35, 'font-style'=>'bold', 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin']);
        $data = $this->processActivityListParams($result);
        foreach ($data as $item) {
            $writer->writeSheetRow($sheet_name, [
                $item['id'],
                $item['title'],
                $item['active_type'] === 1 ? '班级' : '学员',
                $item['target'],
                $item['start_time'],
                $item['end_time'],
                $item['typename'],
                $item['status'],
            ], ['font-size' => 13]);
        }
        $writer->writeToFile("{$dir}{$DS}{$filename}");
        
        return ['downloadUri' => $filename];
    }
    
    /**
     * 获取挑战赛活动列表
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getActivityList($params): array {
        $filed = 'challenger.id,challenger.student_type,challenger.schedule_id,challenger.student_schedule,challenger.title,challenger.active_type,relate.time_type,relate.id AS relate_id';
        $page = $params['page'] ?? 0;
        $limit = $params['limit'] ?? 0;
        $offset = $limit * $page;
        
        [$db, $where, $bind] = $this->getActivityCondition($params);
        $result = $db->select($filed)
            ->where($where)
            ->bindValues($bind)
            ->groupBy(['challenger.id'])
            ->orderByDESC(['relate_id'])
            ->limit($limit)
            ->offset($offset)
            ->query();
        if (!empty($result)) {
            return $this->processActivityListParams($result);
        }
        
        return [];
    }
    
    /**
     * 处理列表中的其他字段的展示
     * @param $result
     * @return array
     * @throws \Exception
     */
    public function processActivityListParams($result): array {
        $time = time();
        foreach ($result as $key => $item) {
            // 活动范围(班级名称/学员等级) : 列出
            if ($item['active_type'] === 2) { // 学员
                if ($item['student_type'] === 14) { // 学员 - 班级
                    // 取时间 班级时间和自定义时间
                    $schedule_info = Thrift::getInstance()
                        ->service('Schedule')
                        ->getScheduleInfo($item['student_schedule']);
                    $result[$key]['schedule_info'] = $schedule_info;
                    $target_name = $schedule_info['name'];
                } else if ($item['student_type'] === 13) { // 学员 - 全体未定级学员
                    $target_name = '全体未定级学员';
                } else if ($item['student_type'] === 12) { // 学员 - 全体定级学员
                    $target_name = '全体定级学员';
                } else if ($item['student_type'] === 11) { // 学员 - 全体付费学员
                    $target_name = '全体付费学员';
                } else { // 学员 - L1-L9
                    $target_name = 'L' . $item['student_type'];
                }
                // 时间 取relate的自定义时间
                $relate_time = Db::slave('zd_netschool')
                    ->from('sty_challenger_task_relate')
                    ->select('MAX(end_time) AS end_time, MIN(start_time) AS start_time')
                    ->where('challenger_id = :challenger_id AND time_type = :time_type AND is_del = 0')
                    ->bindValues(['challenger_id' => $item['id'], 'time_type' => 2])
                    ->row();
                
                $start_date_relate = $relate_time['start_time'];
                $end_date_relate = $relate_time['end_time'];
            } else {
                // 查询班级名称
                $schedule_info = Thrift::getInstance()
                    ->service('Schedule')
                    ->getScheduleInfo($item['schedule_id']);
                $result[$key]['schedule_info'] = $schedule_info;
                $target_name = $schedule_info['name'];
                
                $relate_time = Db::slave('zd_netschool')
                    ->from('sty_challenger_task_relate')
                    ->select('MAX(end_time) AS end_time, MIN(start_time) AS start_time')
                    ->where('challenger_id = :challenger_id AND time_type = :time_type AND is_del = 0')
                    ->bindValues(['challenger_id' => $item['id'], 'time_type' => 2])
                    ->row();
            
                $start_date_relate = $relate_time['start_time'];
                $end_date_relate = $relate_time['end_time'];
            }
        
            if (isset($schedule_info)) { // 班级和学员-班级的都包含班级时间
                $start_date_schedule = $schedule_info['start_time'];
                $end_date_schedule = $schedule_info['end_time'];
            }
        
            $start_time_min = '';
            $end_time_max = '';
            
            if (!empty($schedule_info) || !empty($start_date_relate) || !empty($end_date_relate)) {
                // 开始时间取最小值
                if (!empty($start_date_schedule) && $start_date_relate !== null) {
                    if (strtotime($start_date_schedule) < strtotime($start_date_relate)) {
                        $start_time_min = $start_date_schedule;
                    } else {
                        $start_time_min = $start_date_relate;
                    }
                } else {
                
                    $start_time_min = !empty($start_date_schedule) ? $start_date_schedule : $start_date_relate;
                }
            
                // 结束时间取最大值
                if (!empty($end_date_schedule) && $end_date_relate !== null) {
                    if (strtotime($end_date_schedule) > strtotime($end_date_relate)) {
                        $end_time_max = $end_date_schedule;
                    } else {
                        $end_time_max = $end_date_relate;
                    }
                
                } else {
                    $end_time_max = !empty($end_date_schedule) ? $end_date_schedule : $end_date_relate;
                }
            }
        
            if (strtotime($start_time_min) < $time && strtotime($end_time_max) > $time) {
                $status = '进行中';
            } else if (strtotime($start_time_min) > $time) {
                $status = '未开始';
            } else {
                $status = '已结束';
            }
        
            $result[$key]['target'] = $target_name;
            $result[$key]['start_time'] = $start_time_min;
            $result[$key]['end_time'] = $end_time_max;
            $result[$key]['status'] = $status;
        
            // 活动内容 多个任务的任务类型 默认只取进度型任务
            $task_type_arr = Db::slave('zd_netschool')
                    ->select('task.type')
                    ->from('sty_challenger_task_relate as relate')
                    ->leftJoin('sty_challenger_task as task', 'relate.task_id = task.id')
                    ->where('relate.id = :id AND relate.is_del = 0 AND task.is_del = 0')
                    ->bindValues(['id' => $item['relate_id']])
                    ->groupBy(['type'])
                    ->column() ?? [];
            $typename_arr = ['', '进度型', '排行型'];
            $typename = '';
            foreach ($task_type_arr as $task_type) {
                $typename .= $typename_arr[$task_type] . '/';
            }
            $result[$key]['typename'] = rtrim($typename, '/');
        }
    
        return $result;
    }
    
    /**
     * 活动列表搜索条件处理
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getActivityCondition($params): array {
        $id = $params['id'] ?? 0;
        $title = $params['title'] ?? '';
        $category = $params['category'] ?? '';
        $target = $params['target'] ?? '';
        //$start_date = $params['start_date'] ?? '';
        //$end_date = $params['end_date'] ?? '';
        
        //$table_sty_schedule = 'sty_schedule_temp'; // 上线后 sty_schedule_temp -> sty_schedule
        $where = 'challenger.is_del = :is_del AND relate.is_del = 0'; // 只统计进度型任务的活动
        $bind = ['is_del' => 0];
        
        
        $db = Db::slave('zd_netschool')
            ->from('sty_challenger as challenger')
            ->leftJoin('sty_challenger_task_relate as relate', 'challenger.id = relate.challenger_id')
            ->groupBy(['challenger.id']);
            //->leftJoin('sty_challenger_task as task', 'relate.task_id = task.id');
        if ($id) {
            $where .= ' AND challenger.id = :id';
            $bind['id'] = $id;
        } else {
            if ($title) {
                $where .= ' AND challenger.title LIKE :title';
                $bind['title'] = "%{$title}%";
            }
            if ($category) { // 面向对象 1 班级 2学员
                $where .= ' AND challenger.active_type = :category';
                $bind['category'] = $category;
                // 自定义时间 学员并且是非班级的只有自定义时间
                //$time_custom_person = $category === 2 && $target !== 14;
                if ($category === 2 && $target) {// 面向学员的范围
                    $where .= ' AND challenger.student_type = :student_type';
                    $bind['student_type'] = $target;
                }
            }
            
            // 时间条件
            /*if ($start_date || $end_date) {
                if (!empty($time_custom_person)) { // 自定义时间 仅当对象为非班级或者非学员班级
                    if ($start_date && $end_date) {
                        $where .= ' AND relate.start_time >= :start_time OR relate.end_time <= :end_time';
                        $bind['start_time'] = $start_date . ' 00:00:00';
                        $bind['end_time'] = $end_date . ' 23:59:59';
                    } else if ($start_date) {
                        $where .= ' AND relate.start_time >= :start_time';
                        $bind['start_time'] = $start_date . ' 00:00:00';
                    } else {
                        $where .= ' AND relate.end_time <= :end_time';
                        $bind['end_time'] = $end_date . ' 23:59:59';
                    }
                } else { // 即有按班级时间也有自定义时间 或关系
                    // 按班级时间[班级schedule_id, 学员班级student_schedule]
                    $schedule_type = $category === 2 ? 'student_schedule' : 'schedule_id';
                    
                    $db->leftJoin($table_sty_schedule . ' as schedule', 'schedule.id = challenger.' . $schedule_type);
                    if ($start_date && $start_date) {
                        $where .= ' AND (( schedule.start_time >= :start_time_schedule OR schedule.end_time <= :end_time_schedule) OR (relate.start_time >= :start_time_relate OR relate.end_time <= :end_time_relate))';
                        $bind['start_time_relate'] = $start_date . ' 00:00:00';
                        $bind['end_time_relate'] = $end_date . ' 23:59:59';
                        $bind['start_time_schedule'] = $start_date . ' 00:00:00';
                        $bind['end_time_schedule'] = $end_date . ' 23:59:59';
                    } else if ($start_date) {
                        $where .= ' AND ((schedule.start_time >= :start_time_schedule) OR (relate.start_time >= :start_time_relate OR relate.end_time <= :end_time_relate))';
                        $bind['start_time_relate'] = $start_date . ' 00:00:00';
                        $bind['start_time_schedule'] = $start_date . ' 00:00:00';
                    } else {
                        $where .= ' AND ((schedule.end_time <= :start_time_schedule) OR (relate.end_time <= :end_time_relate))';
                        $bind['end_time_relate'] = $end_date . ' 23:59:59';
                        $bind['end_time_schedule'] = $end_date . ' 23:59:59';
                    }
                }
            }*/
        }
        
        return [$db, $where, $bind];
    }
    
    /**
     * 挑战赛活动的任务列表
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function getTaskList($id): array {
        $challenger_id = $id;
        $challenger_info = Db::slave('zd_netschool')
            ->select()
            ->from('sty_challenger')
            ->where('id = :id')
            ->bindValue('id', $challenger_id)
            ->row();
        
        $result = Db::slave('zd_netschool')
            ->select('relate.id AS relate_id,relate.task_id,relate.cron_flag,relate.time_type,relate.start_time,relate.end_time,task.name,task.type,task.mode,task.cond,task.value,task.desc')
            ->from('sty_challenger_task_relate as relate')
            ->leftJoin('sty_challenger_task as task', 'task.id = relate.task_id')
            ->where('relate.challenger_id = :challenger_id AND relate.is_del = 0 AND task.is_del = 0')
            ->bindValues(['challenger_id' => $challenger_id])
            ->orderByDESC(['relate.task_id'])
            ->query();
        
        foreach ($result as $key => $item) {
            if ($item['time_type'] !== 2) { // 按班级时间
                if ($challenger_info['active_type'] === 1) { // 班级
                    $schedule_id = $challenger_info['schedule_id'];
                } else {
                    $schedule_id = $challenger_info['student_schedule'];
                }
                $schedule_info = Thrift::getInstance()
                    ->service('Schedule')
                    ->getScheduleInfo($schedule_id);
                if ($schedule_info) {
                    $result[$key]['start_time'] = $schedule_info['start_time'];
                    $result[$key]['end_time'] = $schedule_info['end_time'];
                }
            }
            
            // 任务状态
            $status = '已结束';
            $time = time();
            $start_time = strtotime($result[$key]['start_time']);
            $end_time = strtotime($result[$key]['end_time']);
            if ($start_time < $time && $end_time > $time) {
                $status = '进行中';
            } else if ($start_time > $time) {
                $status = '未开始';
            }
            
            // 获取任务关联的奖励
            $rewards = Db::slave('zd_netschool')
                ->select('reward.type,reward.content')
                ->from('sty_challenger_task_reword as task_relate')
                ->leftJoin('sty_challenger_reword as reward', 'task_relate.reword_id = reward.id')
                ->where('task_relate.task_id = :task_id AND task_relate.is_del = 0')
                ->bindValue('task_id', $item['task_id'])
                ->query();
            
            $reward_title = '';
            foreach ($rewards as $reward) {
                $reward_title .= ($reward['type'] === 1 ? '学分' : '') . $reward['content'] . '; ';
            }
            
            $result[$key]['rewards'] = rtrim($reward_title, '; ');
            $result[$key]['status'] = $status;
            $result[$key]['desc'] = strip_tags($item['desc']);
        }
        
        return $result;
    }
    
    /**
     * 获取活动列表的总数据
     * @param $params
     * @return int
     * @throws \Exception
     */
    public function getTaskDetailTotal($params): int {
        [$db,] = $this->getTaskDetailCondition($params);
        
        return $db->select('COUNT(*)')->single();
    }
    
    /**
     * @param $params
     * @return array
     * @throws \Exception
     */
    private function getTaskDetailCondition($params): array {
        $relateId = $params['relateId'];
        $challenge_id = $params['challengeId'];
        
        $data = Db::slave('zd_netschool')
            ->select('relate.id AS relate_id,relate.time_type,relate.cron_flag,relate.start_time,relate.end_time,challenge.active_type,challenge.student_type,challenge.schedule_id,challenge.student_schedule,task.type AS task_type,task.mode AS task_mode,task.cond AS task_cond')
            ->from('sty_challenger as challenge')
            ->leftJoin('sty_challenger_task_relate as relate', 'relate.challenger_id = challenge.id')
            ->leftJoin('sty_challenger_task as task', 'task.id = relate.task_id')
            ->where('challenge.id = :id AND relate.id = :relate_id')
            ->bindValues(['id' => $challenge_id, 'relate_id' => $relateId])
            ->row();
        
        $activity_type = $data['active_type']; // 活动类型 1 班级, 2 学员
        // 学员分类 只有当active_type = 2时有效. 11 全体付费学员,12 全体定级学员,13 全体未定级学员,14 班级, 1-9 L1-L9
        $student_type = $data['student_type'];
        $schedule_id = $data['schedule_id'] ?: $data['student_schedule'];
        if ($activity_type === 1 || $student_type === 14) { // 班级
            //$start_time = $data['start_time'];
            //$end_time = $data['end_time'];
            
            $db = Db::slave('zd_netschool')
                ->from('sty_user_schedule as main')
                ->where('main.schedule_id = :schedule_id AND main.is_del=0 AND main.staff = 0')
                ->bindValue('schedule_id', $schedule_id)
                ->orderByDESC(['main.join_time']);
        } else { // 学员 - 个人
            $db = Db::slave('zd_netschool')
                ->from('sty_user_learn_info as main');
            $c_where = 'main.staff = 0 AND ';
            switch ($student_type) {
                case 13: // 全体未定级学员
                    $c_where .= 'main.grade_id = 0';
                    break;
                case 12: // 全体定级学员
                    $c_where .= 'main.grade_id > 0';
                    break;
                case 11: // 全体付费学员
                    $c_where .= 'main.official_class = 1';
                    break;
                default :
                    $c_where .= "main.grade_id = {$student_type}";
                    break;
            }
            $db->where($c_where);
        }
        
        return [$db, $data];
    }
    
    /**
     * 获取任务的详情
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getTaskDetail($params): array {
        $limit = $params['limit'];
        $offset = $params['page'] * $limit;
        [$db, $challenge_task] = $this->getTaskDetailCondition($params);
        //select('challenge.active_type,challenge.schedule_id,challenge.student_type,challenge.student_schedule,relate.time_type,relate.start_time,relate.end_time,relate.id AS relate_id,task.cond,task.mode,task.value')
        $result = $db->leftJoin('zd_class.jh_common_member jcm', 'jcm.uid = main.uid')
            ->leftJoin('zd_class.jh_common_member_profile profile', 'profile.uid = main.uid')
            ->select('main.uid,jcm.username,profile.realname')
            ->limit($limit)
            ->offset($offset)
            ->query();
        $relate_id = $challenge_task['relate_id'];
        $activity_type = $challenge_task['active_type']; // 活动类型 1 班级, 2 学员
        $student_type = $challenge_task['student_type'];
        
        // 当前用户是否挑战完成
        $task_user_done = [];
        if (!empty($result)) {
            $task_user_done = Db::slave('zd_netschool')
                ->select('uid,reward_status')
                ->from('sty_challenger_task_user')
                ->where($this->whereIn('uid', array_column($result, 'uid')))
                ->where('task_relate_id = ' . $relate_id)
                ->query();
        }
    
        // 任务类型 1 出勤率 2 出勤次数 3直播报道分享数
        $task_mode = $challenge_task['task_mode'];
        // 班级id
        $schedule_id = $challenge_task['student_schedule'] ?: $challenge_task['schedule_id'];
        $custom_type = $challenge_task['time_type'] === 2;
        // 1按课程时间 2指定时间段
        if (!$custom_type) {
            $schedule_info = Thrift::getInstance()
                ->service('Schedule')
                ->getScheduleInfo($schedule_id);
            $start_time = $schedule_info['start_time'];
            $end_time = $schedule_info['end_time'];
        } else {
            $start_time = $challenge_task['start_time'];
            $end_time = $challenge_task['end_time'];
        }
        $start_date = substr($start_time, 0, 10);
        $end_date = substr($end_time, 0, 10);
    
        $total_lesson = -1;
        $schedule_live_report_rate = -1;
        foreach ($result as $key => $item) {
            $uid = $item['uid'];
            // 统计任务类型 时间段 出勤/分享情况
            if ($activity_type === 1) { // 班级 班级总的平均直播出勤率和分享次数
                // 任务的开始结束时间
                if ($task_mode === 1) { // 班级平均出勤率
                    if ($custom_type) { // 指定时间段内的班级平均出勤率(内部员工&&开学典礼&&预约制已排除)
                        if ($schedule_live_report_rate === -1) {
                            $schedule_live_report_rate = Db::slave('zd_jpdata')
                                ->select('AVG(live_report_rate)')
                                ->from('sas_lesson')
                                ->where('start_time >= :start_time AND end_time <= :end_time AND schedule_id = :schedule_id AND is_introduction_class = 0')
                                ->bindValues(['start_time' => $start_time, 'end_time' => $end_time, 'schedule_id' => $schedule_id])
                                ->single();
                        }
                    } else if ($schedule_live_report_rate === -1) { // 按课程时间的班级平均直播出勤率
                        $schedule_live_report_rate = Db::slave('zd_jpdata')
                            ->select('AVG(live_report_rate)')
                            ->from('sas_lesson')
                            ->where('schedule_id = :schedule_id AND is_introduction_class = 0')
                            ->bindValue('schedule_id', $schedule_id)
                            ->single();
                    }
                    $numbers = round($schedule_live_report_rate / 100) . '%';
                } else { // 班级内的分享次数
                    // 获取班级
                    $numbers = Thrift::getInstance()
                        ->service('Learn')
                        ->getClassCheckInShareNumber($schedule_id, $start_date, $end_date);
                }
                // 获取任务类型
                //$start_time = $challenge_task['start_time'];
                //$end_time = $challenge_task['end_time'];
            } else if ($student_type === 14) { // 学员 - 班级(每个人出勤)
                // 自定义时间
                if ($custom_type) {
                    if (strtotime($start_time) > time()) {
                        if ($task_mode === 1) { // 自定义时间 - 班级直播出勤率
                            $bindValues = [
                                'start_time' => $start_time,
                                'end_time' => $end_time,
                                'scheduleId' => $schedule_id
                            ];
                            if ($total_lesson === -1) {
                                $total_lesson = Db::slave('zd_netschool')
                                    ->select('count(*) as num')
                                    ->from('sty_schedule_lesson')
                                    ->where('start_time >= :start_time AND end_time < :end_time AND is_del = 0 AND schedule_id = :scheduleId AND is_introduction_class = 0')
                                    ->bindValues($bindValues)
                                    ->single();
                            }
                            $bindValues['uid'] = $uid;
                            $num = Db::slave('zd_netschool')
                                ->select('count(*)')
                                ->from('sty_user_schedule_lesson susl')
                                ->leftJoin('sty_user_learn_info as suli', 'susl.uid = suli.uid')
                                ->where('susl.check_in_type = 1 AND susl.check_in_time >= :start_time AND susl.check_in_time < :end_time AND susl.check_in_status = 1 AND susl.is_del =0 AND suli.official_class = 1 AND suli.staff = 0 and susl.schedule_id = :scheduleId AND susl.uid = :uid')
                                ->bindValues($bindValues)
                                ->single();
                            $numbers = $total_lesson ? round($num / $total_lesson * 100) . '%' : '--';
                        } else if ($task_mode === 2) { // 自定义时间 - 班级个人出勤次
                            $bindValues = [
                                'start_time' => $start_time,
                                'end_time' => $end_time,
                                'uid' => $uid,
                                'scheduleId' => $schedule_id
                            ];
                            $numbers = Db::slave('zd_netschool')
                                ->select('COUNT(*)')
                                ->from('sty_user_schedule_lesson susl')
                                ->leftJoin('sty_schedule_lesson as ssle', 'ssle.id = susl.schedule_lesson_id')
                                ->leftJoin('sty_user_learn_info as suli', 'susl.uid = suli.uid')
                                ->where('susl.check_in_type = 1 AND susl.check_in_time >= :start_time AND susl.check_in_time < :end_time AND susl.check_in_status = 1 AND susl.is_del =0 AND suli.official_class = 1 AND suli.staff = 0 AND ssle.is_introduction_class=0 AND susl.schedule_id = :scheduleId AND uid = :uid')
                                ->bindValues($bindValues)
                                ->single();
                        } else { // 自定义时间 - 分享数
                            $numbers = Thrift::getInstance()->service('Learn')
                                ->getUserCheckInShareNumber($uid, $start_date, $end_date, $schedule_id);
                        }
                    } else {
                        $numbers = '--';
                    }
                    
                } else if ($task_mode === 1) { // 按班级时间 - 班级直播出勤率
                    $num = DB::slave('zd_jpdata')
                        ->select('live_report_rate')
                        ->from('sas_user_schedule')
                        ->where('schedule_id = :scheduleId AND is_join = 1 AND uid = :uid')
                        ->bindValues(['scheduleId' => $schedule_id, 'uid' => $uid])
                        ->single();
                    $numbers = round($num / 100) . '%';
                } else if ($task_mode === 2) { // 按班级时间 - 班级个人出勤次
                    $numbers = DB::slave('zd_jpdata')
                        ->select('live_report_num')
                        ->from('sas_user_schedule')
                        ->where('schedule_id = :scheduleId AND is_join = 1 AND uid = :uid')
                        ->bindValues(['scheduleId' => $schedule_id, 'uid' => $uid])
                        ->query();
                } else { // 按班级时间 - 分享数
                    $numbers = Thrift::getInstance()->service('Learn')
                        ->getUserCheckInShareNumber($uid, $start_date, $end_date, $schedule_id);
                }
                // 出勤率/出勤次/分享
            } else if ($task_mode === 2) { // 出勤次
                //$numbers =
                $bindValues = [
                    'start_time' => $item['start_time'],
                    'end_time' => $item['end_time'],
                    'uid' => $uid
                ];
                $numbers = Db::slave('zd_netschool')
                    ->select('count(*)')
                    ->from('sty_user_schedule_lesson susl')
                    ->leftJoin('sty_schedule_lesson as ssle', 'ssle.id = susl.schedule_lesson_id')
                    ->leftJoin('sty_user_learn_info as suli', 'susl.uid = suli.uid')
                    ->where('susl.check_in_type = 1 AND susl.check_in_time >= :start_time AND susl.check_in_time < :end_time AND susl.check_in_status = 1 AND susl.is_del =0 AND suli.official_class = 1 AND suli.staff = 0 AND ssle.is_introduction_class = 0 AND susl.uid = :uid')
                    ->bindValues($bindValues)
                    ->single();
            } else if ($task_mode === 3) { // 分享数
                $numbers = Thrift::getInstance()->service('Learn')
                    ->getUserAllCheckInShareNumber($uid, $start_date, $end_date);
            }
            $task_status = '--';
            $reward_status = '--';
            if ($challenge_task['cron_flag'] === 1) { // 任务已结束
                $task_status = '未达成';
                foreach ($task_user_done as $user_done) {
                    if ($user_done['uid'] === $uid) {
                        $task_status = '已达成';
                        $reward_status = ($user_done['reward_status'] ? '已' : '未') . '领取';
                    }
                }
            }
            
            $result[$key]['task_status'] = $task_status;
            $result[$key]['numbers'] = $numbers ?? '--';
            $result[$key]['reward_status'] = $reward_status;
        }
        
        return $result ?? [];
    }
    
    /**
     * 获取班级下的学员
     * @param $scheduleId
     * @param $limit
     * @param $offset
     * @return array
     */
    protected function getScheduleUsers($scheduleId, $limit, $offset): array {
        $students = Db::slave('zd_netschool')
            ->from('sty_user_schedule')
            ->select('uid')
            ->where('schedule_id = :schedule_id AND is_del=0')
            ->bindValue('schedule_id', $scheduleId)
            ->orderByDESC(['join_time'])
            ->limit($limit)
            ->offset($offset)
            ->column();
        
        return $students;
    }
}