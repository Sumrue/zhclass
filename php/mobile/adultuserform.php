<?php
/*
 * @Description:
 * @Author: Ophites
 * @Date: 2020-09-05 13:49:14
 * @LastEditors: Ophites
 * @LastEditTime: 2020-09-09 14:08:59
 */
$at=$_GPC['at'];
if (empty($at)) {
    $school = pdo_fetchall("SELECT * FROM " .tablename("fy_lesson_school"). " WHERE uniacid=:uniacid order by displayorder desc,id desc", array(':uniacid'=>$uniacid));
    foreach ($school as $key=>$item) {
        $lid=json_decode($item['lid'], true);
        $area_list=json_decode($item['area'], true);
        $area=[];
        foreach ($area_list as $aitem) {
            if (!isset($area[$aitem['cid']])) {
                $area[$aitem['cid']]=[];
                $tmp=pdo_fetch("SELECT ext_name FROM " .tablename("fy_lesson_school_addr"). " WHERE id=:id", array('id'=>$aitem['cid']));
                $area[$aitem['cid']]['name']=$tmp['ext_name'];
                $area[$aitem['cid']]['tid']=[];
            }
            $tmp=pdo_fetch("SELECT ext_name FROM " .tablename("fy_lesson_school_addr"). " WHERE id=:id", array('id'=>$aitem['tid']));
            $area[$aitem['cid']]['tid'][$aitem['tid']]=$tmp['ext_name'];
        }
        $school[$key]['area']=$area;
        $level=[];
        foreach ($lid as $lkey=>$litem) {
            $level[] = pdo_fetch("SELECT id,name FROM " .tablename("fy_lesson_school_level"). " WHERE uniacid=:uniacid and id=:id", array(':uniacid'=>$uniacid,'id'=>$lkey));
        }
        $school[$key]['lid']=$level;
    }
} elseif ($at=='pro') {
    $id=$_GPC['id'];
    $lid=$_GPC['lid'];
    $pro = pdo_fetchall("SELECT s.id,p.name FROM " .tablename("fy_lesson_school_spec"). " as s left join " .tablename("fy_lesson_school_pro"). " as p on p.id=s.pid where s.uniacid=:uniacid and s.sid=:id and p.lid=:lid", array(':uniacid'=>$uniacid,':id'=>$id,':lid'=>$lid));
    $this->resultJson($pro);
}

include $this->template("../mobile/{$template}/adultuserform");
