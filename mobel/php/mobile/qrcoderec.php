<?php
/**
 * 二维码推广
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
load()->model('mc');

$font = json_decode($comsetting['font'], true);
$title = $font['poster'] ? $font['poster'] : "我的海报";


if($comsetting['is_sale']==0){
	message("系统未开启该功能", "", "warning");
}

$member = pdo_fetch("SELECT a.*,b.avatar,b.nickname AS mc_nickname FROM " .tablename($this->table_member). " a LEFT JOIN ".tablename($this->table_mc_members). " b ON a.uid=b.uid WHERE a.uniacid=:uniacid AND a.uid=:uid", array(':uniacid'=>$uniacid,':uid'=>$uid));

if(!empty($member)){
	$infourl = $_W['siteroot'] .'app/'. $this->createMobileUrl('index', array('uid'=>$uid));
}

if($member['status']!=1){
	message("您的身份为冻结状态", $this->createMobileUrl('index'), "warning");
}
$sale_desc = $comsetting['sale_desc'] ? explode("\n", $comsetting['sale_desc']) : "";
$sale_content = "";
foreach($sale_desc as $k=>$v){
	$sale_desc[$k] = trim($v);
}

/* 海报配置参数 */
$poster_list = cache_load('fy_lesson_'.$uniacid.'_poster_list');
if(empty($poster_list)){
	$poster_list = pdo_getall($this->table_poster, array('uniacid'=>$uniacid,'poster_type'=>1));
	if(empty($poster_list)){
		$poster_list[0] = array(
			'poster_default' => true,
			'poster_bg' => MODULE_URL."template/mobile/{$template}/images/posterbg.jpg",
			'poster_setting' => array(
				array(
					'left' => '11px',
					'top' => '349px',
					'type' => 'head',
					'width' => '50px',
					'height' => '50px',
				),
				array(
					'left' => '105px',
					'top' => '341px',
					'type' => 'nickname',
					'width' => '40px',
					'height' => '20px',
					'size' => '24px',
					'color' => '#ffffff',
				),
				array(
					'left' => '237px',
					'top' => '367px',
					'type' => 'qr',
					'width' => '80px',
					'height' => '80px',
				),
			),
		);
	}else{
		foreach($poster_list as $k=>$v){
			$poster_list[$k]['poster_setting'] = json_decode($v['poster_setting'], true);
		}
	}
	cache_write('fy_lesson_'.$uniacid.'_poster_list', $poster_list);
}

$this->checkdir("../attachment/images/fy_lessonv2/");
$this->checkdir("../attachment/images/fy_lessonv2/{$uniacid}/");
$dirpath = "../attachment/images/fy_lessonv2/{$uniacid}/";

/* 当前海报为第几张 */
$poster_no = intval($_GPC['poster_no']);
$poster_no = $poster_no <= count($poster_list)-1 ? $poster_no : 0;

foreach($poster_list[$poster_no]['poster_setting'] as $item){
	if($item['type']=='qr'){
		$poster_qr['left'] = $item['left'] * 2;
		$poster_qr['top'] = $item['top'] * 2;
		$poster_qr['width'] = $item['width'] * 2;
		$poster_qr['height'] = $item['height'] * 2;
	}
	if($item['type']=='head'){
		$poster_head['left'] = $item['left'] * 2;
		$poster_head['top'] = $item['top'] * 2;
		$poster_head['width'] = $item['width'] * 2;
		$poster_head['height'] = $item['height'] * 2;
	}
	if($item['type']=='nickname'){
		$poster_name['left'] = $item['left'] * 2;
		$poster_name['top'] = $item['top'] * 2;
		$poster_name['size'] = intval($item['size']);
		$poster_name['rgb'] = $site_common->hexTorgb($item['color']);
	}
}

$imagepath = $dirpath.$uniacid."_".$uid."_".$poster_no."_ok.png";
if(!file_exists($imagepath) || $comsetting['qrcode_cache']==0 || filectime($imagepath) < time()-86400){
	set_time_limit(60); 
	ignore_user_abort(true);
	
	/* 背景图片 */
	$bgimg = "../attachment/images/fy_lessonv2/{$uniacid}/posterbg_{$poster_no}.jpg";
	if(!file_exists($bgimg)){
		$poster_bg = $poster_list[$poster_no]['poster_default'] ? $poster_list[$poster_no]['poster_bg'] : $_W['attachurl'].$poster_list[$poster_no]['poster_bg'];
		$site_common->saveImage($poster_bg, $dirpath."posterbg_{$poster_no}.", '');
	}

	/* 二维码图片 */
	if($poster_qr){
		$codeArray = array (
			'expire_seconds' => 2592000,
			'action_name' => 'QR_LIMIT_STR_SCENE',
			'action_info' => array (
				'scene' => array (
					'scene_str' => "uid_{$uid}",
				),
			),
		);
		$account_api = WeAccount::create();
		$res = $account_api->barCodeCreateFixed($codeArray);

		if(empty($res['ticket'])){
			message("获取二维码失败，错误信息:".$res['errcode']."，".$res['errmsg']);
		}
		$qrcodeurl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$res['ticket'];
		$site_common->saveImage($qrcodeurl, $dirpath.$uniacid."_".$uid."_qrcode.");
		$site_common->resize($dirpath.$uniacid."_".$uid."_qrcode.jpg", $dirpath.$uniacid."_".$uid."_qrcode.jpg", "150", "150", "100");
		$savefield = $site_common->img_water_mark($bgimg, $dirpath.$uniacid."_".$uid."_qrcode.jpg", $dirpath, $uniacid."_".$uid.".png", $poster_qr['left'], $poster_qr['top']);
	}

	/* 合成头像 */
	if($poster_head){
		if(empty($member['avatar'])){
			$avatar = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg";
		}else{
			$inc = strstr($member['avatar'], "http://") || strstr($member['avatar'], "https://");
			$avatar = $inc ? $member['avatar'] : $_W['attachurl'].$member['avatar'];
		}
		
		$suffix = $site_common->saveImage($avatar, $dirpath.$uniacid."_".$uid."_avatar.", 'avatar');

		$avatar_size = filesize($dirpath.$uniacid."_".$uid."_avatar.".$suffix);
		if($avatar_size==0){
			message("获取头像失败，请在个人中心点击头像更新", $this->createMobileUrl('self'), "error");
		}

		if($suffix=='png'){
			$im = imagecreatefrompng($dirpath.$uniacid."_".$uid."_avatar.".$suffix);
		}elseif($suffix=='jpeg' || $suffix=='jpg'){
			$im = imagecreatefromjpeg($dirpath.$uniacid."_".$uid."_avatar.".$suffix);
		}else{
			$im = imagecreatefromjpeg(MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg");
		}
		imagejpeg($im, $dirpath.$uniacid."_".$uid."_avatar.jpg");
		imagedestroy($im);
		
		$site_common->resize($dirpath.$uniacid."_".$uid."_avatar.jpg", $dirpath.$uniacid."_".$uid."_avatar.jpg", $poster_head['width'], $poster_head['height'], "100");
		$savefield = $site_common->img_water_mark($savefield, $dirpath.$uniacid."_".$uid."_avatar.jpg", $dirpath, $uniacid."_".$uid."_".$poster_no."_ok.png", $poster_head['left'], $poster_head['top']);
	}


	$info = getimagesize($savefield);
	/* 通过编号获取图像类型 */
	$type = image_type_to_extension($info[2],false);
	/* 图片复制到内存 */
	if($type=='jpg' || $type=='jpeg'){
		$image = imagecreatefromjpeg($savefield);
	}else{
		$image = imagecreatefrompng($savefield);
	}
	
	/* 合成昵称 */
	if($poster_name){
		/* 设置字体的路径 */
		$font = MODULE_ROOT."/template/mobile/{$template}/ttf/Alibaba-PuHuiTi-Regular.ttf";
		/* 设置字体颜色和透明度 */
		$color = imagecolorallocatealpha($image, $poster_name['rgb']['r'], $poster_name['rgb']['g'], $poster_name['rgb']['b'], 0);
		/* 写入文字 */
		$fun = $dirpath.$uniacid."_".$uid.".png";
		imagettftext($image, $poster_name['size'], 0, $poster_name['left'], $poster_name['top']+45, $color, $font, $member['mc_nickname']);
	}

	/* 保存图片 */
	$fun = "image".$type;
	$okfield = $dirpath.$uniacid."_".$uid."_".$poster_no."_ok.png";
	$fun($image, $okfield);  
	/*销毁图片*/  
	imagedestroy($image);
	
	/* 删除多余文件 */
	unlink($dirpath.$uniacid."_".$uid.".png");
	unlink($dirpath.$uniacid."_".$uid."_qrcode.png");
	unlink($dirpath.$uniacid."_".$uid."_avatar.".$suffix);
}

$imagepath .= "?v=".time();


include $this->template("../mobile/{$template}/qrcode");