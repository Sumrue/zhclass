<?php
/**
 * VIP等级对应课程给
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */
if((!$userAgent) || ($userAgent && !$comsetting['hidden_login'])){
	checkauth();
}

$level_id = intval($_GPC['level_id']);
$level = pdo_fetch("SELECT * FROM " .tablename($this->table_vip_level). " WHERE uniacid=:uniacid AND id=:id", array(':uniacid'=>$uniacid,':id'=>$level_id));
if(empty($level)){
	message("该VIP等级不存在", "", "error");
}

$already_study = $common['index_page']['studyNum'] ? $common['index_page']['studyNum'] : '人已学习';

$pindex =max(1,$_GPC['page']);
$psize = 10;

$condition = " uniacid=:uniacid AND vipview LIKE :vipview AND (status=:status1 OR status=:status2)";
$params[':uniacid'] = $uniacid;
$params[':vipview'] = '%"'.$level_id.'"%';
$params[':status1'] = -1;
$params[':status2'] = 1;

$list = pdo_fetchall("SELECT id,lesson_type,bookname,images,price,section_status,score,buynum,virtual_buynum,vip_number,teacher_number,visit_number,section_status,live_info FROM " .tablename($this->table_lesson_parent). " WHERE {$condition} ORDER BY displayorder DESC  LIMIT " . ($pindex-1) * $psize . ',' . $psize, $params);

if($op=='display'){
	$title = '【'.$level['level_name'].'】课程列表';

}elseif($op=='ajaxgetlist'){
	foreach($list as $key=>$value){
		if($value['price']>0){
			$value['buyTotal'] = $value['buynum'] + $value['virtual_buynum'] + $value['vip_number'] + $value['teacher_number'];
		}else{
			$value['buyTotal'] = $value['buynum'] + $value['virtual_buynum'] + $value['vip_number'] + $value['teacher_number'] + $value['visit_number'];
		}
		$value['soncount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_lesson_son). " WHERE  parentid=:parentid", array(':parentid'=>$value['id']));
		if($value['score']>0){
			$value['score_rate'] = $value['score']*100;
		}else{
			$value['score_rate'] = "";
		}

		if($value['lesson_type']==3){
			$live_info = json_decode($value['live_info'], true);
			$starttime = strtotime($live_info['starttime']);
			$endtime = strtotime($live_info['endtime']);
			if(time() < $starttime){
				$value['icon_live_status'] = 'icon-live-nostart';
			}elseif(time() > $endtime){
				$value['icon_live_status'] = 'icon-live-ended';
			}elseif(time() > $starttime && time() < $endtime){
				$value['icon_live_status'] = 'icon-live-starting';
			}
			$value['learned_hide'] = 'hide';
			unset($value['soncount']);
		}
		$list[$key] = $value;
	}

	$this->resultJson($list);

}


include $this->template("../mobile/{$template}/viplesson");