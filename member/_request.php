<?php
$id = $_REQUEST["id"] ?? '';
$num = $_REQUEST["num"] ?? '';
$pass = $_REQUEST["pass"] ?? '';
$name = $_REQUEST["name"] ?? '';
$userlevel = $_REQUEST["userlevel"] ?? '';
$part = $_REQUEST["part"] ?? '';
$hp = $_REQUEST["hp"] ?? '';
$numorder = $_REQUEST["numorder"] ?? '';
$position = $_REQUEST["position"] ?? '';
$eworks_level = $_REQUEST["eworks_level"] ?? '';
$regist_day = $_REQUEST["regist_day"] ?? '';
$ecountID = $_REQUEST["ecountID"] ?? '';

// 영문으로 수정된 컬럼명
$enterDate = $_REQUEST["enterDate"] ?? '';       // 입사일
$quitDate = $_REQUEST["quitDate"] ?? '';         // 퇴사일
$birthday = $_REQUEST["birthday"] ?? '';         // 생년월일
$address = $_REQUEST["address"] ?? '';           // 자택주소
$dailyworkcheck = $_REQUEST["hidden_dailyworkcheck"] ?? '';  // 체크박스는 히든형태로 다른이름으로 넘긴다.
$company = $_REQUEST["company"] ?? '';
?>
