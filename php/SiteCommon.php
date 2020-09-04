<?php
/**
 * 模块通用工具类
 * ============================================================================
 * 版权所有 2015-2020 风影科技，并保留所有权利。
 * 网站地址: https://www.fylesson.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件，未购买授权用户无论是否用于商业行为都是侵权行为！
 * 允许已购买用户对程序代码进行修改并在授权域名下使用，但是不允许对程序代码以
 * 任何形式任何目的进行二次发售，作者将依法保留追究法律责任的权力和最终解释权。
 * ============================================================================
 */

 defined('IN_IA') or exit('Access Denied');
 class SiteCommon extends WeModuleSite {
	
	public $table_aliyun_upload		 = 'fy_lesson_aliyun_upload';
	public $table_aliyunoss_upload	 = 'fy_lesson_aliyunoss_upload';
	public $table_article			 = 'fy_lesson_article';
	public $table_article_category	 = 'fy_lesson_article_category';
	public $table_attribute			 = 'fy_lesson_attribute';
    public $table_banner			 = 'fy_lesson_banner';
	public $table_blacklist			 = 'fy_lesson_blacklist';
    public $table_cashlog			 = 'fy_lesson_cashlog';
    public $table_category			 = 'fy_lesson_category';
	public $table_lesson_collect	 = 'fy_lesson_collect';
	public $table_commission_level	 = 'fy_lesson_commission_level';
	public $table_commission_log	 = 'fy_lesson_commission_log';
	public $table_commission_setting = 'fy_lesson_commission_setting';
	public $table_coupon			 = 'fy_lesson_coupon';
	public $table_discount			 = 'fy_lesson_discount';
	public $table_discount_lesson	 = 'fy_lesson_discount_lesson';
	public $table_document			 = 'fy_lesson_document';
    public $table_evaluate			 = 'fy_lesson_evaluate';
	public $table_evaluate_score	 = 'fy_lesson_evaluate_score';
    public $table_lesson_history	 = 'fy_lesson_history';
	public $table_index_module		 = 'fy_lesson_index_module';
	public $table_inform			 = 'fy_lesson_inform';
	public $table_inform_fans		 = 'fy_lesson_inform_fans';
	public $table_login_pc			 = 'fy_lesson_login_pc';
	public $table_recommend_junior	 = 'fy_lesson_recommend_junior';
	public $table_recommend_activity = 'fy_lesson_recommend_activity';
	public $table_market			 = 'fy_lesson_market';
	public $table_mcoupon			 = 'fy_lesson_mcoupon';
    public $table_member			 = 'fy_lesson_member';
	public $table_member_buyteacher	 = 'fy_lesson_member_buyteacher';
	public $table_member_coupon		 = 'fy_lesson_member_coupon';
    public $table_member_order		 = 'fy_lesson_member_order';
	public $table_member_vip		 = 'fy_lesson_member_vip';
	public $table_navigation		 = 'fy_lesson_navigation';
    public $table_order				 = 'fy_lesson_order';
	public $table_order_verify		 = 'fy_lesson_order_verify';
    public $table_lesson_parent		 = 'fy_lesson_parent';
    public $table_playrecord		 = 'fy_lesson_playrecord';
	public $table_poster			 = 'fy_lesson_poster';
	public $table_qcloudvod_upload	 = 'fy_lesson_qcloudvod_upload';
	public $table_qcloud_upload		 = 'fy_lesson_qcloud_upload';
	public $table_qiniu_upload		 = 'fy_lesson_qiniu_upload';
    public $table_recommend			 = 'fy_lesson_recommend';
    public $table_setting			 = 'fy_lesson_setting';
	public $table_setting_pc		 = 'fy_lesson_setting_pc';
	public $table_signin			 = 'fy_lesson_signin';
    public $table_lesson_son		 = 'fy_lesson_son';
	public $table_lesson_title		 = 'fy_lesson_title';
	public $table_lesson_spec		 = 'fy_lesson_spec';
	public $table_static			 = 'fy_lesson_static';
	public $table_study_duration	 = 'fy_lesson_study_duration';
	public $table_subscribe_msg		 = 'fy_lesson_subscribe_msg';
    public $table_syslog			 = 'fy_lesson_syslog';
    public $table_teacher			 = 'fy_lesson_teacher';
	public $table_teacher_category	 = 'fy_lesson_teacher_category';
    public $table_teacher_income	 = 'fy_lesson_teacher_income';
	public $table_teacher_order		 = 'fy_lesson_teacher_order';
	public $table_teacher_price		 = 'fy_lesson_teacher_price';
	public $table_tplmessage		 = 'fy_lesson_tplmessage';
    public $table_vip_level			 = 'fy_lesson_vip_level';
    public $table_vipcard			 = 'fy_lesson_vipcard';
	public $table_mc_members		 = 'mc_members';
	public $table_fans				 = 'mc_mapping_fans';
	public $table_core_paylog		 = 'core_paylog';
		public $table_users				 = 'users';
	
	/**
	 * 获取底部导航
	 */
	public function getNavigation($template){
		global $_W;
		$uniacid = $_W['uniacid'];

		$navigation = cache_load('fy_lesson_'.$uniacid.'_navigation');
		if(empty($navigation)){
			$navigation = [];
			$nav = pdo_fetchall("SELECT * FROM " .tablename($this->table_navigation)." WHERE uniacid=:uniacid AND is_pc=:is_pc AND location=:location ORDER BY displayorder ASC", array(':uniacid'=>$uniacid, ':is_pc'=>0, ':location'=>1));
			foreach($nav as $v){
				$v['unselected_icon'] = $_W['attachurl'].$v['unselected_icon'];
				$v['selected_icon']   = $_W['attachurl'].$v['selected_icon'];
				$navigation[$v['nav_ident']] = $v;
			}
			if(!$navigation['index']){
				$navigation['index'] = array(
					'nav_name'        => '首页',
					'unselected_icon' => MODULE_URL."template/mobile/{$template}/images/unselected_index_icon.png",
					'selected_icon'   => MODULE_URL."template/mobile/{$template}/images/selected_index_icon.png",
					'url_link'        => $this->redefineUrl($this->createMobileUrl('index', array('t'=>time()))),
				);
			}
			if(!$navigation['search']){
				$navigation['search'] = array(
					'nav_name'        => '全部课程',
					'unselected_icon' => MODULE_URL."template/mobile/{$template}/images/unselected_search_icon.png",
					'selected_icon'   => MODULE_URL."template/mobile/{$template}/images/selected_search_icon.png",
					'url_link'        => $this->redefineUrl($this->createMobileUrl('search', array('t'=>time()))),
				);
			}
			if(!$navigation['self']){
				$navigation['self'] = array(
					'nav_name'        => '个人中心',
					'unselected_icon' => MODULE_URL."template/mobile/{$template}/images/unselected_self_icon.png",
					'selected_icon'   => MODULE_URL."template/mobile/{$template}/images/selected_self_icon.png",
					'url_link'        => $this->redefineUrl($this->createMobileUrl('self', array('t'=>time()))),
				);
			}
			cache_write('fy_lesson_'.$uniacid.'_navigation', $navigation);
		}

		return $navigation;
	}

	/**
	 * 设置底部菜单选中状态
	 */
	public function setFooter($navigation){
		global $_W, $_GPC;

		parse_str(@array_pop(explode('?', $_W['siteurl'])), $params);
		$com_do = $params['do'] ? $params['do'] : $_GPC['do'];
		$com_op = $params['op'] ? $params['op'] : 'display';

		$foot_params = array();
		if($navigation['index']['url_link']){
			parse_str(@array_pop(explode('?', $navigation['index']['url_link'])), $index_params);
			$index_do = $index_params['do'];
			$index_op = $index_params['op'] ? $index_params['op'] : 'display';
			if($com_do==$index_do && $com_op==$index_op){
				$foot_params['index'] = true;
			}
		}
		if($navigation['search']['url_link']){
			parse_str(@array_pop(explode('?', $navigation['search']['url_link'])), $search_params);
			$search_do = $search_params['do'];
			$search_op = $search_params['op'] ? $search_params['op'] : 'display';
			if($com_do==$search_do && $com_op==$search_op){
				$foot_params['search'] = true;
			}
		}
		if($navigation['diynav']['url_link']){
			parse_str(@array_pop(explode('?', $navigation['diynav']['url_link'])), $diynav_params);
			$diynav_do = $diynav_params['do'];
			$diynav_op = $diynav_params['op'] ? $diynav_params['op'] : 'display';
			if($com_do==$diynav_do && $com_op==$diynav_op){
				$foot_params['diynav'] = true;
			}
		}
		if($navigation['mylesson']['url_link']){
			parse_str(@array_pop(explode('?', $navigation['mylesson']['url_link'])), $lesson_params);
			$lesson_do = $lesson_params['do'];
			$lesson_op = $lesson_params['op'] ? $lesson_params['op'] : 'display';
			if($com_do==$lesson_do && $com_op==$lesson_op){
				$foot_params['mylesson'] = true;
			}
		}
		if($navigation['self']['url_link']){
			parse_str(@array_pop(explode('?', $navigation['self']['url_link'])), $self_params);
			$self_do = $self_params['do'];
			$self_op = $self_params['op'] ? $self_params['op'] : 'display';
			if($com_do==$self_do && $com_op==$self_op){
				$foot_params['self'] = true;
			}
		}

		return $foot_params;
	}

	/* 获取手机端右侧悬浮菜单 */
	public function getRightBarMenu(){
		global $_W;
		$uniacid = $_W['uniacid'];

		$right_menu = cache_load('fy_lesson_'.$uniacid.'_navigation_rightBar');
		if(empty($right_menu)){
			$right_menu = pdo_fetchall("SELECT * FROM " .tablename($this->table_navigation)." WHERE uniacid=:uniacid AND is_pc=:is_pc AND location=:location ORDER BY displayorder DESC, nav_id DESC", array(':uniacid'=>$uniacid, ':is_pc'=>0,':location'=>5));
			cache_write('fy_lesson_'.$uniacid.'_navigation_rightBar', $right_menu);
		}

		return $right_menu;
	}
	
	/* 获取手机端个人中心菜单 */
	public function getSelfMenu(){
		global $_W;
		$uniacid = $_W['uniacid'];

		$right_menu = cache_load('fy_lesson_'.$uniacid.'_navigation_self');
		if(empty($right_menu)){
			$right_menu = pdo_fetchall("SELECT * FROM " .tablename($this->table_navigation)." WHERE uniacid=:uniacid AND is_pc=:is_pc AND location=:location ORDER BY displayorder DESC, nav_id DESC", array(':uniacid'=>$uniacid, ':is_pc'=>0,':location'=>3));
			cache_write('fy_lesson_'.$uniacid.'_navigation_self', $right_menu);
		}

		return $right_menu;
	}

	/**
	 * 获取课程属性列表
	 * $type 1.属性attribute1  2.属性attribute2
	 */
	public function getLessonAttribute($type=''){
		global $_W;
		$uniacid = $_W['uniacid'];

		if($type==1){
			$attribute = cache_load('fy_lesson_'.$uniacid.'_attribute1');
			if(empty($attribute)){
				$attribute = pdo_fetchall("SELECT * FROM " .tablename($this->table_attribute). " WHERE uniacid=:uniacid AND attr_type=:attr_type ORDER BY displayorder DESC", array(':uniacid'=>$uniacid,':attr_type'=>'attribute1'));
				cache_write('fy_lesson_'.$uniacid.'_attribute1', $attribute);
			}
		}elseif($type==2){
			$attribute = cache_load('fy_lesson_'.$uniacid.'_attribute2');
			if(empty($attribute)){
				$attribute = pdo_fetchall("SELECT * FROM " .tablename($this->table_attribute). " WHERE uniacid=:uniacid AND attr_type=:attr_type ORDER BY displayorder DESC", array(':uniacid'=>$uniacid,':attr_type'=>'attribute2'));
				cache_write('fy_lesson_'.$uniacid.'_attribute2', $attribute);
			}
		}else{
			return;
		}

		return $attribute;
	}

	/**
	 * 重定义url
	 */
	public function redefineUrl($url){
		$new_url = str_replace('&m=', '', $url);
		return $new_url.'&m=fy_lessonv2';
	}

	/**
	 * 转发课程推荐下级免费学习
	 * $junior array()
	 * uid		  推荐用户
	 * junior_uid 新用户
	 * lessonid   课程id
	 * $last_junior_id 最新一条记录
	 */
	 public function recommendLessonByFreeStudy($junior, $last_junior_id){
		 global $_W;
		 $uniacid = $_W['uniacid'];

		 $lesson = pdo_get($this->table_lesson_parent, array('uniacid'=>$_W['uniacid'], 'id'=>$junior['lessonid']));
		 $order = pdo_get($this->table_order, array('uid'=>$junior['uid'],'lessonid'=>$junior['lessonid'],'paytype'=>'recgive','is_delete'=>0));
		 if(!$lesson || !$lesson['recommend_free_num'] || $order){
			return;
		 }

		 /* 查询活动记录 */
		 $activity = pdo_get($this->table_recommend_activity, array('uid'=>$junior['uid'],'lessonid'=>$junior['lessonid'],'status'=>0));
		 /* 超时未完成任务直接结束 */
		 if(!empty($activity) && time() > $activity['addtime']+$lesson['recommend_free_limit']*86400){
			pdo_update($this->table_recommend_activity, array('status'=>-1), array('id'=>$activity['id']));
			unset($activity);
		 }

		 if(empty($activity)){
			/* 新创建一个活动记录 */
			$activity_data = array(
				'uniacid'		=> $uniacid,
				'uid'			=> $junior['uid'],
				'lessonid'		=> $junior['lessonid'],
				'bookname'		=> $lesson['bookname'],
				'images'		=> $lesson['images'],
				'invite_number'	=> 1,
				'addtime'		=> time(),
			);
			pdo_insert($this->table_recommend_activity, $activity_data);
			$activity_id = pdo_insertid();
		 }else{
			$activity_id = $activity['id'];
		 }

		 /* 添加邀请下级人员记录 */
		 $junior['activity_id'] = $activity_id;
		 pdo_insert($this->table_recommend_junior, $junior);

		 /* 查询已邀请下级人数 */
		 $already_num = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_recommend_junior). " WHERE activity_id=:activity_id AND uid=:uid AND lessonid=:lessonid", array(':activity_id'=>$activity_id,':uid'=>$junior['uid'],':lessonid'=>$junior['lessonid']));
		 if($already_num >= $lesson['recommend_free_num']){
			/* 添加免费学习订单 */
			$orderdata = array(
				'acid'			=> $_W['account']['acid'],
				'uniacid'		=> $uniacid,
				'ordersn'		=> 'L' . date('Ymd').substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(1000, 9999)),
				'uid'			=> $junior['uid'],
				'lesson_type'	=> $lesson['lesson_type'],
				'lessonid'		=> $junior['lessonid'],
				'bookname'		=> $lesson['bookname'],
				'marketprice'	=> 0,
				'price'			=> 0,
				'spec_day'		=> $lesson['recommend_free_day'],
				'teacherid'		=> $lesson['teacherid'],
				'status'		=> 1,
				'paytype'		=> 'recgive',
				'paytime'		=> time(),
				'validity'		=> time()+$lesson['recommend_free_day']*86400,
				'addtime'		=> time(),
			);
			pdo_insert($this->table_order, $orderdata);
			pdo_update($this->table_lesson_parent, array('buynum +='=>1), array('id'=>$junior['lessonid']));

			if(!empty($activity)){
				pdo_update($this->table_recommend_activity, array('invite_number +='=>1, 'status'=>1, 'update_time'=>time()), array('id'=>$activity['id']));
			}
			
			$first = "您已通过《{$lesson['bookname']}》课程海报或链接邀请{$already_num}位新好友，成功完成任务获得免费学习该课程的奖励，免费学习期限{$lesson['recommend_free_day']}天。";
		 
		 }else{
			if(!empty($activity)){
				pdo_update($this->table_recommend_activity, array('invite_number +='=>1, 'update_time'=>time()), array('id'=>$activity['id']));
			}
			
			$remain_num = $lesson['recommend_free_num'] - $already_num;
			$first = "您成功通过《{$lesson['bookname']}》课程海报或链接邀请1位新好友，再邀请{$remain_num}位新好友，即可获得免费学习该课程的奖励。";
		 }

		 /* 消息通知 */
		 $url = $_W['siteroot'] . 'app/' . $this->redefineUrl($this->createMobileUrl('reclesson', array('op'=>'details','activity_id'=>$activity_id)));
		 $junior_member = pdo_get($this->table_fans, array('uid'=>$junior['junior_uid']), array('nickname','openid'));
		 $rec_member = pdo_get($this->table_fans, array('uid'=>$junior['uid']), array('openid'));
		 $tplmessage = pdo_get($this->table_tplmessage, array('uniacid'=>$uniacid), array('recommend_junior'));
		 if($tplmessage['recommend_junior']){
			$sendmessage1 = array(
				'touser' => $rec_member['openid'],
				'template_id' => $tplmessage['recommend_junior'],
				'url' => $url,
				'topcolor' => "",
				'data' => array(
					'first' => array(
						'value' => $first,
						'color' => "",
						),
					'keyword1' => array(
						'value' => $junior_member['nickname'],
						'color' => "",
					),
					'keyword2' => array(
						'value' => "uid({$junior['junior_uid']})",
						'color' => "",
					),
					'keyword3' => array(
						'value' => date('Y年m月d日', time()),
						'color' => "",
					),
					'remark' => array(
						'value' => $remark,
						'color' => "感谢您的支持。",
					),
				)
			);
			$this->send_template_message($sendmessage1, $uniacid);
		}else{
			$custom = array(
				'msgtype' => 'text',
				'text' => array(
					'content' => urlencode($first.'\n\n昵称：'.$junior_member['nickname'].'\n会员ID：'.$junior['junior_uid'].'\n注册时间：'.date('Y年m月d日', time()).'\n\n感谢您的支持，<a href=\"'.$url.'\">点击此处可查看详情</a>')
				),
				'touser' => $rec_member['openid'],
			);
			$account_api = WeAccount::create();
			$account_api->sendCustomNotice($custom);
		}
	 }

	/* 给新注册会员和直接推荐人发放优惠券 
	 * $member 新会员信息
	 * $recmember 推荐人信息
	 * $setting 基本设置信息
	 */
	public function sendCouponByNewMember($member, $recmember, $setting){
		global $_W;
		$uniacid = $_W['uniacid'];

		$market = pdo_fetch("SELECT * FROM " .tablename($this->table_market). " WHERE uniacid=:uniacid", array(':uniacid'=>$uniacid));
		$tplmessage = pdo_fetch("SELECT receive_coupon,receive_coupon_format FROM " .tablename($this->table_tplmessage). " WHERE uniacid=:uniacid", array(':uniacid'=>$uniacid));
		$receive_coupon_format = json_decode($tplmessage['receive_coupon_format'], true);

		$regGive = json_decode($market['reg_give'], true);
		$recommend = json_decode($market['recommend'], true);
		$remark = $receive_coupon_format['remark'] ? $receive_coupon_format['remark'] : "点击详情可查看您的帐号优惠券详情哦~";
		$url = $_W['siteroot'] . 'app/' . $this->redefineUrl($this->createMobileUrl('coupon'));

		if(!empty($regGive)){
			$regTotal = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_member_coupon). " WHERE uid=:uid AND source=:source", array(':uid'=>$member['uid'], 'source'=>6));
			if(!$regTotal && $member){
				$t = $coupon_amount = 0;
				foreach($regGive as $item){
					$coupon = pdo_fetch("SELECT * FROM " .tablename($this->table_mcoupon). " WHERE id=:id", array(':id'=>$item));
					if(empty($coupon)) continue;
					$regCoupon = array(
						'uniacid'	  => $uniacid,
						'uid'		  => $member['uid'],
						'amount'      => $coupon['amount'],
						'conditions'  => $coupon['conditions'],
						'validity'	  => $coupon['validity_type']==1 ? $coupon['days1'] : time()+ $coupon['days2']*86400,
						'category_id' => $coupon['category_id'],
						'status'	  => 0, /* 未使用 */
						'source'	  => 6, /* 新会员注册 */
						'coupon_id'	  => $coupon['id'],
						'addtime'	  => time(),
					);
					if(pdo_insert($this->table_member_coupon, $regCoupon)){
						$t++;
						$coupon_amount += $coupon['amount'];
					}
				}

				if($t){
					$newFans = pdo_fetch("SELECT openid,nickname FROM " .tablename($this->table_fans). " WHERE uid=:uid", array(':uid'=>$member['uid']));

					if($tplmessage['receive_coupon']){
						$sendmessage1 = array(
							'touser' => $newFans['openid'],
							'template_id' => $tplmessage['receive_coupon'],
							'url' => $url,
							'topcolor' => "#7B68EE",
							'data' => array(
								'first' => array(
									'value' => $newFans['nickname']."，终于等到您了。系统赠您{$t}张新会员专享优惠券已发放到您的帐号，请您查收。",
									'color' => "#2392EA",
								),
								'keyword1' => array(
									'value' => "价值".$coupon_amount."元的优惠券".$t." 张",
									'color' => "",
								),
								'keyword2' => array(
									'value' => date('Y年m月d日', time()),
									'color' => "",
								),
								'remark' => array(
									'value' => $remark,
									'color' => "",
								),
							)
						);
						$this->send_template_message($sendmessage1, $uniacid);
					}else{
						$custom = array(
							'msgtype' => 'text',
							'text' => array(
								'content' => urlencode($newFans['nickname'].'，终于等到您了。系统赠您'.$t.'张新会员专享优惠券已发放到您的帐号，请您查收。\n\n账户名：'.$newFans['nickname'].'\n数量：'.$t.'张\n时间：'.date('Y年m月d日', time()).'\n\n'.$receive_coupon_format['remark'].'<a href=\"'.$url.'\">点击此处可查看详情</a>')
							),
							'touser' => $newFans['openid'],
						);
						$account_api = WeAccount::create();
						$account_api->sendCustomNotice($custom);
					}
				}
			}
		}

		if(!empty($recommend) && !empty($recmember)){
			if($market['recommend_time']){
				$recTotal = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_member_coupon). " WHERE uid=:uid AND source=:source", array(':uid'=>$recmember['uid'], 'source'=>3));
				if($recTotal >= $market['recommend_time']) return;
			}

			$t = $coupon_amount = 0;
			foreach($recommend as $item){
				$coupon = pdo_fetch("SELECT * FROM " .tablename($this->table_mcoupon). " WHERE id=:id", array(':id'=>$item));
				if(empty($coupon)) continue;
				$recCoupon = array(
					'uniacid'	  => $uniacid,
					'uid'		  => $recmember['uid'],
					'amount'      => $coupon['amount'],
					'conditions'  => $coupon['conditions'],
					'validity'	  => $coupon['validity_type']==1 ? $coupon['days1'] : time()+ $coupon['days2']*86400,
					'category_id' => $coupon['category_id'],
					'status'	  => 0, /* 未使用 */
					'source'	  => 3, /* 新会员注册 */
					'coupon_id'	  => $coupon['id'],
					'addtime'	  => time(),
				);
				if(pdo_insert($this->table_member_coupon, $recCoupon)){
					$t++;
					$coupon_amount += $coupon['amount'];
				}
			}

			if($t){
				$recFans = pdo_fetch("SELECT openid,nickname FROM " .tablename($this->table_fans). " WHERE uid=:uid", array(':uid'=>$recmember['uid']));

				if($tplmessage['receive_coupon']){
					$sendmessage2 = array(
						'touser' => $recFans['openid'],
						'template_id' => $tplmessage['receive_coupon'],
						'url' => $url,
						'topcolor' => "#7B68EE",
						'data' => array(
							'first' => array(
								'value' => "恭喜您成功推荐下级成员，系统赠您{$t}张优惠券已发放到您的帐号，请注意查收。",
								'color' => "#2392EA",
								),
							'keyword1' => array(
								'value' => "价值".$coupon_amount."元的优惠券".$t." 张",
								'color' => "",
							),
							'keyword2' => array(
								'value' => date('Y年m月d日', time()),
								'color' => "",
							),
							'remark' => array(
								'value' => $remark,
								'color' => "",
							),
						)
					);
					$this->send_template_message($sendmessage2, $uniacid);
				}else{
					$custom = array(
						'msgtype' => 'text',
						'text' => array(
							'content' => urlencode('恭喜您成功推荐下级成员，系统赠您'.$t.'张优惠券已发放到您的帐号，请注意查收。\n\n账户名：'.$recFans['nickname'].'\n数量：'.$t.'张\n时间：'.date('Y年m月d日', time()).'\n\n'.$receive_coupon_format['remark'].'<a href=\"'.$url.'\">点击此处可查看详情</a>')
						),
						'touser' => $recFans['openid'],
					);
					$account_api = WeAccount::create();
					$account_api->sendCustomNotice($custom);
				}
			}
		}
	}

	/*
	 * 设置会员推荐人ID
	 * $member    会员信息
	 * $recmember 推荐人信息
	 * $setting   基本设置信息
	 */
	public function setMemberParentId($member, $recmember, $setting, $comsetting, $source_id){

		$recid = $recmember['status']==1 ? $recmember['uid'] : 0;
		/*新用户加入通知一级推荐人*/
		if ($comsetting['is_sale'] == 1 && $recid > 0) {
			$this->sendNoticeToMember1($member, $recmember, $setting, $source_id, $comsetting);
		}
		/*新用户加入通知二级推荐人*/
		$recmember2 = pdo_fetch("SELECT * FROM " . tablename($this->table_member) . " WHERE uid=:uid", array(':uid'=>$recmember['parentid']));
		if ($comsetting['is_sale'] == 1 && $recmember2['uid'] > 0) {
			$this->sendNoticeToMember2($member, $recmember2, $setting, $comsetting);
		}
		 
		/*新用户加入通知三级推荐人*/
		$recmember3 = pdo_fetch("SELECT * FROM " . tablename($this->table_member) . " WHERE uid=:uid", array(':uid'=>$recmember2['parentid']));
		if ($comsetting['is_sale'] == 1 && $recmember3['uid'] > 0) {
			$this->sendNoticeToMember3($member, $recmember3, $setting, $comsetting);
		}
	}

	/* 新用户加入通知一级推荐人 
	 * $member 新用户信息
	 * $recmember 一级推荐人信息
	 * $setting 基本设置信息
	 * $comsetting 分销设置
	 **/
	public function sendNoticeToMember1($member, $recmember, $setting, $source_id, $comsetting){
	 	global $_W;
		
	 	if ($comsetting['level'] >= 1) {
	 		$commission = unserialize($comsetting['commission']);
			$fans = pdo_fetch("SELECT nickname,openid FROM " . tablename($this->table_fans) . "  WHERE uid=:uid", array(':uid'=>$recmember['uid']));
			
			/* 开启直推下级获得奖励 */
			$rec_income = json_decode($comsetting['rec_income'], true);
			if (floatval($rec_income['credit1']) > 0) {
                load()->model('mc');
				$log = array(0, '直接推荐下级成员加入', 'fy_lessonv2');
				mc_credit_update($recmember['uid'], 'credit1', $rec_income['credit1'], $log);
            }
			if (floatval($rec_income['credit2']) > 0) {
                pdo_update($this->table_member, array('nopay_commission' => $recmember['nopay_commission'] + $rec_income['credit2']), array('uid' => $recmember['uid']));
                $logarr = array(
                    'uniacid' 	 => $_W['uniacid'],
                    'orderid' 	 => $source_id,
                    'uid' 		 => $recmember['uid'],
                    'nickname' 	 => $fans['nickname'],
                    'bookname' 	 => "推荐下级成员",
                    'change_num' => $rec_income['credit2'],
                    'grade' 	 => 1,
                    'remark' 	 => "直接推荐下级成员加入",
					'buyer_uid'  => $member['uid'],
					'buyer_name' => $member['nickname'],
                    'addtime'	 => time(),
                );
                pdo_insert($this->table_commission_log, $logarr);
            }
			
			if ($recmember['agent_level'] > 0) {
                $level = pdo_fetch("SELECT * FROM " . tablename($this->table_commission_level) . " WHERE id=:id", array(':id'=>$recmember['agent_level']));
            }
            if ($comsetting['self_sale'] == 1) { /* 开启分销内购，一级分销人拿二级佣金 */
                if (!empty($level)) {
                    $commission = $level['commission2'];
                } else {
                    $commission = $commission['commission2'];
                }
            } else {
                if (!empty($level)) {
                    $commission = $level['commission1'];
                } else {
                    $commission = $commission['commission1'];
                }
            }

			if($comsetting['sale_rank']==2){
				/* 如果获得佣金是VIP身份且推荐人不是VIP身份，则不发送下级加入模版消息通知 */
				$member_vip = pdo_fetchall("SELECT * FROM " .tablename($this->table_member_vip). " WHERE uid=:uid AND validity>:validity", array(':uid'=>$recmember['uid'], ':validity'=>time()));
				if(empty($member_vip)){
					return;
				}
			}
			$this->sendNewUserNoticeToRecmember($fans['openid'], $setting, $member['nickname'], $commission, $type=1, $rec_income);
		}
	}

	/* 新用户加入通知二级推荐人 
	 * $member 新用户信息
	 * $recmember 二级推荐人信息
	 * $setting 基本设置信息
	 * $comsetting 分销设置
	 **/
	public function sendNoticeToMember2($member, $recmember, $setting, $comsetting){
	 	global $_W;
		
	 	if ($comsetting['level'] >= 2) {
	 		$commission = unserialize($comsetting['commission']);
			$fans = pdo_fetch("SELECT nickname,openid FROM " . tablename($this->table_fans) . "  WHERE uid=:uid", array(':uid'=>$recmember['uid']));
			
			if ($recmember['agent_level'] > 0) {
                $level = pdo_fetch("SELECT * FROM " . tablename($this->table_commission_level) . " WHERE id=:id", array(':id'=>$recmember['agent_level']));
            }
            if ($comsetting['self_sale'] == 1) { /* 开启分销内购，一级分销人拿二级佣金 */
                if (!empty($level)) {
                    $commission = $level['commission3'];
                } else {
                    $commission = $commission['commission3'];
                }
            } else {
                if (!empty($level)) {
                    $commission = $level['commission2'];
                } else {
                    $commission = $commission['commission2'];
                }
            }
			$this->sendNewUserNoticeToRecmember($fans['openid'], $setting, $member['nickname'], $commission, $type=2, $rec_income);
		}
	}

	/* 新用户加入通知三级推荐人 
	 * $member 新用户信息
	 * $recmember 三级推荐人信息
	 * $setting 基本设置信息
	 * $comsetting 分销设置
	 **/
	public function sendNoticeToMember3($member, $recmember, $setting, $comsetting){
	 	global $_W;
		
	 	if ($comsetting['level'] >= 3) {
	 		$commission = unserialize($comsetting['commission']);
			$fans = pdo_fetch("SELECT nickname,openid FROM " . tablename($this->table_fans) . "  WHERE uid=:uid", array(':uid'=>$recmember['uid']));
			
			if ($recmember['agent_level'] > 0) {
                $level = pdo_fetch("SELECT * FROM " . tablename($this->table_commission_level) . " WHERE id=:id", array(':id'=>$recmember['agent_level']));
            }
            if ($comsetting['self_sale'] == 1) { /* 开启分销内购，一级分销人拿二级佣金 */
                $commission = 0;
            } else {
                if (!empty($level)) {
                    $commission = $level['commission3'];
                } else {
                    $commission = $commission['commission3'];
                }
            }
			$this->sendNewUserNoticeToRecmember($fans['openid'], $setting, $member['nickname'], $commission, $type=3, $rec_income);
		}
	}

	/* 新下级加入 模版消息通知推荐人 
	 * $toOpenid 上级openid
	 * $setting 设置信息
	 * $nickname 下级用户昵称
	 * $commission 佣金比例
	 * $type 等级  1.一级 2.二级 3.三级
	 * $rec_income 直接推荐人奖励积分和佣金
	 */
	public function sendNewUserNoticeToRecmember($toOpenid, $setting, $nickname, $commission, $type, $rec_income=array()){
		global $_W;
		if($type==1){
			if($rec_income['credit1']>0 || $rec_income['credit2']>0){
				$award_tip = "，系统奖励您";
				if($rec_income['credit1']>0) $award_tip .= $rec_income['credit1'].'个积分';
				if($rec_income['credit2']>0) $award_tip .= '，￥'.$rec_income['credit2'].'佣金，已发放到您的账户。';
			}
		}

		$tplmessage = pdo_fetch("SELECT newjoin, newjoin_format FROM " .tablename($this->table_tplmessage). " WHERE uniacid=:uniacid", array(':uniacid'=>$setting['uniacid']));
		$newjoin_format = json_decode($tplmessage['newjoin_format'], true);
		
		$send = array(
            'touser' => $toOpenid,
            'template_id' => $tplmessage['newjoin'],
            'url' => $_W['siteroot'] . 'app/' . $this->redefineUrl($this->createMobileUrl('team', array('level'=>1))),
            'topcolor' => "#e25804",
            'data' => array(
                'first' => array(
                    'value' => $newjoin_format['first'] ? $newjoin_format['first'] : "恭喜您有新的成员加入".$award_tip,
                    'color' => "",
                ),
                'keyword1' => array(
                    'value' => $nickname ? $nickname."({$type})" : "({$type})",
                    'color' => "",
                ),
                'keyword2' => array(
                    'value' => date("Y年m月d日",time()),
                    'color' => "",
                ),
                'remark' => array(
                    'value' => $newjoin_format['remark'] ? $newjoin_format['remark'] : "您的下级成员进行消费时，您将有机会获得奖励~",
                    'color' => "",
                ),
            )
        );
        if ($commission > 0) {
            $this->send_template_message($send, $setting['uniacid']);
        }
	}

	/**
	 * 系统评价课程
	 * $evaluate  array 评价信息
	 */
	public function systemEvaluate($evaluate){
		$evaluate_score = pdo_get($this->table_evaluate_score, array('uniacid'=>$evaluate['uniacid'], 'lessonid'=>$evaluate['lessonid']));
		if(!$evaluate_score){
			/* 总评价条数 */
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_evaluate). " WHERE uniacid=:uniacid AND lessonid=:lessonid AND status=:status", array(':uniacid'=>$evaluate['uniacid'], ':lessonid'=>$evaluate['lessonid'], ':status'=>1));
			/* 课程好评数 */
			$good = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_evaluate). " WHERE uniacid=:uniacid AND lessonid=:lessonid AND grade=:grade AND status=:status", array(':uniacid'=>$evaluate['uniacid'], ':lessonid'=>$evaluate['lessonid'],':grade'=>1, ':status'=>1));

			$data = array(
				'uniacid'			=> $evaluate['uniacid'],
				'lessonid'			=> $evaluate['lessonid'],
				'score'				=> round($good/$total,2),
				'total_goods'		=> $good,
				'total_global'		=> ($total-1)*5 + $evaluate['global_score'],
				'total_content'		=> ($total-1)*5 + $evaluate['content_score'],
				'total_understand'	=> ($total-1)*5 + $evaluate['understand_score'],
				'total_number'		=> $total,
				'update_time'		=> time(),
			);
			$data['global_score'] = round($data['total_global']/$total, 2);
			$data['content_score'] = round($data['total_content']/$total, 2);
			$data['understand_score'] = round($data['total_understand']/$total, 2);

			pdo_insert($this->table_evaluate_score, $data);
		
		}else{
			$data['total_goods']	  = $evaluate['grade']==1 ? $evaluate_score['total_goods']+1 : $evaluate_score['total_goods']; /* 好评总条数 */
			$data['total_global']	  = $evaluate_score['total_global'] + $evaluate['global_score'];   /* 综合评分(总分数) */
			$data['total_content']	  = $evaluate_score['total_content'] + $evaluate['content_score']; /* 内容实用(总分数) */
			$data['total_understand'] = $evaluate_score['total_understand'] + $evaluate['understand_score']; /* 通俗易懂(总分数) */
			$data['total_number']	  = $evaluate_score['total_number'] + 1; /* 评价总人数 */
			
			$data['score']			  = round($data['total_goods']/$data['total_number'], 2);		/* 课程好评率 */
			$data['global_score']	  = round($data['total_global']/$data['total_number'], 2);		/* 综合评分(平均) */
			$data['content_score']	  = round($data['total_content']/$data['total_number'], 2);		/* 内容实用(平均) */
			$data['understand_score'] = round($data['total_understand']/$data['total_number'], 2);	/* 通俗易懂(平均) */

			$data['update_time']  = time();

			pdo_update($this->table_evaluate_score, $data, array('id'=>$evaluate_score['id']));
		}

		pdo_update($this->table_lesson_parent, array('score'=>$data['score']), array('id'=>$evaluate['lessonid']));
	}

	/**
	 * 用户评价课程
	 * $evaluate  array 评价信息
	 */
	public function memberEvaluate($evaluate){
		$evaluate_score = pdo_get($this->table_evaluate_score, array('uniacid'=>$evaluate['uniacid'], 'lessonid'=>$evaluate['lessonid']));
		
		/* 总评价条数 */
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_evaluate). " WHERE uniacid=:uniacid AND lessonid=:lessonid AND status=:status", array(':uniacid'=>$evaluate['uniacid'], ':lessonid'=>$evaluate['lessonid'], ':status'=>1));
		/* 课程好评数 */
		$good = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_evaluate). " WHERE uniacid=:uniacid AND lessonid=:lessonid AND grade=:grade AND status=:status", array(':uniacid'=>$evaluate['uniacid'], ':lessonid'=>$evaluate['lessonid'], ':grade'=>1, ':status'=>1));
		/* 评价总分 */
		$total_score = pdo_fetchall("SELECT SUM(global_score) as total_global, SUM(content_score) as total_content, SUM(understand_score) as total_understand FROM " .tablename($this->table_evaluate). " WHERE uniacid=:uniacid AND lessonid=:lessonid AND status=:status", array(':uniacid'=>$evaluate['uniacid'], ':lessonid'=>$evaluate['lessonid'], ':status'=>1));

		$data = array(
			'uniacid'			=> $evaluate['uniacid'],
			'lessonid'			=> $evaluate['lessonid'],
			'score'				=> round($good/$total, 2),
			'global_score'		=> round($total_score[0]['total_global']/$total, 2),
			'content_score'		=> round($total_score[0]['total_content']/$total, 2),
			'understand_score'	=> round($total_score[0]['total_understand']/$total, 2),
			'total_goods'		=> $good,
			'total_global'		=> $total_score[0]['total_global'],
			'total_content'		=> $total_score[0]['total_content'],
			'total_understand'	=> $total_score[0]['total_understand'],
			'total_number'		=> $total,
			'update_time'		=> time(),
		);
		
		if(!$evaluate_score){
			pdo_insert($this->table_evaluate_score, $data);
		}else{
			pdo_update($this->table_evaluate_score, $data, array('uniacid'=>$evaluate['uniacid'],'id'=>$evaluate_score['id']));
		}
		pdo_update($this->table_lesson_parent, array('score'=>$data['score']), array('id'=>$evaluate['lessonid']));
	}

	/**
	 * 发送模版消息
	 */
    public function send_template_message($messageDatas, $uniacid='') {
        global $_W, $_GPC;
        
		if(!$messageDatas['touser'] || !$messageDatas['template_id']){
			return;
		}
		if($uniacid){
			$account = uni_fetch($uniacid);
		}

        load()->classs('weixin.account');
        $account_api = WeixinAccount::create($account);
        $access_token = $account_api->getAccessToken();

        $urls = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
		$messageDatas = urldecode(json_encode($messageDatas));
        $ress = ihttp_request($urls, $messageDatas);

        return json_decode($ress, true);
    }

	/**
	 * 更新用户vip字段
	 */
	 public function updateMemberVip($uid, $vip){
		 return pdo_update($this->table_member, array('vip'=>$vip), array('uid'=>$uid));
	 }

	/* 获取VIP等级信息 */
	public function getLevelById($level_id){
		global $_W;

		return pdo_get($this->table_vip_level, array('uniacid'=>$_W['uniacid'],'id'=>$level_id));
	}

	/* 获取课程下一个章节id */
	public function getNextSectionid($section, $title_list){
		if($section['title_id']){
			$section_sort = pdo_fetchall("SELECT id FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC", array(':parentid'=>$section['parentid'],':title_id'=>$section['title_id'],':status'=>1));
			foreach($section_sort as $key=>$value){
				if($value['id']==$section['id']){
					$next_sectionid = $section_sort[$key+1]['id'];
					break;
				}
			}
			if(!$next_sectionid){
				foreach($title_list as $key=>$value){
					if($value['title_id']==$section['title_id']){
						$next_title = $title_list[$key+1];
						break;
					}
				}
				$next_section = pdo_fetch("SELECT id FROM " .tablename($this->table_lesson_son). " WHERE title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC LIMIT 1", array(':title_id'=>$next_title['title_id'],':status'=>1));
				if($next_section) $next_sectionid = $next_section['id'];
			}
		}else{
			$section_sort = pdo_fetchall("SELECT id FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC", array(':parentid'=>$section['parentid'],':title_id'=>0,':status'=>1));
			foreach($section_sort as $key=>$value){
				if($value['id']==$section['id']){
					$next_sectionid = $section_sort[$key+1]['id'];
					break;
				}
			}
		}

		return $next_sectionid;
	}

	/* 获取订单核销记录 */
	public function getOrderVerifyLog($orderid){
		global $_W;

		$verify_log = pdo_fetchall("SELECT * FROM " .tablename($this->table_order_verify). " WHERE uniacid=:uniacid AND orderid=:orderid ORDER BY id ASC", array(':uniacid'=>$_W['uniacid'], ':orderid'=>$orderid));

		foreach($verify_log as $k=>$v){
			$v['log'] = ($k+1).'、';
			if($v['verify_type']==0){
				$v['log'] .= '核销员：';
			}elseif($v['verify_type']==1){
				$v['log'] .= '后台管理员：';
			}
			$v['log'] .= $v['verify_name'].'(uid:'.$v['verify_uid'].')，于'.date('Y-m-d H:i', $v['addtime']).'核销。';
			$verify_log[$k] = $v;
		}

		$verify_log['count'] = count($verify_log);

		return $verify_log;
	}

	/* 更新旧的核销订单记录 */
	public function updateOrderVerifyLog(){
		global $_W;
		$uniacid = $_W['uniacid'];

		/* 旧的报名课程订单添加核销记录 */
		$old_order = pdo_fetchall("SELECT id, verify_info FROM " .tablename($this->table_order). " WHERE uniacid=:uniacid AND lesson_type=:lesson_type AND status>:status AND is_verify=:is_verify AND verify_info!='' ", array(':uniacid'=>$uniacid,':lesson_type'=>1, ':status'=>0, ':is_verify'=>1));
		foreach($old_order as $v){
			$verify_info = json_decode($v['verify_info'], true);
			$verify_data = array(
				'uniacid' => $uniacid,
				'orderid' => $v['id'],
				'addtime' => $verify_info['verify_time']
			);
			if($verify_info['verify_uid']){
				$verify_member = pdo_get($this->table_mc_members, array('uniacid'=>$uniacid,'uid'=>$verify_info['verify_uid']), 'nickname');
				$verify_data['verify_type'] = 0;
				$verify_data['verify_uid']  = $verify_info['verify_uid'];
				$verify_data['verify_name'] = $verify_member['nickname'];
			}else{
				$verify_data['verify_type'] = 1;
				$verify_data['verify_name'] = $verify_info['verify_admin'];
			}
			pdo_insert($this->table_order_verify, $verify_data);
			pdo_update($this->table_order, array('verify_info'=>''), array('id'=>$v['id']));
		}

		/* 完成核销的订单 */
		$complete_order = pdo_fetchall("SELECT id,verify_number FROM " .tablename($this->table_order). " WHERE uniacid=:uniacid AND lesson_type=:lesson_type AND status>:status AND is_verify=:is_verify ", array(':uniacid'=>$uniacid,':lesson_type'=>1, ':status'=>0, ':is_verify'=>1));
		foreach($complete_order as $v){
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_order_verify). " WHERE uniacid=:uniacid AND orderid=:orderid", array(':uniacid'=>$uniacid, ':orderid'=>$v['id']));
			if($total == $v['verify_number']){
				pdo_update($this->table_order, array('is_verify'=>2), array('id'=>$v['id']));
			}
		}
	}

	/* 减少或增加课程库存
	 * $lessonid 课程id
	 & $spec_id 规格id
	 * $change 库存变动数量 正数增加，负数减少
	 */
	public function updateLessonStock($lessonid, $spec_id, $change){
		if($change > 0){
			if(pdo_update($this->table_lesson_spec, array('spec_stock +='=>$change), array('spec_id'=>$spec_id))){
				pdo_update($this->table_lesson_parent, array('stock +='=>$change), array('id'=>$lessonid));
			}
		}elseif($change < 0){
			if(pdo_update($this->table_lesson_spec, array('spec_stock -='=>abs($change)), array('spec_id'=>$spec_id))){
				pdo_update($this->table_lesson_parent, array('stock -='=>abs($change)), array('id'=>$lessonid));
			}
		}
	}

	/* 查询上级推荐人 */
    public function getParentid($uid) {
        global $_W;

        $parent = pdo_get($this->table_member, array('uniacid'=>$_W['uniacid'], 'uid'=>$uid), array('parentid'));
        if ($parent) {
            return $parent['parentid'];
        } else {
            return '0';
        }
    }

	/* 计算一级代理佣金
	 * $commission_type   单独佣金类型 0.按比例 1.按固定金额
	 * $lessoncommission  单独设置的一级佣金
	 * $settingcommission 默认级别的一级佣金比例
	 * $price 实付金额
	 * $uid 一级分销员uid
	 */
	public function getAgentCommission1($commission_type=0, $lessoncommission, $settingcommission, $price, $uid){
		global $_W;

		if ($lessoncommission > 0) {
			if(!$commission_type){
				$commission = round($price * $lessoncommission * 0.01, 2);
			}else{
				$commission = $lessoncommission;
			}
        } else {
            /* 查询用户是否属于其他分销代理级别 */
            $member = pdo_fetch("SELECT b.commission1 FROM " .tablename($this->table_member). " a LEFT JOIN " .tablename($this->table_commission_level). " b ON a.agent_level=b.id WHERE a.uniacid=:uniacid AND a.uid=:uid", array(':uniacid'=>$_W['uniacid'],':uid'=>$uid));
			
			if ($member['commission1']) {
                $commission = round($price * $member['commission1'] * 0.01, 2);
            } else {
                $commission = round($price * $settingcommission * 0.01, 2);
            }
        }

		return $commission;
	}
	
	/* 计算二级代理佣金
	 * $commission_type   单独佣金类型 0.按比例 1.按固定金额
	 * $lessoncommission  单独设置的二级佣金
	 * $settingcommission 默认级别的二级佣金比例
	 * $price 实付金额
	 * $uid 二级代理商会员id
	 */
	public function getAgentCommission2($commission_type=0, $lessoncommission, $settingcommission, $price, $uid){
		global $_W;

		if ($lessoncommission > 0) {
			if(!$commission_type){
				$commission = round($price * $lessoncommission * 0.01, 2);
			}else{
				$commission = $lessoncommission;
			}
        } else {
            /* 查询用户是否属于其他分销代理级别 */
			$member = pdo_fetch("SELECT b.commission2 FROM " .tablename($this->table_member). " a LEFT JOIN " .tablename($this->table_commission_level). " b ON a.agent_level=b.id WHERE a.uniacid=:uniacid AND a.uid=:uid", array(':uniacid'=>$_W['uniacid'],':uid'=>$uid));

            if ($member['commission2']) {
                $commission = round($price * $member['commission2'] * 0.01, 2);
            } else {
                $commission = round($price * $settingcommission * 0.01, 2);
            }
        }
		return $commission;
	}
	
	/* 计算三级代理佣金
	 * $commission_type   单独佣金类型 0.按比例 1.按固定金额
	 * $lessoncommission  单独设置的三级佣金
	 * $settingcommission 默认级别的三级佣金比例
	 * $price 实付金额
	 * $uid 三级代理商会员id
	 */
	public function getAgentCommission3($commission_type=0, $lessoncommission, $settingcommission, $price, $uid){
		global $_W;

		if ($lessoncommission > 0) {
			if(!$commission_type){
				$commission = round($price * $lessoncommission * 0.01, 2);
			}else{
				$commission = $lessoncommission;
			}
        } else {
            /* 查询用户是否属于其他分销代理级别 */
			$member = pdo_fetch("SELECT b.commission3 FROM " .tablename($this->table_member). " a LEFT JOIN " .tablename($this->table_commission_level). " b ON a.agent_level=b.id WHERE a.uniacid=:uniacid AND a.uid=:uid", array(':uniacid'=>$_W['uniacid'],':uid'=>$uid));

            if ($member['commission3']) {
                $commission = round($price * $member['commission3'] * 0.01, 2);
            } else {
                $commission = round($price * $settingcommission * 0.01, 2);
            }
        }
		return $commission;
	}

	/**
	 * 获取图文章节的上一节和下一节
	 */
	public function getArticleLastAndNext($section, $title_list){
		if($section['title_id']){
			$section_sort = pdo_fetchall("SELECT id,parentid,title FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC", array(':parentid'=>$section['parentid'],':title_id'=>$section['title_id'],':status'=>1));
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
				$prev_article = pdo_fetch("SELECT id,parentid,title FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC LIMIT 1", array(':parentid'=>$section['parentid'],':title_id'=>$prev_title['title_id'],':status'=>1));
			}
			if(!$next_article){
				$next_article = pdo_fetch("SELECT id,parentid,title FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC LIMIT 1", array(':parentid'=>$section['parentid'],':title_id'=>$next_title['title_id'],':status'=>1));
			}
		}else{
			$section_sort = pdo_fetchall("SELECT id,parentid,title FROM " .tablename($this->table_lesson_son). " WHERE parentid=:parentid AND title_id=:title_id AND status=:status ORDER BY displayorder DESC,id ASC", array(':parentid'=>$section['parentid'],':title_id'=>0,':status'=>1));
			foreach($section_sort as $key=>$value){
				if($value['id']==$section['id']){
					$prev_article = $section_sort[$key-1];
					$next_article = $section_sort[$key+1];
				}
			}
		}

		$data = array(
			'prev_article' => $prev_article,
			'next_article' => $next_article,
		);

		return $data;
	}

	/* 获取课程折扣 */
	public function getLessonDiscount($lessonid){
		global $_W;

		$discount_lesson = pdo_fetch("SELECT discount FROM " .tablename($this->table_discount_lesson). " WHERE uniacid=:uniacid AND lesson_id=:lesson_id AND starttime<:time AND endtime>:time", array(':uniacid'=>$_W['uniacid'],':lesson_id'=>$lessonid,':time'=>time()));
		$discount = $discount_lesson['discount'] ? $discount_lesson['discount']*0.01 : 1;

		return $discount;
	}

	/* 获取营销管理参数 */
	public function getMarketParams(){
		global $_W;

		$market = cache_load('fy_lesson_'.$_W['uniacid'].'_market');
		if(empty($market)){
			$market = pdo_get($this->table_market, array('uniacid'=>$_W['uniacid']));
			cache_write('fy_lesson_'.$_W['uniacid'].'_market', $market);
		}

		return $market;
	}

	/* 获取文章公告分类列表 */
	public function getArticleCategory(){
		global $_W;

		$list = cache_load('fy_lesson_'.$_W['uniacid'].'_article_categorylist');
		if(empty($list)){
			$list = pdo_fetchall("SELECT * FROM " .tablename($this->table_article_category). " WHERE uniacid=:uniacid AND status=:status ORDER BY displayorder DESC, id ASC", array(':uniacid'=>$_W['uniacid'], ':status'=>1));
			cache_write('fy_lesson_'.$_W['uniacid'].'_article_categorylist', $list);
		}

		return $list;
	}

	/* 获取讲师分类列表 */
	public function getTeacherCategory(){
		global $_W;

		$category_list = cache_load('fy_lesson_'.$_W['uniacid'].'_teacher_categorylist');
		if(empty($category_list)){
			$list = pdo_fetchall("SELECT * FROM " .tablename($this->table_teacher_category). " WHERE uniacid=:uniacid AND status=:status ORDER BY displayorder DESC, id ASC", array(':uniacid'=>$_W['uniacid'], ':status'=>1));
			
			$category_list = array();
			foreach($list as $v){
				$category_list[$v['id']] = $v['name'];
			}

			cache_write('fy_lesson_'.$_W['uniacid'].'_teacher_categorylist', $category_list);
		}

		return $category_list;
	}

	/*
     * 使用七牛云存储防盗链
     * $accessKey
     * $secretKey
     * $baseUrl
     */
    public function privateDownloadUrl($accessKey, $secretKey, $baseUrl, $expires = 3600) {
        $deadline = time() + $expires;

        $pos = strpos($baseUrl, '?');
        if ($pos !== false) {
            $baseUrl .= '&e=';
        } else {
            $baseUrl .= '?e=';
        }
        $baseUrl .= $deadline;
        $hmac = hash_hmac('sha1', $baseUrl, $secretKey, true);
        $find = array('+', '/');
        $replace = array('-', '_');
        $hmac = str_replace($find, $replace, base64_encode($hmac));

        $token = $accessKey . ':' . $hmac;
        return "$baseUrl&token=$token";
    }

	/*
     * 使用腾讯云存储防盗链
     * $accessKey
     * $secretKey
     * $baseUrl
     */
	public function tencentDownloadUrl($qcloud, $access_url) {
		$qcloudCos = new QcloudCos();
		if($qcloud['qcloud_type']=='xml'){

			return $qcloudCos->getXmlSignature($qcloud['secretid'], $qcloud['secretkey'], $Method='GET', $access_url);
		}elseif(empty($qcloud['qcloud_type']) || $qcloud['qcloud_type']=='json'){
			return $qcloudCos->getJsonSignature($qcloud, $access_url);
		}
	}

	/*
     * 阿里云OSS播放链接处理
     * $default_url      初始播放链接
     * $aliyunoss array  阿里云oss参数
     * $play_url		 最终播放链接
     */
	public function aliyunOssPlayUrl($default_url, $aliyunoss) {
		$param = explode($aliyunoss['endpoint'] ,$default_url);
		$play_url = 'http://'.$aliyunoss['bucket_url'].$param[1];

		if($aliyunoss['https']){
			$play_url = 'https://'.$aliyunoss['bucket_url'].$param[1];
		}

		return $play_url;
	}

	/* 获取远程图片保存到本地 */
    public function saveImage($url, $image_path, $type = '') {
		global $_W;

		$header = array(     
			'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
			'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
			'Accept-Encoding: gzip, deflate'
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		$data = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		/* 把URL格式的图片转成base64_encode格式 */
		if ($code == 200) {
			$imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
		}
		$img_content = $imgBase64Code;
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result)){
			$type = $result[2]; /* 图片类型 */
			if($type=='jpeg'){
				$type = 'jpg';
			}
			$new_file = $image_path.$type;   
			if (!file_put_contents($new_file, base64_decode(str_replace($result[1], '', $img_content)))){
				if($type=='avatar'){
					echo '获取头像失败，请在个人中心点击头像更新';
				}
			}else{
				return $type;
			}
		}
    }

	/**
     * 图片加水印（适用于png/jpg/gif格式）
     * 
     * @author flynetcn
     *
     * @param $srcImg 原图片
     * @param $waterImg 水印图片
     * @param $savepath 保存路径
     * @param $savename 保存名字
     * @param $positon 水印位置 
     * 
     * @return 成功 -- 加水印后的新图片地址
     *          失败 -- -1:原文件不存在, -2:水印图片不存在, -3:原文件图像对象建立失败
     *          -4:水印文件图像对象建立失败 -5:加水印后的新图片保存失败
     */
    public function img_water_mark($srcImg, $waterImg, $savepath = null, $savename = null, $x, $y, $alpha = 100) {
        $temp = pathinfo($srcImg);
        $name = $temp['basename'];
        $path = $temp['dirname'];
        $exte = $temp['extension'];
        $savename = $savename ? $savename : $name;
        $savepath = $savepath ? $savepath : $path;
        $savefile = $savepath . '/' . $savename;
        $srcinfo = @getimagesize($srcImg);
        if (!$srcinfo) {
            return -1; /* 原文件不存在 */
        }
        $waterinfo = @getimagesize($waterImg);
        if (!$waterinfo) {
            return -2; /* 水印图片不存在 */
        }
        $srcImgObj = $this->image_create_from_ext($srcImg);
        if (!$srcImgObj) {
            return -3; /* 原文件图像对象建立失败 */
        }
        $waterImgObj = $this->image_create_from_ext($waterImg);
        if (!$waterImgObj) {
            return -4; /* 水印文件图像对象建立失败 */
        }

        imagecopymerge($srcImgObj, $waterImgObj, $x, $y, 0, 0, $waterinfo[0], $waterinfo[1], $alpha);
        switch ($srcinfo[2]) {
            case 1: imagegif($srcImgObj, $savefile);
                break;
            case 2: imagejpeg($srcImgObj, $savefile);
                break;
            case 3: imagepng($srcImgObj, $savefile);
                break;
            default: return -5; /* 保存失败 */
        }
        imagedestroy($srcImgObj);
        imagedestroy($waterImgObj);
        return $savefile;
    }

    public function image_create_from_ext($imgfile) {
        $info = getimagesize($imgfile);
        $im = null;
        switch ($info[2]) {
            case 1: $im = imagecreatefromgif($imgfile);
                break;
            case 2: $im = imagecreatefromjpeg($imgfile);
                break;
            case 3: $im = imagecreatefrompng($imgfile);
                break;
        }
        return $im;
    }

	/**
	* desription 压缩图片
	* @param $imgsrc   源图片路径 
	* @param $imgdst     目标图片路径 
	* @param $maxWidth   最大宽 
	* @param $maxHeight  最大高 
	* @param $imgQuality 图片质量
	*/
	public function resize($imgsrc, $imgdst, $maxWidth=1024, $maxHeight=1024, $imgQuality){
		list($width,$height,$type)=getimagesize($imgsrc);

		if ($width < $maxWidth || $height < $maxHeight){
			return;
		}

		$scale = min($maxWidth / $width, $maxHeight / $height);
		if($scale>1){
			return;
		}

		$new_width = floor($scale * $width);
		$new_height = floor($scale * $height);

		switch($type){
			case 1:
				$image_wp = imagecreatetruecolor($new_width, $new_height);
				$image = imagecreatefromgif($imgsrc);
				imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagejpeg($image_wp, $imgdst,$imgQuality);
				imagedestroy($image_wp);
			break;
			case 2:
				$image_wp = imagecreatetruecolor($new_width, $new_height);
				$image = imagecreatefromjpeg($imgsrc);
				imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagejpeg($image_wp, $imgdst,$imgQuality);
				imagedestroy($image_wp);
			break;
			case 3:
				$image_wp = imagecreatetruecolor($new_width, $new_height);
				$image = imagecreatefrompng($imgsrc);
				imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagejpeg($image_wp, $imgdst,$imgQuality);
				imagedestroy($image_wp);
			break;
		}
	}

	/**
	 *	裁切图片为圆形
	 *	$imgpath 源图片路径
	 *	$savepath 新图片路径
	 */
	public function circularImg($imgpath, $savepath){
		$ext = pathinfo($imgpath);
		$src_img = null;
		switch ($ext['extension']) {
			case 'jpg':
				$src_img = imagecreatefromjpeg($imgpath);
				break;
			case 'png':
				$src_img = imagecreatefrompng($imgpath);
				break;
		}
		$wh  = getimagesize($imgpath);
		$w   = $wh[0];
		$h   = $wh[1];
		$w   = min($w, $h);
		$h   = $w;
		$img = imagecreatetruecolor($w, $h);
		imagesavealpha($img, true);
		$bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
		imagefill($img, 0, 0, $bg);
		$r   = $w / 2;
		$y_x = $r;
		$y_y = $r;
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				$rgbColor = imagecolorat($src_img, $x, $y);
				if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
			}
		}

		imagepng($img, $savepath);
		imagedestroy($img);
	}

	/* 操作系统判断 */
	public function checkSystenType(){
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
			return 'ios';
		}else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
			return 'android';
		}else{
			return '';
		}
	}

	/* 检查黑名单 $type visit访问 order下单 */
    public function check_black_list($type, $uid='') {
        global $_W;

		if(!$uid){
			return;
		}

		$member = pdo_get($this->table_member, array('uniacid'=>$_W['uniacid'],'uid'=>$uid), array('blacklist'));
		if(!$member['blacklist']){
			return;
		}
		if($type=='visit' && $member['blacklist']==2){
			message("当前用户已被停止访问，请联系管理员");
		}
		if($type=='order' && $member['blacklist']==1){
			message("当前用户已被停止下单，请联系管理员");
		}
    }

	/*
     * 添加操作日志
     * $admin_uid		管理员id
     * $admin_username  管理员用户名
     * $log_type		操作类型 1.增加 2.删除 3更新
     * $function		操作的功能
     * $content			操作描述
     */
    public function addSysLog($admin_uid, $admin_username, $log_type, $function, $content) {
        global $_W;
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }

        $log_data = array(
            'uniacid' => $_W['uniacid'],
            'admin_uid' => $admin_uid,
            'admin_username' => $admin_username,
            'log_type' => $log_type,
            'function' => $function,
            'content' => $content,
            'ip' => $ip,
            'addtime' => time(),
        );
        return pdo_insert($this->table_syslog, $log_data);
    }

	/* 获取代理级别名称 */
	public function getAgentLevelName($levelId){
		global $_W;

		$level = pdo_fetch("SELECT levelname FROM " .tablename($this->table_commission_level). " WHERE uniacid=:uniacid AND id=:id", array(':uniacid'=>$_W['uniacid'], ':id'=>$levelId));
		
		return $level?$level['levelname']:'默认级别';
	}

	/* 获取下级粉丝总数 */
	public function getFansCount($uid){
		global $_W;

		return pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_member). " WHERE uniacid=:uniacid AND parentid=:parentid", array(':uniacid'=>$_W['uniacid'], ':parentid'=>$uid));
	}

	/* 获取微信商户号和支付订单号 */
	public function getWechatPayNo($tid){
		return pdo_fetch("SELECT uniontid,tag FROM ".tablename('core_paylog'). " WHERE tid=:tid", array(':tid'=>$tid));
	}

	/* 章节更新时间检查 */
	public function tranTime($time){
		$rtime = date("m-d H:i",$time);
		$htime = date("H:i",$time);
		$time = time() - $time;
		if ($time < 60){
			$str = '刚刚';
		}elseif ($time < 60 * 60){
			$min = floor($time/60);
			$str = $min.'分钟前';
		}elseif ($time < 60 * 60 * 24){
			$h = floor($time/(60*60));
			$str = $h.'小时前';
		}elseif ($time < 60 * 60 * 24 * 3){
			$d = floor($time/(60*60*24));
			if($d==1)
				$str = '昨天';
			else
				$str = '前天';
		}else{
			$str = $rtime;
		}
		return $str;
	}

	/**
	 * 时间转为时分秒
	 */
	public function secToTime($seconds, $show_h=true){
		if($seconds >3600){
			$hours = intval($seconds/3600) >=10 ? intval($seconds/3600) : '0'.intval($seconds/3600);
			$minutes = $seconds % 3600;
			$time = $hours.":".gmstrftime('%M:%S',$minutes);
		}else{
			if($show_h){
				$time = gmstrftime('%H:%M:%S',$seconds);
			}else{
				$time = gmstrftime('%M:%S',$seconds);
			}
		}

		return $time;
	}
	
	/**
	 * 时间转为时分秒
	 */
	public function secToTime2($seconds){
		$d = floor($seconds / (3600*24));
		$h = floor(($seconds % (3600*24)) / 3600);
		$m = floor((($seconds % (3600*24)) % 3600) / 60);

		if($seconds==0){
			$time = '0分0秒';
		}elseif($seconds<60){
			$time = '0分'.$seconds.'秒';
		}elseif($seconds==60){
			$time = '1分0秒';
		}elseif($seconds>60 && $seconds<3600){
			$time = intval($seconds/60).'分'.($seconds%60).'秒';
		}elseif($seconds>=3600){
			if($d){
				$time = $d.'天'.$h.'时'.$m.'分';
			}else{
				$time =  $h.'时'.$m.'分';
			}
		}

		return $time;
	}

	/**
	 * 十六进制颜色转为RGB
	 */
	function hexTorgb($hexColor) {
		$color = str_replace('#', '', $hexColor);
		if (strlen($color) > 3) {
			$rgb = array(
				'r' => hexdec(substr($color, 0, 2)),
				'g' => hexdec(substr($color, 2, 2)),
				'b' => hexdec(substr($color, 4, 2))
			);
		} else {
			$color = $hexColor;
			$r = substr($color, 0, 1) . substr($color, 0, 1);
			$g = substr($color, 1, 1) . substr($color, 1, 1);
			$b = substr($color, 2, 1) . substr($color, 2, 1);
			$rgb = array(
				'r' => hexdec($r),
				'g' => hexdec($g),
				'b' => hexdec($b)
			);
		}
		return $rgb;
	}

	/**
	 * 把字符串解析为数组
	 */
	public function char_array($str, $charset="utf-8"){
		$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']	  = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']	  = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);

		return $match[0];
	}

	/* 下载文件 */
	public function downloadFile($fileid){
		global $_W;

		$document = pdo_get($this->table_document, array('uniacid'=>$_W['uniacid'],'id'=>$fileid));
		if(empty($document)){
			message("文件记录不存在");
		}

		$buffer = 102400;
		$ua = $_SERVER["HTTP_USER_AGENT"];
		$filename = $document['title'];
		$encoded_filename = urlencode($filename);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);

		if (!empty($_W['setting']['remote']['type'])) {
			$file_url = $_W['attachurl'].$document['filepath'];
			$file = @fopen($file_url, "r");
			if($file){
				header("Content-type: application/octet-stream");
				/* 浏览器判断 */
				if(preg_match("/MSIE/", $ua) || preg_match("/Trident\/7.0/", $ua) || preg_match("/Edge/", $ua)){
					header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
				} else if (preg_match("/Firefox/", $ua)) {
					header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
				} else {
					header('Content-Disposition: attachment; filename="' . $filename . '"');
				}
				while (!feof($file)) {
					echo fread($file, $buffer);
				}
				fclose($file);
				exit();
			}else{
				message("远程文件不存在");
			}
		}else{
			$file_url = ATTACHMENT_ROOT.$document['filepath'];

			if(!file_exists($file_url)) {
				message("本地文件不存在");
			}
 
			$fp = fopen($file_url, "r");
			$fileSize = filesize($file_url);
			$fileData = '';
			while (!feof($fp)) {
				$fileData .= fread($fp, $buffer);
			}
			fclose($fp);
		 
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-type:application/octet-stream;");
			header("Accept-Ranges:bytes");
			header("Accept-Length:{$fileSize}");
			/* 浏览器判断 */
			if(preg_match("/MSIE/", $ua) || preg_match("/Trident\/7.0/", $ua) || preg_match("/Edge/", $ua)){
				header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
			} else if (preg_match("/Firefox/", $ua)) {
				header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
			//} else if (preg_match("/Safari/", $ua)) {
			
			} else {
				header('Content-Disposition: attachment; filename="' . $filename . '"');
			}
			header("Content-Transfer-Encoding: binary");
			echo $fileData;
			exit();
		}
	}

	/* 获取直播地址 */
	public function getLiveUrl($setting, $live_info, $play_type='mobile'){
		global $_W;
		$video_live = json_decode($setting['video_live'], true);

		if($live_info['type']=='0'){
			return $live_info['videourl'];

		}elseif($live_info['type']=='1'){
			if($play_type=='pc' && $live_info['live_speed']=='0'){
				$live_info['videourl'] = str_replace(".m3u8", ".flv", $live_info['videourl']);
			}

			return $live_info['videourl'];

		}elseif($live_info['type']=='2'){
			$appName = "/live/";

			if($play_type=='pc'){
				$play_suffix = ".flv";
			}else{
				$play_suffix = ".m3u8";
			}

			$uri = $appName.$live_info['stream_name'].$play_suffix;
			if($video_live['aliyun']['transcoding_id']){
				$uri = $appName.$live_info['stream_name']."_".$video_live['aliyun']['transcoding_id'].".m3u8";
			}
			
			$timestamp = time() + 60*$video_live['aliyun']['play_validity'];
			$rand = random(32, true);
			$uid = 0;
			$hashValue = md5($uri."-".$timestamp."-".$rand."-".$uid."-".$video_live['aliyun']['play_key']);
			$play_url = $_W['sitescheme'].$video_live['aliyun']['play_domain'].$uri."?auth_key=".$timestamp."-".$rand."-".$uid."-".$hashValue;

			return $play_url;

		}elseif($live_info['type']=='3'){
			/* YY */
			$url = 'https://interface.yy.com/hls/get/0/'.$live_info['sid'].'/'.$live_info['ssid'].'?appid=0&excid=1200&type=m3u8&isHttps=0&callback=jsonp2';
			$response = ihttp_request($url, array(), array(
				'CURLOPT_REFERER' => 'https://wap.yy.com/mobileweb/'.$live_info['sid'].'/'.$live_info['ssid'].'?tempId='.$live_info['tid'].''
			));
			$result = json_decode(substr($response['content'],7,-1), true);
			if($result['code']==0){
				if(!$result['hls']){
					return;
				}
				return $result['hls'];
			}else{
				message("系统繁忙，请稍候刷新再试");
			}
		}
	}

	/*
	 * 绘图文字分行函数
	 * by COoL
	 * - 输入：
	 * str: 原字符串
	 * fontFamily: 字体
	 * fontSize: 字号
	 * charset: 字符编码
	 * width: 限制每行宽度(px)
	 * - 输出：
	 * 分行后的字符串数组
	 */
	function autoLineSplit ($str, $fontFamily, $fontSize, $charset, $width) {
		$result = [];

		$len = (strlen($str) + mb_strlen($str, $charset)) / 2;

		/* 计算总占宽 */
		$dimensions = imagettfbbox($fontSize, 0, $fontFamily, $str);
		$textWidth = abs($dimensions[4] - $dimensions[0]);

		/* 计算每个字符的长度 */
		$singleW = $textWidth / $len;
		/* 计算每行最多容纳多少个字符 */
		$maxCount = floor($width / $singleW);

		while ($len > $maxCount) {
			/* 成功取得一行 */
			$result[] = mb_strimwidth($str, 0, $maxCount, '', $charset);
			/* 移除上一行的字符 */
			$str = str_replace($result[count($result) - 1], '', $str);
			/* 重新计算长度 */
			$len = (strlen($str) + mb_strlen($str, $charset)) / 2;
		}
		/* 最后一行在循环结束时执行 */
		$result[] = $str;
		
		return $result;
	}

	/**
	 * 导出excel(csv)
	 * @data 导出数据
	 * @headlist 第一行,列名
	 * @fileName 输出Excel文件名
	 */
	public function exportCSV($data=array(), $headlist=array(), $fileName){
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$fileName.'.csv"');
		header('Cache-Control: max-age=0');

		$fp = fopen('php://output', 'a');
		foreach ($headlist as $key => $value) {
			$headlist[$key] = iconv('utf-8', 'gbk', $value);
		}
		fputcsv($fp, $headlist);

		$num = 0;
		$count = count($data);
		for ($i = 0; $i < $count; $i++) {
			$num++;
			if (10000 == $num) { 
				ob_flush();
				flush();
				$num = 0;
			}
			$row = $data[$i];
			foreach ($row as $key => $value) {
				$row[$key] = iconv('utf-8', 'gbk', $value);
			}
			fputcsv($fp, $row);
		}
		exit();
	}
 
 }