<?php
/**
 * 会员优惠券
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
$uid = $_W['member']['uid'];

$member = pdo_fetch("SELECT nickname FROM " .tablename($this->table_mc_members). " WHERE uid=:uid", array(':uid'=>$uid));

if($op=='display'){
	$title = "我的优惠券";
	$typeStatus = new TypeStatus();
	$source = $typeStatus->couponSource();

	$pindex =max(1,$_GPC['page']);
	$psize = 10;
	$status = trim($_GPC['status']);

	$list = pdo_fetchall("SELECT * FROM " .tablename($this->table_member_coupon). " WHERE uniacid=:uniacid AND uid=:uid AND status=:status ORDER BY id DESC LIMIT " . ($pindex-1) * $psize . ',' . $psize, array(':uniacid'=>$uniacid,':uid'=>$uid,':status'=>$status));
	foreach($list as $k=>$v){
		$list[$k]['startDate'] = date('Y.m.d', $v['addtime']);
		$list[$k]['endDate'] = date('Y.m.d', $v['validity']);
		$list[$k]['startTime'] = date(' H:i', $v['addtime']);
		$list[$k]['endTime'] = date(' H:i', $v['validity']);
		$list[$k]['classname'] = $status ==0 ? 'pepper-red' : '';

		$category = pdo_fetch("SELECT name FROM " .tablename($this->table_category). " WHERE id=:id", array(':id'=>$v['category_id']));
		$list[$k]['category_name'] = $category['name'] ? "仅限[".$category['name']."]分类的课程" : "全部课程分类";
		unset($category);

		if($v['category_id']>0){
			$list[$k]['url'] = $this->createMobileUrl('search', array('cat_id'=>$v['category_id']));
		}else{
			$list[$k]['url'] = $this->createMobileUrl('index', array('t'=>1));
		}
		
		$list[$k]['source_name'] = $source[$v['source']];

		if(time()>$v['validity'] && $v['status']==0){
			pdo_update($this->table_member_coupon, array('status'=>-1), array('id'=>$v['id']));
			unset($list[$k]);
		}
	}

	if($_W['isajax']){
		echo json_encode($list);
	}

}elseif($op=='addCoupon'){
	$title = "优惠码转换";

	$password = trim($_GPC['card_password']);
	$coupon = pdo_fetch("SELECT * FROM " .tablename($this->table_coupon). " WHERE uniacid=:uniacid AND password=:password", array(':uniacid'=>$uniacid, ':password'=>$password));
	if(empty($coupon)){
		message("课程优惠码不存在");
	}
	if($coupon['is_use']==1){
		message("课程优惠码已被使用");
	}
	if($coupon['validity'] < time()){
		message("课程优惠码已过期");
	}

	$upcoupon = array(
		'is_use'	=> 1,
		'nickname'	=> $member['nickname'],
		'uid'		=> $uid,
		'use_time'	=> time(),
	);
	$res = pdo_update($this->table_coupon, $upcoupon, array('card_id'=>$coupon['card_id']));

	if($res){
		$membeCoupon = array(
			'uniacid'   => $uniacid,
			'uid'	    => $uid,
			'amount'    => $coupon['amount'],
			'conditions' => $coupon['conditions'],
			'validity'  => $coupon['validity'],
			'password'  => $coupon['password'],
			'status'    => 0,
			'source'	=> 1,
			'addtime'   => time(),
		);

		if(pdo_insert($this->table_member_coupon, $membeCoupon)){
			message("转换成功", $this->createMobileUrl('coupon'), "success");
		}else{
			message("写入会员优惠券失败");
		}
	}else{
		message("转换失败");
	}

}

if(!$_W['isajax']){
	include $this->template("../mobile/{$template}/coupon");
}

?>