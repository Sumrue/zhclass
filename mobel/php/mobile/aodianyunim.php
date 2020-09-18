<?php
/**
 * 奥点云IM通讯
 */

$im_config = json_decode($setting['im_config'], true);

$api = new TisApi($im_config['aodianyun']['accessId'], $im_config['aodianyun']['accessKey']);
$method = $_REQUEST['method'];


$rst = $api->$method($_REQUEST, $_GPC['tisId']);

$this->resultJson($rst);

?>