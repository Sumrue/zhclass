<?php
/**
 * 支付方式选择页面
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

$orderid = intval($_GPC['orderid']);
$params = array(
	'virtual' => false,
);

if($_GPC['ordertype'] == "buyvip"){
	$order = pdo_get($this->table_member_order, array('uniacid'=>$uniacid,'id'=>$orderid));
	if ($order['status'] != '0') {
		message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('vip'), 'error');
	}
	$level = pdo_fetch("SELECT * FROM " .tablename($this->table_vip_level). " WHERE id=:id", array(':id'=>$order['level_id']));

	$params['tid']     = $order['ordersn'];
	$params['user']    = $_W['openid'] ? $_W['openid'] : $order['uid'];
	$params['fee']     = $order['vipmoney'];
	$params['title']   = '购买['.$level['level_name'].']'.$order['viptime'].'天服务';
	$params['ordersn'] = $order['ordersn'];

}elseif($_GPC['ordertype'] == "buylesson"){
	$order = pdo_get($this->table_order, array('uniacid'=>$uniacid,'id'=>$orderid));
	if ($order['status'] != '0') {
		message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('mylesson'), 'error');
	}

	$params['tid']     = $order['ordersn'];
	$params['user']    = $_W['openid'] ? $_W['openid'] : $order['uid'];
	$params['fee']     = $order['price'];
	$params['title']   = '购买['.$order['bookname'].']课程';
	$params['ordersn'] = $order['ordersn'];

}elseif($_GPC['ordertype'] == "buyteacher"){
	$order = pdo_get($this->table_teacher_order, array('uniacid'=>$uniacid,'id'=>$orderid));
	if ($order['status'] != '0') {
		message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('teacher', array('teacherid'=>$order['teacherid'])), 'error');
	}
	$teacher = pdo_get($this->table_teacher, array('id'=>$order['teacherid']));

	$params['tid']     = $order['ordersn'];
	$params['user']    = $_W['openid'] ? $_W['openid'] : $order['uid'];
	$params['fee']     = $order['price'];
	$params['title']   = '购买['.$teacher['teacher'].']讲师'.$order['ordertime'].'天服务';
	$params['ordersn'] = $order['ordersn'];

}

$paylog = pdo_get($this->table_core_paylog, array('tid' => $order['ordersn'], 'status'=>0));
if(!empty($paylog)){
	pdo_delete($this->table_core_paylog, array('tid' => $order['ordersn']));
}

include $this->template("../mobile/{$template}/pay");

?>