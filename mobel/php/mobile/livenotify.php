<?php
/**
 * 直播录制回调
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

$type = intval($_GPC['type']); /* 1.腾讯云直播 2.阿里云直播 */

if($type==1){
	$stream		= $_GPC['__input']['stream_id'];
	$videoid	= $_GPC['__input']['record_file_id'];
	$video_url	= $_GPC['__input']['video_url'];
	$duration	= intval($_GPC['__input']['duration']);
	$file_size	= round(($_GPC['__input']['file_size']/1024)/1024, 2);
	if(empty($stream) || empty($videoid)){
		return;
	}

	$explode = explode("_", $stream);
	$uniacid = $explode[1];
	$lessonid = $explode[2];
	$lesson = pdo_get($this->table_lesson_parent, array('uniacid'=>$uniacid,'id'=>$lessonid));
	if(empty($lesson)){
		return;
	}

	//添加章节
	$data = array();
	$data['uniacid']		= $uniacid;
	$data['parentid']		= $lessonid;
	$data['title']			= "直播回放".date('Y-m-d-H-i', time());
	$data['sectiontype']	= 1;
	$data['savetype']		= 5;
	$data['is_live']		= 0;
	$data['videourl']		= $videoid;
	$data['videotime']		= $site_common->secToTime($duration, false);
	$data['is_free']	    = 0;
	$data['status']			= 1;
	$data['auto_show']		= 0;
	$data['addtime']		= time();
	pdo_insert($this->table_lesson_son, $data);

	//添加到腾讯云点播存储表
	$qcloud = array();
	$qcloud['uniacid']		= $uniacid;
	$qcloud['uid']			= 0;
	$qcloud['teacherid']	= 0;
	$qcloud['name']			= $data['title'];
	$qcloud['videoid']		= $videoid;
	$qcloud['videourl']		= $video_url;
	$qcloud['size']			= $file_size;
	$qcloud['addtime']		= time();
	pdo_insert($this->table_qcloudvod_upload, $qcloud);

}elseif($type==2){
	$aliyunoss = unserialize($setting['aliyunoss']);

	$stream	  = $_GPC['__input']['stream'];
	$uri	  = $_GPC['__input']['uri'];
	$duration = intval($_GPC['__input']['duration']);
	if(empty($stream) || empty($uri)){
		return;
	}

	$explode = explode("_", $stream);
	$uniacid = $explode[1];
	$lessonid = $explode[2];
	$lesson = pdo_get($this->table_lesson_parent, array('uniacid'=>$uniacid,'id'=>$lessonid));
	if(empty($lesson)){
		return;
	}

	//添加章节
	$data = array();
	$data['uniacid']		= $uniacid;
	$data['parentid']		= $lessonid;
	$data['title']			= "直播回放".date('Y-m-d-H-i', time());
	$data['sectiontype']	= 1;
	$data['savetype']		= 6;
	$data['is_live']		= 0;
	$data['videourl']		= 'http://'.$aliyunoss['bucket_url'].'/'.$uri;
	$data['videotime']		= $site_common->secToTime($duration, false);
	$data['is_free']	    = 0;
	$data['status']			= 1;
	$data['auto_show']		= 0;
	$data['addtime']		= time();
	pdo_insert($this->table_lesson_son, $data);

	//添加到阿里云oss存储表
	$oss = array();
	$oss['uniacid']		= $uniacid;
	$oss['uid']			= 0;
	$oss['teacherid']	= 0;
	$oss['name']		= $data['title'];
	$oss['com_name']	= $uri;
	$oss['addtime']		= time();
	pdo_insert($this->table_aliyunoss_upload, $oss);
}