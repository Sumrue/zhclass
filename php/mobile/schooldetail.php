<?php
/*
 * @Description:
 * @Author: Ophites
 * @Date: 2020-09-02 10:03:43
 * @LastEditors: Ophites
 * @LastEditTime: 2020-09-05 09:12:14
 */
$id = intval($_GPC['id']);
if (empty($id)) {
    message("该学校不存在或已被删除！", "", "error");
}
$school = pdo_fetch("SELECT * FROM " .tablename("fy_lesson_school"). " WHERE uniacid=:uniacid AND id=:id", array(':uniacid'=>$uniacid, ':id'=>$id));
if (empty($school)) {
    message("该学校不存在或已被删除！", "", "error");
}
$school["spec"] = pdo_fetch("SELECT * FROM " .tablename("fy_lesson_school_spec"). " WHERE uniacid=:uniacid AND sid=:sid", array(':uniacid'=>$uniacid, ':sid'=>$id));
foreach ($school["spec"] as $key=>$item) {
    $school["spec"][$key]['area']=json_decode($item['area'], true);
}
include $this->template("../mobile/{$template}/schooldetail");
