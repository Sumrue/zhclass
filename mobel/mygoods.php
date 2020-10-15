<?php
/*
 * @Description:
 * @Author: Ophites
 * @Date: 2020-09-23 11:59:51
 * @LastEditors: Ophites
 * @LastEditTime: 2020-10-14 13:37:39
 */
checkauth();
$site_common->check_mobile();
$at=$_GPC['at'];
$id  = intval($_GPC['id']);
if (empty($at)) {
    $condition = " uniacid=:uniacid and id=:id";
    $params[':uniacid'] = $uniacid;
    $params[':id'] = $id;
    $goods=pdo_fetch("SELECT * FROM " .tablename('fy_lesson_goods'). " WHERE {$condition}", $params);
} elseif ($at=='check') {
    $uid=$_W['member']['uid'];
    $data['uniacid']=$uniacid;
    $data['uid']=$uid;
    $data['name']=trim($_GPC['username']);
    $data['mobile']=trim($_GPC['usermobile']);
    $data['address']=trim($_GPC['address']);
    $data['quantity']=intval($_GPC['goodsquantity']);
    $data['gid']=intval($_GPC['goodsid']);
    $data['credit']=intval($_GPC['allpoint']);
    pdo_insert('fy_lesson_goods_order', $data);
    $this->resultJson(['code'=>1,'msg'=>"兑换成功！"]);
}
include $this->template("../mobile/{$template}/mygoods");
