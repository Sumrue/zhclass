<?php
/**
 * 报名课程核销验证
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
$orderid = intval($_GPC['orderid']);

$typeStatus = new TypeStatus();
$orderStatusList = $typeStatus->orderStatus();

if($op == 'display'){
	$title = '课程订单核销详情';
	
	/* 订单信息 */
	$order = pdo_fetch("SELECT a.*,b.saler_uids,b.images FROM " .tablename($this->table_order). " a LEFT JOIN " .tablename($this->table_lesson_parent). " b ON a.lessonid=b.id WHERE a.id=:id AND a.status>:status", array(':id'=>$orderid,':status'=>0));
	if(empty($order)){
		message("订单不存在!");
	}

	/* 报名信息 */
	$appoint_info = json_decode($order['appoint_info'], true);

	$saler = json_decode($order['saler_uids'], true);
	if(!in_array($uid, $saler)){
		message("您没有权限查看此课程订单!");
	}

	/* 核销记录 */
	$verify_log = $site_common->getOrderVerifyLog($orderid);

	if(checksubmit()){
		if($verify_log['count'] && $verify_log['count'] >= $order['verify_number']){
			message("该订单核销次数已用完");
		}

		$member = pdo_get($this->table_mc_members, array('uniacid'=>$uniacid,'uid'=>$uid), array('nickname'));
		$data = array(
			'uniacid'	  => $uniacid,
			'orderid'	  => $orderid,
			'verify_type' => 0,
			'verify_uid'  => $uid,
			'verify_name' => $member['nickname'],
			'addtime'	  => time(),
		);

		if(!$order['is_verify']){
			$order_data['is_verify'] = 1;
		}
		if($verify_log['count'] && $verify_log['count']+1 >= $order['verify_number']){
			$order_data['is_verify'] = 2;
		}
		if($order_data){
			pdo_update($this->table_order, $order_data, array('id'=>$orderid));
		}

		if(pdo_insert($this->table_order_verify, $data)){
			message("核销成功", $this->createMobileUrl('verifyorder', array('orderid'=>$orderid)), "success");
		}else{
			message("核销失败，请稍后重试", "", "error");
		}
	}
}

include $this -> template("../mobile/{$template}/verifyOrder");
