<?php
/**
 * 确认下单
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */
 
checkauth();

$title = "确认下单";
$id = intval($_GPC['id']); /* 课程id */
$lessonurl = $this->createMobileUrl('lesson', array('id'=>$id));
$uid = $_W['member']['uid'];

$nopay_order = pdo_get($this->table_order, array('uniacid'=>$uniacid,'uid'=>$uid,'lessonid'=>$id,'status'=>0,'is_delete'=>0), array('id'));
if (!empty($nopay_order)) {
	message("您还有该课程未付款的订单", $this->createMobileUrl('mylesson', array('status'=>'nopay')), "warning");
}

$pay_order = pdo_fetch("SELECT id FROM " .tablename($this->table_order). " WHERE uniacid=:uniacid AND uid=:uid AND lessonid=:lessonid AND status>=:status AND (validity>:validity OR validity=0) AND is_delete=:is_delete ORDER BY id DESC LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid,':lessonid'=>$id,':status'=>1,':validity'=>time(),':is_delete'=>0));
if(!empty($pay_order)){
	message("您已购买该课程，无需重复购买", $this->createMobileUrl('lesson', array('id' => $id)), "error");
}

/* 检查黑名单操作 */
$site_common->check_black_list('order', $uid);

$lesson = pdo_fetch("SELECT a.*,b.teacher,b.teacherphoto FROM " .tablename($this->table_lesson_parent). " a LEFT JOIN " .tablename($this->table_teacher). " b ON a.teacherid=b.id WHERE a.uniacid=:uniacid AND a.id=:id AND (a.status=1 OR a.status=3) LIMIT 1", array(':uniacid'=>$uniacid, ':id'=>$id));

if (empty($lesson)) {
    message("课程不存在或已下架！", "", "error");
}

/* 课程规格 */
$spec_id = intval($_GPC['spec_id']);
if (empty($spec_id)) {
    message("课程规格不存在！", "", "error");
}
$spec = pdo_get($this->table_lesson_spec, array('uniacid'=>$uniacid,'lessonid'=>$id,'spec_id'=>$spec_id));
if(empty($spec)){
	message("课程规格不存在！", "", "error");
}

if(!empty($lesson['teacherphoto'])){
	$teacherphoto = $_W['attachurl'].$lesson['teacherphoto'];
}else{
	$teacherphoto = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg";
}

/* 检查是否开启库存 */
if($setting['stock_config']==1){
	if($spec['spec_stock'] <=0 ){
		message("您选择的规格已售罄，请重新选择", "", "error");
	}
}

/* 检查用户是否完善信息 */
$member = pdo_fetch("SELECT a.*,b.avatar,b.realname,b.mobile,b.msn,b.idcard,b.occupation,b.company,b.graduateschool,b.grade,b.address,b.education,b.position,b.credit1 FROM " .tablename($this->table_member). " a LEFT JOIN " .tablename($this->table_mc_members). " b ON a.uid=b.uid WHERE a.uid=:uid", array(':uid'=>$uid));

if ($setting['mustinfo'] && ($lesson['lesson_type']==0 || $lesson['lesson_type']==3 || ($lesson['lesson_type']==1 && $setting['appoint_mustinfo']))) {
	$user_info = json_decode($setting['user_info']);
	$jumpurl = $this->createMobileUrl('writemsg', array('lessonid'=>$id, 'spec_id'=>$spec_id, 'type'=>'buylesson'));

	if(!empty($common_member_fields)){
		foreach($common_member_fields as $v){
			if(in_array($v['field_short'],$user_info) && empty($member[$v['field_short']])){
				 message("请完善您的".$v['field_name'], $jumpurl, "warning");
			}
		}
	}
}

/*检查积分抵扣开关和课程是否支持积分抵扣*/
$market = pdo_fetch("SELECT * FROM " .tablename($this->table_market). " WHERE uniacid=:uniacid", array(':uniacid'=>$uniacid));
if($market['deduct_switch']==1 && $market['deduct_money']>0 && $lesson['deduct_integral']>0 && $member['credit1']>0){
	$deduct_switch = 1;
	$deduct_integral = $lesson['deduct_integral'] >= $member['credit1'] ? $member['credit1'] : $lesson['deduct_integral'];
	$deduct_money = $deduct_integral*$market['deduct_money'];
}

/* 检查会员是否享受折扣 */
$memberVip_list = pdo_fetchall("SELECT * FROM  " .tablename($this->table_member_vip). " WHERE uid=:uid AND validity>:validity", array(':uid'=>$uid,':validity'=>time()));

$discount = 100; /* 初始折扣为100，即100% */
if(!empty($memberVip_list)){
	$isVip = true;
	foreach($memberVip_list as $v){
		if($v['discount']>0 && $v['discount'] < $discount) {
			$discount = $v['discount'];
		}
	}
}

/* 检查课程是否参加限时活动 */
$discount_lesson = pdo_fetch("SELECT * FROM " .tablename($this->table_discount_lesson). " WHERE uniacid=:uniacid AND lesson_id=:lesson_id AND starttime<:time AND endtime>:time", array(':uniacid'=>$uniacid,':lesson_id'=>$id,':time'=>time()));
if(!empty($discount_lesson)){
	$spec['spec_price'] = round($spec['spec_price']*$discount_lesson['discount']*0.01, 2);
}

$price = $spec['spec_price'];
if ($isVip && $discount<=100) { /* 折扣开启 */
    if ($spec['spec_price'] > 0) {
        if ($lesson['isdiscount'] == 1) {/* 课程开启折扣 */
			if(!$discount_lesson || ($discount_lesson && $discount_lesson['member_discount'])){
				if ($lesson['vipdiscount']) {
					/* 使用课程单独百分比折扣优惠 */
					$price = round($spec['spec_price'] * $lesson['vipdiscount'] * 0.01, 2);

				}elseif ($lesson['vipdiscount_money']>0) {
					/* 使用课程单独固定金额优惠 */
					$price = $spec['spec_price'] - $lesson['vipdiscount_money'];

				} else {
					/* 使用VIP等级最低折扣 */
					$price = round($spec['spec_price'] * $discount * 0.01, 2);
				}
			}
        }
    } else {
        $price = 0;
    }
}

$vipCoupon = $spec['spec_price'] - $price;

/*判断可用优惠券*/
if($lesson['support_coupon']==1){
	$coupon_list = pdo_fetchall("SELECT * FROM " .tablename($this->table_member_coupon). " WHERE uniacid=:uniacid AND uid=:uid AND conditions<=:conditions AND validity>=:validity AND status=:status ORDER BY amount DESC,validity ASC", array(':uniacid'=>$uniacid,':uid'=>$uid, ':conditions'=>$price, ':validity'=>time(), ':status'=>0));
	foreach($coupon_list as $k=>$v){
		$category = pdo_fetch("SELECT name FROM " .tablename($this->table_category). " WHERE id=:id", array(':id'=>$v['category_id']));
		$coupon_list[$k]['category_name'] = $category['name'] ? "仅限[".$category['name']."]分类的课程" : "全部课程分类";
		unset($category);

		if($v['category_id']>0){
			if($lesson['pid']!=$v['category_id']){
				unset($coupon_list[$k]);
			}
		}
	}
}

/* 报名课程附加个人信息 */
$appoint_info = json_decode($lesson['appoint_info'], true);

/* 报名课程且会员为VIP免费时，报名价格为0 */
if(!empty($memberVip_list) && $lesson['lesson_type']==1){
	foreach($memberVip_list as $v){
		if(in_array($v['level_id'], json_decode($lesson['vipview']))){
			$apply_price = $price;
			$price = 0;
			break;
		}
	}
}


include $this->template("../mobile/{$template}/confirm");


?>