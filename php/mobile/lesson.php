<?php
/**
 * 课程详情页
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */
 
if(!$userAgent && in_array('lesson', $login_visit)){
	checkauth();
}elseif($userAgent && !$comsetting['hidden_login']){
	checkauth();
}

$systemType = $site_common->checkSystenType();

$uid = $_W['member']['uid'];
$site_common->check_black_list('visit', $uid);
$isfollow = json_decode($setting['isfollow'], true); /* 引导关注 */
$index_page  = $common['index_page'];  /* 首页字体 */
$lesson_page = $common['lesson_page']; /* 课程页面字体 */
$lesson_config = json_decode($setting['lesson_config'], true); /* 课程页面设置 */

if($uid && !$_GPC['uid']){
	header("Location:".$_W['siteurl'].'&uid='.$uid);
}

$id = intval($_GPC['id']);/* 课程id */
$sectionid = intval($_GPC['sectionid']);/* 点播章节id */

if($uid>0){
	$member = pdo_fetch("SELECT a.*,b.follow,c.avatar,c.nickname,c.realname,c.mobile,c.msn,c.idcard,c.occupation,c.company,c.graduateschool,c.grade,c.address,c.education,c.position FROM " .tablename($this->table_member). " a LEFT JOIN " .tablename($this->table_fans). " b ON a.uid=b.uid LEFT JOIN " .tablename($this->table_mc_members). " c ON a.uid=c.uid WHERE a.uid=:uid", array(':uid'=>$uid));
}
if(empty($member['avatar'])){
	$avatar = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg";
}else{
	$inc = strstr($member['avatar'], "http://") || strstr($member['avatar'], "https://");
	$avatar = $inc ? $member['avatar'] : $_W['attachurl'].$member['avatar'];
}

$lesson = pdo_fetch("SELECT a.*,b.teacher,b.qq,b.qqgroup,b.qqgroupLink,b.weixin_qrcode,b.online_url,b.teacherphoto,b.teacherdes FROM " .tablename($this->table_lesson_parent). " a LEFT JOIN " .tablename($this->table_teacher). " b ON a.teacherid=b.id WHERE a.uniacid=:uniacid AND a.id=:id AND a.status!=:status LIMIT 1", array(':uniacid'=>$uniacid, ':id'=>$id, ':status'=>0));
if(!$lesson || $lesson['status']==0 || $lesson['status']==2){
	message("该课程已下架，您可以看看其他课程~", "", "error");
}
$lesson['qq'] = $config['teacher_qq'] ? $config['teacher_qq'] : $lesson['qq'];
$lesson['qqgroup'] = $config['teacher_qqgroup'] ? $config['teacher_qqgroup'] : $lesson['qqgroup'];
$lesson['qqgroupLink'] = $config['teacher_qqlink'] ? $config['teacher_qqlink'] : $lesson['qqgroupLink'];
$lesson['weixin_qrcode'] = $config['teacher_qrcode'] ? $config['teacher_qrcode'] : $lesson['weixin_qrcode'];
$lessonNumber = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_lesson_parent). " WHERE teacherid=:teacherid", array(':teacherid'=>$lesson['teacherid']));

/* 课程规格 */
$spec_condition = " uniacid=:uniacid AND lessonid=:lessonid ";
$spec_params = array(
	':uniacid' => $uniacid,
	':lessonid' => $id,
);
if($setting['stock_config']){
	$spec_condition .= " AND spec_stock>:spec_stock";
	$spec_params[':spec_stock'] = 0;
}

$spec_list = pdo_fetchall("SELECT * FROM " .tablename($this->table_lesson_spec). " WHERE {$spec_condition} ORDER BY spec_sort DESC,spec_price ASC", $spec_params);

/* 显示折扣 */
$discount_lesson = pdo_fetch("SELECT * FROM " .tablename($this->table_discount_lesson). " WHERE uniacid=:uniacid AND lesson_id=:lesson_id AND starttime<:time AND endtime>:time", array(':uniacid'=>$uniacid,':lesson_id'=>$id,':time'=>time()));
if(!empty($discount_lesson)){
	foreach($spec_list as $k=>$v){
		$spec_list[$k]['spec_price'] = round($v['spec_price']*$discount_lesson['discount']*0.01, 2);
	}
	$discount_endtime = date('Y/m/d H:i:s', $discount_lesson['endtime']);
	$diacount_price = explode('.', $spec_list[0]['spec_price']);
	$market_price = $lesson['price'];
	$lesson['price'] = round($lesson['price']*$discount_lesson['discount']*0.01, 2);
}

/* 购买讲师价格 */
$teacher_price = pdo_get($this->table_teacher_price, array('uniacid'=>$uniacid, 'teacherid'=>$lesson['teacherid']));

/* 赚取佣金按钮 */
$lesson_commission = unserialize($lesson['commission']);
$commission1 = $lesson_commission['commission1'];
if(empty($commission1)){
	if($member['agent_level']){
		$commission_level = pdo_get($this->table_commission_level, array('id'=>$member['agent_level']));
		$commission1 = $commission_level['commission1'];
	}else{
		$commission = unserialize($comsetting['commission']);
		$commission1 = $commission['commission1'];
	}
}
$commisson1_amount = round($commission1 * $spec_list[count($spec_list)-1]['spec_price'] * 0.01, 2);

//固定金额佣金
if($lesson_commission['commission_type']){
	$commisson1_amount = $commission1;
}

/* 购买按钮名称 */
$buynow_info = json_decode($lesson['buynow_info'], true);
$buynow_name = $buynow_info['buynow_name'] ? $buynow_info['buynow_name'] : $config['buynow_name'];
$buynow_link = $buynow_info['buynow_link'] ? $buynow_info['buynow_link'] : $config['buynow_link'];
$study_name  = $buynow_info['study_name']  ? $buynow_info['study_name']  : $config['study_name'];
$study_link  = $buynow_info['study_link']  ? $buynow_info['study_link']  : $config['study_link'];

if($uid>0){
	/* 查询是否收藏该课程 */
	$collect = pdo_fetch("SELECT * FROM " .tablename($this->table_lesson_collect). " WHERE uniacid=:uniacid AND uid=:uid AND outid=:outid AND ctype=:ctype LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid,':outid'=>$id,':ctype'=>1));

	/* 查询是否购买该课程 */
	$isbuy = pdo_fetch("SELECT * FROM " .tablename($this->table_order). " WHERE uid=:uid AND lessonid=:lessonid AND status>=:status AND (validity>:validity OR validity=0) AND is_delete=:is_delete ORDER BY id DESC LIMIT 1", array(':uid'=>$uid,':lessonid'=>$id,':status'=>1,':validity'=>time(),':is_delete'=>0));
}

/* 标题 */
$title = $lesson['bookname'];

/* 非直播课程 */
if($lesson['lesson_type']!=3){
	/* 章节列表 */
	$section_list = pdo_fetchall("SELECT id FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND status=:status AND auto_show=:auto_show AND show_time<=:show_time", array(':parentid'=>$id, ':status'=>0, ':auto_show'=>1, ':show_time'=>time()));
	foreach($section_list as $item){
	   pdo_update($this->table_lesson_son, array('status'=>1,'auto_show'=>0,'show_time'=>''), array('id'=>$item['id']));
	}

	$first_section = pdo_fetch("SELECT id FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND status=:status ORDER BY displayorder DESC,id ASC LIMIT 1", array(':parentid'=>$id,':status'=>1));

	//已归纳课程目录的章节
	$title_list = pdo_fetchall("SELECT * FROM " .tablename($this->table_lesson_title)." WHERE lesson_id=:lesson_id ORDER BY displayorder DESC,title_id ASC", array('lesson_id'=>$id));
	foreach($title_list as $k=>$v){
		$title_list[$k]['section'] = pdo_fetchall("SELECT * FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC", array(':parentid'=>$id,':title_id'=>$v['title_id'],':status'=>1));
		if(empty($title_list[$k]['section'])){
			unset($title_list[$k]);
		}
	}

	//未归纳课程目录的章节
	$section_list = pdo_fetchall("SELECT * FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC", array(':parentid'=>$id,':title_id'=>0,':status'=>1));

	$section_count = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND status=:status", array(':parentid'=>$id,':status'=>1));
}

/*课程VIP免费学习*/
$level_name = "";
$lesson_vip_list = array();
if(is_array(json_decode($lesson['vipview'])) && $lesson['price']>0){
	foreach(json_decode($lesson['vipview']) as $v){
		$level = $site_common->getLevelById($v);
		if(!empty($level['level_name']) && $level['is_show']==1){
			$level_name .= $level['level_name']."/";
			$lesson_vip_list[] = $level;
		}
	}
	$level_name = trim($level_name, "/");
}

/* 点播章节 */
if($sectionid>0){
	$section = pdo_fetch("SELECT * FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND id=:id AND status=:status LIMIT 1", array(':parentid'=>$id,':id'=>$sectionid,':status'=>1));
}

/**
  $play  用户学习资格标识
  $plays 是否试听用户组
  $show_isbuy 显示开始学习按钮
 */
if($section['is_free']==1){
	$play = true;
	$plays = false;
}
if($lesson['price']==0){
	$play = true;
	$plays = true;
	$show_isbuy = true;
}
if($isbuy){
	if($isbuy['validity']==0){
		$play = true;
		$plays = true;
		$show_isbuy = true;
	}else{
		if($isbuy['validity']>time()){
			$play = true;
			$plays = true;
			$show_isbuy = true;
		}
	}
}
/* 讲师自己课程免费 */
$teacher = pdo_get($this->table_teacher, array('uid'=>$uid), array('id'));

if($lesson['teacherid'] == $teacher['id']){
	$play = true;
	$plays = true;
	$show_isbuy = true;
}

/* 已购买讲师服务 */
$buy_teacher = pdo_fetch("SELECT * FROM " .tablename($this->table_member_buyteacher). " WHERE uid=:uid AND teacherid=:teacherid AND validity>:validity", array(':uid'=>$uid, ':teacherid'=>$lesson['teacherid'], ':validity'=>time()));
if(!empty($buy_teacher)){
	$play = true;
	$plays = false;
	$show_isbuy = true;
}

if($uid){
	/* vip免费学习课程对于普通课程生效 */
	if($lesson['lesson_type']==0 || $lesson['lesson_type']==3){
		$memberVip_list = pdo_fetchall("SELECT level_id FROM  " .tablename($this->table_member_vip). " WHERE uid=:uid AND validity>:validity", array(':uid'=>$uid,':validity'=>time()));
		if(!empty($memberVip_list)){
			foreach($memberVip_list as $v){
				if(in_array($v['level_id'], json_decode($lesson['vipview']))){
					$play = true;
					$plays = true;
					$show_isbuy = true;
					$freeEvaluate = true; //VIP免费评价标识
					$viplesson = true; //vip等级相应课程
					break;
				}
			}
		}
	}

	/* 报名课程核销后才显示购买按钮 */
	if($lesson['lesson_type']==1){
		$apply_order = pdo_fetch("SELECT id,verify_number FROM " .tablename($this->table_order). " WHERE uid=:uid AND lesson_type=:lesson_type AND lessonid=:lessonid AND status>=:status AND is_delete=:is_delete ORDER BY id DESC LIMIT 1", array(':uid'=>$uid,':lesson_type'=>1,':lessonid'=>$id,':status'=>1,':is_delete'=>0));
		if($apply_order){
			$verify_log = $site_common->getOrderVerifyLog($apply_order['id']);
			if($verify_log['count']<$apply_order['verify_number']){
				$show_qrcode = true;
			}
		}
	}

	/* 增加会员课程足迹 */
	$history = pdo_fetch("SELECT * FROM " .tablename($this->table_lesson_history). " WHERE lessonid=:lessonid AND uid=:uid LIMIT 1", array(':lessonid'=>$id,':uid'=>$uid));
	$insertdata = array(
		'uniacid'  => $uniacid,
		'uid'	   => $uid,
		'lessonid' => $id,
		'addtime'  => time(),
	);

	$parent_data = array();
	if($viplesson){
		$insertdata['vipview'] = 1;
		$parent_data['vip_number +='] = 1;
	}
	if($buy_teacher){
		$insertdata['teacherview'] = 1;
		$parent_data['teacher_number +='] = 1;
	}

	
	if(!$history){
		pdo_insert($this->table_lesson_history, $insertdata);
		
		$parent_data['visit_number +='] = 1;
		pdo_update($this->table_lesson_parent, $parent_data, array('id'=>$lesson['id']));
	}else{
		if(($viplesson && !$history['vipview']) || ($buy_teacher && !$history['teacherview'])){
			pdo_update($this->table_lesson_parent, $parent_data, array('id'=>$lesson['id']));
		}
		if((!$history['vipview'] && $insertdata['vipview']) || !$history['teacherview'] && $insertdata['teacherview']){
			pdo_update($this->table_lesson_history, $insertdata, array('lessonid'=>$id,'uid'=>$uid));
		}else{
			pdo_update($this->table_lesson_history, array('addtime'=>time()), array('lessonid'=>$id,'uid'=>$uid));
		}
	}
}

if(!$isbuy && !$viplesson && $lesson['status']=='-1'){
	message("该课程已下架，您可以看看其他课程~");
}

/* 非报名课程检查、报名课程开启后也检查 */
if ($setting['mustinfo']==2 && ($lesson['lesson_type']!=1 || ($lesson['lesson_type']==1 && $setting['appoint_mustinfo']))) {
	$user_info = json_decode($setting['user_info']);
	$jumpurl = $this->createMobileUrl('writemsg', array('lessonid'=>$id, 'sectionid'=>$sectionid, 'type'=>'lesson'));

	if(!empty($common_member_fields)){
		foreach($common_member_fields as $v){
			if(in_array($v['field_short'],$user_info) && empty($member[$v['field_short']])){
				 message("请完善您的".$v['field_name'], $jumpurl, "warning");
			}
		}
	}
}

/* 非直播课程 */
if($sectionid>0 && $lesson['lesson_type']!=3){
	if(empty($section)){
		message("该章节不存在或已被删除！", $this->createMobileUrl('lesson', array('id'=>$id)), "error");
	}

	if(!$play){
		$nobuyTip = $lesson_page['nobuyTip'] ? $lesson_page['nobuyTip'] : '请先购买课程再学习';
		message($nobuyTip, $this->createMobileUrl('lesson', array('id'=>$id)), "warning");
	}

	/**
	 * 视频课程格式
	 * @sectiontype 1.视频章节 2.图文章节 3.音频课程 4、外链章节
	 * @savetype	0.其他存储 1.七牛存储 2.内嵌播放代码模式 3.腾讯云存储 4.阿里云点播 5.腾讯云点播 6.阿里云OSS
	 */
	if(in_array($section['sectiontype'], array('1','3'))){
		/* 已购买用户获取下一个章节id */
		if($show_isbuy){
			$next_sectionid = $site_common->getNextSectionid($section, $title_list);
		}

		if($section['savetype']==1){
			$qiniu = unserialize($setting['qiniu']);
			if($qiniu['https']==1){
				$section['videourl'] = str_replace("http://", "https://", $section['videourl']);
			}
			$section['videourl'] = $site_common->privateDownloadUrl($qiniu['access_key'],$qiniu['secret_key'],$section['videourl']);

		}elseif($section['savetype']==3){
			$qcloud		 = unserialize($setting['qcloud']);
			if($qcloud['https']==1){
				$section['videourl'] = str_replace("http://", "https://", $section['videourl']);
			}
			$section['videourl'] = $site_common->tencentDownloadUrl($qcloud, $section['videourl']);

		}elseif($section['savetype']==4){
			$aliyun = unserialize($setting['aliyunvod']);
			$aliyunVod = new AliyunVod($aliyun['region_id'],$aliyun['access_key_id'],$aliyun['access_key_secret']);

			if($section['sectiontype']==3){/* 音频章节 */
				try {
					$response = $aliyunVod->get_mezzanine_info($section['videourl']);
					$section['videourl'] = $response->Mezzanine->FileURL;
				} catch (Exception $e) {
					message("播放失败，错误原因:".$e->getMessage(), "", "error");
				}

				if(empty($section['videourl'])){
					message("获取播放地址失败，请联系管理员", "", "error");
				}

			}else{/* 视频章节 */
				$file = pdo_get($this->table_aliyun_upload, array('uniacid'=>$uniacid,'videoid'=>$section['videourl']), array('name'));
				$suffix = substr(strrchr($file['name'], '.'), 1);
				$audio = strtolower($suffix)=='mp3' ? true : false;

				try {
					$response = $aliyunVod->getVideoPlayAuth($section['videourl']);
					$playAuth = $response->PlayAuth;
					if(!$audio){
						$m3u8_format = $aliyunVod->get_m3u8_format($section['videourl']);
					}
				} catch (Exception $e) {
					message("播放失败，错误原因:".$e->getMessage(), "", "error");
				}
			}
			
		}elseif($section['savetype']==5){
			$qcloudvod = unserialize($setting['qcloudvod']);
			$newqcloudVod = new QcloudVod($qcloudvod['secret_id'], $qcloudvod['secret_key']);

			
			if($section['sectiontype']==3){/* 音频章节 */
				$section['videourl'] = $newqcloudVod->getUrlPlaySign($qcloudvod['safety_key'],$section['videourl'],$exper='');
				if(empty($section['videourl'])){
					message("获取播放地址失败，请联系管理员", "", "error");
				}

			}else{/* 视频章节 */
				try {
					$qcloudVodRes = $newqcloudVod->getPlaySign($qcloudvod['safety_key'], $qcloudvod['appid'], $section['videourl'], $exper='');
				} catch (Exception $e) {
					message("播放失败，错误原因:".$e->getMessage(), "", "error");
				}
			}
		}elseif($section['savetype']==6){
			include_once dirname(__FILE__).'/../common/AliyunOSS.php';

			$aliyunoss = unserialize($setting['aliyunoss']);
			$params = parse_url($section['videourl']);
			$com_name = trim($params['path'], '/');

			$ossClient = new AliyunOSS($aliyunoss['access_key_id'], $aliyunoss['access_key_secret'], $aliyunoss['endpoint']);
			$default_url = $ossClient->getSignUrl($aliyunoss['bucket'], $com_name, $timeout=7200);
			$section['videourl'] = $site_common->aliyunOssPlayUrl($default_url, $aliyunoss);
		}

		/* 验证访问密码 */
		if($section['password'] && $_W['ispost']){
			$visit_password = trim($_GPC['visit_password']);
			if($section['password'] == $visit_password){
				session_start();
				$_SESSION[$uniacid.'_'.$id.'_'.$sectionid] = true;
			}else{
				message("密码错误，请重新输入");
			}
		}
	}
	
	if($section['sectiontype']==4){
		header("Location:".$section['videourl']);
	}
}

/* 非直播课程，脚部广告 */
if($lesson['lesson_type']!=3){
	$avd = $this->readCommonCache('fy_lesson_'.$uniacid.'_lesson_adv');
	if(empty($avd)){
		$avd = pdo_fetchall("SELECT * FROM " .tablename($this->table_banner). " WHERE uniacid=:uniacid AND is_show=:is_show AND is_pc=:is_pc AND banner_type=:banner_type ORDER BY displayorder DESC", array(':uniacid'=>$uniacid,':is_show'=>1,':is_pc'=>0, 'banner_type'=>1));
		cache_write('fy_lesson_'.$uniacid.'_lesson_adv', $avd);
	}
	if(!empty($avd)){
		$advs = array_rand($avd,1);
		$advs = $avd[$advs];
	}
}

/* 构造分享信息开始 */
$share_info = json_decode($lesson['share'], true);    /* 课程单独分享信息 */
$sharelesson = unserialize($comsetting['sharelesson']);  /* 全局课程分享信息 */

if(!empty($share_info['title'])){
	$sharelesson['title'] = $share_info['title'];
}else{
	$sharelesson['desc'] = $sharelesson['title'];
	if(empty($section)){
		$sharelesson['title'] = $lesson['bookname'];
	}else{
		$sharelesson['title'] = $section['title'].' - '.$lesson['bookname'];
	}
}

$sharelesson['desc'] = $share_info['descript'] ? $share_info['descript'] : str_replace("【课程名称】","《".$title."》",$sharelesson['desc']);
$sharelesson['images'] = $share_info['images'] ? $share_info['images'] : $sharelesson['images'];
if(empty($sharelesson['images'])){
	$sharelesson['images'] = $lesson['images'];
}
$sharelesson['images'] = $_W['attachurl'].$sharelesson['images'];

$sharelesson['link'] = $_W['siteroot'] .'app/'. $this->createMobileUrl('lesson', array('id'=>$id,'sectionid'=>$sectionid,'uid'=>$uid));
/* 构造分享信息结束 */


if($op=='display'){
	/* 详情、目录切换 */
	if($lesson['lesson_show']==1 || $section['content']){
		$show_defails = true;
	}elseif($lesson['lesson_type']==0 && !$section['content'] && $sectionid){
		$show_dir = true;
	}elseif($lesson['lesson_show']==2 && !$section['content']){
		$show_dir = true;
	}else{
		if($setting['lesson_show']==0){
			$show_defails = true;
		}
		if(!$section['content'] && $setting['lesson_show']==1){
			$show_dir = true;
		}
	}

	/* 课件资料 */
	$document_list = pdo_fetchall("SELECT * FROM " .tablename($this->table_document). " WHERE uniacid=:uniacid AND lessonid=:lessonid ORDER BY displayorder DESC, id ASC ", array(':uniacid'=>$uniacid,'lessonid'=>$id));

	/* 评价开关 */
	if($isbuy['status']==1){
		$already_evaluate = pdo_fetch("SELECT id FROM " .tablename($this->table_evaluate). " WHERE uid=:uid AND lessonid=:lessonid AND orderid>:orderid ", array(':uid'=>$uid,':lessonid'=>$id,':orderid'=>0));
		if(empty($already_evaluate)){
			$allow_evaluate = true;
			$evaluate_url   = $this->createMobileUrl("evaluate",array('op'=>'display',"orderid"=>$isbuy['id']));
		}
	}else{
		/* 课程价格为免费 或 会员为VIP身份且课程权限为VIP会员免费观看 */
		if($lesson['price']==0 || $freeEvaluate){
			$already_evaluate = pdo_fetch("SELECT id FROM " .tablename($this->table_evaluate). " WHERE uid=:uid AND lessonid=:lessonid AND orderid=:orderid ", array(':uid'=>$uid,':lessonid'=>$id,':orderid'=>0));
			if(empty($already_evaluate)){
				$allow_evaluate = true;
				$evaluate_url   = $this->createMobileUrl("evaluate",array('op'=>'freeorder',"lessonid"=>$id));
			}
		}
	}
	/* 评价总数 */
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_evaluate) . " WHERE lessonid=:lessonid AND status=:status", array(':lessonid'=>$id,':status'=>1));
	 
	/*生成课程参数二维码*/
	$dirpath = "../attachment/images/{$uniacid}/fy_lessonv2/";
	$this->checkdir($dirpath);

	$imagepath = $dirpath."lesson_{$id}.jpg";
	if((!file_exists($imagepath) || time() > filectime($imagepath)+7*86400) && $isfollow['follow_lesson'] && $userAgent){
		$codeArray = array (
			'expire_seconds' => 2592000,
			'action_name' => 'QR_LIMIT_STR_SCENE',
			'action_info' => array (
				'scene' => array (
					'scene_str' => "lesson_{$id}",
				),
			),
		);
		$account_api = WeAccount::create();
		$res = $account_api->barCodeCreateFixed($codeArray);
		if(!empty($res['ticket'])){
			$qrcodeurl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$res['ticket'];

			$site_common->saveImage($qrcodeurl, $dirpath."qrcode_{$id}.", 'lesson_qrcode');
			$site_common->resize($dirpath."qrcode_{$id}.jpg", $dirpath."qrcode_{$id}.jpg", "170", "170", "100");
			$site_common->img_water_mark("../addons/fy_lessonv2/template/mobile/{$template}/images/lesson-qrcode-bg.jpg", $dirpath."qrcode_{$id}.jpg", $dirpath, "lesson_{$id}.jpg", "16", "24");
			unlink($dirpath."qrcode_{$id}.jpg");
		}
	}

	/* 随机获取客服列表 */
	if($_GPC['ispay']==1){
		$service = json_decode($lesson['service'], true);
		$go_home = false;
		if(empty($service)){
			$service = json_decode($setting['qun_service'], true);
			$go_home = $member['gohome'];
		}
		if(!empty($service)){
			$rand = rand(0, count($service)-1);
			$now_service = $service[$rand];
		}
	}

	/* 课程详情页海报入口 */
	$poster_show = false;
	if($setting['lesson_poster_status']==1 && ($lesson['poster_bg'] || $lesson['images'])){
		$poster_show = true;
	}elseif($setting['lesson_poster_status']==2 && $lesson['poster_bg']){
		$poster_show = true;
	}

	/* 好评率 */
	$evaluate_page = $common['evaluate_page'];
	$evaluate_score = pdo_get($this->table_evaluate_score, array('lessonid'=>$id));
	$evaluate_score['score'] = $evaluate_score['score']*100;
	if(!$evaluate_score){
		$evaluate_score['score']			= $lesson['score']*100;
		$evaluate_score['global_score']		= '5.00';
		$evaluate_score['content_score']	= '5.00';
		$evaluate_score['understand_score'] = '5.00';
	}
	$evaluate_score['score'] = $evaluate_score['score']>100 ? 100 : $evaluate_score['score'];
}

/* 图文章节获取上一节和下一节 */
if($section['sectiontype']==2){
	if($section['title_id']){
		$section_sort = pdo_fetchall("SELECT id,parentid,title FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC", array(':parentid'=>$id,':title_id'=>$section['title_id'],':status'=>1));
		foreach($section_sort as $key=>$value){
			if($value['id']==$section['id']){
				$prev_article = $section_sort[$key-1];
				$next_article = $section_sort[$key+1];
			}
		}
		foreach($title_list as $key=>$value){
			if($value['title_id']==$section['title_id']){
				$prev_title = $title_list[$key-1];
				$next_title = $title_list[$key+1];
			}
		}
		if(!$prev_article){
			$prev_article = pdo_fetch("SELECT id,parentid,title FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC LIMIT 1", array(':parentid'=>$id,':title_id'=>$prev_title['title_id'],':status'=>1));
		}
		if(!$next_article){
			$next_article = pdo_fetch("SELECT id,parentid,title FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC LIMIT 1", array(':parentid'=>$id,':title_id'=>$next_title['title_id'],':status'=>1));
		}
	}else{
		$section_sort = pdo_fetchall("SELECT id,parentid,title FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC", array(':parentid'=>$id,':title_id'=>0,':status'=>1));
		foreach($section_sort as $key=>$value){
			if($value['id']==$section['id']){
				$prev_article = $section_sort[$key-1];
				$next_article = $section_sort[$key+1];
			}
		}
	}

	/* 图文章节 */
	include $this->template("../mobile/{$template}/lesson_article");
	exit();
}


/* 直播课程 */
if($lesson['lesson_type']==3){
	$live_info = json_decode($lesson['live_info'], true);
	$starttime = strtotime($live_info['starttime']);
	$endtime = strtotime($live_info['endtime']);
	if(time() < $starttime){
		//未开始
		$count_down  = $starttime - time();
		$live_status = 0;

		if($uid && $_GPC['play']){
			message('直播未开始，请稍等...');
		}
	}elseif(time() > $endtime){
		//已结束
		$icon_live_status = 'icon-live-ended';
		$live_status = -1;

		if($uid && $_GPC['play']){
			message('直播已结束，下次早点来哦');
		}
	}elseif(time() > $starttime && time() < $endtime){
		//直播中
		$icon_live_status = 'lesson-live-starting';
		$live_status = 1;
	}
	//获取直播地址
	if($_GPC['play']){
		$live_url = $site_common->getLiveUrl($setting, $live_info, $play_type='mobile');
	}

	if($_GPC['req_login']){
		checkauth();
	}
	if(!$play && $_GPC['play']){
		if(!$uid){
			checkauth();
		}
		message("请先购买课程再学习");
	}

	/* 验证访问密码 */
	if($_GPC['play'] && $live_info['password'] && $_W['ispost']){
		$visit_password = trim($_GPC['visit_password']);
		if($live_info['password'] == $visit_password){
			session_start();
			$_SESSION[$uniacid.'_'.$id] = true;
		}else{
			message("密码错误，请重新输入");
		}
	}

	/* 聊天室配置 */
	$im_config = json_decode($setting['im_config'], true);

	if($uid && $live_status==1 && $live_info['chatroom']){
		/* 当前用户 */
		$nickname = $member['nickname'] ? $member['nickname'].'('.$uid.')' : '编号'.$uid.'的用户';
		
		if($im_config['type']==2){
			/* 奥点云IM */
			$room_status = true;
			$api = new TisApi($im_config['aodianyun']['accessId'], $im_config['aodianyun']['accessKey']);

			/* 聊天室状态 */
			$chatroom = pdo_fetch("SELECT * FROM " .tablename($this->table_live_chatroom). " WHERE uniacid=:uniacid AND type=:type AND lessonid=:lessonid ORDER BY id ASC", array(':uniacid'=>$uniacid,':type'=>2,':lessonid'=>$id));
			$tisId = $chatroom['roomid'];
			if(empty($tisId)){
				$aodianyun = array(
					's_key'		  => $im_config['aodianyun']['s_key'],
					'filterKeys'  => $im_config['aodianyun']['filterKeys'],
					'description' => $uniacid.'_'.$id.'_'.random(8),
				);
				$res = $api->createTisRoom($aodianyun);
				if($res['Flag']==100){
					$roomid = $res['GroupId'];
					$room_data = array(
						'uniacid'	=> $uniacid,
						'type'		=> 2,
						'lessonid'	=> $id,
						'roomname'	=> $aodianyun['description'],
						'roomid'	=> $res['id'],
						'addtime'	=> time(),
						'endtime'	=> strtotime($live_info['endtime']),
					);
					pdo_insert($this->table_live_chatroom, $room_data);
				}else{
					$create_room_status = -1; //创建聊天室失败
				}
			}
		}
	}

	include $this->template("../mobile/{$template}/lesson_live");
}else{
	include $this->template("../mobile/{$template}/lesson");
}
