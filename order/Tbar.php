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

 ?>
 
<h3> <?=$rail?> &nbsp; T바(하장바) 형태 </h3>

<div id="btnContainer">
  <button class="btn" onclick="listView()"><i class="fa fa-bars"></i> List</button> 
  <button class="btn active" onclick="gridView()"><i class="fa fa-th-large"></i> Grid</button>
</div>
<br>
<?php
if ($rail=='스크린방화' || $rail=='제연커튼') {
	?>	
<?php 
$num=5;
$cols=4;
for($i=1;$i<=$num;$i++)
{		
   if($i%$cols!=0) { ?>  
<div class="row">
   <?php }   ?>
   
 <div class="column" style="background-color:#aaa;">
   <a href="#" onclick="Tbar_content('../img/Tbar/fssTbar<?=$i?>.jpg');"> <img src="../img/Tbar/fssTbar<?=$i?>.jpg"> </a>
    <p></p>
    </div>	
<?php	
   if($i%$cols==0) { ?>  
</div>
   <?php
              } 
}
   ?>   
<?php
}
	?>	

<?php
if ($rail!='스크린방화' && $rail != '제연커튼') {
	?>	

<?php 
$num=2;
$cols=4;
for($i=1;$i<=$num;$i++)
{		
   if($i%$cols!=0) { ?>  
<div class="row">
   <?php }   ?>
   
 <div class="column" style="background-color:#aaa;">
   <a href="#" onclick="Tbar_content('../img/Tbar/fstTbar<?=$i?>.jpg');"> <img src="../img/Tbar/fstTbar<?=$i?>.jpg"> </a>
    <p></p>
    </div>	
<?php	
   if($i%$cols==0) { ?>  
</div>
   <?php
              } 
}
   ?>   

<?php
}
	?>			

</body>
<script>

// Get the elements with class="column"
var elements = document.getElementsByClassName("column");

// Declare a loop variable
var i;

// List View
function listView() {
  for (i = 0; i < elements.length; i++) {
    elements[i].style.width = "100%";
  }
}

// Grid View
function gridView() {
  for (i = 0; i < elements.length; i++) {
    elements[i].style.width = "50%";
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
function Tbar_content(types) {      
      document.getElementById("we9").value = types;
	  $("#below_area").hide();	
      var tmp= document.getElementById("we9").value;
		 
      document.getElementById("we10").innerHTML = "<img src="+ tmp + " class='maxsmall1' > ";		
  	   $("#we10").show();		  
}




</script>

</html>