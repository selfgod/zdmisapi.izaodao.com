namespace php Services.Schedule
service Schedule
{
    map<string, string> getScheduleInfo(1:i32 id);
    map<string, map<string, string>> getScheduleByIds(1:list<i32> scheduleIds);
    map<string, string> getLessonInfo(1:i32 lessonId);
    map<string, map<string, string>> getLessonByIds(1:list<i32> lessonIds);
    map<string, string> getScheduleAndTeacher(1:i32 id);
    map<string, string> getUserScheduleLessonInfo(1:i32 uid,2:i32 scheduleId,3:i32 lessonId);//用户上课信息
    i32 getScheduleEndLessonNum(1:i32 scheduleId);//阶段课程结课课件数
    list<i32> getUserScheduleIds(1:i32 uid);
    list<map<string, string>> getUserLearnSchedule(1:i32 uid);//用户学过的课程
    list<map<string, string>> getScheduleTeacherInfo(1:i32 scheduleId);//获取阶段课程下主讲老师信息
    bool userJoinedScheduleByMode(1:i32 uid, 2:i32 mode)//用户是否在某模式下是否加入阶段课程
    list<string> getScheduleWeek(1:i32 scheduleId);//获取阶段课程下周信息
    list<map<string, string>> getScheduleBook(1:i32 scheduleId);
    list<map<string, string>> getScheduleDatum(1:i32 scheduleId);//获取阶段课程资料
    list<map<string, string>> getScheduleLabel();//获取课件标签
    list<map<string, string>> getScheduleBasic();//获取阶段课程适合基础
    i32 getScheduleReportNum(1:i32 uid, 2:i32 scheduleId);//阶段课程报到数
    i32 getLessonReportUserNum(1:i32 lessonId, 2:bool live);//课件报到人数
    bool userIsJoinSchedule(1:i32 uid, 2:i32 scheduleId);//用户是否加入阶段课程
    bool userIsReservedLesson(1:i32 uid, 2:i32 lessonId);//用户是否已预约课件
    list<map<string, string>> getScheduleCategory(1:string categoryId);//阶段课程分类
    map<string, string> getScheduleLastEndLesson(1:i32 scheduleId);//阶段课程最后结束的课的信息
    list<map<string, string>> getScheduleLabelNew();
    list<map<string, string>> getScheduleJoinNumByIds(1:map<string, string> scheduleIds, 2:bool rm_staff);//加入阶段的人数
    i32 getEndLessonUnCheck(1:i32 uid, 2:i32 scheduleId);//已结课中用户未报到数
    list<map<string, string>> getUserScheduleInfo(1:i32 uid);//获取学员详情页阶段课程信息
    string getLessonRecordUrl(1:i32 lessonId);//获取课件录播地址
}