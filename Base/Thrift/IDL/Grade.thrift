namespace php Services.Grade
struct JsonResult {
  1:i32 code = 500,
  2:string msg = "fail",
  3:map<string, string> data = []
}
service Grade
{
    map<string, string> getUserGradeInfo(1:i32 uid);//用户等级信息
    list<map<string, string>> getGradeList();//级别信息列表
    map<string, string> getGradeInfo(1:i32 gradeId)//获取等级信息
    list<map<string, string>> getExamList(1:i32 uid);//用户参与考试列表
    bool generateExamQuestion(1:i32 uid, 2:i32 gradeId)//生成考试题
    map<string, string> gradeExamPrepare(1:i32 uid);//等级考试前准备
    string getExamCate(1:i32 uid, 2:string cate);//获取某个分类下的问题信息
    map<string, string> reviewPrepare(1:i32 uid, 2:i32 resultId);//回顾信息准备
    string reviewExamCate(1:i32 uid, 2:i32 resultId, 3:string cate);//回顾
    bool makeChoice(1:i32 uid, 2:i32 questionId, 3:i32 answerId);//选择答案
    JsonResult submitAnswer(1:i32 uid, 2:map<string, string> params);//交卷
    bool setUserZeroBased(1:i32 uid);//设置用户零基础
    list<map<string, string>> getBookList();//获取教材列表
    list<map<string, string>> getProgressByBook(1:i32 bookId);//获取教材进度
    JsonResult getGradeByBook(1:i32 bookId, 2:i32 bookProgressId);//通过教材进度获取等级信息
    JsonResult getUserApplyGrade(1:i32 uid, 2:map<string, string> params);//用户申请调级
    bool isUnGradeApply(1:i32 uid);//是否存在用户未处理的调级申请
    JsonResult getUpgradeInfo(1:i32 uid);//用户升级信息
    i32 getMaxGrade();//最大等级
    bool isPassGrade(1:i32 uid, 2:i32 gradeId);//该等级是否通过考试
    string getExamQuestion(1:i32 uid);//获取考题信息
    string reviewExamQuestion(1:i32 uid, 2:i32 resultId);//回顾某次考试的信息
}