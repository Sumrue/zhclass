<?php
/**
 * 推荐板块课程列表
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

$already_study = $common['index_page']['studyNum'] ? $common['index_page']['studyNum'] : '人已学习';
$pindex = max(1, intval($_GPC['page']));
$psize = 10;

if ($op == 'display') {
	$recid = intval($_GPC['recid']);
	$recommend = pdo_fetch("SELECT * FROM " .tablename($this->table_recommend). " WHERE id=:id AND is_show=:is_show", array(':id'=>$recid, ':is_show'=>1));
	if(empty($recommend)){
		message("该推荐版块不存在", "", "error");
	}

	$list = pdo_fetchall("SELECT * FROM " .tablename($this->table_lesson_parent). " WHERE uniacid='{$uniacid}' AND status=1 AND (recommendid='{$recid}' OR (recommendid LIKE '{$recid},%') OR (recommendid LIKE '%,{$recid}') OR (recommendid LIKE '%,{$recid},%')) ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach ($list as $k => $v) {
		$v['soncount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this -> table_lesson_son) . " WHERE parentid=:parentid AND status=:status", array(':parentid'=>$v['id'],':status'=>1));
		if($v['price']>0){
			$v['buyTotal'] = $v['buynum'] + $v['virtual_buynum'] + $v['vip_number'] + $v['teacher_number'];
		}else{
			$v['buyTotal'] = $v['buynum'] + $v['virtual_buynum'] + $v['vip_number'] + $v['teacher_number'] + $v['visit_number'];
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

		$list[$k] = $v;
	}
}

if(!$_W['isajax']){
	include $this->template("../mobile/{$template}/recommend");
}else{
	echo json_encode($list);
}

?>