<?php
/**
 * 讲师列表
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

$common = json_decode($setting['common'], true);
$self_item = $common['self_item'];

$pindex =max(1,$_GPC['page']);
$psize = 10;

$condition = " uniacid=:uniacid AND status=:status ";
$params[':uniacid'] = $uniacid;
$params[':status']  = 1;

$keyword = trim($_GPC['keyword']);
if(!empty($keyword)){
	$condition .= " AND teacher LIKE :teacher ";
	$params[':teacher'] = "%{$keyword}%";
}
$teacherlist = pdo_fetchall("SELECT id,teacher,teacherdes,digest,teacherphoto FROM " .tablename($this->table_teacher). " WHERE {$condition} ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex-1) * $psize . ',' . $psize, $params);
foreach($teacherlist as $k=>$v){
	$v['teacherdes'] = $v['digest'] ? explode("\n", $v['digest']) : explode("\n", strip_tags(htmlspecialchars_decode($v['teacherdes'])));
	$v['lessonCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_lesson_parent). " WHERE teacherid=:teacherid AND status=:status", array(':teacherid'=>$v['id'], ':status'=>1));

	$teacherlist[$k] = $v;
}

if($op=='display'){
	$title = $common['page_title']['teacherlist'] ? $common['page_title']['teacherlist'] : '讲师列表';

}elseif($op=='ajaxgetteacherlist'){
	$this->resultJson($teacherlist);;
}

include $this->template("../mobile/{$template}/teacherlist");