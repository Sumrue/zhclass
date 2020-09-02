<?php
/**
 * 限时折扣
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

$pindex =max(1,$_GPC['page']);
$psize = 10;

if($op=='display'){
	$discount_id = intval($_GPC['discount_id']);
	$discount = pdo_get($this->table_discount, array('uniacid'=>$uniacid, 'discount_id'=>$discount_id));
	if(empty($discount)){
		message('限时折扣活动不存在');
	}
	if($discount['starttime'] > time()){
		message('限时折扣活动未开始');
	}
	if($discount['endtime'] < time()){
		message('限时折扣活动已结束');
	}

	$banner = pdo_fetch("SELECT * FROM " .tablename($this->table_banner). " WHERE uniacid=:uniacid AND banner_type=:banner_type AND link LIKE :link", array(':uniacid'=>$uniacid,':banner_type'=>2, ':link'=>"%&discount_id={$discount_id}&%"));

	$title = $discount['title'];
	$condition = " b.uniacid=:uniacid AND b.discount_id=:discount_id";
	$params[':uniacid'] = $uniacid;
	$params[':discount_id'] = $discount_id;
	
	$list = pdo_fetchall("SELECT a.*,b.discount FROM " . tablename($this->table_lesson_parent) . " a LEFT JOIN " . tablename($this->table_discount_lesson) . " b ON a.id=b.lesson_id WHERE {$condition} LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
	foreach($list as $k=>$v){
		$list[$k]['discount_name'] = $v['discount']*0.1.'折';
		$list[$k]['discount_price'] = round($v['price']*$v['discount']*0.01, 2);
	}
}

if($_W['isajax']){
	echo json_encode($list);
	exit();
}

include $this->template("../mobile/{$template}/discount");

?>