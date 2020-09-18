<?php
/**
 * 定时课程提醒
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

set_time_limit(180);

$hour = date('H', time());
if($hour<7 || $hour>=22){
	echo 'The send message is not running at the current time.';
	exit();
}

$list = pdo_fetchall("SELECT * FROM " .tablename($this->table_inform). " WHERE uniacid=:uniacid AND status=:status", array(':uniacid'=>$uniacid, ':status'=>1));
foreach($list as $v){
	$tplmessage = pdo_fetch("SELECT newlesson FROM " .tablename($this->table_tplmessage). " WHERE uniacid=:uniacid", array(':uniacid'=>$v['uniacid']));

	$fans_list = pdo_fetchall("SELECT * FROM " .tablename($this->table_inform_fans). " WHERE inform_id=:inform_id LIMIT 0,180", array(':inform_id'=>$v['inform_id'],));

	if(empty($tplmessage['newlesson'])){
		continue;
	}
	if(empty($fans_list) || time()-86400 > $v['addtime']){
		pdo_update($this->table_inform, array('status'=>0), array('inform_id'=>$v['inform_id']));
		pdo_delete($this->table_inform_fans, array('inform_id'=>$v['inform_id']));
		continue;
	}

	$data = json_decode($v['content'], true);
	$url = $data['link'] ? $data['link'] : $_W['siteroot'] . 'app/' . $this->createMobileUrl('lesson', array('id'=>$v['lesson_id']));
	$message = array(
		'template_id' => $tplmessage['newlesson'],
		'url'		  => $url,
		'topcolor'	  => "#222222",
		'data'		  => array(
			'first'  => array(
				'value' => $data['first'],
				'color' => "#222222",
			),
			'keyword1' => array(
				'value' => $data['keyword1'],
				'color' => "#222222",
			),
			'keyword2' => array(
				'value' => $data['keyword2'],
				'color' => "#222222",
			),
			'keyword3' => array(
				'value' => $data['keyword3'],
				'color' => "#222222",
			),
			'keyword4' => array(
				'value' => $data['keyword4'],
				'color' => "#222222",
			),
			'remark' => array(
				'value' => $data['remark'],
				'color' => "#222222",
			),
		)
	);

	foreach($fans_list as $item){
		$message['touser'] = $item['openid'];
		$this->send_template_message($message);
		pdo_delete($this->table_inform_fans, array('inform_fans_id'=>$item['inform_fans_id']));
		sleep(1);
	}

	if(count($fans_list) < 180){
		pdo_update($this->table_inform, array('status'=>0), array('inform_id'=>$v['inform_id']));
	}
}
exit();



?>