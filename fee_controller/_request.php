<?php 
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$basicdate = isset($_REQUEST['basicdate']) ? $_REQUEST['basicdate'] : '';
$item = isset($_REQUEST['item']) ? $_REQUEST['item'] : '{}';
$originalcost = isset($_REQUEST['originalcost']) ? $_REQUEST['originalcost'] : '{}';
$price = isset($_REQUEST['price']) ? $_REQUEST['price'] : '{}';
$memo = isset($_REQUEST['memo']) ? $_REQUEST['memo'] : '';
$update_log = isset($_REQUEST['update_log']) ? $_REQUEST['update_log'] : '';
$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : '';
$searchtag = isset($_REQUEST['searchtag']) ? $_REQUEST['searchtag'] : '';
$yuan = isset($_REQUEST['yuan']) ? $_REQUEST['yuan'] : '{}';
?>