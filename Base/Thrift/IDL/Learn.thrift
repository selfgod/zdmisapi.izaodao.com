namespace php Services.Learn
struct JsonResult {
  1:i32 code = 500,
  2:string msg = "fail",
  3:map<string, string> data = []
}
service Learn
{
    JsonResult checkIn(1:i32 uid,2:i32 lessonId,3:map<string, string> params);//报到
    list<map<string, string>> liveLessonList(1:i32 uid, 2:map<string, string> params);//直播课表
    map<string, string> getLiveLessonClassDate(1:i32 uid, 2:map<string, string> params);//获取直播课哪天有课
    map<string, string> nextLessonInfo(1:i32 uid);//下次课信息
    list<map<string, string>> myScheduleList(1:i32 uid);//我的阶段课程列表
    list<map<string, string>> myReserveLessonList(1:i32 uid, 2:map<string, string> params);//我的预约制课程列表
    i32 myReserveLessonCount(1:i32 uid, 2:map<string, string> params);//我的预约制课程数量
    list<map<string, string>> myScheduleCourse(1:i32 uid, 2:i32 scheduleId, 3:map<string, string> params);//获取我的课程课信息
    list<map<string, string>> myScheduleExercise(1:i32 uid, 2:i32 scheduleId, 3:map<string, string> params);//我的课程做题信息
    map<string, string> myScheduleReport(1:i32 uid, 2:i32 scheduleId)//我的课程报告
    map<string, list<map<string, string>>> selectCourseFilter(1:i32 gradeId);//选课筛选条件
    list<map<string, string>> selectCourseList(1:i32 uid, 2:map<string, string> params);//选课列表
    list<map<string, string>> selectReserveList(1:i32 uid, 2:map<string, string> params);//预约制列表
    i32 selectReserveCount(1:i32 uid, 2:map<string, string> params);//选课预约制课总数
    JsonResult joinSchedule(1:i32 uid, 2:i32 scheduleId, 3:map<string, string> params);//加入阶段课程
    JsonResult delSchedule(1:i32 uid, 2:i32 scheduleId, 3:map<string, string> params);//删除阶段课程
    JsonResult reserveLesson(1:i32 uid, 2:i32 lessonId, 3:bool cancel, 4:map<string, string> params);//预约课程
    JsonResult verifyReserveLesson(1:i32 uid, 2:i32 lessonId, 3:bool cancel);//验证预约
    JsonResult verifyJoinSchedule(1:i32 uid, 2:i32 scheduleId);//验证加入阶段课程
    JsonResult verifyJoinScheduleOld(1:i32 uid, 2:i32 scheduleId);//老版验证加入阶段课程
    list<map<string, string>> getExperienceList(1:string day);//获取体验课某天的课表
    map<string, string> getExperienceClassDate(1:string start_time, 2:string end_time);//获取体验课哪天有课
    list<map<string, string>> getRankList(1:string type, 2:map<string, string> params)//获取学分排行
    map<string, string> getUserRankInfo(1:i32 uid, 2:string type, 3:map<string, string> params);//某用户学分排行信息
    list<map<string, string>> lessonRecentList(1:bool isExperience, 2:i32 uid, 3:i32 limit);//最近课表
    list<map<string, string>> getRecentMajorSchedule(1:map<string, string> params);//获取主修课零基础直播阶段课程
    list<map<string, string>> getRecentReserveList(1:map<string, string> params);//近期预约课列表
    bool updateUserScore(1:i32 uid, 2:map<string, string> params); // 更新用户学分
    bool hasMyReserveLesson(1:i32 uid);//用户下是否加过常规的预约制的课
    JsonResult timingReport(1:i32 uid,2:i32 lessonId,3:map<string, string> params);//计时报道
    bool postTeacherComment(1:i32 uid,2:i32 lessonId,3:map<string, string> params);//对老师评价
    JsonResult getClassNotice(1:i32 uid, 2:i32 lessonId);//获取上课通知配置
    JsonResult setClassNotice(1:map<string, string> data);//配置上课提醒
    i32 getClassCheckInShareNumber(1:i32 scheduleId, 2:string startTime, 3:string endTime);// 获取挑战赛面向班级报到分享次数
    i32 getUserCheckInShareNumber(1:i32 uid, 2:string startTime, 3:string endTime, 4:i32 scheduleId);// 获取挑战赛面向班级个人的报到分享次数
    i32 getUserAllCheckInShareNumber(1:i32 uid, 2:string startTime, 3:string endTime);// 获取挑战赛面向个人所有报到分享次数

}