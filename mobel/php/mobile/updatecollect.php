<?php
/**
 * 收藏课程或讲师
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

$ctype = trim($_GPC['ctype']);
$id = intval($_GPC['id']);
$uid = $_W['member']['uid'];

if($ctype=='lesson'){
	$collect = pdo_fetch("SELECT * FROM " .tablename($this->table_lesson_collect). " WHERE uniacid=:uniacid AND uid=:uid AND outid=:outid AND ctype=:ctype LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid,':outid'=>$id,':ctype'=>1));
	if(empty($collect)){
		$insertdata = array(
			'uniacid' => $uniacid,
			'uid'	  => $uid,
			'outid'   => $id,
			'ctype'   => 1,
			'addtime' => time(),
		);
		pdo_insert($this->table_lesson_collect, $insertdata);
		echo '1';
	}else{
		pdo_delete($this->table_lesson_collect, array('uniacid'=>$uniacid,'uid'=>$uid,'outid'=>$id,'ctype'=>1));
		echo '2';
	}

}elseif($ctype=='teacher'){
	$collect = pdo_fetch("SELECT * FROM " .tablename($this->table_lesson_collect). " WHERE uniacid=:uniacid AND uid=:uid AND outid=:outid AND ctype=:ctype LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid,':outid'=>$id,':ctype'=>2));
	if(empty($collect)){
		$insertdata = array(
			'uniacid' => $uniacid,
			'uid'	  => $uid,
			'outid'   => $id,
			'ctype'   => 2,
			'addtime' => time(),
		);
		pdo_insert($this->table_lesson_collect, $insertdata);
		echo '1';
	}else{
		pdo_delete($this->table_lesson_collect, array('uniacid'=>$uniacid,'uid'=>$uid,'outid'=>$id,'ctype'=>2));
		echo '2';
	}

}


?>