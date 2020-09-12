<?php
/**
 * 完善手机号码/姓名
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
load()->model('mc');
load()->model('user');
$uid = $_W['member']['uid'];
$member = pdo_get($this->table_mc_members, array('uid'=>$uid));

$smsConfig = json_decode($setting['sms'], true);
if($smsConfig['type']==1){
	$sms = $smsConfig['aliyun'];
}elseif($smsConfig['type']==2){
	$sms = $smsConfig['qcloud'];
}

if($op=='display'){
	$title = '完善信息';
	$user_info = json_decode($setting['user_info'], true);

	if(checksubmit()){
		$data = array();

		if(!empty($common_member_fields)){
			foreach($common_member_fields as $v){
				if(in_array($v['field_short'],$user_info)){
					$data[$v['field_short']] = trim($_GPC[$v['field_short']]);
					if(empty($data[$v['field_short']])){
						message("请填写您的".$v['field_name']);
					}
					if($v['field_short']=='mobile'){
						if(!(preg_match("/1\d{10}/",$data['mobile']))){
							message("您输入的手机号码格式有误");
						}
						$exist = pdo_fetch("SELECT uid FROM " .tablename($this->table_mc_members). " WHERE uniacid=:uniacid AND mobile=:mobile", array(':uniacid'=>$uniacid,':mobile'=>$data['mobile']));
						if(!empty($exist) && $member['mobile']!=$data['mobile']){
							message("该手机号码已存在，请重新输入其他手机号码");
						}

						if($sms['template_id']){
							if($data['mobile'] != $_SESSION['mobile_record']){
								message("验证码已过期，请重新获取");
							}

							$mobile_code = trim($_GPC['verify_code']);
							if(empty($mobile_code)){
								message("请输入的短信验证码");
							}
							if($mobile_code != $_SESSION['mobile_code']){
								message("短信验证码错误");
							}
						}
					}
				}
			}
		}

		$result = pdo_update($this->table_mc_members, $data, array('uid'=>$uid));
		if($result){
			/* 销毁短信验证码 */
			unset($_SESSION['mobile_record']);
			unset($_SESSION['mobile_code']);

			if($_GPC['type']=='vip'){ /* VIP购买 */
				message("完善信息成功", $this->createMobileUrl('vip'), "success");
			}elseif($_GPC['type']=='buylesson'){ /* 课程购买 */
				message("完善信息成功", $this->createMobileUrl('confirm', array('id'=>$_GPC['lessonid'],'spec_id'=>$_GPC['spec_id'])), "success");
			}elseif($_GPC['type']=='lesson'){ /* 课程学习 */
				message("完善信息成功", $this->createMobileUrl('lesson', array('id'=>$_GPC['lessonid'],'sectionid'=>$_GPC['sectionid'])), "success");
			}elseif($_GPC['type']=='buyteacher'){ /* 讲师购买 */
				message("完善信息成功", $this->createMobileUrl('teacher', array('teacherid'=>$_GPC['teacherid'])), "success");
			}else{
				message("完善信息成功", $this->createMobileUrl('writemsg'), "success");
			}
		}else{
			if($_GPC['type']=='vip'){
				message("网络错误，请稍后重试", $this->createMobileUrl('vip'), "error");
			}elseif($_GPC['type']=='confirm' || $_GPC['type']=='lesson'){
				message("网络错误，请稍后重试", $this->createMobileUrl('lesson', array('id'=>$_GPC['lessonid'])), "error");
			}elseif($_GPC['type']=='buyteacher'){
				message("网络错误，请稍后重试", $this->createMobileUrl('teacher', array('teacherid'=>$_GPC['teacherid'])), "error");
			}
		}
	}

}elseif($op=='modifyMobile'){
	$title = '绑定手机';
	$userAgent = $this->checkUserAgent();

	if(empty($sms['template_id'])){
		message("短信验证码未配置，请联系管理员");
	}

	if(checksubmit('submit')){
		$data = array();
		if(!empty($_GPC['mobile'])){
			$data['mobile'] = trim($_GPC['mobile']);
			if(empty($data['mobile'])){
				message("请输入您的手机号码");
			}
			if(!(preg_match("/1\d{10}/",$data['mobile']))){
				message("您输入的手机号码格式有误");
			}
			$exist = pdo_fetch("SELECT uid FROM " .tablename($this->table_mc_members). " WHERE uniacid=:uniacid AND mobile=:mobile", array(':uniacid'=>$uniacid,':mobile'=>$data['mobile']));
			if(!empty($exist)){
				message("该手机号码已存在，请重新输入其他手机号码");
			}

			$mobile_code = trim($_GPC['verify_code']);
			if(empty($mobile_code)){
				message("请输入的短信验证码");
			}
			if($mobile_code != $_SESSION['mobile_code']){
				message("短信验证码错误");
			}
			if($data['mobile'] != $_SESSION['mobile_record']){
				message("验证码已过期，请重新获取");
			}
		}

		if(!empty($_GPC['password'])){
			if(strlen($_GPC['password'])<6 || strlen($_GPC['password'])>20){
				message("密码长度应在6~20位");
			}
			if($_GPC['password'] != $_GPC['password2']){
				message("两次密码不一致");
			}

			$data['password'] = md5($_GPC['password'] . $member['salt'] . $_W['config']['setting']['authkey']);
		}

		$result = pdo_update($this->table_mc_members, $data, array('uid'=>$uid));
		if($result){
			cache_build_memberinfo($uid);
			/* 销毁短信验证码 */
			unset($_SESSION['mobile_record']);
			unset($_SESSION['mobile_code']);

			message($member['mobile'] ? "修改成功" : '绑定成功', $this->createMobileUrl('index'), "success");
		}else{
			message($member['mobile'] ? "修改失败" : '绑定失败', "", "error");
		}
	}
}

include $this->template("../mobile/{$template}/writemsg");

?>