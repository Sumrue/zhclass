<?php

if(!$_W['isajax']){
	$this->resultJson('Illegal Request');
}

$lessonid    = intval($_GPC['lessonid']);
$sectionid   = intval($_GPC['sectionid']);
$uid	     = $_W['member']['uid'];

if(empty($uid)){
	$this->resultJson('Uid is null');
}

if($op=='display'){
	$currentTime = intval($_GPC['currentTime']);
	$duration = intval($_GPC['duration']);

	if(empty($currentTime)){
		$this->resultJson('CurrentTime is null');
	}

	$record = pdo_fetch("SELECT * FROM " .tablename($this->table_playrecord). " WHERE uniacid=:uniacid AND uid=:uid AND lessonid=:lessonid AND sectionid=:sectionid LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid,':lessonid'=>$lessonid,':sectionid'=>$sectionid));
	$data = array(
		'uniacid'	 => $uniacid,
		'uid'		 => $uid,
		'lessonid'   => $lessonid,
		'sectionid'  => $sectionid,
		'playtime'	 => $currentTime,
		'duration'	 => $duration,
		'addtime'	 => time(),
	);

	if(empty($record)){
		$r = pdo_insert($this->table_playrecord, $data);
	}else{
		$r = pdo_update($this->table_playrecord, $data, array('uniacid'=>$uniacid,'uid'=>$uid,'lessonid'=>$lessonid, 'sectionid'=>$sectionid));
	}

	if($r){
		$this->resultJson('Recode success');
	}else{
		$this->resultJson('Recode error');
	}

}elseif($op=='realPlay'){
	$realPlayTime = intval($_GPC['realPlayTime']);
	$sectiontype  = intval($_GPC['sectiontype']);

	$studyLog = pdo_get($this->table_study_duration, array('uniacid'=>$uniacid,'uid'=>$uid,'date'=>date('Ymd',time())));
	$study_data = array(
		'uniacid'	 => $uniacid,
		'uid'		 => $uid,
		'date'		 => date('Ymd',time()),
	);

	if($sectiontype==1){
		//视频章节
		$member_duration['video_duration +='] = $realPlayTime;
		if(empty($studyLog)){
			$study_data['video'] = $realPlayTime;
		}else{
			$study_data['video +='] = $realPlayTime;
		}
		
	}elseif($sectiontype==2){
		//图文章节
		$member_duration['article_duration +='] = $realPlayTime;
		if(empty($studyLog)){
			$study_data['article'] = $realPlayTime;
		}else{
			$study_data['article +='] = $realPlayTime;
		}

	}elseif($sectiontype==3){
		//音频章节
		$member_duration['audio_duration +='] = $realPlayTime;
		if(empty($studyLog)){
			$study_data['audio'] = $realPlayTime;
		}else{
			$study_data['audio +='] = $realPlayTime;
		}
	}
	$member_duration['duration_uptime'] = time();

	pdo_update($this->table_member, $member_duration, array('uid'=>$uid));
	if(empty($studyLog)){
		$r = pdo_insert($this->table_study_duration, $study_data);
	}else{
		$r = pdo_update($this->table_study_duration, $study_data, array('study_id'=>$studyLog['study_id']));
	}

	if($r){
		$this->resultJson('Real play time success');
	}else{
		$this->resultJson('Real play time error');
	}
}
