<?php 
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$orderDate = isset($_REQUEST['orderDate']) ? $_REQUEST['orderDate'] : '';
$motorlist = isset($_REQUEST['motorlist']) ? $_REQUEST['motorlist'] : [];
$wirelessClist = isset($_REQUEST['wirelessClist']) ? $_REQUEST['wirelessClist'] : [];
$wireClist = isset($_REQUEST['wireClist']) ? $_REQUEST['wireClist'] : [];
$wirelessLinklist = isset($_REQUEST['wirelessLinklist']) ? $_REQUEST['wirelessLinklist'] : [];
$wireLinklist = isset($_REQUEST['wireLinklist']) ? $_REQUEST['wireLinklist'] : [];
$bracketlist = isset($_REQUEST['bracketlist']) ? $_REQUEST['bracketlist'] : [];
$memo = isset($_REQUEST['memo']) ? $_REQUEST['memo'] : '';
$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : null;
$first_writer = isset($_REQUEST['first_writer']) ? $_REQUEST['first_writer'] : null;
$update_log = isset($_REQUEST['update_log']) ? $_REQUEST['update_log'] : null;
$searchtag = isset($_REQUEST['searchtag']) ? $_REQUEST['searchtag'] : null;
?>
