<?php
/**
 * 个人中心
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

$memberid = $_W['member']['uid'];
$site_common->check_black_list('visit', $memberid);

$title = "个人中心";
$self_item = $common['self_item'];
$font = json_decode($comsetting['font'], true);


$ucenter_bg = cache_load('fy_lessonv2_'.$uniacid.'_ucenter_bg');
if(!$ucenter_bg){
	$ucenter_bg_data = pdo_get($this->table_banner, array('uniacid'=>$uniacid,'banner_type'=>7,'is_pc'=>0,'is_show'=>1), array('picture'));
	$ucenter_bg = $ucenter_bg_data ? $_W['attachurl'].$ucenter_bg_data['picture'] : MODULE_URL."template/mobile/{$template}/images/agency-top.jpg?v=2";
	cache_write('fy_lessonv2_'.$uniacid.'_ucenter_bg', $ucenter_bg);
}


$memberinfo = pdo_fetch("SELECT uid,mobile,credit1,credit2,nickname,avatar FROM " .tablename($this->table_mc_members). " WHERE uid=:uid LIMIT 1", array(':uid'=>$memberid));
$memberinfo['credit1'] = intval($memberinfo['credit1']);

if(empty($memberinfo['avatar'])){
	$avatar = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg";
}else{
	$inc = strstr($memberinfo['avatar'], "http://") || strstr($memberinfo['avatar'], "https://");
	$avatar = $inc ? $memberinfo['avatar'] : $_W['attachurl'].$memberinfo['avatar'];
}

/* 签到判断 */
$today = date('Y-m-d', time());
$signin_log = pdo_get($this->table_signin, array('uniacid'=>$uniacid,'uid'=>$memberid,'sign_date'=>$today));

/* 订阅模板消息判断 */
$subscribe_msg = pdo_get($this->table_subscribe_msg, array('uid'=>$memberid));
$is_subscribe = empty($subscribe_msg) || $subscribe_msg['subscribe'] ? 1 : 0;

/* VIP等级数量 */
$memberListCount = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_vip_level). " WHERE uniacid=:uniacid AND is_show=:is_show", array(':uniacid'=>$uniacid,':is_show'=>1));

/* 已购VIP数量 */
$memberVipCount = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_member_vip). " WHERE uniacid=:uniacid AND uid=:uid AND validity>:validity", array(':uniacid'=>$uniacid,':uid'=>$memberid,':validity'=>time()));

/* 检查会员是否讲师身份 */
$teacher = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_teacher). " WHERE uniacid=:uniacid AND uid=:uid AND status=:status", array(':uniacid'=>$uniacid,':uid'=>$memberid,':status'=>1));

/* 关注的课程数量 */
$collect_lesson = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_lesson_collect) . " WHERE uid=:uid AND ctype=:ctype", array(':uid'=>$memberid, ':ctype'=>1));

/* 关注的讲师数量 */
$collect_teacher = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_lesson_collect) . " WHERE uid=:uid AND ctype=:ctype", array(':uid'=>$memberid, ':ctype'=>2));

/* 机构名下讲师数量 */
$company_teachers = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_teacher) . " WHERE uniacid=:uniacid AND company_uid=:company_uid", array('uniacid'=>$uniacid,':company_uid'=>$memberid));

/* 可用优惠券数量 */
$coupon_count = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_member_coupon). " WHERE uniacid=:uniacid AND uid=:uid AND status=:status", array(':uniacid'=>$uniacid,':uid'=>$memberid,':status'=>0));

/* 自定义菜单 */
$self_menu = $site_common->getSelfMenu();

/* 手动更新会员头像 */
if($_GPC['updateInfo']){
	$fans = pdo_fetch("SELECT openid FROM " .tablename($this->table_fans). " WHERE uid=:uid", array(':uid'=>$memberid));
	if(!empty($fans['openid'])){
		load()->classs('weixin.account');
		$accObj = WeixinAccount::create($_W['acid']);
		$access_token = $accObj->fetch_token();

		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$fans['openid']."&lang=zh_CN";
        $output = ihttp_get($url);
		$res = json_decode($output['content'], true);
		if($res['subscribe']==0){
			message("获取头像需要关注公众号，请关注后重试", $this->createMobileUrl('follow'), "error");
		}
		$data = array(
			'nickname' => $res['nickname'],
			'avatar'   => $res['headimgurl']
		);

		if($_GPC['back_do']=='mylesson'){
			$jump_url = $this->createMobileUrl('mylesson');
		}else{
			$jump_url = $this->createMobileUrl('self');
		}

		pdo_update($this->table_mc_members, $data, array('uid'=>$memberid));
		message("更新成功", $jump_url, "success");
	}
}

include $this->template("../mobile/{$template}/self");


?>