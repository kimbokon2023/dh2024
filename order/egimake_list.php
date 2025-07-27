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
 
  $callname=$_REQUEST["callname"]; 
  $cutwidth=$_REQUEST["cutwidth"]; 
  $cutheight=$_REQUEST["cutheight"]; 
  
if($cutwidth=="")
	  $cutwidth=="5000";
if($cutheight=="")
	  $cutheight=="3000";
 
 require_once("../lib/mydb.php");
 $pdo = db_connect();			  
	
  $page=1;	 
  
 
  $scale = 30;       // 한 페이지에 보여질 게시글 수
  $page_scale = 10;   // 한 페이지당 표시될 페이지 수  10페이지
  $first_num = ($page-1) * $scale;  // 리스트에 표시되는 게시글의 첫 순번.

 
$sql="select * from chandj.egicut order by num asc"  	;				
			
			 
$nowday=date("Y-m-d");   // 현재일자 변수지정   
   
	 try{  
	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $temp=$stmh->rowCount();
	      
	  $total_row = $temp;     // 전체 글수	  		
         					 
     $total_page = ceil($total_row / $scale); // 검색 전체 페이지 블록 수
	 $current_page = ceil($page/$page_scale); //현재 페이지 블록 위치계산			 

		  if ($page<=1)  
			$start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
		  else 
			$start_num=$total_row-($page-1) * $scale;
	  
if($callname=="") $callname="FST";
	  ?>
		
	
 
 <!DOCTYPE HTML>
 <html>
 <head>
 <meta charset="UTF-8">
 <link rel="stylesheet" type="text/css" href="../css/order.css">

 <title> 주일기업 통합정보시스템 </title> 
 </head>
<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="http://5130.co.kr/order/order.js"></script>
  <script src="../js/html2canvas.js"></script>    <!-- 스크린샷을 위한 자바스크립트 함수 불러오기 -->

<style media="screen">
      *{
        margin: 0; padding: 0;
      }
      .slide{
        width: 400px;
        height: 400px;
        overflow: hidden;
        position: relative;
        margin: 0 auto;
      }
      .slide ul{
        width: 11600px;
        position: absolute;
        top:0;
        left:0;
        font-size: 0;
      }
      .slide ul li{
        display: inline-block;
      }
      #back{
        position: absolute;
        top: 0;
        left:0 ;
        cursor: pointer;
        z-index: 1;
      }
      #next{
        position: absolute;
        top: 0;
        right: 0;
        cursor: pointer;
        z-index: 1;
      }
     </style>
 

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
	
<br><br>
<form name="board_form" id="board_form"  method="post"  action="egimake_insert.php?mode=modify&text1=<?=$callname?>&text2=<?=$text2?>&cutwidth=<?=$cutwidth?>&cutheight=<?=$cutheight?>">
<div id="exitall">
<div id="exitcontent">
<div id="r5"> 부호 </div>
<div id="r6"> <input id="callname" name="callname"  type="text" size="10" placeholder="부호" value="<?=$callname?>" >  </div>

<div id="r0"> 절단사이즈 기준> </div>

<div id="r1"> 가로폭 :  </div> 
<div id="r2"> <input id="cutwidth" name="cutwidth" type="text" size="3" placeholder="width" value="<?=$cutwidth?>" required > </div>
<div id="r3"> 세로폭 :  </div> 
<div id="r4"> <input id="cutheight" name="cutheight" type="text" size="4" value="<?=$cutheight?>" required placeholder="height"> </div>

<div id="exitpos"> 비상문 위치 :  </div>
<div id="exitpos1">
	   <select id='exititem' name='exititem'>
           <option value='없음'           selected >없음                           </option>
           <option value='중앙'                    >중앙                              </option>
           <option value='좌측'                    >좌측                              </option>
           <option value='우측'                    >우측                              </option>
		   </select>
</div>
<div id="r3"> 수량 :  </div> 
  
  <div id="r8"> <input id="number" name="number" type="text" size="2" placeholder="수량" value="1" required > </div>
<div id="r7"> 틀 </div> 
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


<br><br>
<div id="result1" name="result1" >
<textarea rows="2" cols="100" name="text2" id="text2"> </textarea>
</div>


</form>
</div>
<br>
   <div class="clear"> </div>
	&nbsp;&nbsp; <button id="calsize_exe" onclick="calsize_exe();"> 절단치수 계산 </button>
	&nbsp;&nbsp; <button id="addline" onclick="addline();" > 행 추가 </button>
	<!-- 일부분 부분-->
  <button onclick="partShot();"> 이미지 저장 </button>
  &nbsp;&nbsp;
   <button  onclick="javascript:del('egimake_delete.php?num=all')" > DATA 전체삭제  </button>
   <div class="clear"> </div><br><br>

<div id="containers" >	
	<div id="display_result" >	
	   <div id="res1"> 번호 </div>
	   <div id="res2"> 부호 </div>
	   <div id="res3"> 세부 절단 사이즈  </div>
       <div class="clear"> </div>
	
  		<?php
		   $counter=0;
	       while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			  $counter++;
			  $num=$row["num"];
			  $text1=$row["text1"];
			  $text2=$row["text2"];
			  $text3=$row["text3"];
			  $text4=$row["text4"];
			  $text5=$row["text5"];
			  ?> 
	   <div id="res1"> <a href="javascript:del('egimake_delete.php?num=<?=$num?>')"> <?=$counter?> </a> </div>
	   <div id="res2"> <a href="javascript:del('egimake_delete.php?num=<?=$num?>')"> <?=$text1?> </a> </div>
	   <div id="res3">  <?=$text2?>   </div>
       <div class="clear"> </div>
	 <?php
			$start_num--;
			 } 
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  }  
   // 페이지 구분 블럭의 첫 페이지 수 계산 ($start_page)
      $start_page = ($current_page - 1) * $page_scale + 1;
   // 페이지 구분 블럭의 마지막 페이지 수 계산 ($end_page)
      $end_page = $start_page + $page_scale - 1;  
 ?>
	 
	       
         </div>   <!-- end of display_result -->
	   </div> <!-- end of containers -->  
	 </div> 
	   
</div>
	  		
		
		<br>
    
	   </div> 

</body>
<script>
$(function(){
	$("#exityesno").hide();	    	
    $("#exititem").change(function(){
			$("#exityesno").hide();	    

        if ( this.value == '없음' ) {
	      	$("#hinge").val("승리");				
		  	$("#hingenum").val("");		
	     $("#intervalnum").val("");	
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
	
});	
	
function calsize_exe() {
  var a = 0;
  var b = 0;
  var c = 0;
  var d = 0;
  var e = 0;
  var f = 0;
  var g = 0;
  var h = 0;
  var i = 0;   //쪽바계산용
  var amount;
  $("#result1").show();
  if($("#intervalnum").val()=='' && $("#exititem").val()!='없음') {
	    alert("띄울 치수를 입력해 주세요.");
		return false;
  }
   
  a=Math.ceil($("#cutheight").val() / 71) ;
  b=$("#cutwidth").val() ;
  e=$("#cutheight").val() ;
  f=Number($("#hingenum").val()) ;
  g=$("#intervalnum").val() ;
  amount=$("#number").val() ;
  
  c= a-29;   // 상바계산
  d= Math.floor((b-(900+f))/2);                // 비상문이 중앙일때 쪽바 폭 계산
  h= 29*2;                // 비상문이 중앙일때 쪽바 매수

  if($("#exititem").val()=='없음') {
  b=$("#cutwidth").val() ;
  $("#text2").text(" 절단 : " + b + "(mm) X  " + a + "(매), " + "수량: " + amount + "틀");
  }
  if($("#exititem").val()=='중앙') {
  b=$("#cutwidth").val() ;
  $("#text2").text("상바 : " + b + "(mm) X " + c + "(매),  쪽바 : " + d + "(mm) X " + h + "(매),  비상문바 : 900(mm) x 29(매), " + "수량: " + amount + "틀");
  }  
if($("#exititem").val()=='좌측' || $("#exititem").val()=='우측' ) {
  i=b-f-g-900; 
  b=$("#cutwidth").val() ;
  $("#text2").text("상바 : " + b + "(mm) X " + c + "(매),  1번 쪽바 : " + g + "(mm) X 29(매), 2번 쪽바 : " + i + "(mm) X 29(매),  비상문바 : 900(mm) x 29(매), " + "수량: " + amount + "틀");
  }    
}
 
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


function addline(){
 calsize_exe();
 document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과    
}
function del(href) 
     {
        if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
           document.location.href = href;
          }
}
	 
function partShot() {
//특정부분 스크린샷
html2canvas(document.getElementById("containers"))
//id container 부분만 스크린샷
.then(function (canvas) {
//jpg 결과값
drawImg(canvas.toDataURL('image/jpeg'));
//이미지 저장
saveAs(canvas.toDataURL(), 'egicut.jpg');
}).catch(function (err) {
console.log(err);
});
}

function drawImg(imgData) {
console.log(imgData);
//imgData의 결과값을 console 로그롤 보실 수 있습니다.
return new Promise(function reslove() {
//내가 결과 값을 그릴 canvas 부분 설정
var canvas = document.getElementById('canvas');
var ctx = canvas.getContext('2d');
//canvas의 뿌려진 부분 초기화
ctx.clearRect(0, 0, canvas.width, canvas.height);

var imageObj = new Image();
imageObj.onload = function () {
ctx.drawImage(imageObj, 10, 10);
//canvas img를 그리겠다.
};
imageObj.src = imgData;
//그릴 image데이터를 넣어준다.

}, function reject() { });

}
function saveAs(uri, filename) {
var link = document.createElement('a');
if (typeof link.download === 'string') {
link.href = uri;
link.download = filename;
document.body.appendChild(link);
link.click();
document.body.removeChild(link);
} else {
window.open(uri);
}
}
	 
	 
	 
</script>  

  <style>
  .fakeimg {
    height: 200px;
    background: #aaa;
  }
  </style>
</html>



