<?php
/*
 * @Description:
 * @Author: Ophites
 * @Date: 2020-10-17 15:10:25
 * @LastEditors: Ophites
 * @LastEditTime: 2020-10-17 15:21:25
 */
if (empty($at)) {
    if (checksubmit('submit')) { /* 排序 */
        if (is_array($_GPC['displayorder'])) {
            foreach ($_GPC['displayorder'] as $pid => $val) {
                $data = array('displayorder' => intval($_GPC['displayorder'][$pid]));
                pdo_update('fy_lesson_question', $data, array('id' => $pid));
            }
        }
        message('操作成功!', referer, 'success');
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    
    $title = trim($_GPC['title']);
    $status  = intval($_GPC['status']);
    
    $condition = " uniacid=:uniacid ";
    $params[':uniacid'] = $uniacid;
    
    if ($title!='') {
        $condition .= " AND name LIKE :name ";
        $params[':title'] = "%".$title."%";
    }
    if ($_GPC['status'] != '') {
        $condition .= " AND status=:status ";
        $params[':status'] = $status;
    }
    
    $list = pdo_fetchall("SELECT * FROM " .tablename('fy_lesson_question'). "  order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($list as $key=>$value) {
        $list[$key]['addtime']=date('Y-m-d H:i:s', $value["addtime"]);
    }
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename("fy_lesson_question"). " WHERE {$condition}", $params);
    $pager = pagination($total, $pindex, $psize);
} elseif ($at=='add') {
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $question = pdo_fetch("SELECT * FROM " . tablename('fy_lesson_question') . " WHERE uniacid=:uniacid AND id=:id", array(':uniacid'=>$uniacid,':id'=>$id));
        if (empty($question)) {
            message("该常见问题不存在或已被删除！", "", "error");
        }
    }
    if (checksubmit('submit')) {
        if (empty($_GPC['title'])) {
            message("抱歉，请输入常见问题名称！");
        }
        $data = array(
        'uniacid'	      => $_W['uniacid'],
        'title'		  => trim($_GPC['title']),
        'answer'		  => trim($_GPC['answer']),
        'displayorder'	  => intval($_GPC['displayorder']),
        'status'		  => intval($_GPC['status']),
        'addtime'		  => time(),
    );
        if (!empty($id)) {
            unset($data['addtime']);
            $res = pdo_update('fy_lesson_question', $data, array('id' => $id));
            if ($res) {
                $site_common->addSysLog($_W['uid'], $_W['username'], 1, "常见问题管理", "新增ID:{$id}的常见问题");
            }
        } else {
            pdo_insert('fy_lesson_question', $data);
            $id = pdo_insertid();
            if ($id) {
                $site_common->addSysLog($_W['uid'], $_W['username'], 3, "常见问题管理", "编辑ID:{$id}的常见问题");
            }
        }
        message("更新专业分类信息成功！", $this->createWebUrl('pagesetting', array('op' => 'questionpost')), "success");
    }
} elseif ($at=='del') {
    $id = intval($_GPC['id']);
    $enter = pdo_fetch("SELECT id FROM " . tablename('fy_lesson_question') . " WHERE uniacid=:uniacid AND id=:id ", array(':uniacid'=>$uniacid,':id'=>$id));
    if (empty($enter)) {
        $this->resultJson(['code'=>0,'msg'=>"抱歉，常见问题不存在或是已经被删除！"]);
    }

    $res = pdo_delete('fy_lesson_question', array('uniacid' => $uniacid, 'id' => $id));
    if ($res) {
        $site_common->addSysLog($_W['uid'], $_W['username'], 2, "常见问题管理", "删除ID:{$id}的常见问题");
    }
    $this->resultJson(['code'=>1,'msg'=>"常见问题删除成功！"]);
}
