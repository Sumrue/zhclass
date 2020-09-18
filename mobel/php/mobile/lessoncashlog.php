<?php
/**
 * 讲师收入提现记录
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

$typeStatus = new TypeStatus();
//提现状态集合
$cashStatusList = $typeStatus->cashStatus();
//提现方式集合
$cashWayList = $typeStatus->cashWayList();

$uid = $_W['member']['uid'];

$pindex =max(1,$_GPC['page']);
$psize = 10;

$list = pdo_fetchall("SELECT * FROM " .tablename($this->table_cashlog). " WHERE uid=:uid AND lesson_type=:lesson_type ORDER BY id DESC LIMIT " . ($pindex-1) * $psize . ',' . $psize, array(':uid'=>$uid, ':lesson_type'=>2));
foreach($list as $k=>$v){
	$v['cash_way'] = $cashWayList[$v['cash_way']];
	$v['statu'] = $cashStatusList[$v['status']];
	$v['disposetime'] = $v['disposetime'] ? date("Y-m-d H:i", $v['disposetime']) : "";
	$v['remark'] = $v['remark'] ? $v['remark'] : "";
	$v['addtime'] = date("Y-m-d", $v['addtime']);

	$list[$k] = $v;
}
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_cashlog). " WHERE uid=:uid AND lesson_type=:lesson_type", array(':uid'=>$uid, ':lesson_type'=>2));

$title = "讲师收入提现记录(".$total.")";

if(!$_W['isajax']){
	include $this->template("../mobile/{$template}/lessoncashlog");
}else{
	echo json_encode($list);
}


?>