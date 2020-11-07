<?php

checkauth();
$uid = $_W['member']['uid'];

/* 订单状态名称 */
$typeStatus = new TypeStatus();
$orderStatusList = $typeStatus->orderStatus();
$orderPaytyoeList = $typeStatus->orderPayType();

$site_common->updateOrderVerifyLog(); //更新旧的核销订单记录

if($op == 'display'){
	$title = '订单详情';

	$orderid = intval($_GPC['orderid']);
	$order = pdo_fetch("SELECT a.*,b.images FROM " .tablename($this->table_order). " a LEFT JOIN " .tablename($this->table_lesson_parent). " b ON a.lessonid=b.id WHERE a.id=:id AND a.uid=:uid ", array(':id'=>$orderid, ':uid'=>$uid));
	if(empty($order)){
		message("订单不存在!");
	}

	/* 报名课程信息 */
	$appoint_info = json_decode($order['appoint_info'], true);

	/* 核销信息 */
	$verify_info = json_decode($order['verify_info'], true);
	if($verify_info['verify_uid']>0){
		$verify_user = pdo_get($this->table_mc_members, array('uid'=>$verify_info['verify_uid']), array('nickname'));
	}

	$verify_log = $site_common->getOrderVerifyLog($orderid);

	/* 报名课程核销二维码 */
	$verifyurl = $_W['siteroot'].'app/'.$this->createMobileUrl('verifyorder', array('orderid'=>$orderid));
}

include $this -> template("../mobile/{$template}/orderDetail");