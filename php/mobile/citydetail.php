<?php
// 城市选择中具体城市专业页
$memberid = $_W['member']['uid'];

$memberinfo = pdo_fetch("SELECT uid,mobile,credit1,credit2,nickname,avatar FROM " .tablename($this->table_mc_members). " WHERE uid=:uid LIMIT 1", array(':uid'=>$memberid));
$memberinfo['credit1'] = intval($memberinfo['credit1']);

if(empty($memberinfo['avatar'])){
  // 无头像时默认头像
	$avatar = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg"; 
}else{
  // $avatar 为引入头像变量
	$inc = strstr($memberinfo['avatar'], "http://") || strstr($memberinfo['avatar'], "https://");
	$avatar = $inc ? $memberinfo['avatar'] : $_W['attachurl'].$memberinfo['avatar'];
}
include $this->template("../mobile/{$template}/citydetail");
?>