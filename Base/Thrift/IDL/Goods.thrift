namespace php Services.Goods
struct JsonResult {
  1:i32 code = 500,
  2:string msg = "fail",
  3:map<string, string> data = []
}
service Goods
{
    map<string, string> getUserGoodsInfo(1:i32 uid, 2:i32 goodsId);
    JsonResult activateGoods(1:i32 uid, 2:i32 goodsId, 3:i32 activateType)//激活商品
    map<string, i32> getUserGoodsByMode(1:i32 uid, 2:map<string, string> params);//获取用户某模式的商品
    JsonResult  createUserGoods(1:i32 uid, 2:i32 goods_id, 3:i32 market_sign, 4:i32 pay_price, 5:i32 use_zy, 6:i32 origin_goods_id);//加课
    map<string, string> getGoodsDetail(1:i32 goodsId);//获取商品信息
    list<map<string, string>> getGoods(1:list<i32> goodsids);//批量商品
    JsonResult  zdmisCreateUserGoods(1:i32 uid, 2:i32 goods_id, 3:i32 market_sign, 4:i32 pay_price, 5:i32 use_zy, 6:i32 origin_goods_id, 7:i32 modify_user);//加课
    bool updateGoods(1:i32 goods_id, 2:map<string, string> params);//更新
    map<string, string> getUserGoodsRenewal(1:i32 id);//通过ID获取用户商品续费信息
    JsonResult userGoodsRenewal(1:i32 renewalId, 2:map<string, string> params);//用户某商品续费
    i32 getRenewalTimeLen(1:i32 userGoodsId, 2:map<string, string> params);//获取用户商品的总续费时长
    bool updateUserGoodsRenewal(1:i32 renewalId, 2:map<string, string> params);//更新用户续费信息
}