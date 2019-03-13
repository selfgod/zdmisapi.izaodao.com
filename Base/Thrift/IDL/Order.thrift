namespace php Services.Order
struct JsonResult {
  1:i32 code = 200,
  2:string msg = "",
  3:map<string, string> data = []
}
service Order
{
    /*
     * open_id 必须 用户open_id 必须
     * order_id 必须 订单号
     * pay_price 必须 支付价 单位是元，必须按照小数点后两位的格式
     * notify_url 必须 异步通知地址
     * return_url 必须 同步通知地址
     * subject 必须 商品名
     * source 必须 来源 1 PC 2 MOBILE 3 APP 4 WX
     * in_app 是否在APP内使用 0否 1是
     * wx_app_id wx app id
     */
    JsonResult getPayCenterParams(1:map<string, string> data);
    /**
     * 解析支付中心异步回调结果
     */
    JsonResult getPayCenterResult(1:string merchantNo, 2:string cdata, 3:map<string, string> params);
    //改价
    JsonResult modifyOrderInfo(1:string oid, 2:i32 price, 3:i32 orderPrice);
    //获取订单信息
    map<string, string> getOrderInfo(1:string oid);
}