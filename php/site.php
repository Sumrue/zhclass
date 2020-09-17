<?php
defined('IN_IA') or exit('Access Denied');

include_once dirname(__FILE__).'/inc/common/AliyunVod.php';
include_once dirname(__FILE__).'/inc/common/Cutpage.php';
include_once dirname(__FILE__).'/inc/common/PHPExcel.php';
include_once dirname(__FILE__).'/inc/common/QcloudVod.php';
include_once dirname(__FILE__).'/inc/common/QcloudCos.php';
include_once dirname(__FILE__).'/library/aodianyun/IM/tis.php';
include_once dirname(__FILE__).'/inc/core/SiteCommon.php';
include_once dirname(__FILE__).'/inc/core/TypeStatus.php';

class fy_lessonv2ModuleSite extends WeModuleSite
{
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

    public $table_live_chatroom		 = 'fy_lesson_plugin_live_chatroom';
    public $table_live_stream		 = 'fy_lesson_plugin_live_stream';

    /***************************** 初始化 ******************************** */
    public function __construct()
    {
        global $_W;
        if (!$_W['setting']['copyright']['develop_status']) {
            error_reporting(E_ERROR);
            ini_set("display_errors", "Off");
        }
    }

    public function getMenus()
    {
        global $_W;

        $menus = array(
            array(
                'title' => '学校管理',
                'url'	=> $this->createWebUrl('school'),
                'icon'  => 'fa fa-university',
            ),
            array(
                'title' => '视频管理',
                'url'	=> $this->createWebUrl('video'),
                'icon'  => 'fa fa-play-circle',
            ),
            array(
                'title' => '课程管理',
                'url'	=> $this->createWebUrl('lesson'),
                'icon'  => 'fa fa-mortar-board',
            ),
            array(
                'title' => '课程分类',
                'url'	=> $this->createWebUrl('category'),
                'icon'  => 'fa fa-list',
            ),
            array(
                'title' => '推荐板块',
                'url'	=> $this->createWebUrl('recommend'),
                'icon'  => 'fa fa-heart',
            ),
            array(
                'title' => '讲师管理',
                'url'	=> $this->createWebUrl('teacher'),
                'icon'  => 'fa fa-user-md',
            ),
            array(
                'title' => '营销管理',
                'url'	=> $this->createWebUrl('market'),
                'icon'  => 'fa fa-gift',
            ),
            array(
                'title' => '课程订单',
                'url'	=> $this->createWebUrl('order'),
                'icon'  => 'fa fa-list-ol',
            ),
            array(
                'title' => 'VIP服务',
                'url'	=> $this->createWebUrl('viporder', array('status'=>1)),
                'icon'  => 'fa fa-diamond',
            ),
            array(
                'title' => '评价管理',
                'url'	=> $this->createWebUrl('comment'),
                'icon'  => 'fa fa-comment-o',
            ),
            array(
                'title' => '分销管理',
                'url'	=> $this->createWebUrl('agent'),
                'icon'  => 'wi wi-user-group',
            ),
            array(
                'title' => '财务管理',
                'url'	=> $this->createWebUrl('finance'),
                'icon'  => 'fa fa-money',
            ),
            array(
                'title' => '文章公告',
                'url'	=> $this->createWebUrl('article'),
                'icon'  => 'fa fa-volume-up',
            ),
            array(
                'title' => '基本设置',
                'url'	=> $this->createWebUrl('setting'),
                'icon'  => 'fa fa-cog',
            ),
        );

        $module = pdo_get('modules', array('name'=>'fy_lessonv2'), array('webapp_support'));
        if ($module['webapp_support']==2) {
            $menus[] = array(
                'title' => 'PC端设置',
                'url'	=> $this->createWebUrl('pcmanage'),
                'icon'  => 'fa fa-laptop',
            );
        }

        $menus[] = array(
            'title' => '清空缓存',
            'url'	=> $this->createWebUrl('clearcache'),
            'icon'  => 'fa fa-refresh',
        );

        $menus[] = array(
            'title' => '日志管理',
            'url'	=> $this->createWebUrl('syslog'),
            'icon'  => 'fa fa-floppy-o',
        );

        return $menus;
    }

    /*****************************  WEB方法  ******************************** */
    
    public function doWebCategory()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebRecommend()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebLesson()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebTeacher()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebOrder()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebViporder()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebComment()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebSetting()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebAgent()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebCommission()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebteam()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebComsetting()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebPoster()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebArticle()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebSyslog()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebFinance()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebRefund()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebMarket()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebTeacherclass()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebGetlessonOrMember()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebVideo()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebAliyunvod()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebAliyunoss()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebQcloudvod()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebPcManage()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebClearcache()
    {
        $this->__web(__FUNCTION__);
    }
    //学校管理
    public function doWebSchool()
    {
        $this->__web(__FUNCTION__);
    }
    public function doWebTest()
    {
    }
    /***************************** Mobile方法 *********************************/

    public function doMobileAddtoorder()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileAjaxuploadimage()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileAodianyunim()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileApplyteacher()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileArticle()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileArticlepage()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileCollect()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileCommission()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileCompany()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileConfirm()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileCoupon()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileCredit()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileCrontab()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileDiscount()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileDownloadfile()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileError()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileEvaluate()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileFollow()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileGetcoupon()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileGetevaluate()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileGetliveinfo()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileHistory()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileIncome()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileIndex()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileLesson()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileLessoncash()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileLessoncashlog()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileLessonqrcode()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileLivenotify()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileMylesson()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileMyteacher()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileNotice()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileOrderdetail()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobilePay()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobilePclogin()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileQrcode()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileQrcoderec()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileReclesson()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileRecommend()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileRecord()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileSearch()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileSectionStudyStatus()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileSelf()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileSendcode()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileSharecoupon()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileSignin()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileStartadv()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileStudyDuration()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileSubscribeMsg()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileTeacher()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileTeachercenter()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileTeacherlist()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileteam()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileUpdatecollect()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileVerify()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileVerifyorder()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileVip()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileViplesson()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileWritemsg()
    {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileTeacherclass()
    {
        $this->__mobile(__FUNCTION__);
    }
    // 新添加页面php开始
    public function doMobileMajorintroduce() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileApplyform() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileSelectplan() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileInvitegift() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileStrategy() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileSchooldetail() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileCitydetail() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileAdultactivity1() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileAdultactivity2() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileAdultuserform() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileOpenform() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileConfirmform() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileConfirm2() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileSelf2() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileShoppingmall() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileMygoods() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileAddress() {
        $this->__mobile(__FUNCTION__);
    }
    // 新添加结束
    /************************************************ 公共方法 ************************************ */
    public function __web($f_name)
    {
        global $_W, $_GPC;
        $versions = "3.5.4";
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $setting = $this->readCache(1);    /* 基本设置 */
        $comsetting = $this->readCache(2); /* 分销设置 */
        $setting_pc = $this->readCache(3); /* PC基本设置 */
        $common_member_fields = $this->disposeMemberFields();
        $common = json_decode($setting['common'], true);
        $template = $setting['template'] ? $setting['template'] : 'default';
        $site_common = new SiteCommon();

        include_once 'inc/web/' . strtolower(substr($f_name, 5)) . '.php';
    }

    public function __mobile($f_name)
    {
        global $_W, $_GPC;
        $t = time();
        $versions = "3.5.4";
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
        
        $setting = $this->readCache(1);    /* 基本设置 */
        $comsetting = $this->readCache(2); /* 分销设置 */
        $setting_pc = $this->readCache(3); /* PC基本设置 */
        $userAgent = $this->checkUserAgent(); /* 是否微信内访问 */
        $config = $this->module['config'];
        $common_member_fields = $this->disposeMemberFields();
        $this->setParentId($_GPC['uid']);

        $common = json_decode($setting['common'], true);
        $login_visit = json_decode($setting['login_visit']); /* 需登录访问页面 */
        $template = $setting['template'] ? $setting['template'] : 'default';
        $module_title = $_W['current_module']['title'] ? $_W['current_module']['title'] : '微课堂V2';
        
        $sharelink = unserialize($comsetting['sharelink']);
        $shareurl = $_W['siteroot'] .'app/'. $this->createMobileUrl('index', array('uid'=>$_W['member']['uid']));

        /* 检查是否允许非微信端访问 */
        if (!$setting['visit_limit'] && $_GPC['do']!='error') {
            $dos = array('crontab', 'notice','downloadfile','livenotify');
            if (!$userAgent && !in_array($_GPC['do'], $dos)) {
                header("Location:".$this->createMobileUrl('error'));
            }
        }

        $site_common = new SiteCommon();
        $systemType = $site_common->checkSystenType();
        $navigation = $site_common->getNavigation($template);
        $foot_params = $site_common->setFooter($navigation);
        $right_menu = $site_common->getRightBarMenu();
        $this->updatelessonmember();
        include_once 'inc/mobile/' . strtolower(substr($f_name, 8)) . '.php';
    }

    /* 支付返回确认 */
    public function payResult($params)
    {
        global $_W, $_GPC;

        $setting = $this->readCache(1, $params['uniacid']);   /* 基本设置 */
        $comsetting = $this->readCache(2, $params['uniacid']);/* 分销设置 */

        include_once dirname(__FILE__).'/inc/core/Payresult.php';
        $pay_result = new Payresult();
        $pay_result->dealResult($params, $setting, $comsetting, $wechat_type='wechat');
    }

    /* 更新课程用户信息 */
    public function updatelessonmember()
    {
        global $_W, $_GPC;
        $site_common = new SiteCommon();
        $setting = $this->readCache(1); /* 基本设置 */
        $comsetting = $this->readCache(2); /* 分销设置 */

        $uid = intval($_W['member']['uid']); /*当前用户id*/
        if (empty($uid)) {
            return;
        }
        $member = pdo_get($this->table_member, array('uniacid'=>$_W['uniacid'],'uid'=>$uid));
        
        /* 推荐人id */
        $recid = intval($_GPC['uid']) ? intval($_GPC['uid']) : intval($_COOKIE['parentId']);
        $recmember = pdo_get($this->table_member, array('uniacid'=>$_W['uniacid'],'uid'=>$recid));
        setcookie('parentId', '', time()-3600);

        if (!empty($member)) {
            /* 如果用户openid为uid且获取到新的openid，则更新openid */
            $this->updateOpenid($member);
        } else {
            $mc_member = pdo_fetch("SELECT * FROM " . tablename($this->table_mc_members) . " WHERE uid=:uid", array(':uid'=>$uid));
            if (!empty($mc_member)) {
                $insertarr = array(
                    'uniacid'	=> $_W['uniacid'],
                    'uid'		=> $uid,
                    'openid'	=> !is_numeric($_W['openid']) && !empty($_W['openid']) ? $_W['openid'] : "",
                    'nickname'  => $_W['nickname'] ? $_W['nickname'] : $mc_member['nickname'],
                    'parentid'  => $recmember['status']==1 ? $recmember['uid'] : 0,
                    'status'	=> $comsetting['agent_status'],
                    'coupon_tip'=> 0,
                    'uptime'	=> 0,
                    'addtime'	=> time(),
                );
                pdo_insert($this->table_member, $insertarr);
                $source_id = pdo_insertid();
                $member = pdo_fetch("SELECT * FROM " . tablename($this->table_member) . " WHERE uid=:uid", array(':uid'=>$uid));
            }

            if ($insertarr['parentid'] && $_GPC['do']=='lesson' && $_GPC['id']) {
                //推荐课程会员
                $junior = array(
                    'uniacid'	 => $_W['uniacid'],
                    'uid'		 => $insertarr['parentid'],
                    'lessonid'	 => $_GPC['id'],
                    'junior_uid' => $uid,
                    'addtime'	 => time(),
                );
                $site_common->recommendLessonByFreeStudy($junior, $last_junior_id);
            }
        }
        if ($source_id>0) {
            /* 新会员注册发放优惠券&&成功推荐下级，给直接推荐人发放优惠券 */
            $site_common->sendCouponByNewMember($member, $recmember, $setting);
            /* 新下级加入、通知一二三级推荐人 */
            $site_common->setMemberParentId($member, $recmember, $setting, $comsetting, $source_id);
        }
    }

    /*
     * 更新课程会员表openid
     * $member 课程会员表会员信息
     */
    private function updateOpenid($member)
    {
        global $_W;
        
        $openid = $_W['openid'];
        /*课程会员表存在会员openid且openid不是数字(uid)*/
        if (!empty($member['openid']) && !is_numeric($member['openid'])) {
            return;
        }
        /*当前获取到的全局openid为空或者为数字(uid)*/
        if (empty($openid) || is_numeric($openid)) {
            return;
        }

        pdo_update($this->table_member, array('openid'=>$openid), array('uid'=>$member['uid']));
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
        $ress = ihttp_request($urls, $messageDatas);

        return json_decode($ress, true);
    }

    /* 把推荐人ID写入cookie */
    public function setParentId($uid)
    {
        global $_W;

        if ($uid && $uid != $_W['member']['uid']) {
            setcookie("parentId", $uid);
        }
    }

    private function object_array($array)
    {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }

    /**
     *  检查目录，不存在则创建
     */
    private function checkdir($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777);
        }
    }

    /**
     * * 企业付款接口
     */
    private function companyPay($post, $fans)
    {
        global $_W, $_GPC;
        $uniacid = $_W['uniacid'];
        $account = $_W['account'];
        
        /* 分销设置 */
        $comsetting = $this->readCache(2);

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $pars = array();
        $pars['mch_appid'] = $account['key']; /* 公众账号appid */
        $pars['mchid'] = $comsetting['mchid'];   /* 商户号 */
        $pars['nonce_str'] = random(32);   /* 随机字符串 */
        $pars['partner_trade_no'] = $comsetting['mchid'] . date('Ymd') . rand(1000000000, 9999999999); /* 商户订单号 */
        $pars['openid'] = $fans['openid'];   /* 用户openid */
        $pars['check_name'] = 'NO_CHECK';   /* 校验用户姓名选项，不校验 */
        $pars['re_user_name'] = $fans['nickname'];   /* 收款用户姓名 */
        $pars['amount'] = $post['total_amount'] * 100; /* 付款金额，单位：分 */
        $pars['desc'] = $post['desc'];    /* 企业付款描述信息 */
        $pars['spbill_create_ip'] = $comsetting['serverIp'] ? $comsetting['serverIp'] : $_SERVER["SERVER_ADDR"]; /* Ip地址 */

        ksort($pars, SORT_STRING);
        $string1 = '';
        foreach ($pars as $k => $v) {
            $string1 .= "{$k}={$v}&";
        }

        $string1 .= "key={$comsetting['mchkey']}";
        $pars['sign'] = strtoupper(md5($string1));
        $xml = '<xml>';
        foreach ($pars as $k => $v) {
            $xml .= "<{$k}>{$v}</{$k}>";
        }
        $xml .= '</xml>';

        $extras = array();
        $extras['CURLOPT_CAINFO'] = MODULE_ROOT . '/cert/rootca' . $uniacid . '.pem';
        $extras['CURLOPT_SSLCERT'] = MODULE_ROOT . '/cert/apiclient_cert' . $uniacid . '.pem';
        $extras['CURLOPT_SSLKEY'] = MODULE_ROOT . '/cert/apiclient_key' . $uniacid . '.pem';
        load()->func('communication');

        $resp = ihttp_request($url, $xml, $extras);
        $tmp = str_replace("<![CDATA[", "", $resp['content']);
        $tmp = str_replace("]]>", "", $tmp);
        $tmp = simplexml_load_string($tmp);
        $result = json_decode(json_encode($tmp), true);

        return $result;
    }

    /*
     * 上传微信企业付款证书
     */
    public function upfile($file, $newfile)
    {
        global $_W;
        if (!empty($file['name'])) {
            $file_types = explode(".", $file['name']);
            $file_type = $file_types[count($file_types) - 1];
            if (strtolower($file_type) != "pem") {
                message("请上传后缀是pem的文件", "", "error");
            }
            /* 设置上传路径 */
            $dirpath = dirname(__FILE__) . "/cert/";
            if (!file_exists($dirpath)) {
                mkdir($dirpath, 0777);
            }
            /* 命名文件，格式：文件名公众号id.文件类型 */
            $file_name = $dirpath . $newfile . $_W['uniacid'] . "." . $file_type;

            /* 是否上传成功 */
            if (!copy($file['tmp_name'], $file_name)) {
                message("上传文件失败，请稍候重试", "", "error");
            }
        }
    }
    
    /* json输出 */
    public function resultJson($data)
    {
        echo json_encode($data);
        exit();
    }

    /* 获取基本设置参数 */
    private function getSetting()
    {
        global $_W;
        return pdo_fetch("SELECT * FROM " .tablename($this->table_setting). " WHERE uniacid=:uniacid", array(':uniacid'=>$_W['uniacid']));
    }

    /* 获取分销设置参数 */
    private function getComsetting()
    {
        global $_W;
        return pdo_fetch("SELECT * FROM " .tablename($this->table_commission_setting). " WHERE uniacid=:uniacid", array(':uniacid'=>$_W['uniacid']));
    }
    
    /* 检查是否在微信中打开 */
    public function checkUserAgent()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            return false;
        } else {
            return true;
        }
    }

    /* 更新缓存
     * $cacheName 缓存名称
     * $cacheData 缓存数据
     */
    private function updateCache($cacheName, $cacheData=null)
    {
        if (empty($cacheData)) {
            $cacheData = $this->getSetting();
        }
        cache_delete($cacheName);
        cache_write($cacheName, $cacheData);
    }

    /* 读取缓存
     * $type 读取缓存类型 1.手机端全局设置表 2.分销设置表  3.PC端全局设置表
     * $uniacid 公众号id
     */
    private function readCache($type, $uniacid='')
    {
        global $_W;

        if (!$uniacid) {
            $uniacid = $_W['uniacid'];
        }

        if ($type==1) {
            $setting = cache_load('fy_lesson_'.$_W['uniacid'].'_setting');
            if (empty($setting)) {
                $setting = $this->getSetting();
                cache_write('fy_lesson_'.$_W['uniacid'].'_setting', $setting);
            }
            return $setting;
        } elseif ($type==2) {
            $comsetting = cache_load('fy_lesson_'.$_W['uniacid'].'_commission_setting');
            if (empty($comsetting)) {
                $comsetting = $this->getComsetting();
                cache_write('fy_lesson_'.$_W['uniacid'].'_commission_setting', $comsetting);
            }
            return $comsetting;
        } elseif ($type==3) {
            $setting_pc = cache_load('fy_lesson_'.$_W['uniacid'].'_setting_pc');
            if (empty($setting_pc)) {
                $setting_pc = pdo_get($this->table_setting_pc, array('uniacid'=>$_W['uniacid']));
                $setting_pc['site_root'] = $setting_pc['site_root'] ? $setting_pc['site_root'] : $_W['siteroot'];
                cache_write('fy_lesson_'.$_W['uniacid'].'_setting_pc', $setting_pc);
            }
            return $setting_pc;
        }
    }

    /* 查询通用缓存 */
    public function readCommonCache($name)
    {
        global $_W;

        $data = cache_load($name);
        $update_time = intval(cache_load('update_time_'.$_W['uniacid']));
        if (empty($data) || time()>$update_time) {
            if (time()>$update_time) {
                cache_write('update_time_'.$_W['uniacid'], time()+15);
            }
            return false;
        } else {
            return $data;
        }
    }

    /**
    * 处理用户完善信息字段
    */
    public function disposeMemberFields()
    {
        global $_W;

        $list = cache_load('fy_lesson_'.$_W['uniacid'].'_common_member_fields');
        if (!empty($list)) {
            return $list;
        }

        $type_status = new TypeStatus();
        $fields = $type_status->memberFields();

        $list = array();
        foreach ($fields as $k=>$v) {
            $field = pdo_get('mc_member_fields', array('uniacid'=>$_W['uniacid'],'fieldid'=>$k), array('title'));
            $list[] = array(
                'field_short' => $v,
                'field_name'  => $field['title'],
            );
        }

        cache_write('fy_lesson_'.$_W['uniacid'].'_common_member_fields', $list);
        return $list;
    }
}
