<?php

session_start();

   $level= $_SESSION["level"];
 if(!isset($_SESSION["level"]) || $level>5) {
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
	  
 if(isset($_REQUEST["amount"]))   // 선택한 메뉴에 대한 저장변수 1은 철재방화 절단치수
	 $amount=$_REQUEST["amount"];
    else
		  $amount=1;

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
// 절곡물에 대한 정보 불러오기

  require_once("../lib/mydb.php");
  $pdo = db_connect();	
  $sql = "select * from chandj.bending where num = ? ";
      $stmh = $pdo->prepare($sql); 
    $num=1;
    $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);
      $bendnum = $row["bendnum"];
      $bendname = $row["bendname"];
	  $no=array();
	  $railsum=0;
	  for($i=1;$i<=20;$i++) {
	  $temp="no" . $i;
      $no[$i] = $row[$temp];
	  $railsum+=$no[$i];
	  }	  
      $sum= $row["sum"];	  
	 }
 $arraynum=1;

?>
 
 <!DOCTYPE HTML>
 <html>
 <head>
 <meta charset="UTF-8">
 <link rel="stylesheet" type="text/css" href="../css/order.css">
 <link rel="stylesheet" type="text/css" href="../css/bendingdisplay.css">

 <title> 주일기업 통합정보시스템 </title> 
 </head>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="http://5130.co.kr/order/order.js"></script>
    <script>            
        $(document).ready (function () {                
            $('.btnAdd').click (function () {                                        
                $('.buttons').append (                        
                    '<input type="text" name="txt"> <input type="text" name="txt"> <input type="text" name="txt"> <input type="text" name="txt"> <input type="button" class="btnRemove" value="Remove"><br>'                    
                ); // end append                            
                $('.btnRemove').on('click', function () { 
                    $(this).prev().remove (); // remove the textbox
                    $(this).prev().remove (); // remove the textbox
                    $(this).prev().remove (); // remove the textbox
                    $(this).prev().remove (); // remove the textbox
                    $(this).next ().remove (); // remove the <br>
                    $(this).remove (); // remove the button
                });
            }); // end click                                            
        }); // end ready       

function display(txt) {
 $("#test1").val("232424"); 

    var fileValue = $("input[name='txt']").length;
    var txt = new Array(fileValue);
    for(var i=0; i<fileValue; i++){                          
         txt[i] = $("input[name='txt']")[i].value;
		 alert(txt[i]);
    }
	
	
}
    </script>  

 

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
  
  <div id="work_title"><img src="../img/title_order.png"></div>         
     <div id="order_title">      셔터 종류 선택         </div>
	 <div id="order_title1">   가로(폭)벽벽 사이즈(mm)  </div>
	 <div id="order_title2">   세로(높이)오픈사이즈(mm)	</div>  	 
	 <div id="order_title3">   비상문	                </div>  
	 <div id="order_title5">   수량	                </div>  
	 <div id="order_title4">   <button id="show_list" class="button button7"> 자재산출 </button> &nbsp; 
                <button id="show_basic_screen" class="button button5"> 스크린기본세팅 </button> &nbsp; 
                <button id="show_basic_egi" class="button button6"> 철재방화기본세팅 </button> &nbsp; 
	 </div>  

	 <br> <br> <br> 
<div id="or" >	  
	<div id="order_input"> 
	 
	    <form name="selectForm" method="post" action="rail.php" enctype="multipart/form-data" >
	       <select id="item_sel" name="item_sel">
           <option value='셔터종류선택'   disabled        >셔터종류선택                           </option>
           <option value='스크린방화'            selected >스크린방화                              </option>
		   
           <option value='제연커튼'                        >제연커튼                               </option>
           <option value='철재방화EGI1.6T'                >철재방화EGI1.6T                         </option>
           <option value='철재방범EGI1.6T'                >철재방범EGI1.6T                         </option>
           <option value='철재방범EGI1.2T'                >철재방범EGI1.2T                         </option>
           <option value='파이프방범16파이싱글'           >파이프방범16파이싱글                    </option>
           <option value='파이프방범19파이싱글'           >파이프방범19파이싱글                    </option>
           <option value='파이프방범19파이더블'           >파이프방범19파이더블                    </option>
           <option value='AL이중단열'                     >AL이중단열                              </option>	   
           <option value='내풍압'                         >내풍압                                  </option>	
		   </select>
		   </form>
		</div>	

		 <div id="order_input1"> <input type="text" id="stwidth" value="5,000" size="5" placeholder="셔터폭(벽벽)" onkeyup="inputNumberFormat(this)"/></div>
		 <div id="order_input2"> <input type="text" id="stheight"  name="stheight" value="3,000" size="5" placeholder="셔터높이(오픈)" onkeyup="inputNumberFormat(this)"/></div>
		 <div id="order_input3"> <input type="text" name="exitpos" value="<?=$exitpos?>" size="10" placeholder="비상문위치" ></div>
		 <div id="order_input4"> <input type="text" name="amount" value="<?=$amount?>" size="1" placeholder="수량" style="width:25px;" ></div>
   
   </div>
   	<div class="clear"> </div>
	<div id="motor_group"> 
	<div id="ww1">  <input type="checkbox" name=motor_Check value="1"  checked > 	전동개폐기(모터) 선택사항  </div>
	<div class="clear"> </div>

           <select name="motormaker" id="motormaker">
           <option value='모터회사'         disabled          >모터회사                                </option>
           <option value='경동'             selected          >경동                                    </option>
           <option value='KST'                                 >KST                                     </option>
           <option value='인성'                                >인성                                    </option>
           <option value='협영'                                >협영                                    </option>
		   </select>
           
		   <select name="power" id="power">
           <option value='전원'              disabled  selected >전원                                   </option>
           <option value='220V'                                >220V                                    </option>
           <option value='380V'                                >380V                                    </option>
		   </select>

           <select name="motortype" id="motortype">
           <option value='스크린용브라켓'       selected            >스크린용브라켓                                </option>
           <option value='일반철재용브라켓'                         >일반철재용브라켓                                </option>
		   </select>
		   
           <select name="motorflange" id="motorflange">
           <option value='샤프트규격'               selected   >샤프트규격                                         </option>
           <option value='3-4인치'                                >3-4인치                                   </option>
           <option value='3-5인치'                                >3-5인치                                   </option>
           <option value='3-6인치'                                >3-6인치                                  </option>
           <option value='4-6인치'                                >4-6인치                               </option>
           <option value='4-8인치'                                >4-8인치                               </option>
           <option value='5-8인치'                                >5-8인치                               </option>
           <option value='6-8인치'                                >6-8인치                               </option>
           <option value='4인치'                                  >4인치                                 </option>
           <option value='5인치'                                  >5인치                                 </option>
           <option value='6인치'                                  >6인치                                 </option>
           <option value='8인치'                                  >8인치                                 </option>
           <option value='10인치'                                 >10인치                                </option>
		   </select>

           <select name="controler" id="controler">
           <option value='연동제어기(없음)'             selected >연동제어기(없음)                                   </option>
           <option value='뒷박스'                                >뒷박스                                    </option>
           <option value='매립형제어기'                                >매립형제어기                                    </option>
           <option value='뒷박스+매립형제어기'                         >뒷박스+매립형제어기                           </option>
           <option value='노출형제어기'                                >노출형제어기                                   </option>
		   </select>

           <select name="switch_ma" id="switch_ma">
           <option value='방범스위치(없음)'            selected >방범스위치(없음)                                    </option>
           <option value='방범스위치'                                 >방범스위치                                   </option>
           <option value='방범스위치+케이스'                          >방범스위치+케이스                                   </option>
           <option value='방범케이스'                                 >방범케이스                                  </option>
           <option value='방범리모컨'                                 > 방범리모컨                                 </option>
  
		   </select>

</div> <!-- end of motor_group -->	
	
	<div class="clear"> </div>
	
<div id="bending_group">	
<div id="bending1"> 	 <input type="checkbox" name=bending_Check value="1"  checked > 절곡물 발주  </div>
	<div class="clear"> </div>
	
	<div id="area1"> 
	<div id="wa1"> 가이드레일 재질 : </div>
           <select name="guiderailmaterial" id="guiderailmaterial">
           <option value='재질선택'         disabled  selected >재질선택                                       </option>
           <option value='SUS H/L 1.2T'                                  >SUS H/L 1.2T                         </option>
           <option value='SUS H/L 1.5T'                                  >SUS H/L 1.5T                         </option>
           <option value='EGI 1.2T'                                     >EGI 1.2T                              </option>
           <option value='EGI 1.6T'                                     >EGI 1.6T                              </option>
           <option value='기타 특수재질'                                >기타 특수재질                             </option>
		   </select>
	<div class="clear"> </div>
	<div id="qq1" > 제작높이: </div> 
	<div id="qq2" >  <input type="text" id="railheight1" value="" size="2"  ></div> 	
	<div id="qq3" > 수량: </div> 
	<div id="qq4" >  <input type="text" id="railamount1" value="" size="2" style="width:25px;" ></div> 
		<div class="clear"> </div> 
	<div id="qq1" > 제작높이: </div> 
	<div id="qq2" >  <input type="text" id="railheight2" value="" size="2"  ></div> 	
	<div id="qq3" > 수량: </div> 
	<div id="qq4" >  <input type="text" id="railamount2" value="" size="2" style="width:25px;" ></div> 	
	</div>
	<div id="wa4"> <button class="button button4" id="gotorail">레일선택 </button> </div> 	
	<div id="wa5"> <input type="text" id="rail_sumnail" name="rail_sumnail" style="display:none" size="1">  </div>
		 <div id="wa6" > </div> 
		 
	<!--	 <div id="wa7" style="display:none">
		<form name="selectForm1" method="post" >
          <select name="guideamount" id="guideamount">
           <option value='양쪽동일'                     selected >양쪽동일                             </option>
           <option value='우측'                                  >우측                                 </option>
		   </select>
		   </form>
		 </div>  -->
		 <div id="area2" >
	<?php 
           for($i=1;$i<=10;$i++) {
                print "<input type='text' id=no" . $i . " name=no" . $i . " value=" . $no[$i] . ">";
		   }			   
		        print '	<div class="clear"> </div>';
           for($i=11;$i<=20;$i++) {
                print "<input type='text' id=no" . $i . " name=no" . $i . " value=" . $no[$i] . ">";
		   }					
                print "<div id='railsum_title'> 가로합 : </div> <input type='text' id='railsum' name='railsum' value='" . $railsum . "'>";
		 ?>
		  </div> 
		<div class="clear"> </div>	
			  <div id="guiderail_area" style="display:none" >  </div> 
	<?php	
	/*
        <input type="text" id="test1"> 	
		<input type="button" onclick="display();" value="출력"><br>    	
		
	        <div class="buttons">            
        <input type="text" name="txt"> 
        <input type="text" name="txt"> 
        <input type="text" name="txt"> 
        <input type="text" name="txt"> 
		
		
		<input type="button" class="btnAdd" value="Add"><br>        
        </div>  	
	
			
		  
		  
	<div id="block1" style="display:none">
         <div id="wa8"> <button class="button button4" id="gotorailanother">우측레일 </button> </div>
		 <div id="wa9"> <input type="text" id="rail_thumbnail" name="rail_thumbnail" style="display:none" size="1">  </div>
		 <div id="wa10" > </div> 		 
	</div>	 	*/
		?>	
	<div class="clear"> </div>	
	<div id="block2" style="display:none">       <!-- block2 삼각쫄대  -->
	<div id="wa11"> <b> 스크린방화용 삼각쫄대 </b> </div> <div id="wa12"> <img src="../img/triangle.jpg"> </div>
		<div id="qq1_1" > 길이: </div> 
	<div id="qq2" >  <input type="text" id="railmolding_lenth1" value="" size="2"  ></div> 	
	<div id="qq3" > 수량: </div> 
	<div id="qq4" >  <input type="text" id="railmolding_num1" value="" size="2" style="width:25px;"  ></div> 

		<div id="qq1_1" > 길이: </div> 
	<div id="qq2" >  <input type="text" id="railmolding_lenth1" value="" size="2"  ></div> 	
	<div id="qq3" > 수량: </div> 
	<div id="qq4" >  <input type="text" id="railmolding_num1" value="" size="2" style="width:25px;" ></div> 	
	 </div>   <!-- end of block2  -->

		<div id="block2_1" >       <!-- block2_1 봉제가스켓  -->
	<div id="wa11"> <b> 스크린방화용 G/R봉제가스켓 </b> </div> 
		<div id="qq1_1" > 길이: </div> 
	<div id="qq5" >  <input type="text" id="gasket" value="" size="2" style="width:25px;" > M(미터)</div> 	
	
	 </div>   <!-- end of block2_1  --> 
	 
	<div id="block3" style="display:none">       <!-- block3 짜부가스켓  -->	 
	<div id="wa13"> <b> 방화용 절곡(짜부)가스켓 </b> </div> <div id="wa14">  <img src="../img/gasket.jpg">	</div>
	<div id="qq1_1" > 길이: </div> 
	<div id="qq2" >  <input type="text" id="railmolding_lenth1" value="3000" size="2"  ></div> 	
	<div id="qq3" > 수량: </div> 
	<div id="qq4" >  <input type="text" id="railmolding_num1" value="" size="2" style="width:25px;"  ></div> 
	 </div>   <!-- end of block3  -->

	<div class="clear"> </div>  
	<div id="wa">
	<div id="lin1" ><a href="#" onclick="reload_box()"> 천장마감: </a> </div> 
		 <div id="lin2" >
		   <form name="selectForm2" method="post" >
           <select name="ceilingbar" id="ceilingbar">
           <option value='마감선택'           disabled  selected >마감선택                             </option>
           <option value='셔터박스'                              >셔터박스                             </option>
           <option value='린텔'                                  >린텔                                 </option>
		   </select>  
		   </form>    
		 </div> 
      <div id="lin3">  <input type="text" id="tt" name="tt" style="display:none" size="1"> </div>
      <div id="lin4"> 
           <select name="ceilingmaterial" id="ceilingmaterial">
           <option value='재질선택'         disabled  selected >재질선택                                       </option>
           <option value='SUS H/L 1.2T'                                  >SUS H/L 1.2T                         </option>
           <option value='SUS H/L 1.5T'                                  >SUS H/L 1.5T                         </option>
           <option value='EGI 1.2T'                                     >EGI 1.2T                              </option>
           <option value='EGI 1.6T'                                     >EGI 1.6T                              </option>
           <option value='전면EGI1.6T+1.2T'                             >전면EGI1.6T+1.2T                      </option>		   
		   </select>	  	  	  
	  </div>
		<div class="clear"> </div> 
			 <div id="ceillingEnd" style="display:none" >  </div> <!--셔터박스/린텔 형택 화면보여주기  -->
		<div class="clear"> </div> 
      <div id="lin5" style="display:none" >   </div>    <!--셔터박스 썸네일 화면보여주기  -->

	  </div>  <!--end of wa  -->
		<div class="clear"> </div>    

 <span class="more">
  <span class="blind"></span>
</span>

	 <div class="board" style="display:none"  >
  <ul class="list">
    <?php
	for($j=1;$j<=9;$j++) {
       print "<div id='b_title" . $j . "'> </div>";
	   for($i=($j-1)*10+1;$i<=$j*10;$i++) {
		
		   print "<div id='b" . $i . "'> </div>";

	   }
       print "<div id='b_sum" . $j . "'> </div>";	 
       print "<div class='clear'> </div>";	 
	   
	}
	       print "<div id='b_total'> </div>";	
	   ?>
  </ul>
  
</div>
			<div class="clear"> </div>   
	<div id="block4"  >   
	<div id="wr1"> R몰딩 재질 : </div>
	<div id="wr2">
           <select name="Rmoldingmaterial">
           <option value='재질선택'         disabled                     >재질선택                             </option>
           <option value='SUS H/L 1.2T'                                  >SUS H/L 1.2T                         </option>
           <option value='SUS H/L 1.5T'                                  >SUS H/L 1.5T                         </option>
           <option value='EGI 1.2T'          selected                    >EGI 1.2T                             </option>
           <option value='EGI 1.6T'                                      >EGI 1.6T                             </option>
		   </select>
	</div>
	<div id="wr3"> <button class="button button4" id="gotoRmolding">R몰딩 </button> </div> 
    <div id="wr4"> <input type="text" id="tt1" name="tt1" style="display:none" size="1"> </div>  <!--R몰딩 숨김문자 -->  
    <div id="wr5">  </div>    <!--R몰딩 이미지 화면보여주기  -->  
	<div id="wr6"> R케이스(GI0.4T) </div>
	<div id="wr7"> </div>
	<div id="wr8"> <button class="button button4" id="gotoRcase">R케이스 </button> </div> 
    <div id="wr9"> <input type="text" id="tt2" name="tt2" style="display:none" size="1"> </div>  <!--R케이스 숨김문자 -->  
    <div id="wr10">  </div>    <!--R 이미지 화면보여주기  -->  	
	 </div> <!-- end of block4 -->
	 
    <div id="block5"  >   
	<div id="we1"> 스크린방화용 (L)엘바 : </div>
	<div id="we2">
           <select id="Lbar" name="Lbar">
           <option value='재질선택'         disabled                     >재질선택                             </option>
           <option value='SUS H/L 1.2T'                                  >SUS H/L 1.2T                         </option>
           <option value='SUS H/L 1.5T'                                  >SUS H/L 1.5T                         </option>
           <option value='EGI 1.2T'                                      >EGI 1.2T                             </option>
           <option value='EGI 1.6T'             selected                 >EGI 1.6T                             </option>
		   </select>
	</div>
	<div id="we3"> <button class="button button8" id="gotoLbar">L바 </button> </div> 
    <div id="we4"> <input type="text" id="tt1" name="tt1" style="display:none" size="1"> </div>  <!--L바 숨김문자 -->  
    <div id="we5">  </div>    <!-- L바 이미지 화면보여주기  -->  
    </div> <!-- end of block5 -->
    <div id="block6" >   	
	<div id="we6"> (T바)하장바 </div>
	<div id="we7">
           <select id="Tbar" name="Tbar">
           <option value='재질선택'         disabled                     >재질선택                             </option>
           <option value='SUS H/L 1.2T'        selected                  >SUS H/L 1.2T                         </option>
           <option value='SUS H/L 1.5T'                                  >SUS H/L 1.5T                         </option>
           <option value='EGI 1.2T'                                      >EGI 1.2T                             </option>
           <option value='EGI 1.6T'                                      >EGI 1.6T                             </option>
		   </select>
 	</div>
	<div id="we8"> <button class="button button8" id="gotoTbar">하장바 </button> </div> 
    <div id="we9"> <input type="text" id="tt2" name="tt2" style="display:none" size="1"> </div>  <!-- 하장바 숨김문자 -->  
    <div id="we10">  </div>    <!--하장바 화면보여주기  -->  	
	 </div> <!-- end of block6 -->
		<div class="clear"> </div> 
	     <div id="below_area" style="display:none" >   <!--기타 형태 화면보여주기  -->   </div> 
		 
      <div id="material_list" style="display:none" >  </div>
	  	  
	  <div id="displayresult" style="display:none" >  </div>	 
</div> <!-- end of bending_group -->		
	<div class="clear"> </div>
 		

	   </div> 
	   </div> 
	   </div> 
  
	  
      </div>
   </div>
	<br>
		<br>
		<br>
		<br>
		<br>
		<br>
  </body>

</html>
