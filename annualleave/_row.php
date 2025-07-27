<?php
$num = isset($row["num"]) ? $row["num"] : '';
$name = isset($row["name"]) ? $row["name"] : '';
$part = isset($row["part"]) ? $row["part"] : '';
$dateofentry = isset($row["dateofentry"]) ? $row["dateofentry"] : '';
$referencedate = isset($row["referencedate"]) ? $row["referencedate"] : date('Y');
$availableday = isset($row["availableday"]) ? $row["availableday"] : 0;
$comment = isset($row["comment"]) ? $row["comment"] : '';
$is_deleted = isset($row["is_deleted"]) ? $row["is_deleted"] : '';
$update_log = isset($row["update_log"]) ? $row["update_log"] : '';
?>
