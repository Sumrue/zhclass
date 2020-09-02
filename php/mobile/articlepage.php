<?php
/**
 * 长文章分页请求
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */


if($_GPC['type']=='lesson'){
	$lessonid = intval($_GPC['id']);
	$sectionid = intval($_GPC['sectionid']);

	$section = pdo_fetch("SELECT * FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND id=:id AND status=:status LIMIT 1", array(':parentid'=>$lessonid,':id'=>$sectionid,':status'=>1));
	if(empty($section)){
		$res = array(
			'msg' => '章节不存在',
		);
		exit(json_encode($res));
	}

	$content = htmlspecialchars_decode($section['content']);
	$page = $_GET["page"] ? intval($_GET["page"]) : 1;
	$CP = new cutpage($content, 5000, $page);
	$pageContent = $CP->cut_str();

	$res = array(
		'content' => $pageContent[$page-1],
	);
	exit(json_encode($res));
}






