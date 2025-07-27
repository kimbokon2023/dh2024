<?php
// 데이터베이스 연결 정보
$host = "211.47.74.49";  // 호스트 이름 cafe24는 localhost , gabia는 전체 주소를 써줘야 함.
$username = "chandj"; // MySQL 계정 아이디
$password = "chan0207!!"; // MySQL 계정 패스워드
$dbname = "dbchandj";  // 데이터베이스 이름

// MySQL 데이터베이스 연결
$conn = mysqli_connect($host, $username, $password, $dbname);

// MySQL 연결 오류 발생 시 스크립트 종료
if (mysqli_connect_errno()) {
  die("Failed to connect to MySQL: " . mysqli_connect_error());
}

?>