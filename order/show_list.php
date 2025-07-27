<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
  <link rel="stylesheet" type="text/css" href="../css/show_list.css">
</head>
<script>
var canvas = document.getElementById("myCanvas");
var ctx = canvas.getContext("2d");
</script>

<style>
* {
  box-sizing: border-box;
}

/* Create two equal columns that floats next to each other */
.column {
  float: left;
  width: 50%;
  padding: 10px;
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}
/* Style the buttons */
.btn {
  border: none;
  outline: none;
  padding: 12px 16px;
  background-color: #f1f1f1;
  cursor: pointer;
}

.btn:hover {
  background-color: #ddd;
}

.btn.active {
  background-color: #666;
  color: white;
}
</style>

<body>
 <?php
 session_start(); 
 
$text1=$_REQUEST["text1"]; 
$text2=$_REQUEST["text2"]; 
$text3=$_REQUEST["text3"]; 
$text4=$_REQUEST["text4"]; 
$ceilingbar=$_REQUEST["ceilingbar"]; 

function conv_num($num) {
$number = (int)str_replace(',', '', $num);
return $number;
}

  require_once("../lib/mydb.php");
  $pdo = db_connect();	
  $sql = "select * from chandj.settings where num = ? ";
      $stmh = $pdo->prepare($sql); 
    $num=1;
    $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);
      $motor1 = $row["motor1"];
      $motor2 = $row["motor2"];
      $motor3 = $row["motor3"];
      $motor4 = $row["motor4"];
      $motor5 = $row["motor5"];
      $motor6 = $row["motor6"];
      $motor7 = $row["motor7"];
      $motor8 = $row["motor8"];
      $motor9 = $row["motor9"];
      $motor10= $row["motor10"];
	 }
	 
// 모터 권상능력 계산
$width=conv_num($text2);
$height=conv_num($text3);
$guiderailheight=$height+150;
$gasketheight=$height+200;

switch ($text1) {
    case '스크린방화':
        $kg=$motor1;
        break;
    case '제연커튼':
        $kg=$motor2;
        break;	
    case '철재방화EGI1.6T':
        $kg=$motor3;
        break;	
    case '철재방범EGI1.6T':
        $kg=$motor4;
        break;	
    case '철재방범EGI1.2T':
        $kg=$motor5;
        break;	
    case '파이프방범16파이싱글':
        $kg=$motor6;
        break;	
    case '파이프방범19파이싱글':
        $kg=$motor7;
        break;	
    case '파이프방범19파이더블':
        $kg=$motor8;
        break;	
    case 'AL이중단열':
        $kg=$motor9;
        break;	
    case '내풍압':
        $kg=$motor10;
        break;	
}

$rollpower=$width/1000 * $height/1000 * $kg;

if(($text1=="스크린방화" || $text1=="제연커튼") && $rollpower>0 && $rollpower<=300)  {
	$bracketX=400;
	$bracketY=200;
	$motor="스크린 300K";
           }
if(($text1=="스크린방화" || $text1=="제연커튼") && $rollpower>300 && $rollpower<=400) {
	$bracketX=400;
	$bracketY=200;
	$motor="스크린 400K";
           }		   		   
if(($text1!="스크린방화" && $text1!="제연커튼") && $rollpower>0 && $rollpower<=300) {
	$bracketX=565;
	$bracketY=320;
	$motor="철재용 300K";	
           }
if(($text1!="스크린방화" && $text1!="제연커튼") && $rollpower>300 && $rollpower<=400) {
	$bracketX=565;
	$bracketY=320;
		$motor="철재용 400K";	
           }		
if(($text1!="스크린방화" && $text1!="제연커튼")  && $rollpower>400 && $rollpower<=600) {
	$bracketX=600;
	$bracketY=350;
		$motor="철재용 600K";	
           }	
if(($text1!="스크린방화" && $text1!="제연커튼")  && $rollpower>600 && $rollpower<=800) {
	$bracketX=690;
	$bracketY=390;
		$motor="철재용 800K";	
           }	
if(($text1!="스크린방화" && $text1!="제연커튼")  && $rollpower>800 && $rollpower<=1000) {
	$bracketX=690;
	$bracketY=390;
		$motor="철재용 1000K";	
           }	
if(($text1!="스크린방화" && $text1!="제연커튼")  && $rollpower>1000 && $rollpower<=1500) {
	$bracketX=910;
	$bracketY=600;
		$motor="철재용 1500K";	
           }		   
		   
// 샤프트 인치 환산
if(($text1=="스크린방화" || $text1=="제연커튼") && $width>0 && $width<=6000)  {
	$shaft=4;
           }		   
if(($text1=="스크린방화" || $text1=="제연커튼") && $width>6000 && $width<=9000)  {
	$shaft=5;
           }
if(($text1=="스크린방화" || $text1=="제연커튼") && $width>9000 && $width<=12000)  {
	$shaft=6;
           }		   
if(($text1=="스크린방화" || $text1=="제연커튼") && $width>12000 && $width<=27000)  {
	$shaft=4;
           }		   
if(($text1!="스크린방화" && $text1!="제연커튼")  && $width>0 && $width<=4800) {
	$shaft=4;
           }
if(($text1!="스크린방화" && $text1!="제연커튼")  && $width>4800 && $width<=6000) {
	$shaft=5;
           }		   
if(($text1!="스크린방화" && $text1!="제연커튼")  && $width>6000 && $width<=7900) {
	$shaft=6;
           }	   
if(($text1!="스크린방화" && $text1!="제연커튼")  && $width>7900 && $width<=9900) {
	$shaft=8;
           }		   
if(($text1!="스크린방화" && $text1!="제연커튼")  && $width>9900 && $width<=12000) {
	$shaft=10;
           }	

$gasket=0;
// 샤프트 인치 환산
if($text1=="스크린방화")   {
	$shaftlength=$width-200;
	$gasket=$width;  // 상부 가스킷
           }
if($text1=="철재방화EGI1.6T")   {
	$gasket=$width;  // 상부 가스킷
           }		   
if($text1=="제연커튼")  {
	$shaftlength=$width-100;
           }		   
if($text1!="스크린방화" && $text1!="제연커튼")  {
	$shaftlength=$width-100;
           }
$samgak=$height+150;


		   
 ?>

<div id="tab" >
<div id="tab1"> <?=$text1?> </div>
<div id="tab2"> &nbsp;&nbsp;  폭(width) : </div>
<div id="tab3"> <?=$text2?> </div>
<div id="tab4"> &nbsp; &nbsp; 높이(height) : </div>
<div id="tab5"> <?=$text3?> </div>
<div id="tab6"> &nbsp; 모터 권상능력 : </div>
<div id="tab7"> <?=$rollpower . "kg" ?> </div>
<div id="tab8"> 모터 :  </div>
<div id="tab9"> <?=$text4 . " " . $motor?> </div>
<div id="tab10"> 브라켓 :  </div>
<div id="bracketX"> <input id="brX" type="text" size="1" value="<?=$bracketX ?>"> </div>
<div id="tab11"> X </div>
<div id="bracketY"> <input id="brY" type="text" size="1" value="<?=$bracketY ?>"> </div>




</div>
<div id="tab" >
<div id="tab1"></div>    <!-- 후렌지 표기 -->
<div id="tab2"> 샤프트 :  </div>
<div id="tab3">    <?=$shaft?>"&nbsp; x &nbsp;<?=$shaftlength?> </div>
<div id="tab4">  </div>
<div id="tab5">  </div>
<div id="tab6"> 천장마감 :<?=$ceilingbar?>   </div>
<div id="tab7"> <?php 
     if($ceilingbar=="린텔") {
          $lintellength=$width;
          $lintelnum=2;	  		 
		  print "L:". $lintellength . "x" . $lintelnum . "EA";
	 }
     if($ceilingbar=="셔터박스") {
          $boxlength=$width; 
		  print "L:".$boxlength;
	 }	 
?> </div>
<div id="tab8"> 
<?php 
     if($ceilingbar=="린텔") {
		 print "R몰딩 : ";   		 
	 }	 
     if($ceilingbar=="셔터박스") {
		 print "&nbsp";   		 
	 }	 	 
?>	 
</div>

<div id="tab9"> 
<?php 
     if($ceilingbar=="린텔") {
          $lintellength = $width;
          $lintelnum = 2;	  		 
		  print "L:". $lintellength . "x" . $lintelnum . "EA";
          $Rcase = ceil($width/1220);           		  
		  print ", R케이스 : 1220 x ". $Rcase . " EA";
	 } 
?>	 
</div>

</div>
<div id="tab" >
<div id="tab1"> </div>
<div id="tab2">
<?php 
     if($text1=="스크린방화" || $text1=="제연커튼")  {
          		  print "L바";
	 } 
?>	
 </div>
<div id="tab3"> 
<?php 
     if($text1=="스크린방화" || $text1=="제연커튼")  {
          $LBar = $width;
          $LBarnum = 2;	  		 
		  print "L". $LBar . "x" . $LBarnum . "EA";
	 } 
?>	
</div>
<div id="tab4"> 하장바 : </div>
<div id="tab5"> L:<?=$width?>x1EA</div>
<div id="tab6"> 
<?php 
     if($text1=="스크린방화" || $text1=="제연커튼")  {
		  print "삼각쫄대 :";
	 } 
?>
</div>
<div id="tab7"> L:<?=$samgak?>x2EA  </div>
<div id="tab8"> 봉제가스켓: </div>
<div id="tab9"> L:<?=$gasketheight*2/1000?>M
</div>


</div>
<div id="diplayit" style="display:none">
<canvas id="myCanvas" width="1250" height="850" style="border:1px solid #d3d3d3;"  >
Your browser does not support the canvas element.
</canvas>
</div>
<br><br><br>
<?php
print "<script> drawbracket();</script>";
?>

</div>

</body>

</html>