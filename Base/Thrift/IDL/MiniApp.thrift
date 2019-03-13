namespace php Services.MiniApp
struct JsonResult {
  1:i32 code = 500,
  2:string msg = "fail",
  3:map<string, string> data = []
}
service MiniApp {
    map<string, string> getEstimator(1:i32 type);//获取自定义估分器的配置
    bool saveEstimatorResult(1:map<string, string> data); // 保存估分器计算后的结果
    map<string, string> lookupDateConf(1:string field); // 查分器获取配置时间
    map<string, string> estimatorDateConf(); // 估分器获取配置时间
    map<string, string> checkScoreChkImgCode(1:string chkImgFlag);//查分器图验 chkImgFlag 客户端缓存图验标识
    JsonResult checkScoreResultSubmit(1:string zkz,2:string idno,3:string chkImgCode,4:string chkImgFlag,5:map<string, string> params);//查分器查询结果提交
}