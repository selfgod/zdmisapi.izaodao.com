namespace php Services.Coupon
struct JsonResult {
  1:i32 code = 200,
  2:string msg = "",
  3:map<string, string> data = []
}
service Coupon
{
    list<map<string, string>> getCouponLq(1:i32 typeId); //获取全部礼券
    JsonResult sendCouponLq(1:i32 uid, 2:i32 type_id, 3:map<string, string> perm); //为用户发放礼券
    list<map<string, string>> getUserCouponLq(1:i32 uid); //获取全部礼券
    list<map<string, string>> getCoupon(1:i32 typeId, 2:i32 couponId); //获取全部卡券
    JsonResult sendCoupon(1:i32 uid, 2:i32 couponId); //为用户发放卡券
    list<map<string, string>> getUserCoupon(1:i32 uid, 2:i32 typeId); //获取全部卡券
}