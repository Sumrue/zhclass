<?php
/**
 * 获取学习人数作为直播人数，过渡使用
 * ============================================================================
 * 版权所有 2015-2018 微课堂团队，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

$lessonid = $_GPC['lessonid'];

$study_number = $this->readCommonCache('fy_lesson_'.$uniacid.'_live_'.$lessonid);
if(empty($study_number)){
	$lesson = pdo_get($this->table_lesson_parent, array('uniacid'=>$uniacid,'id'=>$lessonid),array('buynum','virtual_buynum','vip_number','teacher_number','visit_number','live_info'));
	$live_info = json_decode($lesson['live_info'], true);

	if($lesson['price']>0){
		$study_number = $lesson['buynum']+$lesson['virtual_buynum']+$lesson['vip_number']+$lesson['teacher_number'];
	}else{
		$study_number = $lesson['buynum']+$lesson['virtual_buynum']+$lesson['vip_number']+$lesson['teacher_number']+$lesson['visit_number'];
	}
	$study_number += intval($live_info['virtual_num']);
	cache_write('fy_lesson_'.$uniacid.'_live_'.$lessonid, $study_number);
}

$this->resultJson(intval($study_number));