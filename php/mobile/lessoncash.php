<?php
/**
 * 讲师收入提现
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

$title = "讲师课程收入提现";

if($op=='display'){
	$setting_cashway = unserialize($comsetting['cash_way']);
	$member = pdo_fetch("SELECT a.*,b.nickname,b.avatar FROM " .tablename($this->table_member). " a LEFT JOIN " .tablename($this->table_mc_members). " b ON a.uid=b.uid WHERE a.uid=:uid", array(':uid'=>$uid));
	if(empty($member['avatar'])){
		$avatar = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg";
	}else{
		$inc = strstr($member['avatar'], "http://") || strstr($member['avatar'], "https://");
		$avatar = $inc ? $member['avatar'] : $_W['attachurl'].$member['avatar'];
	}
	
	if($member['nopay_lesson'] < $comsetting['cash_lower_teacher']){
		message("当前提现最低额度为{$comsetting['cash_lower_teacher']}元，您的可提现额度不够", "", "warning");
	}

	$lastcashlog = pdo_fetch("SELECT pay_account,pay_name FROM " .tablename($this->table_cashlog). " WHERE uniacid=:uniacid AND uid=:uid AND cash_way=3 ORDER BY id DESC LIMIT 1", array(':uniacid'=>$uniacid, ':uid'=>$uid));

	if(checksubmit('submit')){
		$cash_way = intval($_GPC['cash_way']); //1.提现到余额 2.提现到微信钱包 3.支付宝
		$cash_num = intval($_GPC['cash_num']*100)/100;
		
		//提现手续费
		if($cash_way==1){
			$service_amount = 0;
		}else{
			$service_amount = round($cash_num * $comsetting['cash_service_num'] * 0.01, 2);
		}

		$pay_account = trim($_GPC['pay_account']);
		$pay_name = trim($_GPC['pay_name']);
		
		if(empty($cash_way)){
			message("请选择提现方式", $this->createMobileUrl('lessoncash'), "error");
		}
		if($cash_way==3 && empty($pay_account)){
			message("请输入提现帐号", $this->createMobileUrl('lessoncash'), "error");
		}
		if($cash_way==3 && empty($pay_name)){
			message("请输入提现户主姓名", $this->createMobileUrl('lessoncash'), "error");
		}
		if($cash_num < $comsetting['cash_lower_teacher']){
			message("当前系统最低提现额度为{$comsetting['cash_lower_teacher']}元", $this->createMobileUrl('lessoncash'), "error");
		}
		if($cash_num > 5000){
			message("单次提现不允许超过5000元", $this->createMobileUrl('lessoncash'), "error");
		}
		if($cash_num > ($member['nopay_lesson']-$service_amount)){
			message("您的可提现佣金额度为".($member['nopay_lesson']-$service_amount)."元", $this->createMobileUrl('lessoncash'), "error");
		}

		/*当前登录会员关联的粉丝*/
		$fans = pdo_fetch("SELECT openid FROM " .tablename($this->table_fans). " WHERE uid=:uid", array(':uid'=>$uid));
		if($cash_way==2 && empty($fans['openid'])){
			message("当前帐号未关联微信，无法提现到微信钱包", $this->createMobileUrl('lessoncash'), "error");
		}

		/**
		 * 减少会员可提现佣金和增加会员已提现佣金
		 */
		$upmember = array(
			'nopay_lesson'	=> $member['nopay_lesson'] - $cash_num - $service_amount,
			'pay_lesson'	=> $member['pay_lesson'] + $cash_num + $service_amount,
		);
		$succ = pdo_update($this->table_member, $upmember, array('id'=>$member['id']));

		if($succ){
			$cashlog = array(
				'uniacid'	  => $uniacid,
				'cash_way'	  => $cash_way, //1.提现到余额  2.提现到微信钱包 3.支付宝
				'pay_account' => $pay_account,
				'pay_name'    => $pay_name,
				'uid'		  => $uid,
				'openid'	  => $fans['openid'],
				'cash_num'    => $cash_num,
				'service_num' => $service_amount,
				'lesson_type' => 2, /* 提现类型 1.分销佣金提现 2.课程收入提现 */
				'addtime'	  => time(),
			);
			
			if($cash_way==1){
				$cashlog['cash_type'] = 2; //提现到余额默认为自动到账
			}elseif($cash_way==3){
				$cashlog['cash_type'] = 1; //提现到支付宝默认为管理员审核
			}else{
				$cashlog['cash_type'] = $comsetting['cash_type'];
			}

			if($cash_way==1){/*  1.提现到余额 */
				load()->model('mc');
				$result = mc_credit_update($uid, 'credit2', $cash_num, array('1'=>'微课堂讲师收入提现'));

				if($result){
					$cashlog['status']       = 1;
					$cashlog['disposetime']  = time();
					$cashlog['remark']		 = "";

					pdo_insert($this->table_cashlog, $cashlog);
					message("提现成功，佣金已发放到您的账户余额！", $this->createMobileUrl('teachercenter'), "success");
				}

			}elseif($cash_way==2 || $cash_way==3){/*  2.提现到微信钱包 3.提现到支付宝 */
				if($cashlog['cash_type']==1){ /* 提现方式为管理员审核 */
					$cashlog['status'] = 0;
					pdo_insert($this->table_cashlog, $cashlog);

					/* 模版消息通知管理员 */
					if($cash_way==2){
						$cash_name = "微信钱包";
					}elseif($cash_way==3){
						$cash_name = "支付宝钱包";
					}

					$tplmessage = pdo_fetch("SELECT newcash, newcash_format FROM " .tablename($this->table_tplmessage). " WHERE uniacid=:uniacid", array(':uniacid'=>$uniacid));
					$newcash_format = json_decode($tplmessage['newcash_format'], true);
			
					$manage = explode(",", $setting['manageopenid']);
					foreach($manage as $manageopenid){
						$sendneworder = array(
							'touser'      => $manageopenid,
							'template_id' => $tplmessage['newcash'],
							'url'         => "",
							'topcolor'    => "",
							'data'        => array(
								'first'=> array(
									'value' => $newcash_format['first'] ? $newcash_format['first'] : "亲，您有一条新的用户提现申请",
									'color' => "",
								),
								'keyword1'  => array(
									'value' => $member['nickname'],
									'color' => "",
								),
								'keyword2'  => array(
									'value' => date('Y-m-d H:i', time()),
									'color' => "",
								),
								'keyword3'  => array(
									'value' => $cash_num."元",
									'color' => "",
								),
								'keyword4'  => array(
									'value' => $cash_name,
									'color' => "",
								),
								'remark'	=> array(
									'value' => $newcash_format['remark'] ? $newcash_format['remark'] : "详情请登录网站后台查看！",
									'color' => "",
								),
							)
						);
						$this->send_template_message($sendneworder);
					}
					message("提交申请成功，请等待管理员审核！", $this->createMobileUrl('teachercenter'), "success");
				}elseif($cashlog['cash_type']==2){ /* 提现方式为自动提现到微信零钱钱包 */
					$desc1 = $_W['current_module']['title'] ? $_W['current_module']['title'] : '微课堂';
					$desc = $desc1."讲师收入提现";
					

					$post = array('total_amount'=>$cash_num, 'desc'=>$desc);
					$fans = array('openid'=>$member['openid'], 'nickname'=>$member['nickname']);
					$result = $this->companyPay($post,$fans);

					if($result['result_code']=='SUCCESS'){
						$cashlog['status']           = 1;
						$cashlog['disposetime']      = strtotime($result['payment_time']);
						$cashlog['partner_trade_no'] = $result['partner_trade_no'];
						$cashlog['payment_no']	     = $result['payment_no'];
						$cashlog['remark']			 = "";

						pdo_insert($this->table_cashlog, $cashlog);
						message("提现成功，提现金额已发放到您的微信钱包！", $this->createMobileUrl('teachercenter'), "success");

					}else{
						/*回滚操作*/
						$rollback = array(
							'nopay_lesson'	=> $member['nopay_lesson'],
							'pay_lesson'	=> $member['pay_lesson'],
						);
						pdo_update($this->table_member, $rollback, array('id'=>$member['id']));
						
						message($result['return_msg'], $this->createMobileUrl("lessoncash"), "error");
					}
				}
			}
		}

	}
}


include $this->template("../mobile/{$template}/lessoncash");

?>