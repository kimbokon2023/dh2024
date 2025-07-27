<?php

session_start();

   $level= $_SESSION["level"];
 if(!isset($_SESSION["level"]) || $level>10) {
          /*   alert("관리자 승인이 필요합니다."); */
		 sleep(2);
         header ("Location:http://5130.co.kr/login/logout.php");
         exit;
   }
   
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header ("Pragma: no-cache"); // HTTP/1.0
header("Expires: 0"); // rfc2616 - Section 14.21   
//header("Refresh:0");  // reload refresh   

 if(isset($_REQUEST["menu"]))   // 선택한 메뉴에 대한 저장변수 1은 철재방화 절단치수
	 $menu=$_REQUEST["menu"];
    else
		  $menu=0;


function getfile()
{  
$myfile = fopen("guiderail.txt", "w") or die("Unable to open file!");
$txt=$item_sel;
fwrite($myfile, $txt);
fclose($myfile);
} 


function insert() {
   $level= $_SESSION["level"];
 if(!isset($_SESSION["level"]) || $level>10) {
		 sleep(1);
         header ("Location:http://5130.co.kr/login/logout.php");
         exit;
   }
      $motor1 = $_REQUEST["motor1"];
      $motor2 = $_REQUEST["motor2"];
      $motor3 = $_REQUEST["motor3"];
      $motor4 = $_REQUEST["motor4"];
      $motor5 = $_REQUEST["motor5"];
      $motor6 = $_REQUEST["motor6"];
      $motor7 = $_REQUEST["motor7"];
      $motor8 = $_REQUEST["motor8"];
      $motor9 = $_REQUEST["motor9"];
      $motor10= $_REQUEST["motor10"];

 require_once("../lib/mydb.php");
 $pdo = db_connect();
			  
	/* 	print "접속완료"	  ; */
     try{
        $pdo->beginTransaction();   
        $sql = "update chandj.settings set motor1=?,motor2=?,motor3=?,motor4=?,motor5=?,motor6=?,motor7=?,motor8=?,motor9=?,motor10=?" ;
    
     $num=1; 
     $stmh = $pdo->prepare($sql); 
     $stmh->bindValue(1, $motor1, PDO::PARAM_STR);  	 
     $stmh->bindValue(2, $motor2, PDO::PARAM_STR);  	 
     $stmh->bindValue(3, $motor3, PDO::PARAM_STR);  	 
     $stmh->bindValue(4, $motor4, PDO::PARAM_STR);  	 
     $stmh->bindValue(5, $motor5, PDO::PARAM_STR);  	 
     $stmh->bindValue(6, $motor6, PDO::PARAM_STR);  	 
     $stmh->bindValue(7, $motor7, PDO::PARAM_STR);  	 
     $stmh->bindValue(8, $motor8, PDO::PARAM_STR);  	 
     $stmh->bindValue(9, $motor9, PDO::PARAM_STR);  	 
     $stmh->bindValue(10, $motor10, PDO::PARAM_STR);  	 
     $stmh->bindValue(11, $num, PDO::PARAM_STR);  	 
	     	   //고유키값이 같나?의 의미로 ?로 num으로 맞춰야 합니다.
     $stmh->execute();
     $pdo->commit(); 
        } catch (PDOException $Exception) {
           $pdo->rollBack();
           print "오류: ".$Exception->getMessage();
       } 
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <link rel="stylesheet" type="text/css" href="../css/common.css">
 <link rel="stylesheet" type="text/css" href="../css/order.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script>
<?php
function displayDB()
{
  // $_POST["text1"]=$result1;
 include "./insertdata.php";   
}
?>

$(function(){
	$("#exityesno").hide();	    	
    $("#exititem").change(function(){
			$("#exityesno").hide();	    

        if ( this.value == '없음' ) {
			$("#hinge").val("승리");				
			$("#hingenum").val("");				
                        return false;						
          		}
        if ( this.value == '중앙' ) {
                        exitcenter();						
          		}				
        if ( this.value == '좌측' ) {
                        exitleft();						
          		}	
        if ( this.value == '우측' ) {
                        exitright();						
          		}					
	});
	
    $("#hinge").change(function(){
 
        if ( this.value == '승리' ) {
                   $("#hingenum").val("85");	        
          		}			
        if ( this.value == '태영' ) {
                   $("#hingenum").val("70");	        
          		}							
        if ( this.value == '굴비' ) {
                   $("#hingenum").val("60");	        
          		}			
        if ( this.value == '대신' ) {
                   $("#hingenum").val("50");	        
          		}					
          							
	});	
	
  
$("#calsize").click(function(){
  var a = 0;
  var b = 0;
  var c = 0;
  var d = 0;
  var e = 0;
  var f = 0;
  var g = 0;
  var h = 0;
  var i = 0;   //쪽바계산용
  
  if($("#intervalnum").val()=='' && $("#exititem").val()!='없음') {
	    alert("띄울 치수를 입력해 주세요.");
		return false;
  }
  
  a=Math.ceil($("#cutheight").val() / 71) ;
  b=$("#cutwidth").val() ;
  e=$("#cutheight").val() ;
  f=Number($("#hingenum").val()) ;
  g=$("#intervalnum").val() ;
  
  c= a-29;   // 상바계산
  d= Math.floor((b-(900+f))/2);                // 비상문이 중앙일때 쪽바 폭 계산
  h= 29*2;                // 비상문이 중앙일때 쪽바 매수

  if($("#exititem").val()=='없음') {
  b=$("#cutwidth").val() ;
  $("#result2").hide();  
  $("#result3").hide();  
  $("#result1").show();
  $("#result1").text(" 절단사이즈 : " + b + "(mm) X  " + a + "(매)");
  }
  if($("#exititem").val()=='중앙') {
  b=$("#cutwidth").val() ;
  $("#result3").hide();  
  $("#result1").show();
  $("#result2").show();
  $("#result1").html("상바 절단사이즈 : " + b + "(mm) X " + c + "(매),  <br> 쪽바 절단사이즈 : " + d + "(mm) X " + h + "(매), <br> 비상문바 : 900(mm) x 29(매)" );
  }  
if($("#exititem").val()=='좌측' || $("#exititem").val()=='우측' ) {
  i=b-f-g-900; 
  b=$("#cutwidth").val() ;
  $("#result1").show();
  $("#result2").hide();    
  $("#result3").show();
  $("#result1").html("상바 절단사이즈 : " + b + "(mm) X " + c + "(매),  <br> 1번 쪽바 절단사이즈 : " + g + "(mm) X 29(매), <br> 2번 쪽바 절단사이즈 : " + i + "(mm) X 29(매),  <br> 비상문바 : 900(mm) x 29(매)" );
  }    
});	

 });
 
  function exitcenter() {
  			$("#exityesno").show();	  
			$("#hinge").val("승리");				
			$("#hingenum").val("85");				
            $("#intervalnum").val("없음");			
  }
  function exitleft() {
  			$("#exityesno").show();	  
						$("#hinge").val("승리");				
			$("#hingenum").val("85");	
            $("#intervalnum").val("");				
  }
  function exitright() {
  			$("#exityesno").show();	
						$("#hinge").val("승리");				
			$("#hingenum").val("85");	
            $("#intervalnum").val("");							
  }  

</script>  

  <style>
  .fakeimg {
    height: 200px;
    background: #aaa;
  }
  </style>
</head>

<body>
<div id="wrap">  
   <div id="header">
   <?php include "../lib/top_login2.php"; ?>
   </div>  
   <div id="menu">
   <?php include "../lib/top_menu2.php"; ?>
   </div>  
  <div id="content">
  <div id="work_col2">  

<div id="showgif"> <img src="../img/cutegislat.gif"> </div>
<br><br>
<form name="board_form" id="board_form"  method="post"  action="egimake_list.php?callname=<?=$callname?>&cutwidth=<?=$cutwidth?>&cutheight=<?=$cutheight?>
                      &exititem=<?=$exititem?>&intervalnum=<?=$intervalnum?>&hingenum=<?=$hingenum?>"  enctype="multipart/form-data">  
<div id="exitall">
<div id="exitcontent">
<div id="r5"> 부호 </div>
<div id="r6"> <input id="callname" name="callname"  type="text" size="10" placeholder="부호" >  </div>
<div id="r0"> 절단사이즈 기준> </div>

<div id="r1"> 가로폭 :  </div> 
<div id="r2"> <input id="cutwidth" name="cutwidth" type="text" size="3" placeholder="width" value="5000" required > </div>
<div id="r3"> 세로폭 :  </div> 
<div id="r4"> <input id="cutheight" name="cutheight" type="text" size="4" value="3000" required placeholder="height"> </div>

<div id="exitpos"> 비상문 위치 :  </div>
<div id="exitpos1">
	   <select id='exititem' name='exititem'>
           <option value='없음'           selected >없음                           </option>
           <option value='중앙'                    >중앙                              </option>
           <option value='좌측'                    >좌측                              </option>
           <option value='우측'                    >우측                              </option>
		   </select>
</div>
</div>
<div id="exityesno">
<div id="excol1"> 띄울 치수(mm) : </div>  
<div id="excol2">  <input id="intervalnum" name="intervalnum"  type="text" size="3" required> </div>
<div id="excol3">  힌지종류 </div>
 	   <select id='hinge' name='hinge'>
           <option value='승리'           selected >승리                           </option>
           <option value='태영'                    >태영                           </option>
           <option value='굴비'                    >굴비                           </option>
           <option value='대신'                    >대신                           </option>
        </select>  

  <input type="text" id="hingenum"  name="hingenum" size="2" >
</div>

</div>
</form>
	&nbsp;&nbsp; <button id="calsize"> 절단치수 계산 </button>
	&nbsp;&nbsp; <button id="addline" onclick="addline();" > 행 추가 </button>
<br><br>
<div id="php_code" style="display:none">

</div>

<div id="result1" name="result1" style="display:none">

</div>
<div id="result2" name="result2" style="display:none">
<img src="../img/makesteel1.jpg"><br><br><br><br><br><br><br><br><br><br><br>
</div>
<div id="result3" name="result3" style="display:none">
<img src="../img/makesteel2.jpg"><br><br><br><br><br><br><br><br><br><br><br>
</div>
</div>
</div>
</div>
<script>
function captureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
}

function recaptureReturnKey(e) {
    if (e.keyCode==13)
        exe_search();
}
function Enter_Check(){
        // 엔터키의 코드는 13입니다.
    if(event.keyCode == 13){
      exe_search();  // 실행할 이벤트
    }
}
function Enter_CheckTel(){
        // 엔터키의 코드는 13입니다.
    if(event.keyCode == 13){
      exe_searchTel();  // 실행할 이벤트
    }
}
function addline(){
 document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과    
}

</script>
</body>
</html>


