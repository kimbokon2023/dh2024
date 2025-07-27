<?php

$DB = "dbchandj";
$WebSite = "https://dh2024.co.kr/";

if(isset($id)) $id=$_REQUEST["uid"];
if(isset($pw)) $pw=$_REQUEST["upw"];

?>

<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" >
	    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" ></script> 
	
	
<link rel="icon" type="image/x-icon" href="../favicon.ico">   <!-- 33 x 33 -->
<link rel="shortcut icon" type="image/png" href="../favicon.png">    <!-- 144 x 144 -->
<link rel="apple-touch-icon" type="image/png" href="../favicon.png">

    <title>(주)대한 DH모터</title>

  </head>
 <style> 
		  html,body {
		  height: 100%;
		}
</style>
<div class="container h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
	<div class="col-1"></div>
        <div class="col-10 text-center">
			<div class="card align-middle" style="width:20rem; border-radius:20px;">
				<div class="card" style="padding:15px;margin:10px;">
					<h4 class="card-title text-center" style="color:#113366;">(주)대한 DH모터 시스템</h4>
				</div>	
				<div class="card-body text-center">
			  <form class="form-signin" method="post" action="login_result.php">
				<h5 class="form-signin-heading">로그인 정보를 입력하세요</h5>
				<label for="inputEmail" class="sr-only">Your ID (영문자 or 숫자) </label>
				<input type="text"  id="uidInput" name="uid" class="form-control" placeholder="Your ID" required autofocus ><br>
				<label for="inputPassword" class="sr-only">Password</label>
				<input type="password"   name="upw" class="form-control" placeholder="Password" required><br>
				  <!--     <div class="checkbox">
			<label>
					<input type="checkbox" value="remember-me"> 기억하기
				  </label> 
				</div> -->
				<button id="btn-Yes" class="btn btn-lg btn-primary btn-block" type="submit">로 그 인</button>
			  </form>			  
				</div>
       	   	</div>
			</div>		
				<div class="col-1"></div>
	  </div>

	</div>	
	
  <!-- JavaScript 코드를 마지막에 배치 -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var input = document.getElementById('uidInput');
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

</body>
</html>