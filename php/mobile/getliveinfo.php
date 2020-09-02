<?php
/**
 * 获取直播课程信息
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

$type = intval($_GPC['type']);

if($type==1){
	//获取直播设置在线人数
	$lessonid = intval($_GPC['lessonid']);
	$cache_data = cache_load('fy_lesson_'.$uniacid.'_'.$lessonid.'_lessonLiveNumber');
	if(empty($cache_data) || $cache_data['time']+15 < time()){
		$cache_data = array();
		$lesson = pdo_get($this->table_lesson_parent, array('uniacid'=>$uniacid,'id'=>$lessonid,'lesson_type'=>3), array('live_info'));
		if(empty($lesson)){
			$number = 0;
		}else{
			$live_info = json_decode($lesson['live_info'], true);
			$number = intval($live_info['virtual_num']);
		}

		$cache_data['time']   = time();
		$cache_data['number'] = $number;
		cache_write('fy_lesson_'.$uniacid.'_'.$lessonid.'_lessonLiveNumber', $cache_data);
	}
	
	$this->resultJson(array('number'=>$cache_data['number']));
}


exit();
?>