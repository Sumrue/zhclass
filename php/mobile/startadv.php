<?php
/**
 * 开屏广告
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */


$avd = $this->readCommonCache('fy_lesson_'.$uniacid.'_start_adv');
if(empty($avd)){
	$avd = pdo_fetchall("SELECT * FROM " .tablename($this->table_banner). " WHERE uniacid=:uniacid AND is_show=:is_show AND is_pc=:is_pc AND banner_type=:banner_type ORDER BY displayorder DESC", array(':uniacid'=>$uniacid,':is_show'=>1,':is_pc'=>0, 'banner_type'=>3));
	cache_write('fy_lesson_'.$uniacid.'_start_adv', $avd);
}
if(!empty($avd)){
	$advs = array_rand($avd,1);
	$advs = $avd[$advs];
}else{
	header("Location:".$this->createMobileUrl('index',array('uid'=>$_GPC['uid'],'t'=>time())));
}


include $this->template("../mobile/{$template}/startadv");

?>