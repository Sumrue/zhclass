<?php
/**
 * 课程优惠码验证
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

$type = intval($_GPC['type']);
if($type==1){
	$password = trim($_GPC['code']);
	$price = trim($_GPC['price']);
	if(empty($password)){
		$data = array(
			'code' => -1,
			'msg'  => "请输入课程优惠码",
		);
		$this->resultJson($data);
	}

	$coupon = pdo_fetch("SELECT * FROM " .tablename($this->table_coupon). " WHERE uniacid=:uniacid AND password=:password", array(':uniacid'=>$uniacid, ':password'=>$password));
	if(empty($coupon)){
		$data = array(
			'code' => 1,
			'msg'  => "课程优惠码不存在",
		);
		$this->resultJson($data);
	}
	if($coupon['is_use']==1){
		$data = array(
			'code' => 2,
			'msg'  => "课程优惠码已被使用",
		);
		$this->resultJson($data);
	}
	if($coupon['validity'] < time()){
		$data = array(
			'code' => 3,
			'msg'  => "课程优惠码已过期",
		);
		$this->resultJson($data);
	}
	if($price < $coupon['conditions']){
		$data = array(
			'code' => 4,
			'msg'  => "付款金额大于".$coupon['conditions']."元才可使用该优惠码",
		);
		$this->resultJson($data);
	}

	$data = array(
		'code' => 0,
	);
	if($coupon['amount'] >= $price){
		$data['total'] = 0;
		$data['amount'] = $price;
	}else{
		$data['total'] = $price - $coupon['amount'];
		$data['amount'] = $coupon['amount'];
	}
	$this->resultJson($data);
}

?>