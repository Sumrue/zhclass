<?php
/**
 * 支付结果处理
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

class Payresult extends WeModuleSite
{
    public $table_aliyun_upload		 = 'fy_lesson_aliyun_upload';
    public $table_aliyunoss_upload	 = 'fy_lesson_aliyunoss_upload';
    public $table_article			 = 'fy_lesson_article';
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
    public $table_evaluate			 = 'fy_lesson_evaluate';
    public $table_evaluate_score	 = 'fy_lesson_evaluate_score';
    public $table_lesson_history	 = 'fy_lesson_history';
    public $table_index_module		 = 'fy_lesson_index_module';
    public $table_inform			 = 'fy_lesson_inform';
    public $table_inform_fans		 = 'fy_lesson_inform_fans';
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
    public $table_lesson_son		 = 'fy_lesson_son';
    public $table_lesson_title		 = 'fy_lesson_title';
    public $table_lesson_spec		 = 'fy_lesson_spec';
    public $table_static			 = 'fy_lesson_static';
    public $table_study_duration	 = 'fy_lesson_study_duration';
    public $table_subscribe_msg		 = 'fy_lesson_subscribe_msg';
    public $table_syslog			 = 'fy_lesson_syslog';
    public $table_teacher			 = 'fy_lesson_teacher';
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
     * $params		支付返回信息
     * $setting		基本设置
     * $comsetting  分销设置
     * $wechat_type 微信支付类型, wechat微信公众号, wxapp微信小程序
     */
    public function dealResult($params, $setting, $comsetting, $wechat_type)
    {
        $ordersn = $params['tid'];
        $order_type = substr($ordersn, 0, 1);

        /* 购买VIP订单 */
        if ($order_type=='V') {
            $viporder = pdo_fetch("SELECT * FROM " .tablename($this->table_member_order). " WHERE ordersn=:ordersn", array(':ordersn'=>$ordersn));
            $uniacid = $viporder['uniacid'];
            $uid = $viporder['uid'];

            $lessonmember = pdo_fetch("SELECT a.*,b.openid FROM " . tablename($this->table_member) . " a LEFT JOIN ".tablename($this->table_fans)." b ON a.uid=b.uid WHERE a.uniacid=:uniacid AND a.uid=:uid", array(':uniacid'=>$uniacid,':uid'=>$uid));

            if ((($params['result'] == 'success' && $params['from'] == 'notify') || $params['type'] == 'credit') && $viporder['status'] == 0) {
                /* 支付成功逻辑操作 */
                $data = array('status' => $params['result'] == 'success' ? 1 : 0);
                if (!empty($params['type'])) {
                    $data['paytype'] = $params['type'];
                } else {
                    $data['paytype'] = $params['trade_type'] ? $wechat_type : '';
                }
                
                $data['paytime'] = time();
                if (pdo_update($this->table_member_order, $data, array('ordersn' => $ordersn))) {
                    /* 更新用户VIP有效期 */
                    $validity = $this->updateVipValidity($uniacid, $lessonmember, $viporder);
                    
                    /* 订单金额加入今日销售额汇总表 */
                    $this->staticAmount($uniacid, 1, $viporder['vipmoney']);
                    
                    /* 判断分销员状态变化 */
                    $this->checkAgentStatus($lessonmember, $comsetting, $viporder['vipmoney']);
                    
                    /* 一级佣金 */
                    if ($viporder['member1'] > 0 && $viporder['commission1'] > 0) {
                        $this->sendCommissionToUser($uniacid, $viporder['member1'], $lessonmember, 1, $setting, $viporder, $viporder['commission1'], $level=1, $comsetting);
                    }
                    
                    /* 二级佣金 */
                    if ($viporder['member2'] > 0 && $viporder['commission2'] > 0) {
                        $this->sendCommissionToUser($uniacid, $viporder['member2'], $lessonmember, 1, $setting, $viporder, $viporder['commission2'], $level=2, $comsetting);
                    }

                    /* 三级佣金 */
                    if ($viporder['member3'] > 0 && $viporder['commission3'] > 0) {
                        $this->sendCommissionToUser($uniacid, $viporder['member3'], $lessonmember, 1, $setting, $viporder, $viporder['commission3'], $level=3, $comsetting);
                    }
                    
                    /* 购买成功模版消息通知用户 */
                    $this->sendMessageToUser($uniacid, $setting, $viporder, 1, $validity);
                    /* 新VIP订单提醒(管理员) */
                    $this->sendOrderMessageToAdmin($setting, $viporder, 1);
                    /* 更新会员vip字段 */
                    $this->updateMemberVip($uid, $vip=1);

                    /* 赠送积分操作 */
                    if ($viporder['integral'] > 0) {
                        $this->handleUserIntegral($type=1, $viporder['ordersn'], $viporder['uid'], $viporder['integral']);
                    }
                }
            }

            if ($params['from'] == 'return') {
                $successUrl = $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&ispay=1&do=vip&m=fy_lessonv2";
                message("购买成功", $successUrl, 'success');
            }
        }

        /* 购买课程订单 */
        if ($order_type=='L') {
            $lessonorder = pdo_fetch("SELECT * FROM " .tablename($this->table_order). " WHERE ordersn=:ordersn", array(':ordersn'=>$ordersn));
            $uniacid = $lessonorder['uniacid'];
            $uid = $lessonorder['uid'];
            
            $lessonmember = pdo_fetch("SELECT a.*,b.openid FROM " . tablename($this->table_member) . " a LEFT JOIN ".tablename($this->table_fans)." b ON a.uid=b.uid WHERE a.uniacid=:uniacid AND a.uid=:uid", array(':uniacid'=>$uniacid,':uid'=>$uid));

            if ((($params['result'] == 'success' && $params['from'] == 'notify') || $params['type'] == 'credit' || $params['fee'] == 0) && ($lessonorder['status']==0 || $lessonorder['status']==-1)) {
                /* 支付成功逻辑操作 */
                $data = array('status' => $params['result'] == 'success' ? 1 : 0);
                if (!empty($params['type'])) {
                    $data['paytype'] = $params['type'];
                } else {
                    $data['paytype'] = $params['trade_type'] ? $wechat_type : '';
                }

                $data['paytime'] = time();
                if ($lessonorder['validity']>0) {
                    $data['validity'] = time()+86400*$lessonorder['validity'];
                }
                if (pdo_update($this->table_order, $data, array('ordersn' => $ordersn))) {
                    /* 增加课程购买人数 */
                    $this->updateLessonNumber($lessonorder['lessonid']);
                    
                    /* 订单金额加入今日销售额汇总表 */
                    $this->staticAmount($uniacid, 2, $lessonorder['price']);
                    
                    /* 判断分销员状态变化 */
                    $this->checkAgentStatus($lessonmember, $comsetting, $lessonorder['price']);
                    
                    /* 一级佣金 */
                    if ($lessonorder['member1'] > 0 && $lessonorder['commission1'] > 0) {
                        $this->sendCommissionToUser($uniacid, $lessonorder['member1'], $lessonmember, 2, $setting, $lessonorder, $lessonorder['commission1'], $level=1, $comsetting);
                    }
                    
                    /* 二级佣金 */
                    if ($lessonorder['member2'] > 0 && $lessonorder['commission2'] > 0) {
                        $this->sendCommissionToUser($uniacid, $lessonorder['member2'], $lessonmember, 2, $setting, $lessonorder, $lessonorder['commission2'], $level=2, $comsetting);
                    }
                    
                    /* 三级佣金 */
                    if ($lessonorder['member3'] > 0 && $lessonorder['commission3'] > 0) {
                        $this->sendCommissionToUser($uniacid, $lessonorder['member3'], $lessonmember, 2, $setting, $lessonorder, $lessonorder['commission3'], $level=3, $comsetting);
                    }
                    
                    /* 讲师分成 */
                    if ($lessonorder['price'] > 0 && $lessonorder['teacher_income'] > 0) {
                        $this->sendCommissionToTeacher($uniacid, $lessonorder, $setting, $type=2);
                    }

                    /* 机构分成 */
                    if ($lessonorder['price'] > 0 && $lessonorder['company_uid'] > 0 && $lessonorder['company_income'] > 0) {
                        $this->sendCommissionToCompany($uniacid, $lessonorder, $setting);
                    }
                    
                    
                    /* 购买成功模版消息通知用户 */
                    $this->sendMessageToUser($uniacid, $setting, $lessonorder, 2, $validity="");
                    /* 新课程订单提醒(管理员) */
                    $this->sendOrderMessageToAdmin($setting, $lessonorder, 2);
                    
                    /* 赠送积分操作 */
                    if ($lessonorder['integral'] > 0) {
                        $this->handleUserIntegral($type=2, $lessonorder['ordersn'], $lessonorder['uid'], $lessonorder['integral']);
                    }

                    /* 给用户发放优惠券 */
                    $this->sendCouponByBuyLesson($lessonmember, $setting);
                }
            }

            if ($params['from'] == 'return') {
                if ($lessonorder['lesson_type']==1) {
                    /* 报名课程 */
                    $successUrl = $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&status=payed&do=mylesson&m=fy_lessonv2";
                    message("购买课程成功", $successUrl, 'success');
                } else {
                    /* 普通课程 */
                    $successUrl = $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&ispay=1&id={$lessonorder['lessonid']}&do=lesson&m=fy_lessonv2";
                    message("购买课程成功", $successUrl, 'success');
                }
            }
        }

        /* 购买讲师订单 */
        if ($order_type=='T') {
            $teacher_order = pdo_fetch("SELECT * FROM " .tablename($this->table_teacher_order). " WHERE ordersn=:ordersn", array(':ordersn'=>$ordersn));
            $uniacid = $teacher_order['uniacid'];
            $uid = $teacher_order['uid'];

            $lessonmember = pdo_fetch("SELECT a.*,b.openid FROM " . tablename($this->table_member) . " a LEFT JOIN ".tablename($this->table_fans)." b ON a.uid=b.uid WHERE a.uniacid=:uniacid AND a.uid=:uid", array(':uniacid'=>$uniacid,':uid'=>$uid));

            if ((($params['result'] == 'success' && $params['from'] == 'notify') || $params['type'] == 'credit') && $teacher_order['status'] == 0) {
                /* 支付成功逻辑操作 */
                $data = array('status' => $params['result'] == 'success' ? 1 : 0);
                if (!empty($params['type'])) {
                    $data['paytype'] = $params['type'];
                } else {
                    $data['paytype'] = $params['trade_type'] ? $wechat_type : '';
                }
                
                $data['paytime'] = time();
                if (pdo_update($this->table_teacher_order, $data, array('ordersn' => $ordersn))) {
                    /* 更新用户讲师有效期 */
                    $validity = $this->updateTeacherValidity($uniacid, $lessonmember, $teacher_order);
                    
                    /* 订单金额加入今日销售额汇总表 */
                    $this->staticAmount($uniacid, 3, $teacher_order['price']);
                    
                    /* 判断分销员状态变化 */
                    $this->checkAgentStatus($lessonmember, $comsetting, $teacher_order['price']);
                    
                    /* 一级佣金 */
                    if ($teacher_order['member1'] > 0 && $teacher_order['commission1'] > 0) {
                        $this->sendCommissionToUser($uniacid, $teacher_order['member1'], $lessonmember, 3, $setting, $teacher_order, $teacher_order['commission1'], $level=1, $comsetting);
                    }
                    
                    /* 二级佣金 */
                    if ($teacher_order['member2'] > 0 && $teacher_order['commission2'] > 0) {
                        $this->sendCommissionToUser($uniacid, $teacher_order['member2'], $lessonmember, 3, $setting, $teacher_order, $teacher_order['commission2'], $level=2, $comsetting);
                    }

                    /* 三级佣金 */
                    if ($teacher_order['member3'] > 0 && $teacher_order['commission3'] > 0) {
                        $this->sendCommissionToUser($uniacid, $teacher_order['member3'], $lessonmember, 3, $setting, $teacher_order, $teacher_order['commission3'], $level=3, $comsetting);
                    }

                    /* 讲师分成 */
                    if ($teacher_order['price'] > 0 && $teacher_order['teacher_income'] > 0) {
                        $this->sendCommissionToTeacher($uniacid, $teacher_order, $setting, $type=3);
                    }
                    
                    /* 购买成功模版消息通知用户 */
                    $this->sendMessageToUser($uniacid, $setting, $teacher_order, 3, $validity);
                    /* 新订单提醒(管理员) */
                    $this->sendOrderMessageToAdmin($setting, $teacher_order, 3);

                    /* 赠送积分操作 */
                    if ($teacher_order['integral'] > 0) {
                        $this->handleUserIntegral($type=3, $teacher_order['ordersn'], $teacher_order['uid'], $teacher_order['integral']);
                    }
                }
            }

            if ($params['from'] == 'return') {
                $successUrl = $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&do=myteacher&m=fy_lessonv2";
                message("购买成功", $successUrl, 'success');
            }
        }
    }

    
    /* VIP订单支付成功，更新用户VIP时长
     * $uniacid 公众号id
     * $lessonmember 微课堂会员信息
     * $order 订单信息
     * return 会员VIP有效期
     **/
    public function updateVipValidity($uniacid, $lessonmember, $order)
    {
        $memberVip = pdo_fetch("SELECT * FROM " .tablename($this->table_member_vip). " WHERE uid=:uid AND level_id=:level_id", array(':uid'=>$order['uid'],':level_id'=>$order['level_id']));
        $newLevel = pdo_fetch("SELECT discount FROM " .tablename($this->table_vip_level). " WHERE id=:id", array(':id'=>$order['level_id']));
        if (!empty($memberVip)) {
            if (time()>=$memberVip['validity']) {
                $vipData = array(
                    'validity' => time()+$order['viptime']*86400,
                    'discount'=> $newLevel['discount'],
                    'update_time' => time(),
                );
            } else {
                $vipData = array(
                    'validity' => $memberVip['validity']+$order['viptime']*86400,
                    'discount'=> $newLevel['discount'],
                    'update_time' => time(),
                );
            }
            pdo_update($this->table_member_vip, $vipData, array('id'=>$memberVip['id']));
        } else {
            $vipData = array(
                'uniacid' => $uniacid,
                'uid'	  => $order['uid'],
                'level_id'=> $order['level_id'],
                'validity'=> time()+$order['viptime']*86400,
                'discount'=> $newLevel['discount'],
                'addtime' => time(),
            );
            pdo_insert($this->table_member_vip, $vipData);
        }

        return $vipData['validity'];
    }

    /* 订单金额加入今日销售额汇总表
     * $uniacid 公众号id
     * $type 订单类型 1.VIP订单 2.课程订单 3.购买讲师订单
     * $orderAmount 订单金额
     */
    public function staticAmount($uniacid, $type, $orderAmount)
    {
        $today= strtotime("today");
        $exit = pdo_fetch("SELECT * FROM " .tablename($this->table_static). " WHERE uniacid=:uniacid AND static_time=:static_time", array(':uniacid'=>$uniacid,':static_time'=>$today));
        if (empty($exit)) {
            if ($type==1) {
                $staticData = array(
                    'uniacid' 		  => $uniacid,
                    'vipOrder_num'    => 1,
                    'vipOrder_amount' => $orderAmount,
                    'static_time'     => $today
                );
            } elseif ($type==2) {
                $staticData = array(
                    'uniacid' 		     => $uniacid,
                    'lessonOrder_num'    => 1,
                    'lessonOrder_amount' => $orderAmount,
                    'static_time'        => $today
                );
            } elseif ($type==3) {
                $staticData = array(
                    'uniacid' 		     => $uniacid,
                    'teacherOrder_num'   => 1,
                    'teacherOrder_amount'=> $orderAmount,
                    'static_time'        => $today
                );
            }
            pdo_insert($this->table_static, $staticData);
        } else {
            if ($type==1) {
                $staticData = array(
                    'vipOrder_num +='    => 1,
                    'vipOrder_amount +=' => $orderAmount,
                );
            } elseif ($type==2) {
                $staticData = array(
                    'lessonOrder_num +='    => 1,
                    'lessonOrder_amount +=' => $orderAmount,
                );
            } elseif ($type==3) {
                $staticData = array(
                    'teacherOrder_num +='   => 1,
                    'teacherOrder_amount +='=> $orderAmount,
                );
            }
            pdo_update($this->table_static, $staticData, array('uniacid'=>$uniacid,'static_time'=>$today));
        }
    }

    /**
     * 检查分销商状态变化
     * $member 分销商会员信息
     * $comsetting 分销设置参数
     * $price 订单价格
     */
    public function checkAgentStatus($member, $comsetting, $price)
    {
        $orderAmount = $member['payment_amount'] ? $member['payment_amount']+$price : $this->getMemberOrderAmount($member['uid']);
        $orderTotal = $member['payment_order'] ? $member['payment_order']+1 : $this->getMemberOrderNumber($member['uid']);

        $memberinfo = array(
            'payment_amount' => $orderAmount,
            'payment_order'  => $orderTotal
        );

        /* 分销商状态变更 */
        $agent_condition = unserialize($comsetting['agent_condition']);
        if ($member['status']==0) {
            if ($orderAmount >= $agent_condition['order_amount'] && $orderTotal >= $agent_condition['order_total']) {
                $memberinfo['status'] = 1;
            }
        }

        /* 分销商等级变更 2. 购买订单总额 3.购买订单笔数 */
        if ($comsetting['upgrade_condition']==2) {
            $upgradeLevel = $this->upgradeAgentLevel($member['uniacid'], $member['agent_level'], $orderAmount, $comsetting);
            if (!empty($upgradeLevel)) {
                $memberinfo['agent_level'] = $upgradeLevel;
            }
        } elseif ($comsetting['upgrade_condition']==3) {
            $upgradeLevel = $this->upgradeAgentLevel($member['uniacid'], $member['agent_level'], $orderTotal, $comsetting);
            if (!empty($upgradeLevel)) {
                $memberinfo['agent_level'] = $upgradeLevel;
            }
        }

        pdo_update($this->table_member, $memberinfo, array('uid'=>$member['uid']));
    }

    /* 获取用户购买订单总额 */
    public function getMemberOrderAmount($uid)
    {
        $lessonAmount = pdo_fetchall("SELECT SUM(price) as amount FROM " .tablename($this->table_order). " WHERE uid=:uid AND status>=:status", array(':uid'=>$uid, ':status'=>1));
        $vipAmount = pdo_fetchall("SELECT SUM(vipmoney) as amount FROM " .tablename($this->table_member_order). " WHERE uid=:uid AND status=:status", array(':uid'=>$uid, ':status'=>1));
            
        $orderAmount = $lessonAmount[0]['amount'] + $vipAmount[0]['amount'];
        return $orderAmount;
    }

    /* 获取用户购买订单笔数 */
    public function getMemberOrderNumber($uid)
    {
        $lessonTotal = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_order). " WHERE uid=:uid AND status>=:status ", array(':uid'=>$uid, ':status'=>1));
        $vipTotal = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_member_order). " WHERE uid=:uid AND status=:status", array(':uid'=>$uid, ':status'=>1));

        $orderTotal = $lessonTotal + $vipTotal;
        return $orderTotal;
    }

    /* 检查分销商是否升级 */
    public function upgradeAgentLevel($uniacid, $agentLevel, $total_commission, $comsetting)
    {
        $levellist = pdo_fetchall("SELECT * FROM " . tablename($this->table_commission_level) . " WHERE uniacid=:uniacid ORDER BY id ASC", array(':uniacid'=>$uniacid));
        if (!empty($levellist)) {
            /* 分销商升级处理开始 */
            if ($agentLevel == 0) {
                $commission = unserialize($comsetting['commission']);
                if ($commission['updatemoney'] > 0 && $total_commission >= $commission['updatemoney']) {
                    foreach ($levellist as $key => $value) {
                        if ($value['updatemoney'] > 0 && $total_commission >= $value['updatemoney']) {
                            $upgradeLevel = intval($levellist[$key + 1]['id']);
                        } else {
                            break;
                        }
                    }
                    if (empty($upgradeLevel)) {
                        $upgradeLevel = $levellist[0]['id'];
                    }
                }
            } else {
                $level = pdo_fetch("SELECT * FROM " . tablename($this->table_commission_level) . " WHERE id=:id", array(':id'=>$agentLevel));
                if ($level['updatemoney'] > 0 && $total_commission >= $level['updatemoney']) {
                    foreach ($levellist as $key => $value) {
                        if ($value['id'] == $level['id']) {
                            $levelkey = $key;
                        }
                        if ($value['updatemoney'] > 0 && $total_commission >= $value['updatemoney']) {
                            $upgradeLevel = intval($levellist[$key + 1]['id']);
                        } else {
                            break;
                        }
                    }
                    if ($upgradeLevel == $level['id']) {
                        $upgradeLevel = $levellist[$levelkey + 1]['id'];
                    }
                }
            }

            return $upgradeLevel;
            /* 分销商升级处理结束 */
        }
    }

    /* 发放佣金操作
     * $uniacid 公众号id
     * $uid 用户id
     * $lessonmember 用户信息
     * $type 1.vip订单 2.课程订单 3.购买讲师订单
     * $setting 全局配置信息
     * $order 订单信息
     * $commission 佣金
     * $level 佣金级别 1.一级佣金 2.二级佣金 3.三级佣金
     * $comsetting 分销设置
     */
    public function sendCommissionToUser($uniacid, $uid, $lessonmember, $type, $setting, $order, $commission, $level, $comsetting)
    {
        global $_W;

        $tplmessage = pdo_fetch("SELECT cnotice,cnotice_format FROM " .tablename($this->table_tplmessage). " WHERE uniacid=:uniacid", array(':uniacid'=>$uniacid));
        $cnotice_format = json_decode($tplmessage['cnotice_format'], true);

        if ($type==1) {
            $first = $cnotice_format['vip_first'] ? $cnotice_format['vip_first'] : "您获得了一笔新的VIP分销佣金。";
            $orderContent = "[{$order['level_name']}]服务-{$order['viptime']}天";
            $price = $order['vipmoney'];
            $remark = $cnotice_format['vip_remark'] ? $cnotice_format['vip_remark'] : "点击详情即可查看佣金明细。";
        } elseif ($type==2) {
            $first = $cnotice_format['lesson_first'] ? $cnotice_format['lesson_first'] : "您获得了一笔新的课程分销佣金。";
            $orderContent = "课程：《{$order['bookname']}》";
            $price = $order['price'];
            $remark = $cnotice_format['lesson_remark'] ? $cnotice_format['lesson_remark'] : "点击详情即可查看佣金明细。";
        } elseif ($type==3) {
            $first = $cnotice_format['buyteacher_first'] ? $cnotice_format['buyteacher_first'] : "您获得了一笔新的分销佣金。";
            $orderContent = "[{$order['teacher_name']}]讲师服务-{$order['ordertime']}天";
            $price = $order['price'];
            $remark = $cnotice_format['buyteacher_remark'] ? $cnotice_format['buyteacher_remark'] : "点击详情即可查看佣金明细。";
        }
        
        $member = pdo_fetch("SELECT openid FROM " .tablename($this->table_fans). " WHERE uid=:uid", array(':uid'=>$uid));
        $customer =  pdo_fetch("SELECT nickname FROM " .tablename($this->table_mc_members). " WHERE uid=:uid", array(':uid'=>$order['uid']));

        $senddata = array(
            'openid' 	  => $member['openid'],
            'cnotice' 	  => $tplmessage['cnotice'],
            'url' 		  => $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&op=commissionlog&do=commission&m=fy_lessonv2",
            'first' 	  => $first,
            'keyword1' 	  => "{$commission}元",
            'keyword2' 	  => "{$price}元",
            'keyword3' 	  => date('Y年m月d日 H:i', time()),
            'remark' 	  => "下级成员：{$customer['nickname']}\n消费内容：{$orderContent}\n".$remark,
        );
        if ($comsetting['sale_rank'] == 2) {/* VIP身份才可获得佣金 */
            $memberVip = pdo_fetchall("SELECT * FROM " .tablename($this->table_member_vip). " WHERE uid=:uid AND validity>:validity", array(':uid'=>$uid,':validity'=>time()));
            if (!empty($memberVip)) {
                $this->changecommisson($order, "{$orderContent}分销订单", $uid, $commission, $level, $level."级佣金:订单号" . $order['ordersn'], $customer['nickname'], $senddata, $comsetting);
            } else {
                if ($level==1) {
                    pdo_update($this->table_member_order, array('commission1' => 0), array('id' => $order['id']));
                } elseif ($level==2) {
                    pdo_update($this->table_member_order, array('commission2' => 0), array('id' => $order['id']));
                } elseif ($level==3) {
                    pdo_update($this->table_member_order, array('commission3' => 0), array('id' => $order['id']));
                }
            }
        } else {
            $this->changecommisson($order, "{$orderContent}分销订单", $uid, $commission, $level, $level."级佣金:订单号" . $order['ordersn'], $customer['nickname'], $senddata, $comsetting);
        }
    }

    /**
     * 更新用户佣金和添加日志
     * $order 订单信息
     * $uid 获得佣金会员ID
     * $change_num 变动数目
     * $grade 佣金等级 1.一级佣金 2.二级佣金 3.三级佣金
     * $remark 变动备注说明
     * $customer_name 购买者昵称
     * $senddata 发送模版消息内容
     * $comsetting 分销设置
     */
    public function changecommisson($order, $bookname, $uid, $change_num, $grade, $remark, $customer_name, $senddata, $comsetting)
    {
        global $_W;

        $agentMember = pdo_fetch("SELECT * FROM " .tablename($this->table_member). " WHERE uid=:uid", array(':uid'=>$uid));
        $uniacid = $agentMember['uniacid'];
        
        if ($agentMember['status'] == 1) {
            $memupdate = array();

            /* 查询该分销代理商是否升级 */
            if ($comsetting['upgrade_condition']==1) {//分销累计佣金
                $total_commission = $agentMember['pay_commission'] + $agentMember['nopay_commission'] + $change_num;
                $upgradeLevel = $this->upgradeAgentLevel($uniacid, $agentMember['agent_level'], $total_commission, $comsetting);
                if (!empty($upgradeLevel)) {
                    $memupdate['agent_level'] = $upgradeLevel;
                }
            }

            $memupdate['nopay_commission'] = $agentMember['nopay_commission'] + $change_num;
            pdo_update($this->table_member, $memupdate, array('uid' => $agentMember['uid']));

            $member = pdo_fetch("SELECT nickname FROM " . tablename($this->table_mc_members) . " WHERE uid=:uid", array(':uid'=>$uid));
            $logarr = array(
                'uniacid'		=> $uniacid,
                'orderid'		=> $order['id'],
                'uid'			=> $uid,
                'nickname'		=> $member['nickname'],
                'bookname'		=> $bookname,
                'order_amount'	=> $order['vipmoney'] ? $order['vipmoney'] : $order['price'],
                'change_num'	=> $change_num,
                'grade'			=> $grade,
                'remark'		=> $remark,
                'buyer_uid'		=> $order['uid'],
                'buyer_name'	=> $customer_name,
                'addtime'		=> time(),
            );
            pdo_insert($this->table_commission_log, $logarr);
            $this->commissionMessage($senddata, $uniacid); /* 发放佣金模版消息通知 */
        } else {
            if ($grade == 1) {
                $updatearr['commission1'] = 0;
            } elseif ($grade == 2) {
                $updatearr['commission2'] = 0;
            } elseif ($grade == 3) {
                $updatearr['commission3'] = 0;
            }
            pdo_update($this->table_order, $updatearr, array('id' => $order['id']));
        }
    }

    /* 获得佣金模通知 */
    public function commissionMessage($data, $uniacid)
    {
        $message = array(
            'touser' => $data['openid'],
            'template_id' => $data['cnotice'],
            'url' => $data['url'],
            'topcolor' => "",
            'data' => array(
                'first' => array(
                    'value' => $data['first'],
                    'color' => "",
                ),
                'keyword1' => array(
                    'value' => $data['keyword1'],
                    'color' => "",
                ),
                'keyword2' => array(
                    'value' => $data['keyword2'],
                    'color' => "",
                ),
                'keyword3' => array(
                    'value' => $data['keyword3'],
                    'color' => "",
                ),
                'remark' => array(
                    'value' => $data['remark'],
                    'color' => "",
                ),
            )
        );
        $this->send_template_message($message, $uniacid);
    }

    /* 发送模版消息 */
    public function send_template_message($messageDatas, $uniacid='')
    {
        global $_W, $_GPC;
        
        if (!$messageDatas['touser'] || !$messageDatas['template_id']) {
            return;
        }
        if ($uniacid) {
            $account = uni_fetch($uniacid);
        }

        load()->classs('weixin.account');
        $account_api = WeixinAccount::create($account);
        $access_token = $account_api->getAccessToken();

        $urls = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
        $messageDatas = urldecode(json_encode($messageDatas));
        $ress = $this->http_request($urls, $messageDatas);

        return json_decode($ress, true);
    }

    /* https请求（支持GET和POST） */
    public function http_request($url, $messageDatas = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($messageDatas)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $messageDatas);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /*
     * 支付订单成功通知用户
     * $uniacid 公众号id
     * $setting 全局配置参数
     * $order 订单信息
     * $type 订单类型 1.VIP订单 2.课程订单 3.购买讲师订单
     * $validity VIP有效期
     */
    public function sendMessageToUser($uniacid, $setting, $order, $type, $validity)
    {
        global $_W;

        $tplmessage = pdo_fetch("SELECT buysucc,buysucc_format FROM " .tablename($this->table_tplmessage). " WHERE uniacid=:uniacid", array(':uniacid'=>$uniacid));
        $buysucc_format = json_decode($tplmessage['buysucc_format'], true);
        
        if ($order['integral']) {
            $integral = "赠送积分：{$order['integral']} 积分";
        }
        if ($type==1) {
            $url = $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&do=vip&m=fy_lessonv2";
            $first = $buysucc_format['first1'] ? $buysucc_format['first1'] : "您已购买成功!";
            $orderContent = "购买[{$order['level_name']}]服务-{$order['viptime']}天";
            $price = $order['vipmoney'];
            $remark = "有效期至：" . date('Y年m月d日', $validity)."\n".$integral;

            if (!empty($buysucc_format['remark1'])) {
                $remark .= "\n".$buysucc_format['remark1'];
            }
        } elseif ($type==2) {
            $url = $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&do=self2&m=fy_lessonv2";
            $first = $buysucc_format['first2'] ? $buysucc_format['first2'] : "您已购买成功!";
            $orderContent = "《{$order['bookname']}》";
            $price = $order['price'];
            $remark = $integral;

            if (!empty($buysucc_format['remark2'])) {
                $remark .= "\n".$buysucc_format['remark2'].$integral;
            }
        } elseif ($type==3) {
            $url = $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&do=myteacher&m=fy_lessonv2";
            $first = $buysucc_format['first3'] ? $buysucc_format['first3'] : "您已购买成功!";
            $orderContent = "购买[{$order['teacher_name']}]讲师服务-{$order['ordertime']}天";
            $price = $order['price'];
            $remark = "有效期至：" . date('Y年m月d日', $validity)."\n".$integral;
            
            if (!empty($buysucc_format['remark1'])) {
                $remark .= "\n".$buysucc_format['remark3'];
            }
        }
        
        $fans = pdo_get($this->table_fans, array('uid'=>$order['uid']), array('openid'));
        $sendmessage = array(
            'touser' => $fans['openid'],
            'template_id' => $tplmessage['buysucc'],
            'url' => $url,
            'topcolor' => "",
            'data' => array(
                'first' => array(
                    'value' => $first,
                    'color' => "",
                ),
                'keyword1' => array(
                    'value' => $orderContent,
                    'color' => "",
                ),
                'keyword2' => array(
                    'value' => $price.' 元',
                    'color' => "",
                ),
                'keyword3' => array(
                    'value' => date('Y年m月d日', time()),
                    'color' => "",
                ),
                'remark' => array(
                    'value' => $remark,
                    'color' => "",
                ),
            )
        );
        $this->send_template_message($sendmessage, $uniacid);
    }

    /*
    * 新订单提醒管理员
    * $setting 全局配置参数
    * $order 订单信息
    * $type 订单类型 1.VIP订单 2.课程订单 3.购买讲师订单
    */
    public function sendOrderMessageToAdmin($setting, $order, $type)
    {
        $tplmessage = pdo_fetch("SELECT neworder, neworder_format FROM " .tablename($this->table_tplmessage). " WHERE uniacid=:uniacid", array(':uniacid'=>$setting['uniacid']));
        $neworder_format = json_decode($tplmessage['neworder_format'], true);

        if ($type==1) {
            $first = $neworder_format['vip_first'] ? $neworder_format['vip_first'] : "您有一条新的VIP订单消息";
            $orderContent = "购买[{$order['level_name']}]服务-{$order['viptime']}天\n";
            $amount = $order['vipmoney'];
            $remark = $neworder_format['vip_remark'] ? $neworder_format['vip_remark'] : "详情请登录网站后台查看！";
        } elseif ($type==2) {
            $first = $neworder_format['lesson_first'] ? $neworder_format['lesson_first'] : "您有一条新的课程订单消息";
            $orderContent = "课程：《{$order['bookname']}》\n";
            $amount = $order['price'];
            $paytype = $order['coupon_amount'] ? "(优惠券已抵扣".$order['coupon_amount']."元)" : "";
            $remark = $neworder_format['lesson_remark'] ? $neworder_format['lesson_remark'] : "详情请登录网站后台查看！";
        } elseif ($type==3) {
            $first = $neworder_format['buyteacher_first'] ? $neworder_format['buyteacher_first'] : "您有一条新的购买讲师订单消息";
            $orderContent = "购买[{$order['teacher_name']}]讲师服务-{$order['ordertime']}天\n";
            $amount = $order['price'];
            $remark = $neworder_format['buyteacher_remark'] ? $neworder_format['buyteacher_remark'] : "详情请登录网站后台查看！";
        }
        
        $manage = explode(",", $setting['manageopenid']);
        foreach ($manage as $manageopenid) {
            $sendneworder = array(
                'touser' => $manageopenid,
                'template_id' => $tplmessage['neworder'],
                'url' => "",
                'topcolor' => "#7B68EE",
                'data' => array(
                    'first' => array(
                        'value' => $first,
                        'color' => "",
                    ),
                    'keyword1' => array(
                        'value' => $order['ordersn'],
                        'color' => "",
                    ),
                    'keyword2' => array(
                        'value' => "{$amount} 元{$paytype}",
                        'color' => "",
                    ),
                    'keyword3' => array(
                        'value' => date('Y年m月d日 H:i', $order['addtime']),
                        'color' => "",
                    ),
                    'remark' => array(
                        'value' => $orderContent.$remark,
                        'color' => "",
                    ),
                )
            );
            $this->send_template_message($sendneworder, $order['uniacid']);
        }
    }

    /**
     * 更新用户vip字段
     */
    public function updateMemberVip($uid, $vip)
    {
        return pdo_update($this->table_member, array('vip'=>$vip), array('uid'=>$uid));
    }

    /* 用户积分操作
    * $type 订单类型 1.VIP订单 2.课程订单 3.购买讲师订单
    * $ordersn 订单编号
    * $uid 用户id（需要操作积分的用户）
    * $integral 操作积分数额   +.增加  -.减少
    */
    public function handleUserIntegral($type, $ordersn, $uid, $integral)
    {
        $modules = pdo_get('modules', array('name'=>'fy_lessonv2'), array('title'));
        $modules_title = $modules['title'] ? $modules['title'] : '微课堂';
        if ($type==1) {
            $typeName = $modules_title.'VIP订单';
        } elseif ($type==2) {
            $typeName = $modules_title.'课程订单';
        } elseif ($type==3) {
            $typeName = $modules_title.'购买讲师订单';
        }

        load()->model('mc');
        $log = array(
            '0' => "", /* 操作管理员uid */
            '1' => $typeName."：{$ordersn}", /* 增减积分备注 */
            '2' => 'fy_lessonv2', /* 模块标识 */
            '3' => '', /* 店员uid */
            '4' => '', /* 门店id */
            '5' => '', /* 1(线上操作) 2(系统后台,公众号管理员和操作员) 3(店员) */
        );
        mc_credit_update($uid, 'credit1', $integral, $log);
    }

    /* 增加课程购买人数
     * $lessonid 课程id
     */
    public function updateLessonNumber($lessonid)
    {
        pdo_update($this->table_lesson_parent, array('buynum +='=>1), array('id'=>$lessonid));
    }

    /* 讲师课程佣金处理
     * $uniacid 公众号id
     * $lessonid 课程id
     * $order 订单信息
     * $type  订单类型  2.课程订单 3.购买讲师订单
     */
    public function sendCommissionToTeacher($uniacid, $order, $setting, $type)
    {
        global $_W;
        $teacher = pdo_fetch("SELECT a.uid,a.teacher,b.openid FROM " .tablename($this->table_teacher). " a LEFT JOIN ".tablename($this->table_fans)." b ON a.uid=b.uid WHERE a.id=:id", array(':id'=>$order['teacherid']));

        if ($teacher['uid']>0) {
            $teachermember = pdo_fetch("SELECT id,uid,nopay_lesson FROM " . tablename($this->table_member) . " WHERE uid=:uid", array(':uid'=>$teacher['uid']));
            $nopay_lesson = round($order['price'] * $order['teacher_income'] * 0.01, 2);

            if ($nopay_lesson<=0) {
                return;
            }
            
            pdo_update($this->table_member, array('nopay_lesson' => $teachermember['nopay_lesson'] + $nopay_lesson), array('uid' => $teacher['uid']));

            $tplmessage = pdo_get($this->table_tplmessage, array('uniacid'=>$uniacid), array('cnotice','cnotice_format'));
            $cnotice_format = json_decode($tplmessage['cnotice_format'], true);
            if ($type==2) {
                $bookname = $order['bookname'];
                $tpl_first = $cnotice_format['teacher_first'] ? $cnotice_format['teacher_first'] : "您的课程《{$order['bookname']}》成功出售，您获得了一笔新的课程佣金。";
                $tpl_remark = $cnotice_format['teacher_remark'] ? $cnotice_format['teacher_remark'] : "详情请进入讲师中心查看课程收入。";
            } elseif ($type==3) {
                $bookname = "出售讲师服务-{$order['ordertime']}天";
                $tpl_first = $cnotice_format['buyteacher_first'] ? $cnotice_format['buyteacher_first'] : "您的讲师服务成功出售，您获得了一笔新的佣金。";
                $tpl_remark = $cnotice_format['buyteacher_remark'] ? $cnotice_format['buyteacher_remark'] : "详情请进入讲师中心查看。";
            }
            
            $incomedata = array(
                'uniacid' 		 => $uniacid,
                'uid' 			 => $teacher['uid'],
                'teacher' 		 => $teacher['teacher'],
                'ordersn' 		 => $order['ordersn'],
                'ordertype'		 => $type,
                'bookname' 		 => $bookname,
                'orderprice' 	 => $order['price'],
                'teacher_income' => $order['teacher_income'],
                'income_amount'  => $nopay_lesson,
                'addtime' 		 => time(),
            );
            pdo_insert($this->table_teacher_income, $incomedata);

            $sendteacher = array(
                'openid' 	  => $teacher['openid'],
                'cnotice' 	  => $tplmessage['cnotice'],
                'url' 		  => $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&do=income&m=fy_lessonv2",
                'first' 	  => $tpl_first,
                'keyword1' 	  => $nopay_lesson . "元",
                'keyword2' 	  => $order['price'] . "元",
                'keyword3' 	  => date("Y年m月d日 H:i", time()),
                'remark' 	  => $tpl_remark,
            );
            $this->commissionMessage($sendteacher, $uniacid);
        }
    }

    /* 机构课程佣金处理
     * $uniacid 公众号id
     * $lessonid 课程id
     * $order 订单信息
     */
    public function sendCommissionToCompany($uniacid, $order, $setting)
    {
        global $_W;

        $fans = pdo_get($this->table_fans, array('uid'=>$order['company_uid']), array('openid','nickname'));

        $member = pdo_fetch("SELECT id,uid,nopay_commission FROM " . tablename($this->table_member) . " WHERE uid=:uid", array(':uid'=>$order['company_uid']));
        $nopay_commission = round($order['price'] * $order['company_income'] * 0.01, 2);

        pdo_update($this->table_member, array('nopay_commission' => $member['nopay_commission'] + $nopay_commission), array('uid' => $order['company_uid']));

        $incomedata = array(
            'uniacid'	 => $uniacid,
            'orderid'	 => $order['id'],
            'uid'		 => $order['company_uid'],
            'nickname'	 => $order['ordersn'],
            'bookname'	 => $order['bookname'],
            'change_num' => $nopay_commission,
            'grade'		 => 0,
            'remark'	 => "下级讲师课程《{$order['bookname']}》成功出售，订单编号：".$order['ordersn'],
            'company_income' => 1,
            'addtime'	 => time(),
        );
        pdo_insert($this->table_commission_log, $incomedata);

        $tplmessage = pdo_get($this->table_tplmessage, array('uniacid'=>$uniacid), array('cnotice'));

        $sendteacher = array(
            'openid' 	  => $fans['openid'],
            'cnotice' 	  => $tplmessage['cnotice'],
            'url' 		  => $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&op=commissionlog&do=commission&m=fy_lessonv2",
            'first' 	  => "您的下级讲师课程《{$order['bookname']}》成功出售，您获得了一笔新的佣金。",
            'keyword1' 	  => $nopay_commission . "元",
            'keyword2' 	  => $order['price'] . "元",
            'keyword3' 	  => date("Y-m-d H:i", time()),
            'remark' 	  => "详情请进入佣金明细查看。",
        );
        $this->commissionMessage($sendteacher, $uniacid);
    }

    /* 用户购买课程赠送优惠券 */
    public function sendCouponByBuyLesson($member, $setting)
    {
        global $_W;
        $uniacid = $setting['uniacid'];

        $market = pdo_fetch("SELECT * FROM " .tablename($this->table_market). " WHERE uniacid=:uniacid", array(':uniacid'=>$uniacid));
        $buyLesson = json_decode($market['buy_lesson'], true);
        $url = $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&do=coupon&m=fy_lessonv2";

        if (!empty($buyLesson)) {
            if ($market['buy_lesson_time']>0) {
                $buyTotal = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename($this->table_member_coupon). " WHERE uid=:uid AND source=:source", array(':uid'=>$member['uid'], 'source'=>2));
                if ($buyTotal >= $market['buy_lesson_time']) {
                    return;
                }
            }

            $t = $coupon_amount = 0;
            foreach ($buyLesson as $item) {
                $coupon = pdo_fetch("SELECT * FROM " .tablename($this->table_mcoupon). " WHERE id=:id", array(':id'=>$item));
                if (empty($coupon)) {
                    continue;
                }
                $lessonCoupon = array(
                    'uniacid'	  => $uniacid,
                    'uid'		  => $member['uid'],
                    'amount'      => $coupon['amount'],
                    'conditions'  => $coupon['conditions'],
                    'validity'	  => $coupon['validity_type']==1 ? $coupon['days1'] : time()+ $coupon['days2']*86400,
                    'category_id' => $coupon['category_id'],
                    'status'	  => 0, /* 未使用 */
                    'source'	  => 2, /* 购买课程赠送 */
                    'coupon_id'	  => $coupon['id'],
                    'addtime'	  => time(),
                );
                if (pdo_insert($this->table_member_coupon, $lessonCoupon)) {
                    $t++;
                    $coupon_amount += $coupon['amount'];
                }
            }

            if ($t) {
                $fans = pdo_fetch("SELECT openid,nickname FROM " .tablename($this->table_fans). " WHERE uid=:uid", array(':uid'=>$member['uid']));
                $tplmessage = pdo_fetch("SELECT receive_coupon, receive_coupon_format FROM " .tablename($this->table_tplmessage). " WHERE uniacid=:uniacid", array(':uniacid'=>$uniacid));
                $receive_coupon_format = json_decode($tplmessage['receive_coupon_format'], true);

                if ($tplmessage['receive_coupon']) {
                    $sendmessage = array(
                        'touser' => $fans['openid'],
                        'template_id' => $tplmessage['receive_coupon'],
                        'url' => $url,
                        'topcolor' => "#7B68EE",
                        'data' => array(
                            'first' => array(
                                'value' => "恭喜您购买成功，系统赠您{$t}张优惠券已发放到您的帐号，请注意查收。",
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
                                'value' => $receive_coupon_format['remark'] ? $receive_coupon_format['remark'] : "点击详情可查看您的帐号优惠券详情哦~",
                                'color' => "",
                            ),
                        )
                    );
                    $this->send_template_message($sendmessage, $uniacid);
                } else {
                    $custom = array(
                        'msgtype' => 'text',
                        'text' => array(
                            'content' => urlencode('恭喜您购买成功，系统赠您'.$t.'张优惠券已发放到您的帐号，请注意查收。\n\n账户名：'.$fans['nickname'].'\n数量：'.$t.'张\n时间：'.date('Y年m月d日', time()).'\n\n'.$receive_coupon_format['remark'].'<a href=\"'.$url.'\">点击此处可查看详情</a>')
                        ),
                        'touser' => $fans['openid'],
                    );
                    $account_api = WeAccount::create();
                    $account_api->sendCustomNotice($custom);
                }
            }
        }
    }

    /* 购买讲师订单支付成功，更新用户时长
     * $uniacid 公众号id
     * $lessonmember 微课堂会员信息
     * $order 订单信息
     * return 购买讲师有效期
     **/
    public function updateTeacherValidity($uniacid, $lessonmember, $order)
    {
        $buyTeacher = pdo_get($this->table_member_buyteacher, array('uid'=>$order['uid'],'teacherid'=>$order['teacherid']));
        if (!empty($buyTeacher)) {
            if (time()>=$buyTeacher['validity']) {
                $data = array(
                    'validity'	  => time() + $order['ordertime'] * 86400,
                    'update_time' => time(),
                );
            } else {
                $data = array(
                    'validity'	  => $buyTeacher['validity'] + $order['ordertime'] * 86400,
                    'update_time' => time(),
                );
            }
            pdo_update($this->table_member_buyteacher, $data, array('id'=>$buyTeacher['id']));
        } else {
            $data = array(
                'uniacid'	=> $uniacid,
                'uid'		=> $order['uid'],
                'teacherid' => $order['teacherid'],
                'validity'	=> time() + $order['ordertime'] * 86400,
                'addtime'	=> time(),
                'update_time' => time(),
            );
            pdo_insert($this->table_member_buyteacher, $data);
        }

        return $data['validity'];
    }
}
