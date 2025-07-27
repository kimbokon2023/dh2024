<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
</head>

<style>
* {
  box-sizing: border-box;
}

/* Create two equal columns that floats next to each other */
.column {
  float: left;
  width: 25%;
  padding: 5px;
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
  padding: 10px;
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
 
$rail=$_REQUEST["rail"]; 
$sel=$_REQUEST["sel"]; 
//$rail=$_SESSION['rail']; 
//$rail="스크린방화";

 ?>
 
<h3> <?=$rail?> &nbsp; 가이드레일 선택 </h3>

<?php
// $_POST["bendnum"]=1;
// include "load_bending.php";  // include는 _post로 값을 넘기고 받는다.
?>

<?php
if ($rail=='스크린방화') {

$num=8;
$cols=4; 
for($i=1;$i<=$num;$i++)
{		
   if($i%$cols!=0) { ?>  
<div class="row">
   <?php }   ?>
   
 <div class="column" style="background-color:#aaa;">
   <a href="#" onclick="guiderail_content('../img/guiderail/fss/rail<?=$i?>.jpg',<?=$sel?>,<?=$i?>);"> <img src="../img/guiderail/fss/rail<?=$i?>.jpg"> </a>
    <p></p>
    </div>	
<?php	
   if($i%$cols==0) { ?>  
</div>
   <?php
              } 
}
}
   ?>   
 
<?php
if ($rail=='제연커튼') {
$num=2;
$cols=4; 
for($i=1;$i<=$num;$i++)
{		
   if($i%$cols!=0) { ?>  
<div class="row">
   <?php }   ?>
   
 <div class="column" style="background-color:#aaa;">
   <a href="#" onclick="guiderail_content('../img/guiderail/fsh/rail<?=$i?>.jpg',<?=$sel?>,<?=$i+100?>);"> <img src="../img/guiderail/fsh/rail<?=$i?>.jpg"> </a>
    <p></p>
    </div>	
<?php	
   if($i%$cols==0) { ?>  
</div>
   <?php
              } 
}
}
   ?>   	
	
<?php
if ($rail=='철재방화EGI1.6T') {
	$num=10;
$cols=4; 
for($i=1;$i<=$num;$i++)
{		
   if($i%$cols!=0) { ?>  
<div class="row">
   <?php }   ?>
   
 <div class="column" style="background-color:#aaa;">
   <a href="#" onclick="guiderail_content('../img/guiderail/fst/rail<?=$i?>.jpg',<?=$sel?>,<?=$i+200?>);"> <img src="../img/guiderail/fst/rail<?=$i?>.jpg"> </a>
    <p></p>
    </div>	
<?php	
   if($i%$cols==0) { ?>  
</div>
   <?php
              } 
}
}
   ?>   		
<?php
 if ($rail=='철재방범EGI1.6T'     ||     
$rail=='철재방범EGI1.2T'         || 
$rail=='파이프방범16파이싱글'    || 
$rail=='파이프방범19파이싱글'    || 
$rail=='파이프방범19파이더블'    || 
$rail=='AL이중단열'               || 
$rail=='내풍압' 
 ) {
	 
$num=9;
$cols=4; 
for($i=1;$i<=$num;$i++)
{		
   if($i%$cols!=0) { ?>  
<div class="row">
   <?php }   ?>
   
 <div class="column" style="background-color:#aaa;">
   <a href="#" onclick="guiderail_content('../img/guiderail/st/rail<?=$i?>.jpg',<?=$sel?>,<?=$i+1000?>);"> <img src="../img/guiderail/st/rail<?=$i?>.jpg"> </a>
    <p></p>
    </div>	
<?php	
   if($i%$cols==0) { ?>  
</div>
   <?php
              } 
}
}
   ?> 
</body>
<script>

// Get the elements with class="column"
var elements = document.getElementsByClassName("column");

// Declare a loop variable
var i;

// Grid View
function gridView() {
  for (i = 0; i < elements.length; i++) {
    elements[i].style.width = "25%";
  }
}

/* Optional: Add active class to the current button (highlight it) */
var container = document.getElementById("btnContainer");
var btns = container.getElementsByClassName("btn");
for (var i = 0; i < btns.length; i++) {
  btns[i].addEventListener("click", function() {
    var current = document.getElementsByClassName("active");
    current[0].className = current[0].className.replace(" active", "");
    this.className += " active";
  });
}
function guiderail_content(types,sel,num) {

      document.getElementById("rail_sumnail").value = types;
	  $("#guiderail_area").hide();	
	  sumnaildraw(sel);  
	  
	      $("#guiderail_area").show();	
            var ua = window.navigator.userAgent;
            var postData; 
            var sendData = "./load_bending.php?bendnum=" + num ; 

            // 윈도우라면 ? 
            if (ua.indexOf('MSIE') > 0 || ua.indexOf('Trident') > 0) {
                postData = encodeURI(sendData);
            } else {
                postData = sendData;
            }	
      $("#guiderail_area").load(postData);  
	  
//opener.document.getElementById("wa3").value = "c_val"; 
// $("#wa3", opener.document).val("c_val"); 
	
// opner.document.getElementById( "rail_sumnail" ).value = type; 

/* $( "#lue", opener.document ).value( type ); */


}

function sumnaildraw(sel) {
 	     var tmp= document.getElementById("rail_sumnail").value; 

	     document.getElementById("wa6").innerHTML = "<img src="+ tmp + " class='maxsmall_new_rail' > ";	
   
}



</script>

</html>