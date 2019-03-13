namespace php Services.Exam
struct JsonResult {
  1:i32 code = 200,
  2:string msg = "",
  3:map<string, string> data = []
}

service Exam
{
    /**
     * 获取试卷基本信息
     */
    map<string, string> getPaperInfo(1:i32 id, 2:map<string, string> params);
}