<?php
/*
 * @Description:
 * @Author: Ophites
 * @Date: 2020-09-02 10:03:40
 * @LastEditors: Ophites
 * @LastEditTime: 2020-09-24 09:42:06
 */
checkauth();

$uid = $_W['member']['uid'];
$userMsg = $_W["fans"];
$userMsg2 = $_W['member'];

$condition = " a.uniacid=:uniacid ";
$params[':uniacid'] = $uniacid;
$condition .= " AND a.parentid =:parentid ";
$params[':parentid'] = $uid;
$orderby = " ORDER BY a.uid DESC";
$list  = pdo_fetchall("SELECT b.nickname,b.avatar FROM " .tablename($this->table_member). " a LEFT JOIN " .tablename($this->table_mc_members). " b ON a.uid=b.uid WHERE {$condition} {$orderby} LIMIT 20", $params);
foreach ($list as $key=>$value) {
    if (empty($value['avatar'])) {
        $list[$key]['avatar'] = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg";
    } else {
        $list[$key]['avatar'] = (strstr($value['avatar'], "http://") || strstr($value['avatar'], "https://")) ? $value['avatar'] : $_W['attachurl'].$value['avatar'];
    }
}
include $this->template("../mobile/{$template}/invitegift");
