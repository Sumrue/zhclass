<?php
/**
 * PC端登录
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

if($op=='display'){
	$login_token = trim($_GPC['login_token']);

	if(!$login_token){
		header("Location:".$this->createMobileUrl('pclogin', array('op'=>'return','return_type'=>'warn','message'=>'二维码已过期，请刷新页面重新扫码')));
		exit();
	}

	$record = pdo_get($this->table_login_pc, array('uniacid'=>$uniacid, 'login_token'=>$login_token));
	if(empty($record)){
		$login_data = array(
			'uniacid' => $uniacid,
			'login_token' => $login_token,
			'status' => 0,
			'addtime' => time()
		);
		pdo_insert($this->table_login_pc, $login_data);
		$record['id'] = pdo_insertid();
	}else{
		if($record['status'] != 0){
			header("Location:".$this->createMobileUrl('pclogin', array('op'=>'return','return_type'=>'warn','message'=>'二维码已过期，请刷新页面重新扫码')));
			exit();
		}
	}

	if(checksubmit()){
		$result = pdo_update($this->table_login_pc, array('uid'=>$uid,'status'=>1,'login_time'=>time()), array('id'=>$record['id']));
		if($result){
			header("Location:".$this->createMobileUrl('pclogin', array('op'=>'return','return_type'=>'success','message'=>'授权登陆成功')));
			exit();
		}else{
			header("Location:".$this->createMobileUrl('pclogin', array('op'=>'return','return_type'=>'warn','message'=>'系统繁忙，请稍候重新扫码登录')));
			exit();
		}
	}

}


include $this->template("../mobile/{$template}/pclogin");

?>