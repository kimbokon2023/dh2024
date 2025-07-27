<!DOCTYPE html>
<html>

<style>
* {
  box-sizing: border-box;
}

/* Create two equal columns that floats next to each other */
.column {
  float: left;
  width: 33.3%;
  padding: 2px;
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
 
<h4> &nbsp; 스크린방화셔터용 셔터박스 선택 </h4>

<div id="btnContainer">
<!--  <button class="btn" onclick="listView()"><i class="fa fa-bars"></i> List</button>  
  <button class="btn active" onclick="gridView()"><i class="fa fa-bars"></i> Grid</button> -->
</div>
<br>

<?php 
$num=17;
$cols=3;
for($i=1;$i<=$num;$i++)
{		
   if($i%$cols!=0) { ?>  
<div class="row">
   <?php }   ?>
   
 <div class="column" style="background-color:#aaa;">
   <a href="#" onclick="box_content('../img/box/fss/box<?=$i?>.jpg',<?=$i?>);"> <img src="../img/box/fss/box<?=$i?>.jpg"> </a>
    <p></p>
    </div>	
<?php	
   if($i%$cols==0) { ?>  
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

function box_content(types,num) {
	  document.getElementById("lin3").value = types;
	   boxdraw();
	  $("#ceillingEnd").hide();	
	  

            var ua = window.navigator.userAgent;
            var postData; 
            var sendData = "./load_bendingData.php?bendnum=" + num ; 

            // 윈도우라면 ? 
            if (ua.indexOf('MSIE') > 0 || ua.indexOf('Trident') > 0) {
                postData = encodeURI(sendData);
            } else {
                postData = sendData;
            }
				
	   $("#displayresult").load(postData);  

}

function boxdraw() {
 	     var tmp= document.getElementById("lin3").value; 
	     document.getElementById("lin5").innerHTML = "<img src="+ tmp + " class='maxsmall' > ";		
}



</script>

</html>