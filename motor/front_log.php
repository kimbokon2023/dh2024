 <?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
 
$title_message = "모터 수주 Front Log" ; 
 
?>
 
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

 if(!isset($_SESSION["level"]) || $level>=5) {
		 sleep(1);
         header ("Location:" . $WebSite . "login/logout.php");
         exit;
 }
 ?>

<title> <?=$title_message?> </title>

</head>

<body>

   <?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<?php


$readIni = array();   // 환경파일 불러오기
$readIni = parse_ini_file("./estimate.ini",false);	
// 초기 서버를 이동중에 저정해야할 변수들을 저장하면서 작업한다. 자료를 추가 불러올때 카운터 숫자등..
$init_read = array();   // 환경파일 불러오기
$init_read = parse_ini_file("./estimate.ini",false);	

// var_dump($init_read);

include "_request.php";

function check_in_range($start_date, $end_date, $user_date)
{  
  $start_ts = strtotime($start_date);
  $end_ts = strtotime($end_date);
  $user_ts = strtotime($user_date);
  
  return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
}	  

require_once("../lib/mydb.php");
$pdo = db_connect();	

// /////////////////////////첨부파일 있는 것 불러오기 
$savefilename_arr=array(); 
$realname_arr=array(); 
$attach_arr=array(); 
$tablename='ceiling';
$item = 'ceiling';

  $sum=array();
 
 
  if(isset($_REQUEST["mode"]))
     $mode=$_REQUEST["mode"];
  else 
     $mode="";     
   
   if(isset($_REQUEST["find"]))   //목록표에 제목,이름 등 나오는 부분
   $find=$_REQUEST["find"];
  
  
$now = date("Y-m-d");	 // 현재 날짜와 크거나 같으면 출고예정으로 구분

// 미출고 리스트

$attached=" and ((workday='') or (workday='0000-00-00')) ";
$whereattached=" where workday='' ";
	
		
$a= " ";  
$b=  " ";

// $_REQUEST 배열에서 'search' 파라미터 확인
$search = isset($_REQUEST["search"]) ? $_REQUEST["search"] : '';


// 검색을 위해 모든 검색변수 공백제거
$search = str_replace(' ', '', $search);  

		  if($search==""){
					 $sql="select * from " . $DB . ".ceiling " . $whereattached . $a; 					
	                 $sqlcon = "select * from " . $DB . ".ceiling "  . $whereattached .  $b;   // 전체 레코드수를 파악하기 위함.
			       }
				   
             else {
					  $sql ="select * from " . $DB . ".ceiling where ((replace(workplacename,' ','') like '%$search%' ) or (firstordman like '%$search%' )  or (secondordman like '%$search%' )  or (chargedman like '%$search%' ) ";
					  $sql .="or (delicompany like '%$search%' ) or (type like '%$search%' ) or (firstord like '%$search%' ) or (secondord like '%$search%' ) or (car_insize like '%$search%' ) or (memo like '%$search%' ) or (memo2 like '%$search%' ) or (material1 like '%$search%' ) or (material2 like '%$search%' ) or (material3 like '%$search%' ) or (material4 like '%$search%' ) or (material5 like '%$search%' ) or (air_su like '%$search%' ))  " . $attached . $a; 
					  
                      $sqlcon ="select * from " . $DB . ".ceiling where ((replace(workplacename,' ','') like '%$search%' ) or (firstordman like '%$search%' )  or (secondordman like '%$search%' )  or (chargedman like '%$search%' ) ";
					  $sqlcon .="or (delicompany like '%$search%' ) or (type like '%$search%' ) or (firstord like '%$search%' ) or (secondord like '%$search%' ) or (car_insize like '%$search%' ) or (memo like '%$search%' )   or (memo2 like '%$search%' ) or (material1 like '%$search%' ) or (material2 like '%$search%' ) or (material3 like '%$search%' ) or (material4 like '%$search%' ) or (material5 like '%$search%' ) or (air_su like '%$search%' ))  " . $attached . $b; 
				  }
				  		       
   
$lc_total_12 = 0;
$bon_total_12 = 0;
$lc_total_13to17 = 0;
$bon_total_13to17 = 0;
$lc_total_18 = 0;
$bon_total_18 = 0;

try {
  $allstmh = $pdo->query($sqlcon);

  while ($row = $allstmh->fetch(PDO::FETCH_ASSOC)) {		
    $inseung = $row["inseung"];			  		
    $bon_su = $row["bon_su"];			  
    $lc_su = $row["lc_su"];
    
    $inseung_num = intval($inseung);
    $lc_su_num = intval($lc_su);
    $bon_su_num = intval($bon_su);
    
    if ($inseung_num <= 12 && $lc_su_num >= 1) {
      $lc_total_12 += $lc_su_num ;
    }
    if ($inseung_num >= 13 && $inseung_num <= 17 && $lc_su_num >= 1) {
      $lc_total_13to17 += $lc_su_num;
    }
    if ($inseung_num >= 18 && $lc_su_num >= 1) {
      $lc_total_18 += $lc_su_num;
    }
    
    if ($inseung_num <= 12 && $bon_su_num >= 1) {
      $bon_total_12 += $bon_su_num;
    }
    if ($inseung_num >= 13 && $inseung_num <= 17 && $bon_su_num >= 1) {
      $bon_total_13to17 +=  $bon_su_num;
    }
    if ($inseung_num >= 18 && $bon_su_num >= 1) {
      $bon_total_18 +=  $bon_su_num;
    }
  }
} catch (PDOException $Exception) {
  print "오류: " . $Exception->getMessage();
}


$sum_12 = $lc_total_12 + $bon_total_12; 
$sum_13to17 = $lc_total_13to17 + $bon_total_13to17; 
$sum_18 = $lc_total_18 + $bon_total_18; 

// 합계표 만들기

// bon_total_12 12인승 이하
$bon_total_12_amount = str_replace(',', '', $bon_total_12) * intval(str_replace(',', '', $readIni["bon_unit_12"]));
$lc_total_12_amount = str_replace(',', '', $lc_total_12) * intval(str_replace(',', '', $readIni["lc_unit_12"]));
$bon_total_12_total = $bon_total_12_amount + $lc_total_12_amount ;
$bon_total_12_num = $bon_total_12 + $lc_total_12;


$bon_total_13to17_amount = str_replace(',', '', $bon_total_13to17) * intval(str_replace(',', '', $readIni["bon_unit_13to17"]));
$lc_total_13to17_amount = str_replace(',', '', $lc_total_13to17) * intval(str_replace(',', '', $readIni["lc_unit_13to17"]));
$bon_total_13to17_total = $bon_total_13to17_amount + $lc_total_13to17_amount ;
$bon_total_13to17_num = $bon_total_13to17 + $lc_total_13to17;


$bon_total_18_amount = str_replace(',', '', $bon_total_18) * intval(str_replace(',', '', $readIni["bon_unit_12"]));
$lc_total_18_amount = str_replace(',', '', $lc_total_18) * intval(str_replace(',', '', $readIni["lc_unit_12"]));
$bon_total_18_total = $bon_total_18_amount + $lc_total_18_amount ;
$bon_total_18_num = $bon_total_18 + $lc_total_18;

$total = $bon_total_12_total + $bon_total_13to17_total + $bon_total_18_total ;      
   
// 전체 레코드수를 파악한다.
try{  
	$stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
	$total_row=$stmh->rowCount();    		
			 
 ?>


<div class="container">  

<style>
.fixed-table {
		position: sticky;
		top: 0;
		background-color: #fff;
		z-index: 1;
		margin-bottom: 10px;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	}
	
    .form-group {
        display: flex;
        justify-content: center;
    }

    .form-group input {
        margin: auto;
		text-align : center;
    }	


/* 우측배너 제작 */

.sideBanner {
  position: absolute;
  width: calc(100vw - 90vw);
  height:calc(100vh - 70vh);
  top: calc(100vh - 70vh);
  left: calc(100vw - 15vw);  
}


</style>	
	

<form id="board_form" name="board_form" class="form-signin" method="post" action="front_log.php">		
	<input type="hidden" id="mode" name="mode" value="<?=$mode?>" >
	<input type="hidden" id="num" name="num" value="<?=$num?>" >                        
	<input type="hidden" id="user_name" name="user_name" value="<?=$user_name?>" >

<div class="row justify-content-center align-items-center mt-1">
	<div class="col-sm-4 text-center">
	  <div class="card align-middle justify-content-center " style="border-radius: 15px;">
		<div class="card-body mt-2 mb-2 justify-content-center">	
	<span class="card-title fs-6" style="color: #113366; ">조명천장&본천장 제품별 단가</span> 
						
	<div class="d-flex justify-content-center align-items-center mt-2">	   									
	<table class="table table-bordered justify-content-center" >
		<thead class="table-primary">
			<tr>
				<th >인승</th>            
				<th >조명천장</th>            
				<th >본천장</th>            
			</tr>        
		</thead>
		<tbody>
			<tr>
				<td>12인승 이하</td>
				<td>
				   <div class="d-flex justify-content-center">
						<input type="text" class="form-control w-75 text-end" name="lc_total_12" value="<?=$readIni['lc_unit_12']?>"  data-separator="," />
					</div>
				</td>
				<td>
				   <div class="d-flex justify-content-center">			
						<input type="text" class="form-control w-75 text-end" name="bon_total_12" value="<?=$readIni['bon_unit_12']?>" data-separator="," />
					</div>            
				</td>
			</tr>
			<tr>
				<td>13인승 이상 17인승 이하</td>
				<td>
				   <div class="d-flex justify-content-center">			
						<input type="text" class="form-control w-75 text-end" name="lc_unit_13to17"  value="<?=$readIni['lc_unit_13to17']?>" data-separator="," />
					</div>				
				</td>
				<td>
				   <div class="d-flex justify-content-center">			
						<input type="text" class="form-control w-75 text-end" name="bon_unit_13to17" value="<?=$readIni['bon_unit_13to17']?>"  data-separator="," />
					</div>				
				</td>
			</tr>
			<tr>
				<td>18인승 이상</td>
				<td>
				   <div class="d-flex justify-content-center">			
						<input type="text" class="form-control w-75 text-end" name="lc_unit_18"  value="<?=$readIni['lc_unit_18']?>" data-separator="," />
					</div>				
				</td>
				<td>
				   <div class="d-flex justify-content-center">			
						<input type="text" class="form-control w-75 text-end" name="bon_unit_18"  value="<?=$readIni['bon_unit_18']?>" data-separator="," />
					</div>				
				</td>
			</tr>
		</tbody>
	</table>	
	</div>		

	<div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center "> 
		<button id="writeBtn" type="button" class="btn btn-primary btn-sm" >  <i class="bi bi-pencil-square"></i> 단가 자료수정  </button> &nbsp;&nbsp;&nbsp;&nbsp;
	</div>
</div>
</div>
</div>


<div class="col-sm-8 text-center">	
<div class="card align-middle justify-content-center " style="border-radius: 20px;">
<div class="card-body mt-2 mb-2 justify-content-center">	
<div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center "> 	
  <span class="fs-6 mt-2">
     Front Log (발주접수 후 미청구 제품 결재 예측)  
	</span>
</div>


<div class="d-flex justify-content-center align-items-center mt-2">
<table id="table2" class="table table-bordered justify-content-center" >
    <thead class="table-primary">
        <tr>
            <th rowspan = "2" >인승</th>
            <th rowspan = "2" >수량합</th>
            <th colspan = "2" >조명천장</th>
            <th colspan = "2" >본천장</th>
            <th rowspan = "2"  >합계</th>
        </tr>        
        <tr>            
            <th >수량</th>            
            <th >금액</th>
			<th >수량</th>            
            <th >금액</th>
        </tr>        
    </thead>	
 <tbody>
    <tr>
		<td >  12인승 이하</td>
        <td>
			<div class="d-flex justify-content-center">			
				<input type="text"  class="form-control w-75 text-end"  value="<?= number_format($sum_12) ?>"  />
			</div>	
        </td>
        <td>
			<div class="d-flex justify-content-center">			
				<input type="text" class="form-control w-75 text-end"   value="<?= number_format($lc_total_12) ?>"   />
			</div>
        </td>
        <td>
			<div class="d-flex justify-content-center">			
				<input type="text"  class="form-control text-end"  value="<?= number_format($lc_total_12_amount) ?>"   />
			</div>
        </td>
        <td>
			<div class="d-flex justify-content-center">			
				<input type="text"  class="form-control w-75 text-end"   value="<?= number_format($bon_total_12) ?>" />
			</div>
        </td>
        <td>
			<div class="d-flex justify-content-center">			
				<input type="text"  class="form-control  text-end"  value="<?= number_format($bon_total_12_amount) ?>" />
			</div>
        </td>
        <td>
		    <div class="d-flex justify-content-center">			
				<input type="text"  class="form-control text-end fw-bold"   value="<?= number_format($bon_total_12_total) ?>"  />
			</div>
        </td>			
    </tr>
    <tr>
            <td>13인승 이상 17인승 이하</td>
        <td>
		    <div class="d-flex justify-content-center">			
				<input type="text"   class="form-control w-75 text-end"   value="<?= number_format($sum_13to17) ?>"  />
			</div>
        </td>	
        <td>
		    <div class="d-flex justify-content-center">			
				<input type="text"  class="form-control w-75 text-end"    value="<?= number_format($lc_total_13to17) ?>"   />
			</div>
        </td>		
        <td>
			<div class="d-flex justify-content-center">	
				<input type="text"   class="form-control  text-end"   value="<?= number_format($lc_total_13to17_amount) ?>"   />
			</div>
        </td>
        <td>
		    <div class="d-flex justify-content-center">			
				<input type="text"  class="form-control w-75 text-end"    value="<?= number_format($bon_total_13to17) ?>"   />
			</div>
        </td>		
        <td>
			<div class="d-flex justify-content-center">	
				<input type="text"   class="form-control  text-end"  value="<?= number_format($bon_total_13to17_amount) ?>" />
			</div>
        </td>
        <td>
		    <div class="d-flex justify-content-center">			
				<input type="text"   class="form-control  text-end fw-bold"  value="<?= number_format($bon_total_13to17_total) ?>"  />
			</div>
        </td>			
    </tr>
	
    <tr>	
	
	  <td>18인승 이상</td>
	  
        <td>
		    <div class="d-flex justify-content-center">					
				<input type="text"   class="form-control w-75 text-end"  value="<?= number_format($sum_18) ?>"  />
			</div>
        </td>			
        <td>
		    <div class="d-flex justify-content-center">					
				<input type="text"  class="form-control w-75 text-end"  value="<?= number_format($lc_total_18) ?>"   />
			</div>
        </td>		
        <td>
		    <div class="d-flex justify-content-center">					
				<input type="text"   class="form-control text-end"  value="<?= number_format($lc_total_18_amount) ?>"   />
			</div>
        </td>
        <td>
		    <div class="d-flex justify-content-center">					
				<input type="text"  class="form-control w-75 text-end"  value="<?= number_format($bon_total_18) ?>"   />
			</div>
        </td>		
        <td>
		    <div class="d-flex justify-content-center">					
				<input type="text"    class="form-control text-end"  value="<?= number_format($bon_total_18_amount) ?>" />
			</div>
        </td>		
        <td>
		    <div class="d-flex justify-content-center">					
				<input type="text"   class="form-control  text-end"   name="bon_total_18_total" style=" font-weight: bold;" value="<?= number_format($bon_total_18_total) ?>"  />
			</div>
        </td>			
    </tr>
    <tr>
        <td colspan="6" >합계</td>            
        <td>
		    <div class="d-flex justify-content-center">					
				<input type="text" class="form-control  text-end text-primary" style=" font-weight: bold;"  name="total"  value="<?= number_format($total) ?>"  />
			</div>
        </td>
    </tr>		
</tbody>

</table>

                  
		</div>
		</div>
	</div>
</div>
</div>
</div>
  
  
  
  </form>

					
  <div id="vacancy" style="display:none">  </div>	
  
<div class="container-fluid">  

<div class="card mt-2 mb-4">  
<div class="card-body">    
  		
				<input type="hidden" id="voc_alert" name="voc_alert" value="<?=$voc_alert?>" size="5" > 	
				<input type="hidden" id="ma_alert" name="ma_alert" value="<?=$ma_alert?>" size="5" > 	
				<input type="hidden" id="order_alert" name="order_alert" value="<?=$order_alert?>" size="5" > 	
				<input type="hidden" id="page" name="page" value="<?=$page?>" size="5" > 	
				<input type="hidden" id="scale" name="scale" value="<?=$scale?>" size="5" > 	
				<input type="hidden" id="yearcheckbox" name="yearcheckbox" value="<?=$yearcheckbox?>" size="5" > 	
				<input type="hidden" id="year" name="year" value="<?=$year?>" size="5" > 	
				<input type="hidden" id="check" name="check" value="<?=$check?>" size="5" > 	
				<input type="hidden" id="output_check" name="output_check" value="<?=$output_check?>" size="5" > 	
				<input type="hidden" id="plan_output_check" name="plan_output_check" value="<?=$plan_output_check?>" size="5" > 	
				<input type="hidden" id="team_check" name="team_check" value="<?=$team_check?>" size="5" > 	
				<input type="hidden" id="measure_check" name="measure_check" value="<?=$measure_check?>" size="5" > 	
				<input type="hidden" id="cursort" name="cursort" value="<?=$cursort?>" size="5" > 	
				<input type="hidden" id="sortof" name="sortof" value="<?=$sortof?>" size="5" > 	
				<input type="hidden" id="stable" name="stable" value="<?=$stable?>" size="5" > 	
				<input type="hidden" id="sqltext" name="sqltext" value="<?=$sqltext?>" > 		
				<input type="hidden" id="list" name="list" value="<?=$list?>" > 				
		

<div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center"> 

<div class="table-responsive">
  <table class="table table-hover" id="myTable">
	<thead class="table-primary">
      <tr>
        <th class="text-center">번호</th>
        <th class="text-center">접수</th>
        <th class="text-center">현장명</th>
        <th class="text-center">발주처</th>
        <th class="text-center">타입</th>
        <th class="text-center">인승</th>
        <th class="text-center">Car inside</th>
        <th class="text-center">|본</th>
        <th class="text-center">L/C</th>
        <th class="text-center">기타</th>
        <th class="text-center">공청|</th>
        <th class="text-center">납품1</th>
        <th class="text-center">주문일</th>
        <th class="text-center">납품2</th>
        <th class="text-center">주문일</th>
        <th class="text-center">납품3</th>
        <th class="text-center">주문일</th>          
        <th class="text-center">비고</th>
        
      </tr>
    </thead>
    <tbody>		
		     
	<?php  		  
		$start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번		  
	    
		$sum = array(0, 0, 0, 0, 0, 0);
		
	       while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			  
			  include 'rowDB.php';
			  
			// 첨부파일이 있는경우 '(비규격)' 앞에 문구 넣어주는 루틴임
			for($i=0;$i<count($attach_arr);$i++)
	            if($attach_arr[$i] == $num)
					  $workplacename = '(비규격)' .  $workplacename;
			  
			  
			  $sum[0] = $sum[0] + (int)$su;
			  $sum[1] += (int)$bon_su;
			  $sum[2] += (int)$lc_su;
			  $sum[3] += (int)$etc_su;
			  $sum[4] += (int)$air_su;
			  $sum[5] += (int)$su + (int)$bon_su + (int)$lc_su + (int)$etc_su + (int)$air_su;
			  
			  $dis_text = " (종류별 합계)    결합단위 : " . $sum[0] . " (SET),  본천장 : " . $sum[1] . " (EA),  L/C : "  . $sum[2] . "  (EA), 기타 : "  . $sum[3] . "  (EA), 공기청정기 : "  . $sum[4] . " (EA) "; 			   			  

		      $workday=trans_date($workday);
		      $demand=trans_date($demand);
		      $orderday=trans_date($orderday);
		      $deadline=trans_date($deadline);
		      $testday=trans_date($testday);
		      $lc_draw=trans_date($lc_draw);
		      $lclaser_date=trans_date($lclaser_date);
		      $lcbending_date=trans_date($lcbending_date);
		      $lclwelding_date=trans_date($lclwelding_date);
		      $lcwelding_date=trans_date($lcwelding_date);
		      $lcassembly_date=trans_date($lcassembly_date);
		      $main_draw=trans_date($main_draw);			
		      $eunsung_make_date=trans_date($eunsung_make_date);			
		      $eunsung_laser_date=trans_date($eunsung_laser_date);			
		      $mainbending_date=trans_date($mainbending_date);			
		      $mainwelding_date=trans_date($mainwelding_date);			
		      $mainpainting_date=trans_date($mainpainting_date);			
		      $mainassembly_date=trans_date($mainassembly_date);										

		      $order_date1=trans_date($order_date1);					   
		      $order_date2=trans_date($order_date2);					   
		      $order_date3=trans_date($order_date3);					   
		      $order_date4=trans_date($order_date4);					   
		      $order_input_date1=trans_date($order_input_date1);					   
		      $order_input_date2=trans_date($order_input_date2);					   
		      $order_input_date3=trans_date($order_input_date3);					   
		      $order_input_date4=trans_date($order_input_date4);				  
			  	  				  
			  $state_work=0;
			  if($row["checkbox"]==0) $state_work=1;
			  if(substr($row["workday"],0,2)=="20") $state_work=2;
			  if(substr($row["endworkday"],0,2)=="20") $state_work=3;	
			  
			   $main_draw_arr="";			  
			  if(substr($main_draw,0,2)=="20")  $main_draw_arr= iconv_substr($main_draw,5,5,"utf-8");		    
			     elseif($bon_su<1) $main_draw_arr= "X";		    
   
   		        $lc_draw_arr="";			  
			  if(substr($lc_draw,0,2)=="20")  $lc_draw_arr= iconv_substr($lc_draw,5,5,"utf-8") ;
			     elseif($lc_su<1) $lc_draw_arr = "X";	
			  if($type=='011'||$type=='012'|| $type=='013D'||$type=='025'||$type=='017'||$type=='014'||$type=='037')
			                         $lc_draw_arr = "X";	
				 

			  $mainassembly_arr="";			  
			  if(substr($mainassembly_date,0,2)=="20")  
				      $mainassembly_arr= iconv_substr($mainassembly_date,5,5,"utf-8");		    
			     elseif($bon_su<1) 
				     $mainassembly_arr= "X";		    
   
			  $lcassembly_arr="";			  
			  if(substr($lcassembly_date,0,2)=="20")  
				  $lcassembly_arr= iconv_substr($lcassembly_date,5,5,"utf-8") ;
			     elseif($lc_su<1  || $type=='011'||$type=='012'|| $type=='013D'||$type=='025'||$type=='017'||$type=='014')
    				 $lcassembly_arr = "X";	
		

		  $sqltmp=" select * from " . $DB . ".ceilpicfile where parentnum ='$num'";	
		  $tmpmsg = "";
			 try{  
			// 레코드 전체 sql 설정
			   $stmhtmp = $pdo->query($sqltmp);    
			   
			   while($rowtmp = $stmhtmp->fetch(PDO::FETCH_ASSOC)) {
					$tmpmsg = "등록" ;
					}		 
			   } catch (PDOException $Exception) {
				print "오류: ".$Exception->getMessage();
			}  		
				 
				 
			 $workitem="";
				 
				 if($su!="")
					    $workitem= $su . " , "; 
				 if($bon_su!="")
					    $workitem .="본 " . $bon_su . ", "; 					
				 if($lc_su!="")
					    $workitem .="L/C " . $lc_su . ", "; 											
				 if($etc_su!="")
					    $workitem .="기타 "  . $etc_su . ", "; 																	
				 if($air_su!="")
					    $workitem .="공기청정기 "  . $air_su . " "; 																							
						
				 $part="";
				 if($order_com1!="")
					    $part= $order_com1 . "," ; 
				 if($order_com2!="")
					    $part .= $order_com2 . ", " ; 						
				 if($order_com3!="")
					    $part .= $order_com3 . ", " ; 												
				 if($order_com4!="")
					    $part .= $order_com4 . ", " ; 
						
                 $deli_text="";
				 if($delivery!="" || $delipay!=0)
				 		  $deli_text = $delivery . " " . $delipay ;  
           
			 ?>	 			  
		
	<tr  onclick="redirectToView('<?=$num?>')">
		<td class="text-center"> <?php echo echo_null($start_num); ?> </td>
		<td class="text-center"> <?php echo echo_null($orderday); ?> </td>               
		<td class="text-left"> <?php echo echo_null($workplacename); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($secondord, 0, 5, "utf-8")); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($type, 0, 5, "utf-8")); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($inseung, 0, 2, "utf-8")); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($car_insize, 0, 9, "utf-8")); ?> </td>
		<td class="text-center"> <?php echo echo_null($bon_su); ?> </td>
		<td class="text-center"> <?php echo echo_null($lc_su); ?> </td>
		<td class="text-center"> <?php echo echo_null($etc_su); ?> </td>
		<td class="text-center"> <?php echo echo_null($air_su); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($order_com1, 0, 4, "utf-8")); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($order_date1, 5, 5, "utf-8")); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($order_com2, 0, 4, "utf-8")); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($order_date2, 5, 5, "utf-8")); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($order_com3, 0, 4, "utf-8")); ?> </td>
		<td class="text-center"> <?php echo echo_null(iconv_substr($order_date3, 5, 5, "utf-8")); ?> </td>
		<td> <?php echo echo_null(iconv_substr($memo, 0, 8, "utf-8")); ?> </td>
	</tr>

		<?php	
		  $start_num--;
			 } 
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  }  

 ?>
 
         </tbody>
		 </table>
	   </div>
   </div>
  

   </div> <!--card-body-->
   </div> <!--card -->
   

 <div class="d-flex mb-1 mt-1 justify-content-center  align-items-center " >  
<? include '../footer_sub.php'; ?>
</div>
			
   </div>
	
</body>
  
<script> 


var dataTable; // DataTables 인스턴스 전역 변수
var workfrontlogpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {			
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 50,
        "lengthMenu": [25, 50, 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[0, 'desc']]
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('workfrontlogpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var workfrontlogpageNumber = dataTable.page.info().page + 1;
        setCookie('workfrontlogpageNumber', workfrontlogpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('workfrontlogpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('workfrontlogpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}


$(document).ready(function(){
	
$("#writeBtn").click(function(){ 	
    popupCenter('./estimate.php', '쟘 제품 단가입력', 1050, 600);	   
 
	});	
});	


function dis_text()
{  
		var dis_text = '<?php echo $dis_text; ?>';
		$("#dis_text").val(dis_text);
}	

function search_condition(con)
{	
			
				$("#check").val(con);							
				$("#page").val('1');							
				$("#search").val('');							
				$("#stable").val('0');							
				$('#board_form').submit();		// 검색버튼 효과
				
}

function button_condition(con)
{	
			
				$("#sortof").val(con);							
				$("#page").val('1');											
				$("#stable").val('0');							
				$('#board_form').submit();		// 검색버튼 효과
				
}

function redirectToView(num) {
	
    var page = workfrontlogpageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)
    	
    var url = "view.php?menu=no&num=" + num;         

	customPopup(url, '천장 수주내역', 1800, 850); 		    
}
	


</script>
  
  </html> 
 