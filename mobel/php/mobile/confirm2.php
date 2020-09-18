<?php
/*
 * @Description:
 * @Author: Ophites
 * @Date: 2020-09-12 17:15:33
 * @LastEditors: Ophites
 * @LastEditTime: 2020-09-12 17:28:18
 */
checkauth();
$uid=$_W['member']['uid'];
$enter = pdo_fetch("SELECT e.name,e.`code`,e.mobile,e.addr,s.name as sname,l.name as lname,c.name as cname,p.name as pname FROM " .tablename('fy_lesson_enter'). " as e left join " .tablename('fy_lesson_school'). " as s on s.id=e.sid left join " .tablename('fy_lesson_school_level'). " as l on l.id=e.lid left join " .tablename('fy_lesson_school_classify'). " as c on c.id=s.cid left join " .tablename('fy_lesson_school_spec'). " as sp on sp.id=e.pid left join " .tablename('fy_lesson_school_pro'). " as p on p.id=sp.pid WHERE e.uniacid=:uniacid AND e.uid=:uid LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid));
$pay_order = pdo_fetch("SELECT id FROM " .tablename($this->table_order). " WHERE uniacid=:uniacid AND uid=:uid AND lessonid=:lessonid AND status>=:status AND (validity>:validity OR validity=0) AND is_delete=:is_delete ORDER BY id DESC LIMIT 1", array(':uniacid'=>$uniacid,':uid'=>$uid,':status'=>1,':validity'=>time(),':is_delete'=>0));

include $this->template("../mobile/{$template}/confirm2");
