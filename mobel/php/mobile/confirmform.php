<?php
/*
 * @Description:
 * @Author: Ophites
 * @Date: 2020-09-12 16:51:06
 * @LastEditors: Ophites
 * @LastEditTime: 2020-09-12 16:56:33
 */
checkauth();
$uid=$_W['member']['uid'];
$enter = pdo_fetch("SELECT id FROM " .tablename('fy_lesson_enter'). " WHERE uniacid=:uniacid AND uid=:uid LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid));
if (!empty($enter)) {
    message("您已提交过报名表，无需重复提交", $this->createMobileUrl('confirm2', null), "error");
}
$pay_order = pdo_fetch("SELECT id FROM " .tablename($this->table_order). " WHERE uniacid=:uniacid AND uid=:uid AND lessonid=:lessonid AND status>=:status AND (validity>:validity OR validity=0) AND is_delete=:is_delete ORDER BY id DESC LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid,':status'=>1,':validity'=>time(),':is_delete'=>0));
if (!empty($pay_order)) {
    message("您已购买该套餐，无需重复购买", $this->createMobileUrl('confirm2', null), "error");
}
$phonenumber = $_W['member']['mobile'];
include $this->template("../mobile/{$template}/confirmform");
