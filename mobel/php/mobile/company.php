<?php
/**
 * 机构中心
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

if($op=='display'){
	$title = "我的机构中心";

	$member = pdo_fetch("SELECT a.*,b.nickname,b.avatar FROM " .tablename($this->table_member). " a LEFT JOIN " .tablename($this->table_mc_members). " b ON a.uid=b.uid WHERE a.uid=:uid", array(':uid'=>$uid));
	if(empty($member['avatar'])){
		$avatar = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg";
	}else{
		$inc = strstr($member['avatar'], "http://") || strstr($member['avatar'], "https://");
		$avatar = $inc ? $member['avatar'] : $_W['attachurl'].$member['avatar'];
	}

	/* 已购买VIP等级 */
	$memberVip = pdo_fetchall("SELECT * FROM " .tablename($this->table_member_vip). " WHERE uid=:uid AND validity>:validity", array(':uid'=>$uid,':validity'=>time()));
	$cash_lower = empty($memberVip) ? $comsetting['cash_lower_common'] : $comsetting['cash_lower_vip'];

	/* 机构累计收入 */
	$change_nums = pdo_fetchall("SELECT SUM(change_num) AS amount FROM " .tablename($this->table_commission_log). " WHERE uniacid=:uniacid AND uid=:uid AND company_income=:company_income", array(':uniacid'=>$uniacid,':uid'=>$uid,':company_income'=>1));
	$income_amount = $change_nums[0]['amount'] ? $change_nums[0]['amount'] : '0';

	/* 机构下属讲师总数 */
	$myteachercount = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_teacher) . " WHERE uniacid=:uniacid AND status=:status AND company_uid=:company_uid", array(':uniacid'=>$uniacid,':status'=>1,':company_uid'=>$uid));

}elseif($op=='myteacher'){
	$title = "我的讲师";

	$condition = " uniacid=:uniacid AND status=:status AND company_uid=:company_uid ";
	$params[':uniacid'] = $uniacid;
	$params[':status']  = 1;
	$params[':company_uid']  = $uid;

	$teacherlist = pdo_fetchall("SELECT id,teacher,teacherdes,teacherphoto FROM " .tablename($this->table_teacher). " WHERE {$condition} ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex-1) * $psize . ',' . $psize, $params);
	foreach($teacherlist as $key=>$value){
		$value['teacherdes'] = strip_tags(htmlspecialchars_decode($value['teacherdes']));
		$value['lessonCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_lesson_parent). " WHERE teacherid=:teacherid AND status=:status", array(':teacherid'=>$value['id'], ':status'=>1));

		$teacherlist[$key] = $value;
	}

	if($_GPC['ajax']){
		$this->resultJson($teacherlist);
	}
}elseif($op=='income'){
	$title = "机构收入明细";

	$condition = " uid=:uid AND company_income=:company_income ";
	$params = array(
		':uid' => $uid,
		':company_income' => 1
	);

	$list = pdo_fetchall("SELECT * FROM " .tablename($this->table_commission_log). " WHERE {$condition} ORDER BY id DESC LIMIT " . ($pindex-1) * $psize . ',' . $psize, $params);

	foreach($list as $key=>$value){
		$list[$key]['remarks'] = '['.$value['bookname'].']课程出售';
		$list[$key]['addtime'] = date("Y-m-d", $value['addtime']);
	}
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_commission_log). " WHERE {$condition}", $params);

	$title .= "(".$total.")";

	if($_GPC['ajax']){
		echo json_encode($list);
		exit();
	}
}

include $this->template("../mobile/{$template}/company");

?>