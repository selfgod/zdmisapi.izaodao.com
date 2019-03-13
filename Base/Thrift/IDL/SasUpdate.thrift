namespace php Services.SasUpdate
service SasUpdate
{
    list<map<string,string>> getSasLessonIdByEndTime ( 1:string startTime,2:string endTime); //获取结课时间段内的课件ID
    list<map<string,string>> getSasLessonIdByStartTime ( 1:string startTime,2:string endTime); //获取开课时间段内的课件ID
    bool insertSasJoinScheduleLog(1:i32 uid,2:i32 schedule_id, 3:i32 is_join); //添加加入阶段课程日志
    bool updateSasLesson(1:i32 LessonId,2:i32 scheduleId); //课件添加修改 - 更新
    bool updateSasLessonByTeacher(1:i32 scheduleId,2:i32 teacherId); //课件添加修改 - 更新
    bool updateSasLessonDel(1:i32 LessonId,2:i32 scheduleId); //课件删除 - 更新
    bool updatePlanSasLessonShouldReportNum( 1:string startTime,2:string endTime); //更新课件应报道人数 (计划)
    bool updatePlanSasLessonEndClass(1:i32 LessonId,2:i32 scheduleId);
    bool updatePlanSasLessonReport( 1:string startTime,2:string endTime); //课件录播报道和做题更新（计划）
    bool updateSasSchedule(1:i32 scheduleId); //阶段课程添加修改 - 更新
    bool updateSasScheduleUserNum(1:i32 scheduleId); //更新阶段课程学员数（在学，休学，毕业，退出）
    bool updatePlanSasScheduleUserNum(1:i32 scheduleId); //更新阶段课程学员数（活跃，n次未出勤，昨日新增）计划
    bool updatePlanSasScheduleAvgRateEndClass(1:i32 scheduleId); //下课更新更新阶段课程平均率（计划）
    bool updatePlanSasScheduleAvgRate(1:i32 scheduleId); //录播报道,做题更新阶段课程平均率（计划）
    bool updatePlanSasScheduleOther(1:i32 scheduleId); //更新阶段课程其他（节课课件数，结课一课一练配题数，结课单元测试配题数）计划
    bool getSasScheduleTestNum(1:i32 scheduleId); //更新阶段课程其他（节课课件数，结课一课一练配题数，结课单元测试配题数）计划
    bool insertPlanSasTeacherClassHourEndClass(1:i32 scheduleId,2:i32 lessonId); //课件下课更新（计划）
    bool insertPlanSasTeacherClassHour(1:string startTime,2:string endTime); //更新其他课课时（计划）
    bool updatePlanSasTeacherSummaryEndClass(1:i32 lessonId); //课件下课更新（计划）
    bool updatePlanSasTeacherSummary(1:string startTime,2:string endTime); //定时更新（计划）
    bool insertSasUserLesson(1:string startTime,2:string endTime); //添加用户上课记录信息
    bool deleteSasUserLesson(1:string startTime,2:string endTime); //删除用户上课记录信息
    bool updatePlanSasUserLessonEndClass(1:i32 scheduleId,2:i32 lessonId); //下课更新 （计划）
    bool updatePlanSasUserLessonEndClass2(1:i32 scheduleId,2:i32 lessonId); //下课更新 更新 休学 毕业 请假（计划）
    bool updatePlanSasUserLessonReport(1:i32 scheduleId,2:i32 lessonId); //录播报道更新 （计划）
    bool updateSasUserScheduleJoin(1:i32 uid,2:i32 scheduleId); //加入阶段课程 - 更新
    bool updateSasUserScheduleQuit(1:i32 uid,2:i32 scheduleId); //退出阶段课程 - 更新
    bool updatePlanSasUserScheduleReport(1:i32 scheduleId,2:i32 type); //报道 - 更新 （计划）
    bool updatePlanSasUserScheduleTest(1:i32 scheduleId); //做题 - 更新 （计划）
    bool updatePlanSasUserScheduleNoReportNum(1:i32 scheduleId); //连续未报道数 - 更新 （计划）
    bool updateSasUserSummaryLesson(1:i32 uid); //加入删除阶段课程 - 更新加入阶段课程数,预约数
    bool updateSasUserSummaryBuyGoods(1:i32 uid); //商品购买 激活- 更新
    bool updateSasUserSummaryUpGoods(1:i32 uid,2:i32 type); //商品删除 - 更新
    bool updateSasUserSummaryLearnStatus(1:i32 uid,2:i32 type); //学员学习状态 - 更新 (休学 毕业 逾期 退学 等)
    bool updateSasUserSummaryReport(1:i32 uid); //报道 - 更新 (直播 录播 等)
    bool insertSasUserQuestionLog(1:i32 LessonId,2:i32 scheduleId,3:i32 uid,4:i32 type); //做题 - 更新
    bool updateQuestionSasLesson(1:i32 LessonId); //做题 - 更新
    bool updateQuestionSasUser(1:i32 scheduleId,2:i32 uid); //做题 - 更新
    bool updateSasUserSummaryOther(1:i32 uid); //学员其他信息 - 更新 (内部员工 学员组 等级 身份 等)
}
