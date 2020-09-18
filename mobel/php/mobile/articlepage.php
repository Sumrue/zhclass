<?php



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






