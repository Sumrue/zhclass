<?php
/**
 * 我的足迹
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
$already_study = $common['index_page']['studyNum'] ? $common['index_page']['studyNum'] : '人已学习';

$pindex =max(1,$_GPC['page']);
$psize = 10;

$condition = " b.uniacid=:uniacid AND b.uid=:uid ";
$params[':uniacid'] = $uniacid;
$params[':uid'] = $uid;

$lessonlist = pdo_fetchall("SELECT a.id,a.lesson_type,a.bookname,a.images,a.price,a.score,a.section_status,a.ico_name,a.buynum+a.virtual_buynum+a.vip_number AS buynum,a.visit_number,a.live_info, b.addtime FROM " .tablename($this->table_lesson_parent). " a LEFT JOIN " .tablename($this->table_lesson_history). " b ON a.id=b.lessonid WHERE {$condition} ORDER BY b.addtime DESC  LIMIT " . ($pindex-1) * $psize . ',' . $psize, $params);
foreach($lessonlist as $k=>$v){
	$v['addtime'] = date('Y-m-d H:i', $v['addtime']);
	$v['sectionid'] = $v['section_title'] = $v['progress'] = $v['playtime'] = $v['duration'] = '';
	
	$play_record = pdo_fetch("SELECT * from " .tablename($this->table_playrecord). " WHERE uniacid=:uniacid AND uid=:uid AND lessonid=:lessonid ORDER BY addtime DESC LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid,':lessonid'=>$v['id']));
	if(!empty($play_record)){
		$section = pdo_get($this->table_lesson_son, array('uniacid'=>$uniacid,'id'=>$play_record['sectionid']), array('id','title'));
		$v['sectionid'] = $section['id'];
		$v['section_title'] = $section['title'];
		if($play_record['duration']){
			$v['duration'] = $play_record['duration'];
			$v['progress'] = round($play_record['playtime']/$play_record['duration'],2)*100;
		}
		$v['playtime'] = gmstrftime('%H:%M:%S', $play_record['playtime']);
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
	}

	$lessonlist[$k] = $v;
}

if($op=='display'){
	$title = '我的足迹';

}elseif($op=='ajaxgetlist'){
	$this->resultJson($lessonlist);

}

include $this->template("../mobile/{$template}/history");
