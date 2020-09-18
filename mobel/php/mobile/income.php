<?php
/**
 * 讲师收入明细
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

$pindex =max(1,$_GPC['page']);
$psize = 10;

$list = pdo_fetchall("SELECT * FROM " .tablename($this->table_teacher_income). " WHERE uniacid=:uniacid AND uid=:uid ORDER BY id DESC LIMIT " . ($pindex-1) * $psize . ',' . $psize, array(':uniacid'=>$uniacid, ':uid'=>$uid));
foreach($list as $key=>$value){
	$list[$key]['remark']  = "课程售价：".$value['orderprice']." 元，收入提成：".$value['teacher_income']."%";
	$list[$key]['addtime'] = date("Y-m-d", $value['addtime']);
}
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_teacher_income). " WHERE uniacid=:uniacid AND uid=:uid", array(':uniacid'=>$uniacid, ':uid'=>$uid));

$title = "我的收入明细(".$total.")";

if(!$_W['isajax']){
	include $this->template("../mobile/{$template}/incomelog");
}else{
	echo json_encode($list);
}


?>