<?php
session_start();

$root_dir = $_SERVER['DOCUMENT_ROOT'] ;

$level= $_SESSION["level"];
$user_name= $_SESSION["name"];
$WebSite = "http://8440.co.kr/";

 if(!isset($_SESSION["level"]) || $level>=5) {
		 sleep(1);
         header ("Location:" . $WebSite . "login/logout.php");
         exit;
   }  
$title_message = "원자재 입출고 차트";

?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>   

<title> <?=$title_message?> </title> 
<style>
    	
#showextract {
	display: inline-block;
	position: relative;
}
		
#showextractframe {
    display: none;
    position: absolute;
    width: 800px;
    z-index: 1000;
    left: 50%; /* 화면 가로축의 중앙에 위치 */
    top: 110px; /* Y축은 절대 좌표에 따라 설정 */
    transform: translateX(-50%); /* 자신의 너비의 반만큼 왼쪽으로 이동 */
}

</style>

</head>


<body>

<?php

if(isset($_REQUEST["search"]))   //목록표에 제목,이름 등 나오는 부분
  $search=$_REQUEST["search"];
	 
if(isset($_REQUEST["Bigsearch"]))   //목록표에 제목,이름 등 나오는 부분
 $Bigsearch=$_REQUEST["Bigsearch"];	 	
  else
	  $Bigsearch='';		 
	 
  if(isset($_REQUEST["separate_date"]))   //출고일 접수일
	 $separate_date=$_REQUEST["separate_date"];	 
  if(isset($_REQUEST["display_sel"]))   //목록표에 제목,이름 등 나오는 부분
	 $display_sel=$_REQUEST["display_sel"];	 
	 else
		$display_sel='doughnut';	 

   if(isset($_REQUEST["find"]))   //목록표에 제목,이름 등 나오는 부분
	 $find=$_REQUEST["find"];	  
	 
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();  

  if(isset($_REQUEST["mode"]))
     $mode=$_REQUEST["mode"];
  else 
     $mode="";     
   
  if($separate_date=="") $separate_date="2";
 
 // 기간을 정하는 구간
$fromdate=$_REQUEST["fromdate"];	 
$todate=$_REQUEST["todate"];	 

if($fromdate=="")
{
	$fromdate=substr(date("Y-m-d",time()),0,4) ;
	$fromdate=$fromdate . "-01-01";
}
if($todate=="")
{
	$todate=substr(date("Y-m-d",time()),0,4) . "-12-31" ;
	$Transtodate=strtotime($todate.'+1 days');
	$Transtodate=date("Y-m-d",$Transtodate);
}
    else
	{
	$Transtodate=strtotime($todate);
	$Transtodate=date("Y-m-d",$Transtodate);
	}

$sql="select * from mirae8440.steelsource"; 					

try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $rowNum = $stmh->rowCount();  
   $counter=0;
   $steelsource_num=array();
   $steelsource_item=array();
   $steelsource_spec=array();
   $steelsource_take=array();   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
	   $counter++;
	   
 			  $steelsource_num[$counter]=$row["num"];			  
 			  $steelsource_item[$counter]=$row["item"];
 			  $steelsource_spec[$counter]=$row["spec"];
		      $steelsource_take[$counter]=$row["take"];   			  

	 } 	 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  

if($separate_date=="1") $SettingDate="outdate ";
    else
		 $SettingDate="indate ";

$common="   where (outdate between date('$fromdate') and date('$Transtodate')) and (which='$separate_date') " ;
 
 // 전체합계(입고부분)를 산출하는 부분 
$sum_title=array(); 
$sum=array();

$sql="select * from mirae8440.steel " .$common; 	
 
  try{  
// 레코드 전체 sql 설정
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
	   
              $num=$row["num"];
			  
			  $item=$row["item"];			  
			  $spec=$row["spec"];
			  $steelnum=$row["steelnum"];			  
			  $company=$row["company"];
			  $comment=$row["comment"];
			  $which=$row["which"];	 	
			  
			  $tmp=$item . $spec;
	
        for($i=1;$i<=$rowNum;$i++) {			 			  

	          $sum_title[$i]=$steelsource_item[$i] . $steelsource_spec[$i];
			  if($which=='1' and $tmp==$sum_title[$i])
				    $sum[$i]=$sum[$i] + (int)$steelnum;		// 입고숫자 더해주기 합계표	
			// $sum[$i]=(float)-1;				
		           }
	
			}		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  

 // 전체합계(출고부분)를 처리하는 부분 

$sql="select * from mirae8440.steel "; 	 
	 try{  
// 레코드 전체 sql 설정
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {

              $num=$row["num"];
			  
			  $item=$row["item"];			  
			  $spec=$row["spec"];
			  $steelnum=$row["steelnum"];			  
			  $company=$row["company"];
			  $comment=$row["comment"];
			  $which=$row["which"];	 	
			  
			  $tmp=$item . $spec;
	
        for($i=1;$i<=$rowNum;$i++) {
			 			  
 			  
	          $sum_title[$i]=$steelsource_item[$i] . $steelsource_spec[$i];
			  if($which=='2' and $tmp==$sum_title[$i])
				    $sum[$i]=$sum[$i] - (int)$steelnum;			
		           }		  

			}		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  
  
  if($mode=="search")
    {
		if($search==""){
			$sql="select * from mirae8440.steel where (outdate between date('$fromdate') and date('$Transtodate')) and (which='$separate_date')  "; 						                       			
		 }
		elseif($search!="" && $find!="all")  { // 각 필드별로 검색어가 있는지 쿼리주는 부분						
		  $sql ="select * from mirae8440.steel where ($find like '%$search%') ";
		  $sql .=" and (outdate between date('$fromdate') and date('$Transtodate')) and (which='$separate_date')  ";
		}	   				   
		elseif($search!="" && $find=="all") { // 각 필드별로 검색어가 있는지 쿼리주는 부분						
		  $sql ="select * from mirae8440.steel where ((outdate like '%$search%')  or (outworkplace like '%$search%') ";
		  $sql .="or (item like '%$search%') or (spec like '%$search%') or (company like '%$search%') or (model like '%$search%')  or (comment like '%$search%')) and (outdate between date('$fromdate') and date('$Transtodate')) and (which='$separate_date')  ";
		}
	}
else {
		$sql="select * from mirae8440.steel where (outdate between date('$fromdate') and date('$Transtodate')) and (which='$separate_date')  "; 									
	}		
				            
$nowday=date("Y-m-d");   // 현재일자 변수지정   
	
$output_item_arr = array();	
$output_weight_arr = array();	
$input_arr = array();	
$temp_arr = array();	
$count=0;


// 철판 종류 또는 규격 불러오기 함수
function loadSteelData($pdo, $tableName, $columnName) {
    $sql = "SELECT * FROM mirae8440." . $tableName;
    try {
        $stmh = $pdo->query($sql);
        $dataArr = array();
		
        while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
            array_push($dataArr, $row[$columnName]);
        }
		
        sort($dataArr);  // 오름차순으로 배열 정렬
        return $dataArr;

    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
        return array();
    }
}

// 철판 종류 불러오기
$steelitem_arr = loadSteelData($pdo, "steelitem", "item");
// 철판 규격 불러오기
$spec_arr = loadSteelData($pdo, "steelspec", "spec");
$item_counter = count($steelitem_arr);
$spec_counter = count($spec_arr);

// 검색조건의 옵션넣기
$findarr=array('전체','입고','출고');  
 
try{  
	$stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
	$total_row=$stmh->rowCount();

?>
		 
<form name="board_form" id="board_form"  method="post" action="list_statistics.php?mode=search&search=<?=$search?>">        

<div class="container-fluid">
<div class="card">
<div class="card-body">  

 	<div class="d-flex mb-3 mt-4 justify-content-center align-items-center"> 		 
		<H5>
			 <?=$titlemessage?>
		</H5>		 
	</div>	
    
		
	<div class="d-flex mt-3 mb-2 justify-content-center align-items-center">           
		  <h3 class="justify-content-center" > 원자재 입출고 차트보기 </h3> 
	</div>	
		  
	<div class="d-flex mt-2 mb-2 justify-content-center align-items-center">   
    ▷ <?= $total_row ?> &nbsp;&nbsp;
	
	 <?php
	    if($separate_date=="1") {
			 ?>
			&nbsp; 입고일 <input type="radio" checked name="separate_date" value="1">
			&nbsp; 출고일 <input type="radio" name="separate_date" value="2">
			<?php
             		}    ?>	 
			 <?php
	    if($separate_date=="2") {
			 ?>
			&nbsp; 입고일 <input type="radio"  name="separate_date" value="1">
			&nbsp; 출고일 <input type="radio" checked name="separate_date" value="2">
			<?php
             		}    ?>	 	
	</div>
	<div class="d-flex mt-1 mb-1 justify-content-center align-items-center">   
								<!-- 기간부터 검색까지 연결 묶음 start -->								
			<span id="showdate" class="btn btn-dark btn-sm " > 기간 </span>	&nbsp; 
				<select name="dateRange" id="dateRange" class="form-control me-1" style="width:80px;">
					<?php
					$dateRangeArray = array('최근3개월','최근6개월', '최근1년', '최근2년','직접설정','전체');
					$savedDateRange = $_COOKIE['dateRange'] ?? ''; // 쿠키에서 dateRange 값 읽기

					foreach ($dateRangeArray as $range) {
						$selected = ($savedDateRange == $range) ? 'selected' : '';
						echo "<option $selected value='$range'>$range</option>";
					}
					?>
				</select>			
			<div id="showframe" class="card">
				<div class="card-header " style="padding:2px;">
					<div class="d-flex justify-content-center align-items-center">  
						기간 설정
					</div>
				</div>
				<div class="card-body">
					<div class="d-flex justify-content-center align-items-center">  	
						<button type="button" class="btn btn-outline-success btn-sm me-1 change_dateRange"   onclick='alldatesearch()' > 전체 </button>  
						<button type="button" id="preyear" class="btn btn-outline-primary btn-sm me-1 change_dateRange"   onclick='pre_year()' > 전년도 </button>  
						<button type="button" id="three_month" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='three_month_ago()' > M-3월 </button>
						<button type="button" id="prepremonth" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='prepre_month()' > 전전월 </button>	
						<button type="button" id="premonth" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='pre_month()' > 전월 </button> 						
						<button type="button" class="btn btn-outline-danger btn-sm me-1 change_dateRange "  onclick='this_today()' > 오늘 </button>
						<button type="button" id="thismonth" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='this_month()' > 당월 </button>
						<button type="button" id="thisyear" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='this_year()' > 당해년도 </button> 
					</div>
				</div>
			</div>		
			   <input type="date" id="fromdate" name="fromdate" size="12"  class="form-control"   style="width:100px;" value="<?=$fromdate?>" placeholder="기간 시작일">  &nbsp;   ~ &nbsp;  
			   <input type="date" id="todate" name="todate" size="12"   class="form-control"   style="width:100px;" value="<?=$todate?>" placeholder="기간 끝">  &nbsp;     </span> 
			   &nbsp;&nbsp;			 
				<select name="find" id="find"  class="form-control" style="width:50px;" >
					   <?php			   
					   for($i=0;$i<count($findarr);$i++) {
							 if($find==$findarr[$i]) 
										print "<option selected value='" . $findarr[$i] . "'> " . $findarr[$i] .   "</option>";
								 else   
						   print "<option value='" . $findarr[$i] . "'> " . $findarr[$i] .   "</option>";
					   } 		   
							?>	  
				</select>

				<select name="Bigsearch" id="Bigsearch" class="form-control" style="width:200px;" >
					<?php
					array_unshift($steelitem_arr, " ");
					for($i=0;$i<count($steelitem_arr);$i++) {
						if($Bigsearch==$steelitem_arr[$i])
							print "<option selected value='" . $steelitem_arr[$i] . "'> " . $steelitem_arr[$i] .   "</option>";
						else
							print "<option value='" . $steelitem_arr[$i] . "'> " . $steelitem_arr[$i] .   "</option>";
					}
					?>
				</select>					
			<div class="inputWrap">
				<input type="text" id="search" name="search" value="<?=$search?>" autocomplete="off"  class="form-control" style="width:150px;" > &nbsp;			
				<button class="btnClear"></button>
			</div>	     
				&nbsp;				
			<span id="showextract" class="btn btn-primary btn-sm " > <ion-icon name="build-outline"></ion-icon> </span>	&nbsp; 
				<div id="showextractframe" class="card">
					<div class="card-header text-center " style="padding:2px;">
						자주사용하는 사이즈
					</div>					
						<div class="card-body">
							 <div class="p-1 m-1" >
								 <button type="button" class="btn btn-primary btn-sm" onclick="HL304_list_click();" > 304 HL </button>	&nbsp;   
								 <button type="button" class="btn btn-success btn-sm" onclick="MR304_list_click();" > 304 MR </button>	&nbsp;    			 
								 <button type="button" class="btn btn-secondary btn-sm" onclick="VB_list_click();" > VB </button>	&nbsp;    
								 <button type="button" class="btn btn-warning btn-sm" onclick="EGI_list_click();" > EGI </button>	&nbsp;    
								 <button type="button" class="btn btn-danger btn-sm" onclick="PO_list_click();" > PO </button>	&nbsp;    
								 <button type="button" class="btn btn-dark btn-sm" onclick="CR_list_click();" > CR </button>	&nbsp;  
								 <button type="button" class="btn btn-success btn-sm" onclick="MR201_list_click();" > 201 2B MR </button>	&nbsp;  
								   </div>	
								  <div class="p-1 m-1" >
								  <span class="text-success "> <strong> 쟘 1.2T &nbsp; </strong> </span>	
									<button type="button" class="btn btn-outline-success btn-sm" onclick="size1000_1950_list_click();"> 1000x1950  </button> &nbsp;
									<button type="button" class="btn btn-outline-success btn-sm" onclick="size1000_2150_list_click();"> 1000x2150  </button> &nbsp;				   
									<button type="button"  class="btn btn-outline-success btn-sm"   onclick="size42150_list_click();">  4'X2150 </button> &nbsp;
									<button type="button"  class="btn btn-outline-success btn-sm"   onclick="size1000_8_list_click();"> 1000x8' </button> &nbsp; 
								  </div>	
								  <div class="p-1 m-1" >
								 &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								  <button type="button"   class="btn btn-outline-success btn-sm"  onclick="size4_8_list_click();"> 4'x8' </button> &nbsp;
								  <button type="button"  class="btn btn-outline-success btn-sm"  onclick="size1000_2700_list_click();"> 1000x2700 </button> &nbsp;
								   <button type="button" class="btn btn-outline-success btn-sm"  onclick="size4_2700_list_click();"> 4'x2700 </button> &nbsp;
								   <button type="button" class="btn btn-outline-success btn-sm"  onclick="size4_3200_list_click();"> 4'x3200  </button> &nbsp;
								   <button type="button" class="btn btn-outline-success btn-sm"   onclick="size4_4000_list_click();"> 4'x4000 </button> &nbsp;	   			  
								  </div>			  
								  <div class="p-1 m-1" >
								  <span class="text-success "> <strong> 신규쟘 1.5T(HL) &nbsp; </strong> </span>	
								   <button type="button" class="btn btn-outline-success btn-sm" onclick="size15_4_2150_list_click();"> 4'x2150 </button> &nbsp;				
								   <button type="button" class="btn btn-outline-success btn-sm" onclick="size15_4_8_list_click();"> 4'x8' </button> &nbsp;								  
								  <span class="text-success "> <strong> 신규쟘 2.0T(EGI) &nbsp; </strong> </span>	
								   <button type="button" class="btn btn-outline-success btn-sm" onclick="size20_4_8_list_click();"> 4'x8'  </button> &nbsp;
									
								  </div>			  

								<div class=" p-1 m-1" >	   
								   천장 1.2T(CR)  </button> &nbsp; 
								  <button type="button"  class="btn btn-outline-danger btn-sm" onclick="size12_4_1680_list_click();"> 4'x1680 </button> &nbsp;
								  <button type="button"  class="btn btn-outline-danger btn-sm" onclick="size12_4_1950_list_click();"> 4'x1950 </button> &nbsp;
								  <button type="button"  class="btn btn-outline-danger btn-sm"  onclick="size12_4_8_list_click();"> 4'x8' </button> &nbsp;
								  </div>			  
								  <div class=" p-1 m-1" >			  				   
								  천장 1.6T(CR)   &nbsp; 	  
								  <button type="button"  class="btn btn-outline-primary btn-sm" onclick="size16_4_1680_list_click();"> 4'x1680 </button> &nbsp;
								  <button type="button"  class="btn btn-outline-primary btn-sm"  onclick="size16_4_1950_list_click();"> 4'x1950 </button> &nbsp;
								  <button type="button"  class="btn btn-outline-primary btn-sm"  onclick="size16_4_8_list_click();"> 4'x8' </button> &nbsp;		   		   
								  </div>
								  <div class=" p-1 m-1" >	
								   천장 2.3T(PO)  &nbsp; 	  
								   <button type="button" class="btn btn-outline-secondary btn-sm" onclick="size23_4_1680_list_click();"> 4'x1680 </button> &nbsp;
								   <button type="button" class="btn btn-outline-secondary btn-sm"  onclick="size23_4_1950_list_click();"> 4'x1950 </button> &nbsp;
								   <button type="button" class="btn btn-outline-secondary btn-sm"  onclick="size23_4_8_list_click();"> 4'x8'  </button> &nbsp;					  
								   천장 3.2T(PO)  &nbsp; 	  
								   <button type="button" class="btn btn-outline-secondary btn-sm" onclick="size32_4_1680_list_click();"> 4'x1680 </button> &nbsp;
								   
								  </div>
							   
						</div>
					</div>			   		 
		 
		<button type="button" id="searchBtn" class="btn btn-dark btn-sm me-4"  > <i class="bi bi-search"></i>  </button>					

    </div>		
	
  <div class="card mt-2 mb-2">
<div class="card-body">  
	 <?php
	    
	       while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {

              $num=$row["num"];
 			  $outdate=$row["outdate"];			  
			  
			  $indate=$row["indate"];
			  $outworkplace=$row["outworkplace"];
			  
			  $item=$row["item"];			  
			  $spec=$row["spec"];
			  $steelnum=$row["steelnum"];			  
			  $company=$row["company"];
			  $comment=$row["comment"];
			  $which=$row["which"];	 	
			  $model=$row["model"];	 	
			  $temp_arr = explode("*", $spec);
			  
			//  print $temp_arr[0];
			  
			  $output_weight_arr[$count]=floor(($temp_arr[0] * $temp_arr[1] * $temp_arr[2] * 7.93 * (int)$steelnum)/1000000) ;
			  
							switch ($item) {
								case   "304 HL"     :   $output_item_arr[0] += $output_weight_arr[$count]; break;
								case   "304 MR"     :   $output_item_arr[1] += $output_weight_arr[$count]; break;	

								case   "PO"     :   $output_item_arr[3] += $output_weight_arr[$count]; break;	
								case   "EGI"     :   $output_item_arr[4] += $output_weight_arr[$count]; break;
								case   "CR"     :   $output_item_arr[5] += $output_weight_arr[$count]; break;									
								default:  $output_item_arr[2] += $output_weight_arr[$count];break;	
							}					  		  			  

				$count++;
				$start_num--;  
		 } 
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  }  
  
  print " <div class='d-flex mt-2 mb-3 justify-content-center align-items-center mt-1 mb-1'> ";
  print " <h6> <span style='color:red' > 304 HL </span>&nbsp; " . number_format($output_item_arr[0]) . "KG, &nbsp;&nbsp;   ";
  print " <span style='color:blue' > 304 MR </span>&nbsp; " . number_format($output_item_arr[1]) . "KG, &nbsp;&nbsp;   " ; 
  print " <span style='color:orange' > 기타SUS </span>&nbsp; " . number_format($output_item_arr[2]) . "KG,  &nbsp;&nbsp;  " ; 
  print " <span style='color:green' > PO </span>&nbsp; " . number_format($output_item_arr[3]) . "KG, &nbsp;&nbsp;   " ; 
  print " <span style='color:purple' > EGI </span>&nbsp; " . number_format($output_item_arr[4]) . "KG, &nbsp;&nbsp;   " ; 
  print " <span style='color:brown' > CR </span> &nbsp;" . number_format($output_item_arr[5]) . "KG </h6>  </div>  " ; 

		switch ($display_sel) {
			case   "doughnut"     :   $chartchoice[0]='checked'; break;
			case   "pie"     :   $chartchoice[1]='checked'; break;
			case   "bar"     :   $chartchoice[2]='checked'; break;
			case   "line"     :   $chartchoice[3]='checked'; break;
			case   "radar"     :   $chartchoice[4]='checked'; break;
			case   "polarArea"     :   $chartchoice[5]='checked'; break;
		}
 ?>
  <div class="d-flex justify-content-center align-items-center mt-2 mb-2">	   
	   <input id="display_sel" name="display_sel" type='hidden' value='<?=$display_sel?>' >
				&nbsp; 도넛 <input type="radio" <?=$chartchoice[0]?> name="chart_sel" value="doughnut">
				&nbsp; 파이 <input type="radio" <?=$chartchoice[1]?> name="chart_sel" value="pie">
				&nbsp; 바 <input type="radio" <?=$chartchoice[2]?> name="chart_sel" value="bar">
				&nbsp; 라인 <input type="radio" <?=$chartchoice[3]?> name="chart_sel" value="line">
				&nbsp; 레이더 <input type="radio" <?=$chartchoice[4]?> name="chart_sel" value="radar">
				&nbsp; Polar Area <input type="radio" <?=$chartchoice[5]?> name="chart_sel" value="polarArea"> 
	</div>
	<div class="d-flex justify-content-center align-items-center">		
		<canvas id="myChart" width="800" height="500"></canvas>
	</div>   
	 </div>       
   </div>
   </div>
   </div>
   </div> <!-- end of content -->      
</form>
   
<script>
$(document).ready(function() { 
	$("input:radio[name=separate_date]").click(function() { 
		document.getElementById('board_form').submit(); 	
	}) 
});

$(document).ready(function() {

    // 쿠키에서 dateRange 값을 읽어와 셀렉트 박스에 반영
    var savedDateRange = getCookie('dateRange');
    if (savedDateRange) {
        $('#dateRange').val(savedDateRange);
    }

    // dateRange 셀렉트 박스 변경 이벤트 처리
    $('#dateRange').on('change', function() {
        var selectedRange = $(this).val();
        var currentDate = new Date(); // 현재 날짜
        var fromDate, toDate;

        switch(selectedRange) {
            case '최근3개월':
                fromDate = new Date(currentDate.setMonth(currentDate.getMonth() - 3));
                break;
            case '최근6개월':
                fromDate = new Date(currentDate.setMonth(currentDate.getMonth() - 6));
                break;
            case '최근1년':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 1));
                break;
            case '최근2년':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 2));
                break;
            case '직접설정':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 1));
                break;   
            case '전체':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 20));
                break;            
            default:
                // 기본 값 또는 예외 처리
                break;
        }

        // 날짜 형식을 YYYY-MM-DD로 변환
        toDate = formatDate(new Date()); // 오늘 날짜
        fromDate = formatDate(fromDate); // 계산된 시작 날짜

        // input 필드 값 설정
        $('#fromdate').val(fromDate);
        $('#todate').val(toDate);
		
		var selectedDateRange = $(this).val();
       // 쿠키에 저장된 값과 현재 선택된 값이 다른 경우에만 페이지 새로고침
        if (savedDateRange !== selectedDateRange) {
            setCookie('dateRange', selectedDateRange, 30); // 쿠키에 dateRange 저장
			document.getElementById('board_form').submit();      
        }		
		
		
    });
});

/* Checkbox change event */
$('input[name="chart_sel"]').change(function() {
    // 모든 radio를 순회한다.
    $('input[name="chart_sel"]').each(function() {
        var value = $(this).val();              // value
        var checked = $(this).prop('checked');  // jQuery 1.6 이상 (jQuery 1.6 미만에는 prop()가 없음, checked, selected, disabled는 꼭 prop()를 써야함)
        // var checked = $(this).attr('checked');   // jQuery 1.6 미만 (jQuery 1.6 이상에서는 checked, undefined로 return됨)
        // var checked = $(this).is('checked');
        var $label = $(this).next();
 
        if(checked)  {
           $("#display_sel").val(value);
	       document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 	
		}

    });
});

    var arr1 = <?php echo json_encode($output_item_arr);?> ;
	var ctx = document.getElementById('myChart');
    var chart_type = document.getElementById('display_sel').value;

	var myChart = new Chart(ctx, {
		type: chart_type,
		data: {
			labels: ['304 HL', '304 MR', '기타SUS', 'PO','EGI','CR'],
			datasets: [{
				label: '#주요 원자재 입/출고현황 단위(KG) ',
				data: [arr1[0], arr1[1], arr1[2],arr1[3], arr1[4], arr1[5] ],
				backgroundColor: [
					'rgba(255, 99, 132, 0.2)',
					'rgba(54, 162, 235, 0.2)',
					'rgba(255, 206, 86, 0.2)',
					'rgba(75, 192, 192, 0.2)',
					'rgba(153, 102, 255, 0.2)',
					'rgba(255, 159, 64, 0.2)'
				],
				borderColor: [
					'rgba(255, 99, 132, 1)',
					'rgba(54, 162, 235, 1)',
					'rgba(255, 206, 86, 1)',
					'rgba(75, 192, 192, 1)',
					'rgba(153, 102, 255, 1)',
					'rgba(255, 159, 64, 1)'
				],
				borderWidth: 1
			}]
		},
		options: {
			responsive: false,
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					}
				}]
			},
		}
	});

function blinker() {
	$('.blinking').fadeOut(500);
	$('.blinking').fadeIn(500);
}
setInterval(blinker, 1000);

  </script>

  </body>

  </html>