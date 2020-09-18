<?php
/**
 * 订阅模板消息
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

$uid = $_W['member']['uid'];

if($op == 'display'){
	$subscribe = intval($_GPC['subscribe']);

	if(empty($uid)){
		$data = array(
			'code' => -1,
			'msg'  => '获取会员信息失败，请稍后重试',
		);
		$this->resultJson($data);
	}
	
	$fans = pdo_get($this->table_fans, array('uid'=>$uid), array('openid'));
	
	$record = pdo_get($this->table_subscribe_msg, array('uid'=>$uid));
	$params = array(
		'uniacid'   => $uniacid,
		'uid'		=> $uid,
		'openid'	=> $fans['openid'],
		'subscribe' => $subscribe,
		'update_time' => time(),
	);

	if($record){
		$result = pdo_update($this->table_subscribe_msg, $params, array('id'=>$record['id']));
	}else{
		$params['addtime'] = time();
		$result = pdo_insert($this->table_subscribe_msg, $params);
	}

	if($result){
		$data = array(
			'code' => 0,
			'msg'  => $subscribe ? '订阅消息成功' : '取消订阅成功，您不会再收到课程提醒',
		); 
	}else{
		$data = array(
			'code' => -1,
			'msg'  => $subscribe ? '订阅消息失败，请稍后重试' : '取消订阅消息失败，请稍后重试',
		); 
	}

	$this->resultJson($data);
}

