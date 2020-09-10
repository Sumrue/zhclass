<?php
checkauth();
$title = "评价订单";
$evaluate_page = $common['evaluate_page']; //评价字体自定义
 
$uid = $_W['member']['uid'];
$orderid = intval($_GPC['orderid']); /* 课程订单id */
$member = pdo_fetch("SELECT nickname FROM " .tablename($this->table_mc_members). " WHERE uid=:uid", array(':uid'=>$uid));


if ($op=='display') {
    $order = pdo_fetch("SELECT a.id,a.ordersn,a.uid,a.lessonid,a.status,a.teacherid, b.bookname,b.images,b.price, c.nickname FROM " .tablename($this->table_order). " a LEFT JOIN " .tablename($this->table_lesson_parent). " b ON a.lessonid=b.id LEFT JOIN " .tablename($this->table_mc_members). " c ON a.uid=c.uid WHERE a.uniacid=:uniacid AND a.id=:id AND a.uid=:uid", array(':uniacid'=>$uniacid,':id'=>$orderid,':uid'=>$uid));
    if (empty($order)) {
        message("该订单不存在或已被删除！", "", "error");
    }

    if ($order['status']==2) {
        message("该订单已评价！", $this->createMobileUrl('mylesson'), "warning");
    }

    /* 提交评价 */
    if (checksubmit('submit')) {
        $data = array(
            'uniacid'			=> $uniacid,
            'orderid'			=> $orderid,
            'ordersn'			=> $order['ordersn'],
            'lessonid'			=> $order['lessonid'],
            'bookname'			=> $order['bookname'],
            'teacherid'			=> $order['teacherid'],
            'uid'				=> $order['uid'],
            'nickname'			=> $order['nickname'],
            'grade'				=> intval($_GPC['grade']),
            'global_score'		=> intval($_GPC['global_score']),
            'content_score'		=> intval($_GPC['content_score']),
            'understand_score'  => intval($_GPC['understand_score']),
            'content'			=> trim($_GPC['content']),
            'status'			=> $setting['audit_evaluate']==0 ? 1 : 0,
            'type'				=> 1,
            'addtime'			=> time(),
        );

        if (strlen($data['content'])<10) {
            message("评论内容不得少于10个字符");
        }
        if (!$data['grade']) {
            message("请对课程进行总体评价");
        }
        if (!$data['global_score'] || !$data['content_score'] || !$data['understand_score']) {
            message("请点亮评分的每一行星星");
        }

        $result = pdo_insert($this->table_evaluate, $data);
        if ($result) {
            /* 更新订单状态 */
            pdo_update($this->table_order, array('status'=>2), array('id'=>$order['id']));

            /* 用户评价课程 */
            $site_common->memberEvaluate($data);

            $evaluate_word = $setting['audit_evaluate']==0 ? "评价成功" : "评价成功，等待管理员审核";
            message($evaluate_word, $this->createMobileUrl('mylesson'), "success");
        } else {
            message("评价失败！", $this->createMobileUrl('mylesson'), "error");
        }
    }
} elseif ($op=='freeorder') {
    $lessonid = intval($_GPC['lessonid']);
    $lesson = pdo_fetch("SELECT * FROM " .tablename($this->table_lesson_parent). " WHERE uniacid=:uniacid AND id=:id LIMIT 1", array(':uniacid'=>$uniacid,':id'=>$lessonid));
    if (empty($lesson)) {
        message("该课程不存在或已被删除！", "", "error");
    }

    $already_evaluate = pdo_fetch("SELECT id FROM " .tablename($this->table_evaluate). " WHERE uid=:uid AND lessonid=:lessonid AND orderid=:orderid", array(':uid'=>$uid,':lessonid'=>$lessonid,':orderid'=>0));
    if (!empty($already_evaluate)) {
        message("该课程已评价！", "", "warning");
    }

    $order = array(
        'images'   => $lesson['images'],
        'bookname' => $lesson['bookname'],
        'teacherid'=> $lesson['teacherid'],
        'price'    => $lesson['price'],
        'uid'	   => $uid,
        'nickname' => $member['nickname'],
    );

    /* 提交评价 */
    if (checksubmit('submit')) {
        $data = array(
            'uniacid'			=> $uniacid,
            'orderid'			=> '',
            'ordersn'			=> '',
            'lessonid'			=> $lessonid,
            'bookname'			=> $order['bookname'],
            'teacherid'			=> $order['teacherid'],
            'uid'				=> $order['uid'],
            'nickname'			=> $order['nickname'],
            'grade'				=> intval($_GPC['grade']),
            'global_score'		=> intval($_GPC['global_score']),
            'content_score'		=> intval($_GPC['content_score']),
            'understand_score'  => intval($_GPC['understand_score']),
            'content'			=> trim($_GPC['content']),
            'status'			=> $setting['audit_evaluate']==0 ? 1 : 0,
            'type'				=> 1,
            'addtime'			=> time(),
        );

        if (strlen($data['content'])<10) {
            message("评论内容不得少于10个字符");
        }
        if (!$data['grade']) {
            message("请对课程进行总体评价");
        }
        if (!$data['global_score'] || !$data['content_score'] || !$data['understand_score']) {
            message("请点亮评分的每一行星星");
        }

        $result = pdo_insert($this->table_evaluate, $data);
        if ($result) {
            /* 用户评价课程 */
            $site_common->memberEvaluate($data);
            
            $evaluate_word = $setting['audit_evaluate']==0 ? "评价成功" : "评价成功，等待管理员审核";
            message($evaluate_word, $this->createMobileUrl('lesson', array('id'=>$lessonid)), "success");
        } else {
            message("评价失败！", $this->createMobileUrl('lesson', array('id'=>$lessonid)), "error");
        }
    }
} elseif ($op=='free') {
    $data = array(
            'uniacid'			=> $uniacid,
            'orderid'			=> 0,
            'ordersn'			=> 0,
            'lessonid'			=> 0,
            'bookname'			=> 0,
            'teacherid'			=> '',
            'uid'				=> $uid,
            'nickname'			=> $member['nickname'],
            'grade'				=> 1,
            'global_score'		=> 5,
            'content_score'		=> 5,
            'understand_score'  => 5,
            'content'			=> trim($_GPC['content']),
            'status'			=> $setting['audit_evaluate']==0 ? 1 : 0,
            'type'				=> 1,
            'addtime'			=> time(),
        );

    if (strlen($data['content'])<10) {
        $this->resultJson(['code'=>0,'msg'=>"评论内容不得少于10个字符"]);
    }
    if (!$data['grade']) {
        $this->resultJson(['code'=>0,'msg'=>"请进行总体评价"]);
    }
    if (!$data['global_score'] || !$data['content_score'] || !$data['understand_score']) {
        $this->resultJson(['code'=>0,'msg'=>"请点亮评分的每一行星星"]);
    }

    $result = pdo_insert($this->table_evaluate, $data);
    if ($result) {
        $evaluate_word = $setting['audit_evaluate']==0 ? "评价成功" : "评价成功，等待管理员审核";
        $this->resultJson(['code'=>1,'msg'=>$evaluate_word]);
    } else {
        $this->resultJson(['code'=>0,'msg'=>"评价失败！"]);
    }
} elseif ($op=='getfree') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $condition = " e.uniacid=:uniacid ";
    $params[':uniacid'] = $uniacid;
    $list = pdo_fetchall("SELECT e.nickname,e.content,e.reply,e.global_score,e.content_score,e.understand_score,m.avatar FROM " .tablename($this->table_evaluate). " as e left join " .tablename($this->table_mc_members). " as m on m.uid=e.uid WHERE {$condition} and e.status=1 ORDER BY e.id desc, e.addtime DESC LIMIT " .($pindex - 1) * $psize. ',' . $psize, $params);
    foreach ($list as $key=>$item) {
        if (empty($item['avatar'])) {
            $avatar = MODULE_URL."template/mobile/{$template}/images/default_avatar.jpg";
        } else {
            $avatar = strstr($item['avatar'], "http://") ? $item['avatar'] : $_W['attachurl'].$item['avatar'];
        }
        $list[$key]['avatar']=$avatar;
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' .tablename($this->table_evaluate). " as e WHERE {$condition} and e.status=1", $params);
    $this->resultJson(array( "list" => $list, "pagesize" => $psize, "total" => $total ));
}

include $this->template("../mobile/{$template}/evaluate");
