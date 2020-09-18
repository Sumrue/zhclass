<?php
/**
 * 收藏课程/讲师
 * ============================================================================
 */

checkauth();

$ctype = intval($_GPC['ctype']); /* 收藏类型 */
$uid = $_W['member']['uid'];
$already_study = $common['index_page']['studyNum'] ? $common['index_page']['studyNum'] : '人已学习';

$pindex =max(1,$_GPC['page']);
$psize = 10;

$condition = " b.uniacid=:uniacid AND b.uid=:uid ";
$params[':uniacid'] = $uniacid;
$params[':uid'] = $uid;

if($ctype==1){
	$title = '我收藏的课程';
	$condition .= "  AND b.ctype=:ctype ";
	$params[':ctype'] = 1;
	
	$lessonlist = pdo_fetchall("SELECT a.id,a.lesson_type,a.bookname,a.images,a.price,a.score,a.ico_name,a.buynum,a.virtual_buynum,a.vip_number,a.teacher_number,a.section_status,a.live_info FROM " . tablename($this->table_lesson_parent) . " a LEFT JOIN " .tablename($this->table_lesson_collect). " b ON a.id=b.outid WHERE {$condition} ORDER BY b.addtime DESC  LIMIT " . ($pindex-1) * $psize . ',' . $psize, $params);
	foreach($lessonlist as $k=>$v){
		$v['soncount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_lesson_son) . " WHERE parentid=:parentid", array(':parentid'=>$v['id']));
		if($v['price']>0){
			$v['buynum_total'] = $v['buynum'] + $v['virtual_buynum'] + $v['vip_number'] + $v['teacher_number'];
		}else{
			$v['buynum_total'] = $v['buynum'] + $v['virtual_buynum'] + $v['vip_number'] + $v['teacher_number'] + $v['visit_number'];
		}
		if($v['score']>0){
			$v['score_rate'] = $v['score']*100;
		}else{
			$v['score_rate'] = "";
		}

		$v['discount'] = $site_common->getLessonDiscount($v['id']);
		$v['price'] = round($v['price']*$v['discount'], 2);
		if($v['discount']<1 && !$v['ico_name']){
			$v['ico_name'] = 'ico-discount';
		}

		if($v['lesson_type']==3){
			$live_info = json_decode($v['live_info'], true);
			$starttime = strtotime($live_info['starttime']);
			$endtime = strtotime($live_info['endtime']);
			if(time() < $starttime){
				$v['icon_live_status'] = 'icon-live-nostart';
			}elseif(time() > $endtime){
				$v['icon_live_status'] = 'icon-live-ended';
			}elseif(time() > $starttime && time() < $endtime){
				$v['icon_live_status'] = 'icon-live-starting';
			}
			$v['learned_hide'] = 'hide';
			unset($v['soncount']);
		}

		$lessonlist[$k] = $v;
	}

}elseif($ctype==2){
	$title = '我收藏的讲师';
	$condition .= "  AND b.ctype=:ctype ";
	$params[':ctype'] = 2;
	
	$teacherlist = pdo_fetchall("SELECT a.id,a.teacher,a.teacherdes,a.teacherphoto FROM " . tablename($this->table_teacher) . " a LEFT JOIN " .tablename($this->table_lesson_collect). " b ON a.id=b.outid WHERE {$condition} ORDER BY b.addtime DESC  LIMIT " . ($pindex-1) * $psize . ',' . $psize, $params);
	foreach($teacherlist as $k=>$v){
		$v['teacherdes'] = strip_tags(htmlspecialchars_decode($v['teacherdes']));
		$v['lessonCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_lesson_parent). " WHERE teacherid=:teacherid AND status=:status", array(':teacherid'=>$v['id'], ':status'=>1));

		$teacherlist[$k] = $v;
	}
}

if($op=='ajaxgetlesson'){
	$this->resultJson($lessonlist);

}elseif($op=='ajaxgetteacher'){
	$this->resultJson($teacherlist);

}else{
	include $this->template("../mobile/{$template}/collect");

}
