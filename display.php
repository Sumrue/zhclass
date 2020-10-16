<?php
/*
 * @Description:
 * @Author: Ophites
 * @Date: 2020-09-02 10:04:13
 * @LastEditors: Ophites
 * @LastEditTime: 2020-09-25 15:08:12
 */

$login_visit  = json_decode($setting['login_visit'], true);
$isfollow	  = json_decode($setting['isfollow'], true);
$index_verify = json_decode($setting['index_verify'], true);
$wxapp_follow = '../attachment/images/'.$uniacid.'/fy_lessonv2/wxapp_follow.png';

if (checksubmit('submit')) {
    $data = array(
        'uniacid'				=> $uniacid,
        'stock_config'			=> intval($_GPC['stock_config']),
        'visit_limit'			=> intval($_GPC['visit_limit']),
        'login_visit'			=> json_encode($_GPC['login_visit']),
        'isfollow'				=> json_encode($_GPC['isfollow']),
        'qrcode'				=> $_GPC['qrcode'],
        'manageopenid'			=> trim($_GPC['manageopenid']),
        'closespace'			=> intval($_GPC['closespace']),
        'show_teacher_income'	=> intval($_GPC['show_teacher_income']),
        'audit_evaluate'		=> intval($_GPC['audit_evaluate']),
        'show_evaluate_time'	=> intval($_GPC['show_evaluate_time']),
        'autogood'				=> intval($_GPC['autogood']),
        'modify_mobile'			=> $_GPC['modify_mobile'],
        'index_verify'			=> json_encode($_GPC['index_verify']),
        'addtime'				=> time(),
    );

    if (empty($glo_setting)) {
        $result = pdo_insert($this->table_setting, $data);
    } else {
        //$data['ios_pay'] = $_GPC['ios_pay']=='' ? $glo_setting['ios_pay'] : intval($_GPC['ios_pay']);
        $result = pdo_update($this->table_setting, $data, array('uniacid' => $uniacid));
    }

    if ($result) {
        /* 更新设置表缓存 */
        $this->updateCache('fy_lesson_'.$uniacid.'_setting');

        $site_common->addSysLog($_W['uid'], $_W['username'], 3, "基本设置->全局设置", "编辑全局设置");
        message('更新成功', $this->createWebUrl('setting'), 'success');
    } else {
        message('更新失败，请稍候重试', "", 'error');
    }
}
