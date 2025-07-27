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
  width: 33.3%;
  padding: 3px;
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
  padding: 5px;
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
 ?>
 
<h3> &nbsp; 철재용 노출형 셔터박스 </h3>

<div id="btnContainer">
</div>
<br>

<?php 
$num=16;
$cols=3;        // 열의 개수
$startnum=1;   // 파일 시작번호

for($i=$startnum;$i<=$num;$i++)
{		
   if($i % $cols != 0) { ?>  
<div class="row">
   <?php }   ?>
   
 <div class="column" style="background-color:#aaa;">
   <a href="#" onclick="box_content('../img/box/fst/box<?=$i?>.jpg');"> <img src="../img/box/fst/box<?=$i?>.jpg"> </a>
    <p></p>
    </div>	
<?php	
   if($i % $cols == 0) { ?>  
</div>
   <?php
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
    elements[i].style.width = "33.3%";
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

function box_content(types) {
	  document.getElementById("lin3").value = types;
	  $("#ceillingEnd").hide();	
	  boxdraw();	
}

function boxdraw() {
 	     var tmp= document.getElementById("lin3").value; 
	     document.getElementById("lin5").innerHTML = "<img src="+ tmp + " class='maxsmall' > ";		
}



</script>

</html>