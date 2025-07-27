<?php
$num = $row["num"];
$id = $row["id"];
$name = $row["name"];
$subject = $row["subject"];
$content = $row["content"];
$regist_day = $row["regist_day"];
$hit = $row["hit"];
$is_html = $row["is_html"];
$noticecheck = $row["noticecheck"];
$searchtext = $row["searchtext"];

// 추가된 input 요소 값
$first_writer = $row["first_writer"];
$chargedPerson = $row["chargedPerson"];
$dueDate = $row["dueDate"];
$doneDate = $row["doneDate"];
$chargedPersonStatus = isset($row["chargedPersonStatus"]) ? $row["chargedPersonStatus"] : '';
?>
