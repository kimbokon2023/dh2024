<?php

header("Content-Type: text/html; charset=UTF-8 ");

isset($_REQUEST["question"]) ? $question = $_REQUEST["question"] : $question=""; 
isset($_REQUEST["email"]) ? $email = $_REQUEST["email"] : $email=""; 
isset($_REQUEST["name"]) ? $name = $_REQUEST["name"] : $name=""; 
isset($_REQUEST["tel"]) ? $tel = $_REQUEST["tel"] : $tel=""; 

$to ="mirae@8440.co.kr" ; // 받는사람
$title = "[홈페이지 견적/제작] 문의가 접수되었습니다. " . $name . " Tel : " . $tel ;  // 제목 
$title_encode = "=?utf-8?B?".base64_encode($title)."?=\n"; //제목 인코딩


// $headers .= "Cc: "sda0503@naver.com"\r\n"; //참조
// $headers .= "Bcc: "sda0503@naver.com"\r\n"; //숨은참조

  //첨부 파일 읽기
  $fp = fopen($_FILES["file"]["tmp_name"], "r");
  $file = fread($fp, $_FILES["file"]["size"]);
  fclose($fp);  
  
$message = "" ; //내용
$message .=  "<html> <body> 의뢰인 성명 : " . $name . " <br> <br> \r\n 연락처 : " . $tel . "  \r\n <br><br>"; 
$message .=  "내용 :  <br> " . $question ; //내용
$message .=  "</body> </html>";	
  
// 이메일 본문 인코딩 설정
$message = iconv('UTF-8', 'EUC-KR', $message);

// 첨부 파일 경로 및 파일명

$filename=  $_FILES["file"]["tmp_name"];
$basename = $_FILES["file"]["name"];

// 첨부 파일 MIME 타입 확인
$filetype = mime_content_type($basename);

// 첨부 파일 읽기 및 base64 인코딩
$filedata = chunk_split(base64_encode(file_get_contents($filename)));

// 이메일 헤더 작성
$headers = "From: " . $email . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"boundary\"\r\n";
$headers .= "Content-Disposition: attachment; filename=\"$basename\"\r\n";

// 이메일 본문 작성
$body = "--boundary\r\n";
$body .= "Content-Type: text/html; charset=\"EUC-KR\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
$body .= chunk_split(base64_encode($message));

// 첨부 파일 작성
$body .= "--boundary\r\n";
$body .= "Content-Type: $filetype; name=\"$basename\"\r\n";
$body .= "Content-Disposition: attachment; filename=\"$basename\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
$body .= $filedata . "\r\n\r\n";

// 메일 전송
$send_mail = mail($to, $title_encode, $body, $headers);		
	
echo $send_mail; //성공하면 1을 실패하면 0을 출력
  
?>