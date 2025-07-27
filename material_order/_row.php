<?php 
$num = isset($row['num']) ? $row['num'] : '';
$orderDate = isset($row['orderDate']) ? $row['orderDate'] : '';
$motorlist = isset($row['motorlist']) ? json_decode($row['motorlist'], true) : [];
$wirelessClist = isset($row['wirelessClist']) ? json_decode($row['wirelessClist'], true) : [];
$wireClist = isset($row['wireClist']) ? json_decode($row['wireClist'], true) : [];
$wirelessLinklist = isset($row['wirelessLinklist']) ? json_decode($row['wirelessLinklist'], true) : [];
$wireLinklist = isset($row['wireLinklist']) ? json_decode($row['wireLinklist'], true) : [];
$bracketlist = isset($row['bracketlist']) ? json_decode($row['bracketlist'], true) : [];
$memo = isset($row['memo']) ? $row['memo'] : '';
$is_deleted = isset($row['is_deleted']) ? $row['is_deleted'] : null;
$first_writer = isset($row['first_writer']) ? $row['first_writer'] : null;
$update_log = isset($row['update_log']) ? $row['update_log'] : null;
$searchtag = isset($row['searchtag']) ? $row['searchtag'] : null;
?>
