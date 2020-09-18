<?php
/**
 * 获取课程评价列表
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */
 
/* 课程id */
$id = intval($_GPC['id']);

$pindex =max(1,$_GPC['page']);
$psize = 10;

$evaluate_list = pdo_fetchall("SELECT a.lessonid,a.bookname,a.nickname,a.grade,a.content,a.reply,a.addtime, b.avatar FROM " .tablename($this->table_evaluate). " a LEFT JOIN " .tablename($this->table_mc_members). " b ON a.uid=b.uid WHERE a.lessonid=:lessonid AND a.status=:status ORDER BY a.type DESC,a.addtime DESC, a.id DESC LIMIT " . ($pindex-1) * $psize . ',' . $psize, array('lessonid'=>$id,':status'=>1));
foreach($evaluate_list as $k=>$v){
	if($v['grade']==1){
		$v['grade'] = "好评";
		$v['grade_ico'] = MODULE_URL.'template/mobile/default/images/oc-h.png';
	}elseif($v['grade']==2){
		$v['grade'] = "中评";
		$v['grade_ico'] = MODULE_URL.'template/mobile/default/images/oc-z.png';
	}elseif($v['grade']==3){
		$v['grade'] = "差评";
		$v['grade_ico'] = MODULE_URL.'template/mobile/default/images/oc-c.png';
	}
	if($setting['show_evaluate_time']){
		$v['addtime'] = date('Y-m-d', $v['addtime']);
	}else{
		$v['addtime'] = '';
	}
	
	if(empty($v['avatar'])){
		$v['avatar'] = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg";
	}else{
		$inc = strstr($v['avatar'], "http://") || strstr($v['avatar'], "https://");
		$v['avatar'] = $inc ? $v['avatar'] : $_W['attachurl'].$v['avatar'];
	}

	$v['content'] = htmlspecialchars_decode($v['content']);

	$evaluate_list[$k] = $v;
}

$this->resultJson($evaluate_list);