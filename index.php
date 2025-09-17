<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
$WebSite = "https://dh2024.co.kr/";	

// 모바일 사용여부 확인하는 루틴
$mAgent = array("iPhone","iPod","Android","Blackberry", 
    "Opera Mini", "Windows ce", "Nokia", "sony" );
$chkMobile = false;
for($i=0; $i<sizeof($mAgent); $i++){
    if(stripos( $_SERVER['HTTP_USER_AGENT'], $mAgent[$i] )){
        $chkMobile = true;
		// print '권영철';
        break;
    }
}

isset($_REQUEST["home"]) ? $home = $_REQUEST["home"] : $home=""; 

// $home에 1이 들어오면 홈페이지 보도록 변경
if(isset($_SESSION["name"])  && $home!='1') 
{ 
      header ("Location:../index2.php");
	  exit; 
}

// 메인상단 이미지를 AI로 그린 그림으로 10개 랜덤으로 뽑아내서 그려주기
$rnd = rand(1, 10);
// $imgsrc = 'img/homepage/' . $rnd . '.png';
$imgsrc = 'img/motor1.jpg';
?>

<!doctype html>
<html class="h-100" lang="ko">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">  

<meta property="og:type" content="DH모터의 모든것 (주)대한">
<meta property="og:title" content="DH모터의 모든것 (주)대한">
<meta property="og:url" content="www.dh2024.co.kr">
<meta property="og:description" content="DH모터의 모든것 (주)대한">
<meta property="og:image" content="https://dh2024.co.kr/img/dh.jpg"/>

<!--head 태그 내 추가-->
<!-- Favicon-->	
<link rel="icon" type="image/x-icon" href="favicon.ico">   <!-- 33 x 33 -->
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">    <!-- 144 x 144 -->
<link rel="apple-touch-icon" type="image/x-icon" href="favicon.ico">

<title> (주)대한 DH모터 </title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="css/theme.css">  
  
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-zoom/1.6.1/jquery.zoom.min.js" integrity="sha512-xhvWWTTHpLC+d+TEOSX2N0V4Se1989D03qp9ByRsiQsYcdKmQhQ8fsSTX3KLlzs0jF4dPmq0nIzvEc3jdYqKkw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>	
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://dh2024.co.kr/js/common.js"> </script>

<style>
.dropdown:hover .dropdown-menu {
    display: block;
    margin-top: 0;
}
/* 마우스 오버하면 드롭다운하기 */

/* 파일선택 CSS */
.box-file-input label{
  display:inline-block;
  background:#23a3a7;
  color:#fff;
  padding:0px 15px;
  line-height:35px;
  cursor:pointer;
}

.box-file-input label:after{
  content:"파일등록";
}

.box-file-input .file-input{
  display:none;
}

.box-file-input .filename{
  display:inline-block;
  padding-left:10px;
}

.bigPicture {
	position: absolute;
	display:flex;
	justify-content: center;
	align-items: center;
}

.bigPicture img {
	height:100%; /*새로기준으로 꽉차게 보이기 */
}

 /* 우측배너 제작 */
.sideBanner {
  position: absolute;
  width: 170px;
  height: 200px;
  top: calc(100vh - 300px);
  left: calc(100vw - 190px);  
}

* {
  margin: 0;
  padding: 0;
  list-style: none;
  box-sizing: border-box;  
  font-family: Pretendard;
}

.flip { 
  width:  calc(100vw - 77vw);
  height: calc(100vh - 70vh);
  position: relative; 
  perspective: 1100px;
  margin: 2rem;
}

.card {
  width: 100%; 
  height: 100%; 
  position: relative;
  transition: .4s;
  transform-style: preserve-3d;
} 

.front, .back {
  position: absolute;
  width: 100%; 
  height: 100%;
  backface-visibility: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
}

.front {
  color: #000000;
  background: #dcdcdc; 
}

.back { 
  color: #FFFF;
  background: royalblue; 
  transform: rotateY(180deg);
}

.flip:hover .card {
  transform: rotateY(180deg);
}

/*  모바일에서 보이도록 설정하기 */
@media screen and (max-width: 1280px) {			
	.flip { 
	  width:  calc(100vw - 30vw);
	  height: calc(100vh - 60vh);
	  position: relative; 
	  perspective: 1100px;
	  margin: 2rem;
	}

	.card {
	  width: 100%; 
	  height: 100%; 
	  position: relative;
	  transition: .4s;
	  transform-style: preserve-3d;
	} 

	.front, .back {
	  position: absolute;
	  width: 100%; 
	  height: 100%;
	  backface-visibility: hidden;
	  display: flex;
	  justify-content: center;
	  align-items: center;
	}

	.front {
	  color: #000000;
	  background: #dcdcdc; 
	}

	.back { 
	  color: #FFFF;
	  background: royalblue; 
	  transform: rotateY(180deg);
	}

	.flip:hover .card {
	  transform: rotateY(180deg);
	}
}

#myMsgDialog {
	width:40%; 
	background-color: #BEEFFF; 
	border:1px solid black; 
	border-radius: 7px;
}		

#closeDialog {
	width:25%; 
	background-color: #BEEFFF; 
	border:1px solid black; 
	border-radius: 7px;
}		

#mButton, #closeButton {
	padding: 7px 30px;
	background-color: #66ccff;
	color: white;
	font-size: 15px;
	border: 0;
	outline: 0;
}

#cButton {
	padding: 7px 30px;
	background-color: #2828CD;
	color: white;
	font-size: 15px;
	border: 0;
	outline: 0;
}

@media screen and (max-width: 1280px) {		
	#myMsgDialog {
		width:100%; 
		background-color: #BEEFFF; 
		border:1px solid black; 
		border-radius: 7px;
	}		

	#closeDialog {
		width:25%; 
		background-color: #BEEFFF; 
		border:1px solid black; 
		border-radius: 7px;
	}		

	#mButton, #closeButton {
		padding: 7px 30px;
		background-color: #66ccff;
		color: white;
		font-size: 15px;
		border: 0;
		outline: 0;
	}
	
	#cButton {
		padding: 7px 30px;
		background-color: #2828CD;
		color: white;
		font-size: 15px;
		border: 0;
		outline: 0;
	}
}	

.modal-dialog.modal-80size {
  width: 80%;
  height: 50%;
  margin: 0;
  padding: 0;
  z-index: 9999;
}

.modal-content.modal-80size {
  height: auto;
  min-height: 40%;
  z-index: 9999;
}

.modal.modal-center {
  text-align: center;
  z-index: 9999;
}

@media screen and (min-width: 768px) {
  .modal.modal-center:before {
    display: inline-block;
    vertical-align: middle;
    content: " ";
    height: 80%;
	z-index: 9999;
  }
}

.modal-dialog.modal-center {
  display: inline-block;
  text-align: left;
  vertical-align: middle;
   z-index: 9999;
}

.login-button {
	display: inline-block;
	padding: 5px 10px;
	font-size: 12px;
	text-align: center;
	cursor: pointer;
	background-color: #16D5FF;
	color: white;
	border: none;
	border-radius: 5px;
	transition: background-color 0.3s;
}

.login-button:hover {
	background-color: #16D500;
}

.bg-full {
    background-image: url('<?$root_dir?>/img/firstpage1.jpg'); /* 경로 변수에 맞게 조정 */
    background-size: cover; /* 컨테이너 전체 영역을 커버 */
    background-position: center; /* 배경 이미지를 중앙에 위치 */
    background-repeat: no-repeat; /* 이미지 반복 없음 */
    height: 100vh; /* 뷰포트의 전체 높이 */
    width: 100%; /* 전체 너비 */
}

nav {
    z-index: 1000; /* 더 높은 z-index로 상위에 위치하도록 */
}

.bg-full {
    z-index: 1; /* 낮은 z-index */
}

.dropdown-item {
	color: black; /* 기본 글자 색 */
	background-color: white; /* 기본 배경 색 */
	transition: all 0.3s ease; /* 부드러운 색상 전환 효과 */
}
.dropdown-item:hover {
	color: white; /* hover 시 글자 색 반전 */
	background-color: #555555; /* hover 시 배경 색 중간 블랙 */
}

.normal-bold-text {    
    font-weight: bold;
  }

.small-bold-text {
    font-size: smaller;
    font-weight: bold;
  }

</style>
</head>

<body data-bs-spy="scroll" data-bs-target="#navScroll">
<div class="bg-full"></div> <!-- 배경 이미지 컨테이너 -->
<nav id="navScroll" class="navbar navbar-expand-lg navbar-light fixed-top mb-5" tabindex="0">
  <div class="container">
    <a class="navbar-brand " href="/" style="width:26%;">
		<img src="<?$root_dir?>/img/dhlogo_homepage.png" style="width:40%;"> 
	</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

  <li class="nav-item me-1">
    <a class="nav-link normal-bold-text" href="#aboutus" style="color: white;">
      회사소개
    </a>
  </li>
                
  <li class="nav-item dropdown me-1">
    <a href="#" class="nav-link dropdown-toggle normal-bold-text" data-bs-toggle="dropdown" style="color: white;">품질인정컨설팅</a>
    <div class="dropdown-menu shadow-sm m-0">
      <a href="#qcwarranty1" class="dropdown-item small-bold-text">품질인정컨설팅이란? </a>
      <a href="#qcwarranty2" class="dropdown-item small-bold-text">자동방화셔터 부속품에 관한 법규</a>
    </div>
  </li>    
  
  <li class="nav-item dropdown me-1">
    <a href="#" class="nav-link dropdown-toggle normal-bold-text" data-bs-toggle="dropdown" style="color: white;">DH 방화모터</a>
    <div class="dropdown-menu shadow-sm m-0">
      <a href="#dhfeatures"  class="dropdown-item small-bold-text">DH무선형 모터 특징</a>
      <a href="#dh_warranty"  class="dropdown-item small-bold-text"> DH모터 성적서(KC인증)</a>
      <a href="#dhdetails"  class="dropdown-item small-bold-text">모터상세 제원표</a>      
      <a href="#wire_controller_detail" class="dropdown-item small-bold-text">유선형 모터 설정방법</a>
      <a href="#wireless_controller_detail" class="dropdown-item small-bold-text">무선형 모터 설정방법</a>
      <a href="#fall_prevention_brake_bracket" class="dropdown-item small-bold-text">낙하제동방지 브라켓트</a>      
    </div>
  </li>    
  <li class="nav-item dropdown me-1">
    <a href="#" class="nav-link dropdown-toggle normal-bold-text" data-bs-toggle="dropdown" style="color: white;">DH 방범모터</a>
    <div class="dropdown-menu shadow-sm m-0">
      <a href="#dhprevent_detail"  class="dropdown-item small-bold-text">DH 방범모터 제원표</a>    
      <a href="#dhprevent_settings" class="dropdown-item small-bold-text">DH방범모터 설정방법</a>
      <a href="#dhprevent_spec" class="dropdown-item small-bold-text">DH방범모터 스펙</a>
      <a href="#dhprevent_parts" class="dropdown-item small-bold-text">DH방범모터 브라켓트 구성품 </a>      
    </div>
  </li>    
  <li class="nav-item dropdown me-1">
    <a href="#" class="nav-link dropdown-toggle normal-bold-text" data-bs-toggle="dropdown" style="color: white;">DH OHD모터</a>
    <div class="dropdown-menu shadow-sm m-0">
      <a href="#dhc_motor"  class="dropdown-item small-bold-text">DH-C 모터</a>
      <a href="#dhc_cert"  class="dropdown-item small-bold-text">DH-C 인증서</a>      
      <a href="#dhc_controller"  class="dropdown-item small-bold-text"> DH-C 제어기</a>
      <a href="#dhc_spec" class="dropdown-item small-bold-text">DH-C 제원표</a>
      <a href="#dhc_wiring" class="dropdown-item small-bold-text">DH-C 배선도</a>
      <a href="#dhc_error" class="dropdown-item small-bold-text">DH-C 오류코드</a>      
    </div>
  </li>    
  <li class="nav-item dropdown me-1">
    <a href="#" class="nav-link dropdown-toggle normal-bold-text" data-bs-toggle="dropdown" style="color: white;">DH SPD모터</a>
    <div class="dropdown-menu shadow-sm m-0">
	  <a href="#dha_motor"  class="dropdown-item small-bold-text">DH-A 모터</a>
      <a href="#dha_cert"  class="dropdown-item small-bold-text">DH-A 인증서</a>            
      <a href="#dha_controller" class="dropdown-item small-bold-text"> DH-A 제어기</a>
      <a href="#dha_wiring" class="dropdown-item small-bold-text">DH-A 배선도</a>
      <a href="#dha_error" class="dropdown-item small-bold-text">DH-A 오류코드</a>                 
    </div>
  </li>    
  <li class="nav-item dropdown me-1">
    <a href="#" class="nav-link dropdown-toggle normal-bold-text" data-bs-toggle="dropdown" style="color: white;">스크린원단</a>
    <div class="dropdown-menu shadow-sm m-0">
      <a href="#slat_wire"  class="dropdown-item small-bold-text">DH-와이어원단</a>
      <a href="#slat_gas"  class="dropdown-item small-bold-text">DH-가스켓/버미글라스/제연용원단</a>
      <a href="#slat_cert"  class="dropdown-item small-bold-text">DH-원단성적서</a>
    </div>
  </li>    
   
  <li class="nav-item dropdown me-1">
    <a href="#" class="nav-link dropdown-toggle normal-bold-text" data-bs-toggle="dropdown" style="color: white;">게시판</a>
    <div class="dropdown-menu shadow-sm m-0">
      <a href="#" onclick="popup_board1();return false;" class="dropdown-item small-bold-text">법규 자료실</a>
      <a href="#" onclick="popup_board2();return false;" class="dropdown-item small-bold-text">자주 묻는 질문</a>
      <a href="#" onclick="popup_board3();return false;" class="dropdown-item small-bold-text">공지사항</a>
    </div>
  </li>    
<?php

	if($chkMobile == false) 
	{
	?>	
	</div>
	<div class="h-100 d-lg-inline-flex align-items-center d-none">               
	<!--! Font Awesome Pro 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
	<path d="M96 128V70.2c0-13.3 8.3-25.3 20.8-30l96-36c7.2-2.7 15.2-2.7 22.5 0l96 36c12.5 4.7 20.8 16.6 20.8 30V128h-.3c.2 2.6 .3 5.3 .3 8v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V136c0-2.7 .1-5.4 .3-8H96zm48 48c0 44.2 35.8 80 80 80s80-35.8 80-80V160H144v16zM111.9 327.7c10.5-3.4 21.8 .4 29.4 8.5l71 75.5c6.3 6.7 17 6.7 23.3 0l71-75.5c7.6-8.1 18.9-11.9 29.4-8.5C401 348.6 448 409.4 448 481.3c0 17-13.8 30.7-30.7 30.7H30.7C13.8 512 0 498.2 0 481.3c0-71.9 47-132.7 111.9-153.6zM208 48V64H192c-4.4 0-8 3.6-8 8V88c0 4.4 3.6 8 8 8h16v16c0 4.4 3.6 8 8 8h16c4.4 0 8-3.6 8-8V96h16c4.4 0 8-3.6 8-8V72c0-4.4-3.6-8-8-8H240V48c0-4.4-3.6-8-8-8H216c-4.4 0-8 3.6-8 8z"/></svg>
	</a>				
	<?php
	if(!isset($_SESSION["name"]))
		
		{ ?>

	<form id="login_form" name="login_form" method="post" >
	<input type="text"  id="uidInput" name="uid" class="form-control" style="width: 80px; font-size: 10px; display: inline-block;" placeholder="Your ID" required autofocus>
	<label for="inputPassword" class="sr-only"></label>
	<input type="password"  id="inputPassword"   name="upw" class="form-control" style="width: 60px; font-size: 10px;display: inline-block;" placeholder="Password" required>
	<button id="loginBtn" class="login-button" type="button" data-home="<?php echo $home; ?>" style="font-size: 10px;">Login</button>
	</form>		

	<?php
		}
	if(isset($_SESSION["name"]))
		
		{ ?>

		<form id="login_form" name="login_form" method="post">
			<span style="color: white;" ><?php echo $_SESSION["name"]; ?>  님 환영</span>
			<button id="logoutBtn" class="login-button" type="button">Log Out</button>
		</form>
	<?php
		}
	}
	else
	{

	if(!isset($_SESSION["name"]))
		
		{ ?>				

	<form id="login_form" name="login_form" method="post" >
		<input type="text" id="uidInput" name="uid" class="form-control" style="width: 120px; display: inline-block;" placeholder="Your ID" required autofocus >
	<label for="inputPassword" class="sr-only"></label>
	<input  id="inputPassword"  type="password" name="upw" class="form-control" style="width: 120px; display: inline-block;" placeholder="Password" required>
	<button id="loginBtn" class="login-button" type="button" data-home="<?php echo $home; ?>" >Login</button>									
	</form>		

	<?php
		}
	if(isset($_SESSION["name"]))
		
		{ ?>

		<form id="login_form" name="login_form" method="post">
			<span><?php echo $_SESSION["name"]; ?>  님 환영</span>
			<button id="logoutBtn" class="login-button" type="button" style="font-size: 10px;">Log Out</button>
		</form>


	<?php  }   }  ?>	
	</ul>
	</div>
	
</nav>
<?php
// 모바일인 경우는 바로가기 안나오게 하기
// 우측에 플로팅되는 바로가기 메뉴구성 
 if($chkMobile==false) { ?>
 <!--
 <div class="sideBanner">
    <span class="txt-label text-dark fw-bold">
      바로가기  
    </span>
	<div class="mb-1 mt-1">
	<button type="button" class="btn btn-primary rounded-pill" onclick="location.href='#qcwarranty1'" >품질인정컨설팅 </button>	 </div>
	<div class="mb-1 mt-1">
	<button type="button" class="btn btn-primary rounded-pill" onclick="location.href='#dhfeatures'" >DH 모터</button>	 </div>
	<div class="mb-1 mt-1 text-primary">
	<button type="button" class="btn btn-primary rounded-pill" onclick="location.href='#screen'">스크린원단</button> </div>
	<div class="mb-1 mt-1">
	<button type="button" class="btn btn-primary rounded-pill" onclick="location.href='#shaft'">철강</button> </div>
	<div class="mb-1 mt-1">
	<button type="button" class="btn btn-primary rounded-pill" onclick="showMsg();">견적/제작</button> </div>
	
  </div>
  -->

<?php   } ?>

 <form id="board_form" name="board_form" method="post" >

	<!-- 아래 dialog 태그 영역이 메시지 창 -->
	<?php if($chkMobile==false) { ?>
	<dialog id="myMsgDialog" style="position: fixed;  top: 20%;  left: 30%;" >
    <?php } else  { ?>
	<dialog id="myMsgDialog"  >
	<?php }  ?>
		<!-- 문의사항 등록 section-->
		<section class="py-0">   				 
		<div class="container">
			<div class="input-form-backgroud row">
			  <div class="input-form col-md-12 mx-auto">
				<h3 class="mb-3 text-center">견적/제작 문의</h3>
				<form class="validation-form" novalidate>	    

				  <div class="row">
					<div class="col-md-6 mb-3">
					  <label for="name">성함</label>
					  <input type="text" class="form-control" id="name" name="name" placeholder="" value="" required>
					  <div class="invalid-feedback">
						이름을 입력해주세요.
					  </div>
					</div>
					  <div class="col-md-6 mb-3">
					  <label for="tel">연락처 </label>
					  <input type="text" class="form-control" id="tel" name="tel" placeholder="" value="" required>
					  <div class="invalid-feedback">
						전화번호를 입력해주세요.
					  </div>
					</div>
				  </div>		
				  
				  <div class="row">
					<div class="col-md-6 mb-3">
					<label for="email">답변 받으실 Email</label>
					<input type="email" class="form-control" id="email" name="email" placeholder="이메일@gmail.com" >
					<div class="invalid-feedback">
					  이메일을 입력해주세요.
					</div>
				  </div>	
				   <!--
					  <div class="col-md-6 mb-3">
						  <label for="password">비밀번호(열람 및 수정시 사용)</label>
					  <input type="text" class="form-control" id="password" name="password" placeholder="" value="" required>
					  <div class="invalid-feedback">
						비밀번호를 입력해주세요.
					  </div>
					</div> 
					-->
				  </div>						
				  

				  <div class="mb-3">
					<label for="address">문의 내용</label>
					<textarea type="text" class="form-control" rows="5" id="question"  name="question"  required> </textarea>
					
				
					
				<div class="row d-flex mt-2 mb-1 justify-content-center">  
				<label> 파일첨부(10M 이하) PDF, 이미지 </label>
				<input type="file" id="file" name="file" class="file-input" onchange="this.value" accept="image/*"> 
				
				</div>					
					
					
							
				<div class="row mt-1 mb-1 justify-content-center">  	 		 			
					<div id ="displayfile" class="d-flex mt-3 mb-1 justify-content-center" style="display:none;">  	 		 					
						 
					</div>			
				</div>

				  <div class="mb-4"></div> 	
				  <div class="mb-4 justify-content-center text-center">	
						<input type="button" id="cButton"  value=" 문의하기 전송 " > &nbsp;&nbsp;&nbsp;
						<input type="button" id="mButton" onclick="closeMsg();" value=" 창닫기 " >
						</div> 	
				</form>
			  </div>
			</div>
		</div>
		</section>			
		
	</dialog>

	<!-- 메시지 접수 dialog 태그 영역이 메시지 창 -->
	<dialog id="closeDialog" style="position: fixed;  top: 30%;  left: 40%;" >

		<!-- 문의사항 등록 section-->
		<section class="py-0">   				 
		<div class="container">
			<div class="input-form-backgroud row">
			  <div class="input-form col-md-12 mx-auto">
				<h3 class="mb-3 text-center"></h3>
				<h3 class="mb-3 text-center">문의가 접수되었습니다.</h3>
				
				  <div class="mb-4"></div> 	
				  <div class="mb-4 justify-content-center text-center">							
						<input type="button" id="closeButton" onclick="closeDialog();" value=" 창닫기 " >
						</div> 					
			  </div>
			</div>
		</div>
		</section>			
		
	</dialog>

</form>

<main>

<!-- 메뉴 바로 밑 본문 첫번째 단락 -->		

<div class="w-100 overflow-hidden bg-gray-10">	  
	<div class="container position-relative">
		<div class="row">
			<div class="col-lg-12 py-vh-5 position-relative" data-aos="fade-right">				 
				<div class="d-flex justify-content-center mt-1">
					<a href="index2.php" class="btn btn-dark btn-xl me-1 rounded-2 my-5"> 통합정보 들어가기</a>
				</div>
			</div>
		</div>
	</div>
</div>
	  
</main> 


<!-- DH aboutus Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="aboutus">
	<div class="container">       
		<div class="row d-flex justify-content-between align-items-center">
			<div class="col-lg-12">  
				<div class="col-md-12">
					<div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dh1.jpg);--bs-aspect-ratio: 60%;">
		  </div>
		</div>
	</div>		
</div>
</div>	
</div>	
<!-- 특징설명 end -->

<a name="aboutus"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  		

<!-- 품질인정컨설틸  start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="qcwarranty1">
  <div class="container">       
<div class="row d-flex justify-content-between align-items-center">
  <div class="col-lg-12">  
    <div class="col-md-12">
      <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/qcw1.jpg);--bs-aspect-ratio: 55%;">
      </div>
    </div>
	</div>		
  </div>
		</div>	
</div>	
<!-- 특징설명 end -->

<a name="qcwarranty1"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="qcwarranty2">
  <div class="container">       
<div class="row d-flex justify-content-between align-items-center">
  <div class="col-lg-12">  
    <div class="col-md-12">
      <div class="shadow ratio ratio rounded bg-cover bp-center"  data-aos="fade-right" style="background-image: url(img/qcw2.jpg);--bs-aspect-ratio: 55%;">
      </div>
    </div>
	</div>		
  </div>
		</div>	
</div>	
<!-- 특징설명 end -->

<a name="qcwarranty2"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 


<!-- DH 특징 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhfeatures">
	<div class="container">       
		<div class="row d-flex justify-content-between align-items-center">
			<div class="col-lg-12">  
				<div class="col-md-12">
					<div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dh_features1.jpg);--bs-aspect-ratio: 60%;">
						</div>
		</div>
	</div>		
</div>
</div>	
</div>	
<!-- 특징설명 end -->

<a name="dhfeatures"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 

<!-- DH 모터성적서 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dh_warranty">
	<div class="container">       
		<div class="row d-flex justify-content-between align-items-center">
			<div class="col-lg-12">  
				<div class="col-md-12">
					<div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dh_warranty.jpg);--bs-aspect-ratio: 56%;">
						</div>
					</div>
				</div>		
			</div>
		</div>	
	</div>	
	<!-- 모터성적서 end -->
	
	<a name="dh_warranty"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 
<!-- 모터제원 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhdetails">
  <div class="container">       
<div class="row d-flex justify-content-between align-items-center">
  <div class="col-lg-12">  
    <div class="col-md-12">
      <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/homepage_details1.jpg);--bs-aspect-ratio: 62%;">
      </div>
    </div>
	</div>		
  </div>
		</div>	
</div>	

<a name="dhdetails"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  	

<!--모터 특성 1 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhfeatures1">
<div class="container">       
<div class="row d-flex justify-content-between align-items-center">
 <div class="col-lg-12">  
   <div class="col-md-12">
      <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/homepage_details2.jpg);--bs-aspect-ratio: 61%;">
      </div>
    </div>
 </div>		
</div>
</div>	
</div>	

<a name="dhfeatures1"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  	

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhfeatures2">
	<div class="container">       
		<div class="row d-flex justify-content-between align-items-center">
			<div class="col-lg-12">  
				<div class="col-md-12">
					<div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/homepage_details3.jpg);--bs-aspect-ratio: 58%;">
						</div>
					</div>
				</div>		
			</div>
		</div>	
	</div>	

<a name="dhfeatures2"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  	

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhfeatures3">
  <div class="container">       
<div class="row d-flex justify-content-between align-items-center">
  <div class="col-lg-12">  
    <div class="col-md-12">
      <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/homepage_details44.jpg);--bs-aspect-ratio: 60%;">
      </div>
    </div>
	</div>		
  </div>
		</div>	
</div>	
<!-- 특징설명 end -->


<a name="dhfeatures3"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  		


<!-- 연동제어기 콘트롤러 특징 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="wire_controller_detail">
	<div class="container">       
		<div class="row d-flex justify-content-between align-items-center">
			<div class="col-lg-12">  
				<div class="col-md-12">
					<div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/wire_controller_detail1.jpg);--bs-aspect-ratio: 58%;">
						</div>
					</div>
				</div>		
			</div>
		</div>	
	</div>	
	<!-- 특징설명 end -->
<a name="wire_controller_detail"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  		

<!-- 연동제어기 콘트롤러 특징 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="wire_controller_detail1">
	<div class="container">       
		<div class="row d-flex justify-content-between align-items-center">
			<div class="col-lg-12">  
				<div class="col-md-12">
					<div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/wire_controller_detail2.jpg);--bs-aspect-ratio: 58%;">
						</div>
					</div>
				</div>		
			</div>
		</div>	
	</div>	
	<!-- 특징설명 end -->
    <a name="wire_controller_detail1"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  		
	
	<!-- 연동제어기 콘트롤러 특징 Start -->
	<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="wire_controller_detail2">
		<div class="container">       
			<div class="row d-flex justify-content-between align-items-center">
				<div class="col-lg-12">  
					<div class="col-md-12">
						<div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/wire_controller_detail3.jpg);--bs-aspect-ratio: 58%;">
							</div>
						</div>
					</div>		
				</div>
			</div>	
		</div>	
	<!-- 특징설명 end -->

<a name="wireless_controller_detail"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  		


<!-- 무선 연동제어기 설정방법 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="wire_controller_detail3">
  <div class="container">       
<div class="row d-flex justify-content-between align-items-center">
  <div class="col-lg-12">  
    <div class="col-md-12">
      <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/controllermenual1.jpg);--bs-aspect-ratio: 58%;">
      </div>
    </div>
	</div>		
  </div>
		</div>	
</div>	
<!-- 무선 연동제어기 설정방법 end -->

	
<a name="wire_controller_detail3"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  

<!-- 연동제어기 메뉴얼2 start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="wire_controller_detail4">
	<div class="container">       
		<div class="row d-flex justify-content-between align-items-center">
			<div class="col-lg-12">  
				<div class="col-md-12">
					<div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/controllermenual2.jpg);--bs-aspect-ratio: 58%;">
						</div>
					</div>
				</div>		
			</div>
		</div>	
</div>	
	
<a name="wire_controller_detail4"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  
<!-- 연동제어기 메뉴얼3 start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="wire_controller_detail5">
  <div class="container">       
<div class="row d-flex justify-content-between align-items-center">
  <div class="col-lg-12">  
    <div class="col-md-12">
      <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/controllermenual3.jpg);--bs-aspect-ratio: 58%;">
      </div>
    </div>
	</div>		
  </div>
		</div>	
</div>	
<!-- 연동제어기 메뉴얼 end -->

<!-- 방범모터 제원 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhprevent_detail">
    <div class="container">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-lg-12">
                <div class="col-md-12">
                    <div class="shadow ratio ratio rounded bg-cover bp-center"
                         data-aos="fade-right"
                         style="background-image: url(img/dhprevent_detail.jpg);--bs-aspect-ratio: 58%;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<a name="dhprevent_detail"></a> <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->  	

<!-- 방범모터 설정방법 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhprevent_settings">
    <div class="container">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-lg-12">
                <div class="col-md-12">
                    <div class="shadow ratio ratio rounded bg-cover bp-center"
                         data-aos="fade-right"
                         style="background-image: url(img/dhprevent_settings.jpg);--bs-aspect-ratio: 58%;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<a name="dhprevent_settings"></a> 

<!-- 방범모터 스펙 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhprevent_spec1">
    <div class="container">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-lg-12">
                <div class="col-md-12">
                    <div class="shadow ratio ratio rounded bg-cover bp-center"
                         data-aos="fade-right"
                         style="background-image: url(img/dhprevent_spec1.jpg);--bs-aspect-ratio: 58%;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<a name="dhprevent_spec"></a> 


<!-- 방범모터 스펙 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhprevent_spec2">
    <div class="container">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-lg-12">
                <div class="col-md-12">
                    <div class="shadow ratio ratio rounded bg-cover bp-center"
                         data-aos="fade-right"
                         style="background-image: url(img/dhprevent_spec2.jpg);--bs-aspect-ratio: 58%;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 방범모터 스펙 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhprevent_spec3">
    <div class="container">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-lg-12">
                <div class="col-md-12">
                    <div class="shadow ratio ratio rounded bg-cover bp-center"
                         data-aos="fade-right"
                         style="background-image: url(img/dhprevent_spec3.jpg);--bs-aspect-ratio: 58%;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- 방범모터 구성품 Start -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="dhprevent_parts">
    <div class="container">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-lg-12">
                <div class="col-md-12">
                    <div class="shadow ratio ratio rounded bg-cover bp-center"
                         data-aos="fade-right"
                         style="background-image: url(img/dhprevent_parts.jpg);--bs-aspect-ratio: 58%;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<a name="dhprevent_parts"></a> 

<a name="dhc_motor"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 
<!-- DH-C OHD 모터 -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dhc_motor.jpg);--bs-aspect-ratio: 55%;">
		  </div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-C OHD 모터 end -->

<!-- DH-C OHD 인증서 -->
<a name="dhc_cert"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dhc_cert.jpg);--bs-aspect-ratio: 55%;">
		  </div>
		</div>
	   </div>		
	</div>
</div>	
</div>	
<!-- DH-C OHD 인증서 end -->

<!-- DH-C OHD 제어기 -->
<a name="dhc_controller"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dhc_controller.jpg);--bs-aspect-ratio: 55%;">
		  </div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-C OHD 제어기 end -->

<!-- DH-C OHD 제원표 -->
<a name="dhc_spec"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden">
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dhc_spec.jpg);--bs-aspect-ratio: 55%;">
		  </div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-C OHD 제원표 end -->

<!-- DH-C OHD 배선도 -->	
<a name="dhc_wiring"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dhc_wiring.jpg);--bs-aspect-ratio: 55%;"></div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-C OHD 배선도 end -->

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden">
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dhc_wiring1.jpg);--bs-aspect-ratio: 55%;"></div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-C OHD 배선도 end -->

<!-- DH-C OHD 오류코드 -->	
<a name="dhc_error"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dhc_error.jpg);--bs-aspect-ratio: 55%;"></div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-C OHD 오류코드 end -->

<a name="dha_motor"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 
<!-- DH-A OHD 모터 -->

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dha_motor.jpg);--bs-aspect-ratio: 55%;">
		  </div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-A OHD 모터 end -->

<!-- DH-A OHD 모터2 -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden">
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dha_motor2.jpg);--bs-aspect-ratio: 55%;">
		  </div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-A OHD 모터2 end -->

<!-- DH-A OHD 인증서 -->
<a name="dha_cert"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dha_cert.jpg);--bs-aspect-ratio: 55%;">
		  </div>
		</div>
	   </div>		
	</div>
</div>	
</div>	
<!-- DH-A OHD 인증서 end -->

<!-- DH-A OHD 제어기 -->
<a name="dha_controller"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dha_controller.jpg);--bs-aspect-ratio: 55%;">
		  </div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-A OHD 제어기 end -->

<!-- DH-A OHD 배선도 -->	
<a name="dha_wiring"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 --> 

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dha_wiring.jpg);--bs-aspect-ratio: 55%;"></div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-A OHD 배선도 end -->

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden">
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dha_wiring1.jpg);--bs-aspect-ratio: 55%;"></div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-A OHD 배선도 end -->

<!-- DH-A OHD 오류코드 -->	
<a name="dha_error"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->

<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/dha_error.jpg);--bs-aspect-ratio: 55%;"></div>
		</div>
	   </div>		
    </div>
</div>	
</div>	

<!-- DH-A OHD 오류코드 end -->
<!-- DH-와이어원단 -->	
<a name="slat_wire"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/slat_wire.jpg);--bs-aspect-ratio: 55%;"></div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-와이어원단 end -->

<!-- DH-가스켓/버미글라스/제연용원단 -->	
<a name="slat_gas"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
  <div class="container">       
	<div class="row d-flex justify-content-between align-items-center">
	  <div class="col-lg-12">  
		<div class="col-md-12">
		  <div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/slat_gas.jpg);--bs-aspect-ratio: 55%;"></div>
		</div>
	   </div>		
    </div>
</div>	
</div>	
<!-- DH-가스켓/버미글라스/제연용원단 end -->

<!-- DH-원단성적서 -->	
<a name="slat_cert"></a>	     <!-- 위치를 여기로 해야 자연스럽게 윗 여백을 확보하며 책갈피 기능을 수행함 -->
<div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" >
	<div class="container">       
		<div class="row d-flex justify-content-between align-items-center">
		<div class="col-lg-12">  
			<div class="col-md-12">
			<div class="shadow ratio ratio rounded bg-cover bp-center" data-aos="fade-right" style="background-image: url(img/slat_cert.jpg);--bs-aspect-ratio: 55%;"></div>
			</div>
		</div>		
		</div>
	</div>	
</div>	
<!-- DH-원단성적서 end -->
  
  <div class="col-12">
    <a href="#" class="btn btn-warning btn-xl shadow me-1 mt-4" data-aos="fade-down">사이트 둘러보기</a>
  </div>
</div>

    </div>
  </div>  
   
  
</div>
	

    </div>
  </div>
</div>

<footer>
  <div class="container small border-top">
    <div class="row py-5 d-flex justify-content-between">

<div class="col-12 col-lg-6 col-xl-3 border-end p-5">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-layers-half" viewbox="0 0 16 16">
    <path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8 .264 9.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.813 8l2.922-1.559a.5.5 0 0 0 0-.882l-7.5-4zM8 9.433 1.562 6 8 2.567 14.438 6 8 9.433z"/>
  </svg>
  <address class="text-secondary mt-3">
    <strong>주소</strong><br>
   경기도 김포시 통진읍 월하로 485 <br>
    Email : dhm2024@naver.com <br>
    <abbr title="Phone">P:</abbr>
    010-3966-2024
  </address>
</div>
<div class="col-12 col-lg-6 col-xl-3 border-end p-5">
  <h3 class="h6 mb-3">(주) 대한 </h3>
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link link-secondary ps-0" aria-current="page" href="#aboutus">회사소개</a>
    </li>    
  </ul>
</div>
<div class="col-12 col-lg-6 col-xl-3 border-end p-5">
  <h3 class="h6 mb-3">제품(Products)</h3>
  <ul class="nav flex-column">
</div>
<div class="col-12 col-lg-6 col-xl-3 p-5">  
</div>
</div>
</div>

<div class="container text-center py-3 small">Made by <a href="https://templatedeck.com" class="link-fancy" target="_blank">templatedeck.com</a>
</div>
</footer>
 
  <div class="container">  
	<!-- 80%size Modal at Center -->
	<div class="modal modal-center fade" id="my80sizeCenterModal" tabindex="-1" role="dialog" aria-labelledby="my80sizeCenterModalLabel">
	  <div class="modal-dialog modal-80size modal-center" role="document">
		<div class="modal-content modal-80size">
		  <div class="modal-header justify-content-center">			
			<h4 class="modal-title justify-content-center" id="myModalLabel">입력 확인 </h4>
		  </div>
		  <div class="modal-body">
			빈칸을 확인해 주세요!
		  </div>
		  <div class="modal-footer">
			<button  id="closeModalBtn" type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
		  </div>
		</div>
	  </div>
	</div>  
</div>  
  
</div>
</div>

 </body>

</html>

<script src="js/aos.js"></script>
 <script>
 
// 기본 위치(top)값
var floatPosition = parseInt($(".sideBanner").css('top'))

// scroll 인식
$(window).scroll(function() {
  // 모바일에선 나타나지 않게 하기
  let chkMobile = '<?php echo $chkMobile; ?>';
  if(chkMobile !== '1') {
    // 현재 스크롤 위치
    var currentTop = $(window).scrollTop();
    var bannerTop = currentTop + floatPosition + "px";

    //이동 애니메이션
    $(".sideBanner").stop().animate({
      "top" : bannerTop
    }, 500);
  }
}).scroll();
 
 
 AOS.init({
   duration: 800, // values from 0 to 3000, with step 50ms
 });

  let scrollpos = window.scrollY
  const header = document.querySelector(".navbar")
  const header_height = header.offsetHeight

  const add_class_on_scroll = () => header.classList.add("scrolled", "shadow-sm")
  const remove_class_on_scroll = () => header.classList.remove("scrolled", "shadow-sm")

  window.addEventListener('scroll', function() {
    scrollpos = window.scrollY;

    if (scrollpos >= header_height) { add_class_on_scroll() }
    else { remove_class_on_scroll() }

    // console.log(scrollpos)
  })
  


$(document).ready(function(){
		
				 
	$("#closeModalBtn").click(function(){ 
		$('#my80sizeCenterModal').modal('hide');
		showMsg();
		
	});			
	
// 전체화면에 꽉찬 이미지 보여주는 루틴
		$(document).on("click","img",function(){
			var path = $(this).attr('src');
			showImage(path);
			// alert(path);
			
		});//end click event

		
		$("#floatingInput").click(function(){  
		  
			showMsg();
			
		});//end click event	
		
        // 메일전송버튼 눌렀을때
		$("#cButton").click(function(){  
		
		if($('#name').val() == '' || $('#tel').val() == '' || $('#email').val() == '' || $('#question').val() == '' )
		{
			closeMsg();
		    $('#my80sizeCenterModal').modal('show');
		}
		else
		{			
	
			var form = $('#board_form')[0];  	    
		  // Create an FormData object          
		   var data = new FormData(form); 
					   // data저장을 위한 ajax처리구문
			$.ajax({
				enctype: 'multipart/form-data',  // file을 서버에 전송하려면 이렇게 해야 함 주의
				url: "/PHPMailer/sendmail.php",
				processData: false,    
				contentType: false,      
				cache: false,           
				timeout: 600000, 							
				type: "post",		
				data: data,		
				success : function( data ){
				 // alert(data);
				 	var dialog = document.getElementById("closeDialog");
					dialog.showModal();				 
				},
				error : function( jqxhr , status , error ){
					console.log( jqxhr , status , error );
				} 			      		
			   });		
		
				closeMsg();
		   }
			
			
		});//end click event
		
		function showImage(fileCallPath){		    
		   	    
		    $(".bigPicture")
		    .html("<img src='"+fileCallPath+"' >")
		    .animate({width:'100%', height: '100%'}, 1000);
			
			$('#myModal').modal('show'); 
		    
		  }//end fileCallPath
		  
		$("#myModal").on("click", function(e){
		    $(".bigPicture").animate({width:'0%', height: '0%'}, 1000);
		    setTimeout(function(){
		       $('#myModal').modal('hide'); 
		    }, 1000);
		  }); 
		    		  
		// 파일선택시 화면에 리스트 출력해주기  	  
		  
// end of 이미지 꽉찬 화면 보여주기
}); 
  
  

function showMsg(){
	var dialog = document.getElementById("myMsgDialog");
	dialog.showModal();
}

function closeMsg(){
	var dialog = document.getElementById("myMsgDialog");
	dialog.close();
}
function closeDialog(){
	var dialog = document.getElementById("closeDialog");
	dialog.close();
}
		
function sendMsg(){
	var dialog = document.getElementById("myMsgDialog");
	dialog.close();
}
  
// 엔터 키 이벤트 처리
document.addEventListener('keyup', function(event) {
  // keyCode 13은 엔터 키를 의미합니다.
  if (event.keyCode === 13) {
    event.preventDefault(); // 기본 동작(새로고침)을 방지합니다.
    document.getElementById('loginBtn').click(); // 버튼 클릭 이벤트를 실행합니다.
  }
});
  
 
ajaxRequest_sheet = null;	 
  
$(document).ready(function(){
	
	$("#loginBtn").click(function(){ 
	
     const home = $('#loginBtn').data('home');
		
	  
	  if (ajaxRequest_sheet !== null) {
		ajaxRequest_sheet.abort();
	  }

	 // data 전송해서 php 값을 넣기 위해 필요한 구문
		ajaxRequest_sheet = $.ajax({
			url: '/login/login_confirm.php',
			type: "post",		
			data: {
					uid: $('#uidInput').val(),
					upw: $('#inputPassword').val()
				},

			dataType:"json",
			success : function( data ){						
				    // console.log(data);				
						if (data["error"] === '') {
							if (data["redirect"]) {
								// 서버에서 redirect URL을 보냈다면, 그곳으로 이동
								location.href = data["redirect"];
							} else {
								location.href = '/index2.php';  // 기본 통합사이트로 이동
							}
						} else {
							// sweetalert를 사용하여 오류 메시지 표시
							Swal.fire({
								icon: 'error',
								title: '로그인 실패',
								text: data["error"], // 서버에서 전송된 오류 메시지
								confirmButtonColor: '#3085d6',
								confirmButtonText: '확인'
							});
						}

				},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
						} 			      		
		   });				  
	});		
		
	$("#logoutBtn").click(function(){ 	
			location.href = '/login/logout.php';		  // log out
	});		
	
	$("#loginIconBtn").click(function(){		
		const home = '<?php echo $home; ?>';	
		
		// console.log(name);
		
		if( home==='1')
			location.href = 'index2.php';		  
			else		
			   $('#loginModal').modal('show');
    
	});
	
	$("#close_loginModal").click(function(){						
	   $('#loginModal').modal('hide');    
	});
			
    // 로그인 모달창 닫기				 
	$("#modal_loginCloseBtn").click(function(){ 
		$('#modal_login').modal('hide');
	});
				
				 
	$("#closeModalBtn").click(function(){ 
		$('#my80sizeCenterModal').modal('hide');
		showMsg();
		
	});
});
		
  
function popup_board1()
{
 popupCenter('board1/list.php' , '법규자료실', 1400, 800);	
}	
  
function popup_board2()
{
 popupCenter('board2/list.php' , '자주 묻는 질문(FAQ)', 1400, 800);	
}	 
function popup_board3()
{
 popupCenter('board3/list.php' , '문의 게시판', 1400, 800);	
}	
  
</script>

<!-- 아이디 입력 하이픈 금지시키고 영문자와 숫자만 입력가능 -->
<script>
$(document).ready(function(){
var input = document.getElementById('uidInput');
// console.log(input);  // 콘솔에 입력 필드 요소 출력
if (input) {
  input.addEventListener('input', function (e) {
	  var value = e.target.value;
	  var validPattern = /^[A-Za-z0-9]+$/;
	  if (!validPattern.test(value)) {
		  e.target.value = value.slice(0, -1);
	  }
  });
} else {
  console.log("입력 필드를 찾을 수 없습니다.");
}
});
</script>
			