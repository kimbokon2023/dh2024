<?php
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : '';
$registration_date = isset($_REQUEST["registration_date"]) ? $_REQUEST["registration_date"] : '';
$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : '';
$name = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
$subject = isset($_REQUEST["subject"]) ? $_REQUEST["subject"] : '회의내용 요약';
$content = isset($_REQUEST["content"]) ? $_REQUEST["content"] : '';
$regist_day = isset($_REQUEST["regist_day"]) ? $_REQUEST["regist_day"] : '';
$hit = isset($_REQUEST["hit"]) ? $_REQUEST["hit"] : '';
$is_html = isset($_REQUEST["is_html"]) ? $_REQUEST["is_html"] : '';
$suggestioncheck = isset($_REQUEST["suggestioncheck"]) ? $_REQUEST["suggestioncheck"] : '';
$searchtext = isset($_REQUEST["searchtext"]) ? $_REQUEST["searchtext"] : '';
?> 