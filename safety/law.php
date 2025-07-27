<?php
if(!isset($_SESSION))      
	session_start(); 
if(isset($_SESSION["DB"]))
	$DB = $_SESSION["DB"] ;	
	$level= $_SESSION["level"];
	$user_name= $_SESSION["name"];
	$user_id= $_SESSION["userid"];	
   
   
 $mcno=$_REQUEST["mcname"];
 
 if(!isset($_SESSION["level"]) || $level>8) {
          /*   alert("관리자 승인이 필요합니다."); */
		 $_SESSION["url"]='http://8440.co.kr/safetycard/laser.php?mcno=' + $mcno ; 		  		 
		 sleep(1);
	          header("Location:http://8440.co.kr/login/login_form.php"); 
         exit;
   }


 ?>
 
 <?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?> 

<meta property="og:type" content="미래기업 통합정보시스템">
<meta property="og:title" content="위험성평가 전산시스템">
<meta property="og:url" content="8440.co.kr">
<meta property="og:description" content="정확한 업무처리를 위한 필수사이트!">
<meta property="og:image" content="http://8440.co.kr/img/miraethumbnail.jpg"> 

<title> 사업장 위험성평가에 관한 지침 </title> 
</head>		 

  <style>
    .image-container {
      width: 100%;
      height: 300px;
      overflow: hidden;
    }

    .image-container img {
      object-fit: 20%;
      object-position: 0px 0px;
    }
  </style>

<body id="page-top">

<?php include $_SERVER['DOCUMENT_ROOT'] . '/myheader.php'; ?>   
	
	<!-- Related items section-->
	<section class="py-5 bg-light">
	<div class="container px-1 px-lg-1 mt-1">
		<div class="row justify-content-center">
		<h2 > 사업장 위험성평가에 관한 지침 </h2>
		</div>
		<div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
          [시행 2023. 5. 22.] [고용노동부고시 제2023-19호, 2023. 5. 22., 일부개정]
		</div>
     </div>
	</section>
	 
    <div class="container-fluid w-90 " >		
             <iframe src="https://www.law.go.kr/%ED%96%89%EC%A0%95%EA%B7%9C%EC%B9%99/%EC%82%AC%EC%97%85%EC%9E%A5%EC%9C%84%ED%97%98%EC%84%B1%ED%8F%89%EA%B0%80%EC%97%90%EA%B4%80%ED%95%9C%EC%A7%80%EC%B9%A8" width="100%" height="600px;"></iframe>		
	</div>
		
<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
	$pdo = db_connect();
?>		
		
	<!-- ajax 전송으로 DB 수정 -->
	<? include "../formload.php"; ?>	
		
	<!-- Footer-->
	<? include "../shop/footer.php" ?>  		
			<!-- Core theme JS-->
       
    </body>
	
</html>
  
  
<script>

function choiceMC(qrcode, name){

var link ;
link = 'http://8440.co.kr/safetycard/laser.php?qrcode=' + qrcode + '&name=' + name;       

window.open(link, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=50,left=50,width=1700,height=850");
	
}	


 </script>