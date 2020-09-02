<?php
/**
 * 定时任务
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

set_time_limit(0); 
ignore_user_abort(true);

$hour = date('H', time());
$minute = date('i', time());

/*
 * 检查超期未支付订单
 */
if (time() > $setting['closelast'] + $setting['closespace'] * 60 && $setting['closespace'] != 0) {
	$time = time() - $setting['closespace'] * 60;

	/* 取消指定时间内未支付订单 */
	$nopay_order = pdo_fetchall("SELECT id FROM " . tablename($this->table_order) . " WHERE uniacid=:uniacid AND status=:status AND addtime<:addtime LIMIT 5000", array(':uniacid'=>$uniacid, ':status'=>0, ':addtime'=>$time));

	foreach ($nopay_order as $item) {
		$order = pdo_fetch("SELECT id,ordersn,uid,lessonid,coupon,deduct_integral,spec_id FROM " .tablename($this->table_order). " WHERE id=:id AND status=:status LIMIT 1", array(':id'=>$item['id'],':status'=>0));
		if(empty($order)) continue;

		if($setting['stock_config']==1){
			$site_common->updateLessonStock($order['lessonid'], $order['spec_id'], '1');
		}

		pdo_update($this->table_order, array('status' => '-1'), array('id' => $order['id']));
		if($order['coupon']>0){
			$upcoupon = array(
				'status'	=> 0,
				'ordersn'	=> "",
				'update_time' => "",
			);
			pdo_update($this->table_member_coupon, $upcoupon, array('id'=>$order['coupon']));
		}
		if($order['deduct_integral']>0){
			load()->model('mc');
			mc_credit_update($order['uid'], 'credit1', $order['deduct_integral'], array(0, '取消微课堂订单，sn:'.$order['ordersn']));
		}
	}

	/* 更新执行时间 */
	pdo_update($this->table_setting, array('closelast' => time()), array('id' => $setting['id']));
}


/*
 * 检查超期未评价订单
 */
if($setting['autogood']>0){
	$paytime = time()-$setting['autogood']*86400;
	$order = pdo_fetchall("SELECT a.id,a.ordersn,a.uid,a.lessonid,a.bookname,a.teacherid,b.nickname FROM " .tablename($this->table_order). " a LEFT JOIN " .tablename($this->table_mc_members). " b ON a.uid=b.uid WHERE a.uniacid=:uniacid AND a.status=:status AND a.paytime<:paytime LIMIT 5000", array(':uniacid'=>$uniacid,':status'=>1,':paytime'=>$paytime));

	foreach($order as $value){
		$data = array(
			'uniacid'			=> $uniacid,
			'orderid'			=> $value['id'],
			'ordersn'			=> $value['ordersn'],
			'lessonid'			=> $value['lessonid'],
			'bookname'			=> $value['bookname'],
			'teacherid'			=> $value['teacherid'],
			'uid'				=> $value['uid'],
			'nickname'			=> $value['nickname'],
			'grade'				=> 1,
			'global_score'		=> 5,
			'content_score'		=> 5,
			'understand_score'  => 5,
			'content'			=> $common['evaluate_page']['default_good'] ? $common['evaluate_page']['default_good'] : "好评!",
			'type'				=> 0,
			'addtime'			=> time(),
		);

		if(pdo_insert($this->table_evaluate, $data)){
			/* 更新订单状态 */
			pdo_update($this->table_order, array('status'=>2), array('id'=>$value['id']));

			/* 订单评价 */
			$site_common->systemEvaluate($data);
		}
	}
}

/* 检查默认好评的评价，置为系统评价类型 */
$evaluate_list = pdo_fetchall("SELECT id FROM " .tablename($this->table_evaluate). " WHERE uniacid=:uniacid AND content=:content LIMIT 5000", array(':uniacid'=>$uniacid,':content'=>'好评!'));
foreach($evaluate_list as $item){
	pdo_update($this->table_evaluate, array('type'=>0), array('id'=>$item['id']));
}


/*
 * 检查已过期优惠券
 */
 $coupon_list = pdo_fetchall("SELECT * FROM " .tablename($this->table_member_coupon). " WHERE uniacid=:uniacid AND status=:status AND validity<=:validity LIMIT 5000", array(':uniacid'=>$uniacid,':status'=>0, ':validity'=>time()));
 foreach($coupon_list as $value){
	 pdo_update($this->table_member_coupon, array('status'=>-1, 'update_time'=>time()), array('id'=>$value['id']));
 }

/*
 * 检查课程章节定期上架
 */
 $section_list = pdo_fetchall("SELECT id FROM " .tablename($this->table_lesson_son). " WHERE uniacid=:uniacid AND status=:status AND auto_show=:auto_show AND show_time<=:show_time LIMIT 5000", array(':uniacid'=>$uniacid, ':status'=>0, ':auto_show'=>1, ':show_time'=>time()));
 foreach($section_list as $item){
   pdo_update($this->table_lesson_son, array('status'=>1,'auto_show'=>0,'show_time'=>''), array('id'=>$item['id']));
 }


/*
 * 检查过期会员
 */
 $vipmember = pdo_fetchall("SELECT uid FROM " .tablename($this->table_member). " WHERE vip=:vip", array(':vip'=>1));
 foreach($vipmember as $member){
	 $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_member_vip). " WHERE uid=:uid AND validity>:validity LIMIT 5000", array(':uid'=>$member['uid'], ':validity'=>time()));
	 if($total==0){
		$this->updateMemberVip($member['uid'], 0);
	 }
 }

/*
 * 课程总库存校准
 * 每天凌晨03:10~03:30执行
 */
if($setting['stock_config'] && $hour==3 && $minute>10 && $minute<30){
	$list = pdo_getall($this->table_lesson_parent, array('uniacid'=>$uniacid), array('id'));
	foreach($list as $item){
		$total_stock = pdo_fetchcolumn("SELECT SUM(spec_stock) FROM " .tablename($this->table_lesson_spec). " WHERE uniacid=:uniacid AND lessonid=:lessonid", array(':uniacid'=>$uniacid,':lessonid'=>$item['id']));
		pdo_update($this->table_lesson_parent, array('stock'=>$total_stock), array('uniacid'=>$uniacid,'id'=>$item['id']));
	}
 }



 /* 自动同步粉丝昵称和头像到mc_members会员表
  *
 $hour = date('H', time());
 if($hour>=2 && $hour<=5){
	$members_list = pdo_fetchall("SELECT a.uid,b.fanid,b.nickname FROM " .tablename('mc_members'). " a LEFT JOIN " .tablename('mc_mapping_fans') . " b ON a.uid=b.uid WHERE a.uniacid={$uniacid} AND b.uid>0 AND a.avatar='' LIMIT 1000");
	foreach($members_list as $v){
		$fans_tag = pdo_get('mc_fans_tag', array('fanid'=>$v['fanid']), array('headimgurl'));
		pdo_update('mc_members', array('nickname'=>$v['nickname'],'avatar'=>$fans_tag['headimgurl']), array('uniacid'=>$uniacid,'uid'=>$v['uid']));
	}
 }
 */


 echo "success:".date('Y-m-d H:i:s');

?>