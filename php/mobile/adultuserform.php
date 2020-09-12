<?php
/*
 * @Description:
 * @Author: Ophites
 * @Date: 2020-09-05 13:49:14
 * @LastEditors: Ophites
 * @LastEditTime: 2020-09-12 17:03:55
 */
checkauth();
$at=$_GPC['at'];
if (empty($at)) {
    $cid=$_GPC['cid'];
    $school = pdo_fetchall("SELECT id,name,lid,area FROM " .tablename("fy_lesson_school"). " WHERE uniacid=:uniacid and cid=:cid order by displayorder desc,id desc", array(':uniacid'=>$uniacid,':cid'=>$cid));
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
} elseif ($at=='enter') {
    $data['uid']=$_W['member']['uid'];
    $data['name']=trim($_GPC['name']);
    $data['code']=trim($_GPC['code']);
    $data['mobile']=trim($_GPC['mobile']);
    $data['addr']=trim($_GPC['addr']);
    $data['tid']=intval($_GPC['tid']);
    $data['mid']=intval($_GPC['mid']);
    $data['cid']=intval($_GPC['cid']);
    $data['sid']=intval($_GPC['sid']);
    $data['lid']=intval($_GPC['lid']);
    $data['pid']=intval($_GPC['pid']);
    $data['aid']=intval($_GPC['aid']);
    pdo_insert('fy_lesson_enter', $data);
    $this->resultJson(['code'=>1,'msg'=>"报名成功！"]);
}

include $this->template("../mobile/{$template}/adultuserform");
