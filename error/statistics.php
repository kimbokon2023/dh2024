<?php
if(!isset($_SESSION))      
		session_start(); 
if(isset($_SESSION["DB"]))
		$DB = $_SESSION["DB"] ;	
 $level= $_SESSION["level"];
 $user_name= $_SESSION["name"];
 $user_id= $_SESSION["userid"];	
 $WebSite = "http://8440.co.kr/"; 

$exclude_bad_choice = isset($_COOKIE['exclude_bad_choice']) ? $_COOKIE['exclude_bad_choice'] : 'false';
$Allmonth = isset($_COOKIE['Allmonth']) ? $_COOKIE['Allmonth'] : 'false';
   
 ?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>
  
 <title> 미래기업 부적합(품질) 통계 </title>
  
<body>

<? include $_SERVER['DOCUMENT_ROOT'] . '/myheader.php'; ?>   
	
 <?php 
   
 if(!isset($_SESSION["level"]) || $level>5) {
          /*   alert("관리자 승인이 필요합니다."); */
		 sleep(1);
	          header("Location:" . $WebSite . "login/login_form.php"); 
         exit;
   }
   
 
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
isset($_REQUEST["tabName"])  ? $tabName=$_REQUEST["tabName"] :  $tabName='';   // 신규데이터에 생성할때 임시저장키  
 
  if(isset($_REQUEST["load_confirm"]))   // 초기 당월 차트보이도록 변수를 저장하고 다시 부르면 실행되지 않도록 하기 위한 루틴
	 $load_confirm=$_REQUEST["load_confirm"];	 
  
  if(isset($_REQUEST["display_sel"]))   //목록표에 제목,이름 등 나오는 부분
	 $display_sel=$_REQUEST["display_sel"];	 
	 else
	   $display_sel='bar';	

  if(isset($_REQUEST["item_sel"]))   //목록표에 제목,이름 등 나오는 부분
	 $item_sel=$_REQUEST["item_sel"];	 
	 else
	   $item_sel='년도비교';	  

 if(isset($_REQUEST["check"])) 
	 $check=$_REQUEST["check"]; // 미출고 리스트 request 사용 페이지 이동버튼 누를시`
   else
     $check=$_POST["check"]; // 미출고 리스트 POST사용 
 

  $sum=array(); 
	 
  if(isset($_REQUEST["mode"]))
     $mode=$_REQUEST["mode"];
  else 
     $mode="";        
 
 if(isset($_REQUEST["find"]))   //목록표에 제목,이름 등 나오는 부분
 $find=$_REQUEST["find"]; 
  
// 기간을 정하는 구간
 
$fromdate=$_REQUEST["fromdate"];	 
$todate=$_REQUEST["todate"];	 

 // 당월을 날짜 기간으로 설정
 
	 if($fromdate=="")
	{
		$fromdate=date("Y",time()) ;
		$fromdate=$fromdate . "-01-01";
	}
	if($todate=="")
	{
		$todate=substr(date("Y-m-d",time()),0,4) . "-12-31";
		$Transtodate=strtotime($todate.'+1 days');
		$Transtodate=date("Y-m-d",$Transtodate);
	}
    else
	{
	$Transtodate=strtotime($todate);
	$Transtodate=date("Y-m-d",$Transtodate);
	}
 
  if(isset($_REQUEST["search"]))   
 $search=$_REQUEST["search"];

 $orderby=" order by workday desc "; 
	
$now = date("Y-m-d");	 // 현재 날짜와 크거나 같으면 출고예정으로 구분		

if($mode=="search"){
		  if($search==""){
					 $sql="select * from mirae8440.work where workday between date('$fromdate') and date('$Transtodate')" . $orderby;  			
			       }
			 elseif($search!="")
			    { 
					  $sql ="select * from mirae8440.work where  (bad_choice like '%$search%' ) and ( workday between date('$fromdate') and date('$Transtodate'))" . $orderby;				  		  		   
			     }    
}
	  
// print $search;
// print $sql;

   $counter=0;
   $workday_arr=array();
   $workplacename_arr=array();
   $address_arr=array();
   $sum_arr=array();
   $delicompany_arr=array();
   $delipay_arr=array();
   $secondord_arr=array();
   $worker_arr=array();
   $sum1=0;
   $sum2=0;
   $sum3=0;
   
if (!empty(trim($sql))) {   

 try{      
   $stmh = $pdo->prepare($sql);            // 검색조건에 맞는글 stmh
   $rowNum = $stmh->rowCount();  
   
   $total_row = 0;
   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {	
   
			  $checkstep=$row["checkstep"];
			  $workplacename=$row["workplacename"];
			  $address=$row["address"];
			  $firstord=$row["firstord"];
			  $firstordman=$row["firstordman"];
			  $firstordmantel=$row["firstordmantel"];
			  $secondord=$row["secondord"];
			  $secondordman=$row["secondordman"];
			  $secondordmantel=$row["secondordmantel"];
			  $chargedman=$row["chargedman"];
			  $chargedmantel=$row["chargedmantel"];
			  $orderday=$row["orderday"];
			  $measureday=$row["measureday"];
			  $drawday=$row["drawday"];
			  $deadline=$row["deadline"];
			  $workday=$row["workday"];
			  $worker=$row["worker"];
			  $endworkday=$row["endworkday"];
			  $material1=$row["material1"];
			  $material2=$row["material2"];
			  $material3=$row["material3"];
			  $material4=$row["material4"];
			  $material5=$row["material5"];
			  $material6=$row["material6"];
			  $widejamb=$row["widejamb"];
			  $normaljamb=$row["normaljamb"];
			  $smalljamb=$row["smalljamb"];
			  $memo=$row["memo"];
			  $regist_day=$row["regist_day"];
			  $update_day=$row["update_day"];
			  $demand=$row["demand"];  	   
			  $startday=$row["startday"];
			  $testday=$row["testday"];
			  $hpi=$row["hpi"];	   
			  $delicompany=$row["delicompany"];	   
			  $delipay=$row["delipay"];	   
	
		   
		   $workday_arr[$counter]=$workday;
		   $workplacename_arr[$counter]=$workplacename;
		   $address_arr[$counter]=$address;
		   $delicompany_arr[$counter]=$delicompany;   
		   $delipay_arr[$counter]=$delipay;   
		   $secondord_arr[$counter]=$secondord;   
		   $worker_arr[$counter]=$worker;   
		   
   
		   // 불량이란 단어가 들어가 있는 수량은 제외한다.		   
		   $findstr = '불량';

		   $pos = stripos($workplacename, $findstr);							   

		   if($pos==0)  {
   				 $workitem="";
				 if($widejamb!="")   {
					    $workitem="막판" . $widejamb . " "; 
						$sum1 += (int)$widejamb;
									}
				 if($normaljamb!="")   {
					    $workitem .="막(無)" . $normaljamb . " "; 					
						$sum2 += (int)$normaljamb;						
						}
				 if($smalljamb!="") {
					    $workitem .="쪽쟘" . $smalljamb . " "; 												   
						$sum3 += (int)$smalljamb;												
						}		   
				$sum_arr[$counter]=$workitem;
			}
		
	   $counter++;
	   $total_row++ ;
	   
	 } 	 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
 }  
}  

$all_sum = $sum1 + $sum2 + $sum3;		 
$jamb_total = "막판:" . $sum1 . ", " . "막판(無):" . $sum2 . ", " . "쪽쟘:" . $sum3  . "  합계:" . $all_sum;		 

$item_arr = array();	
$work_sum = array();	
$month_sum = array();	

$item_arr[0]='막판';
$item_arr[1]='막(無)';
$item_arr[2]='쪽쟘';

$work_sum[0]=$sum1;
$work_sum[1]=$sum2;
$work_sum[2]=$sum3;

$year=substr($fromdate,0,4) ;

 // print $fromdate . '   ' .  $Transtodate ;

if($item_sel=='월별비교') 
		{
		$month_count=0;      // 월별 차트 통계 내는 부분
		while($month_count<12)		 
		{	
	
					$year=substr($fromdate,0,4) ;
					
					$month=$month_count + 1;
						switch ($month_count) {
							case   0   :   $day=31; break;
							case   1   :   $day=28; break;
							case   2   :   $day=31; break;
							case   3   :   $day=30; break;
							case   4   :   $day=31; break;
							case   5   :   $day=30; break;
							case   6   :   $day=31; break;
							case   7   :   $day=31; break;
							case   8   :   $day=30; break;
							case   9   :   $day=31; break;
							case   10  :   $day=30; break;
							case   11  :   $day=31; break;

						}
					  
					$month_fromdate = sprintf("%04d-%02d-%02d", $year, $month, 1);  // 날짜형식으로 바꾸기
					$month_todate = sprintf("%04d-%02d-%02d", $year, $month, $day);  // 날짜형식으로 바꾸기
					
					$sql="select * from mirae8440.work where workday between date('$month_fromdate') and date('$month_todate') order by workday desc" ;					
					$counter=0;
					$sum1=0;
					$sum2=0;
					$sum3=0;

					 try{  
						$stmh = $pdo->prepare($sql);            // 검색조건에 맞는글 stmh
					   $rowNum = $stmh->rowCount();  
					    $total_row = 0;
					   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {	

								  $widejamb=$row["widejamb"];
								  $normaljamb=$row["normaljamb"];
								  $smalljamb=$row["smalljamb"];
								  $workplacename=$row["workplacename"];										  
						   
							   // 불량이란 단어가 들어가 있는 수량은 제외한다.		   
							   $findstr = '불량';

							   $pos = stripos($workplacename, $findstr);							   
 
							   if($pos==0)  {									  
					   
									 $workitem="";
									 if($widejamb!="")   {
											$workitem="막판" . $widejamb . " "; 
											$sum1 += (int)$widejamb;
														}
									 if($normaljamb!="")   {
											$workitem .="막(無)" . $normaljamb . " "; 					
											$sum2 += (int)$normaljamb;						
											}
									 if($smalljamb!="") {
											$workitem .="쪽쟘" . $smalljamb . " "; 												   
											$sum3 += (int)$smalljamb;												
											}
					   
									$sum_arr[$counter]=$workitem;		
								   $counter++;	   
									$total_row++;
							   }
						 } 	 
					   } catch (PDOException $Exception) {
						print "오류: ".$Exception->getMessage();
					}  

					$month_sum[$month_count]= $sum1 + $sum2 + $sum3/4;

					$month_count++;

		}
}

if($item_sel=='년도비교') 
		{
				$year_count=0;
				$date_count=0;    // 24개월을 기준으로 데이터를 작성하는 합계변수 2020년1월 2021년1월 이런식으로 계산할 것임.
				$year_sum = array();	
				$year=substr($fromdate,0,4) -1;				
				
			while($year_count<2)	
			  {
				$month_count=0;      // 년도비교 차트 통계 내는 부분
				while($month_count<12)		 
				{		 

							$month=$month_count + 1;
								switch ($month_count) {
									case   0   :   $day=31; break;
									case   1   :   $day=28; break;
									case   2   :   $day=31; break;
									case   3   :   $day=30; break;
									case   4   :   $day=31; break;
									case   5   :   $day=30; break;
									case   6   :   $day=31; break;
									case   7   :   $day=31; break;
									case   8   :   $day=30; break;
									case   9   :   $day=31; break;
									case   10  :   $day=30; break;
									case   11  :   $day=31; break;

								}
							  
							$month_fromdate = sprintf("%04d-%02d-%02d", $year, $month, 1);  // 날짜형식으로 바꾸기
							$month_todate = sprintf("%04d-%02d-%02d", $year, $month, $day);  // 날짜형식으로 바꾸기
							
							$sql="select * from mirae8440.work where workday between date('$month_fromdate') and date('$month_todate')" ;							
							$counter=0;
							$sum1=0;
							$sum2=0;
							$sum3=0;

							 try{  
							   $stmh = $pdo->prepare($sql);            // 검색조건에 맞는글 stmh
							   $rowNum = $stmh->rowCount();  
							   $total_row = 0;
							   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {	

										  $widejamb=$row["widejamb"];
										  $normaljamb=$row["normaljamb"];
										  $smalljamb=$row["smalljamb"];										  
										  $workplacename=$row["workplacename"];										  
						   
							   // 불량이란 단어가 들어가 있는 수량은 제외한다.		   
							   $findstr = '불량';

							   $pos = stripos($workplacename, $findstr);							   
 
							   if($pos==0)  {							   
											 $workitem="";
											 if($widejamb!="")   {
													$sum1 += (int)$widejamb;
																}
											 if($normaljamb!="")   {
													$sum2 += (int)$normaljamb;						
													}
											 if($smalljamb!="") {
													$sum3 += (int)$smalljamb;												
													}							   
								   $counter++;	   
								   $total_row++;
									}
								 } 	 
							   } catch (PDOException $Exception) {
								print "오류: ".$Exception->getMessage();
							}  

							$year_sum[$date_count]= $sum1 + $sum2 + $sum3/4;
							$month_count++;
							$date_count++;
				   }
			  $year_count++;
			  $year++;			   
				   
			}
}

?>

<form name="board_form" id="board_form"  method="post" action="statistics.php?mode=search&year=<?=$year?>&search=<?=$search?>&process=<?=$process?>&asprocess=<?=$asprocess?>&fromdate=<?=$fromdate?>&todate=<?=$todate?>&up_fromdate=<?=$up_fromdate?>&up_todate=<?=$up_todate?>&separate_date=<?=$separate_date?>&view_table=<?=$view_table?>">  
   <input type="hidden" id="tabName" name="tabName"  value="<?=$tabName?>" >	

<div class="container mt-2 mb-5">
<div class="d-flex mb-1 mt-2 justify-content-center align-items-center ">   
    <h4> 원자재 부적합 통계 </h4> 
</div>	
<div class="row"> 		  
	<div class="d-flex mt-1 mb-2 justify-content-center align-items-center "> 		
	<!-- 기간설정 칸 -->
	 <?php include $_SERVER['DOCUMENT_ROOT'] . '/setdate.php' ?>
	</div>
</div>

<div id="qualityIssues" class="tabcontent" style="display: none;">
   	
<!-- '소장제외' 체크박스 추가 -->
<div class="d-flex mb-3 mt-2 justify-content-center">
  <h4>
    <label>
      <input type="checkbox" id="Allmonth" name="Allmonth" value="<?=$Allmonth?>" <?= $Allmonth === 'true' ? 'checked' : '' ?> onchange="updateAllmonth()">
      <span class="checkmark"></span>
      (기간무시)월별그래프 표시
    </label>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <label>
      <input type="checkbox" id="exclude_bad_choice" name="exclude_bad_choice" value="<?=$exclude_bad_choice?>" <?= $exclude_bad_choice === 'true' ? 'checked' : '' ?> onchange="updateGraph()">
      <span class="checkmark"></span>
      소장불량/업체불량 제외
    </label>    
    
  </h4>
</div>

	
<?

$readIni = array();   // 환경파일 불러오기
$readIni = parse_ini_file("../steel/settings.ini",false);	
			  					   
$PO=$readIni['PO'];
$CR=$readIni['CR'];
$EGI=$readIni['EGI'];
$HL304=$readIni['HL304'];
$MR304=$readIni['MR304'];
$etcsteel=$readIni['etcsteel'];

$price_per_kg = [
    'CR' => $CR,
    'PO' => $PO,
    'EGI' => $EGI,
    '304 HL' => $HL304,
    '201 2B MR' => '3.0',
    '201 MR' => '3.0',
    '201 HL' => '2.8',
    '304 MR' => $MR304,
    'etcsteel' => $etcsteel
];

// var_dump($exclude_bad_choice) ;

		 // 소장 체크 제외인경우
	if($exclude_bad_choice !=='false') 
		$sql = "SELECT EXTRACT(YEAR FROM outdate) AS year, EXTRACT(MONTH FROM outdate) AS month, item, spec, steelnum, bad_choice FROM mirae8440.steel WHERE (bad_choice IS NOT NULL AND bad_choice != '' AND bad_choice != '해당없음'  AND bad_choice != '소장' AND bad_choice != '업체') AND outdate BETWEEN date('$fromdate') AND date('$Transtodate') ORDER BY year, month";
	else
		$sql = "SELECT EXTRACT(YEAR FROM outdate) AS year, EXTRACT(MONTH FROM outdate) AS month, item, spec, steelnum, bad_choice FROM mirae8440.steel WHERE (bad_choice IS NOT NULL AND bad_choice != '' AND bad_choice != '해당없음' ) AND outdate BETWEEN date('$fromdate') AND date('$Transtodate') ORDER BY year, month";	
 
$total_sum = 0;

try{  
    // 레코드 전체 sql 설정
    $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh

		while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {			
			
			$item = trim($row["item"]);
			$spec = trim($row["spec"]);
			$steelnum = $row["steelnum"];
			$bad_choice = $row["bad_choice"];

			$spec_parts = explode('*', $spec);
			$thickness = $spec_parts[0];
			$width = $spec_parts[1];
			$length = $spec_parts[2];

			$weight = $thickness * $width * $length/1000;
          if((int)$steelnum !== 0)  // 수량이 1이상일때 잔재는 제외함
		  {
			if (array_key_exists($item, $price_per_kg)) {
				$price = $price_per_kg[$item];
			} else {
				$price = $price_per_kg['etcsteel'];
			}

			$total_price = ($weight * $price * $steelnum * 7.93);
			$total_sum += $total_price;
		  }
		  else
		  {
			$total_price = 0 ;
			$total_sum += $total_price;			  
		  }
		}

} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}	

// 최종 결과를 포맷팅하여 출력
$formatted_total_sum = number_format($total_sum, 0, '.', ',');

	echo '
	<div class="container">
	  <div class="row justify-content-center">
		<div class="col-5">
		  <div class="card shadow-sm bg-danger text-white m-1 p-1">
			<div class="card-body text-center">
			  <h4 class="card-title">부적합 비용</h4>
			  <h4 class="card-text mb-1">' . $formatted_total_sum . '원</h4>
			</div>
		  </div>
		</div>
	  </div>
	</div>';


// 차트를 구현하는 부분
// 소장을 제외하는 코드로 일부 수정함

// 소장 체크 제외인경우
if($Allmonth === 'true')
{
		 // 소장/업체 체크 제외인경우
	if($exclude_bad_choice ==='false') 
			$sql = "SELECT EXTRACT(YEAR FROM outdate) AS year, EXTRACT(MONTH FROM outdate) AS month, item, spec, steelnum, bad_choice FROM mirae8440.steel WHERE (bad_choice IS NOT NULL AND bad_choice != '' AND bad_choice != '해당없음') ORDER BY year, month";	
	else
			$sql = "SELECT EXTRACT(YEAR FROM outdate) AS year, EXTRACT(MONTH FROM outdate) AS month, item, spec, steelnum, bad_choice FROM mirae8440.steel WHERE (bad_choice IS NOT NULL AND bad_choice != '' AND bad_choice != '해당없음' AND bad_choice != '소장'  AND bad_choice != '업체' ) ORDER BY year, month";	
		 // 소장/업체 체크 제외인경우
}
else
{
	if($exclude_bad_choice ==='false') 
		$sql = "SELECT EXTRACT(YEAR FROM outdate) AS year, EXTRACT(MONTH FROM outdate) AS month, item, spec, steelnum, bad_choice FROM mirae8440.steel WHERE (bad_choice IS NOT NULL AND bad_choice != '' AND bad_choice != '해당없음') AND outdate BETWEEN date('$fromdate') AND date('$Transtodate') ORDER BY year, month";
	else
		$sql = "SELECT EXTRACT(YEAR FROM outdate) AS year, EXTRACT(MONTH FROM outdate) AS month, item, spec, steelnum, bad_choice FROM mirae8440.steel WHERE (bad_choice IS NOT NULL AND bad_choice != '' AND bad_choice != '해당없음'  AND bad_choice != '소장'  AND bad_choice != '업체'  ) AND outdate BETWEEN date('$fromdate') AND date('$Transtodate') ORDER BY year, month";
	
 }



//print 'allmonth ' . $Allmonth ; 
// var_dump($sql) ; 


//  다음 월별/년도별 합계를 계산하기 위해 다음과 같이 코드를 수정합니다.
$monthly_totals = [];
 
$total_sum = 0;

try{  
    // 레코드 전체 sql 설정
    $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh

while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
	
	        $item = trim($row["item"]);
			$spec = trim($row["spec"]);
			$steelnum = $row["steelnum"];
			$bad_choice = $row["bad_choice"];

			$spec_parts = explode('*', $spec);
			$thickness = $spec_parts[0];
			$width = $spec_parts[1];
			$length = $spec_parts[2];

			$weight = $thickness * $width * $length/1000;
          if((int)$steelnum !== 0)  // 수량이 1이상일때 잔재는 제외함
		  {
			if (array_key_exists($item, $price_per_kg)) {
				$price = $price_per_kg[$item];
			} else {
				$price = $price_per_kg['etcsteel'];
			}

			$total_price = ($weight * $price * $steelnum * 7.93);
			$total_sum += $total_price;
		  }
		  else
		  {
			$total_price = 0 ;
			$total_sum += $total_price;			  
		  }	

    $year = $row["year"];
    $month = $row["month"];

    if (!isset($monthly_totals["$year-$month"])) {
        $monthly_totals["$year-$month"] = 0;
    }

    $monthly_totals["$year-$month"] += $total_price;
}

} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}	

		 // 소장 체크 제외인경우
	if($exclude_bad_choice !=='false') 
		$sql = "SELECT EXTRACT(YEAR FROM outdate) AS year, EXTRACT(MONTH FROM outdate) AS month, outdate, outworkplace, item, spec, steelnum, bad_choice FROM mirae8440.steel WHERE (bad_choice IS NOT NULL AND bad_choice != '' AND bad_choice != '해당없음' AND bad_choice != '소장' AND bad_choice != '업체') AND outdate BETWEEN date('$fromdate') AND date('$Transtodate') ORDER BY outdate desc ";
	else
		$sql = "SELECT EXTRACT(YEAR FROM outdate) AS year, EXTRACT(MONTH FROM outdate) AS month, outdate, outworkplace, item, spec, steelnum, bad_choice FROM mirae8440.steel WHERE (bad_choice IS NOT NULL AND bad_choice != '' AND bad_choice != '해당없음') AND outdate BETWEEN date('$fromdate') AND date('$Transtodate') ORDER BY  outdate desc ";	


 
$total_sum = 0;

try{  
    // 레코드 전체 sql 설정
    $stmh = $pdo->query($sql);    
?>
    <div id="mychart" style="width: 100%; height: 400px;"></div>

    <div class="container">
        <h4 class="my-4">부적합(품질불량) 세부내역 </h4>
		<table class="table table-hover" id="myTable">
		   <thead class="table-primary" >
                <tr>
                    <th class=" text-center">출고일</th>                    
					<th class=" text-center">불량유형</th>
                    <th class=" text-center">현장명</th>
                    <th class=" text-center">종류</th>
                    <th class=" text-center">규격</th>
                    <th class=" text-center">수량</th>
                    <th class=" text-center">발생비용</th>                    
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
					$total_price = 0;
					$item = trim($row["item"]);
					$spec = trim($row["spec"]);
					$steelnum = $row["steelnum"];
					$bad_choice = $row["bad_choice"];

					$spec_parts = explode('*', $spec);
					$thickness = $spec_parts[0];
					$width = $spec_parts[1];
					$length = $spec_parts[2];

					$spec_parts = explode('*', $spec);
					$thickness = $spec_parts[0];
					$width = $spec_parts[1];
					$length = $spec_parts[2];

					$weight = $thickness * $width * $length/1000;
				  if((int)$steelnum !== 0)  // 수량이 1이상일때 잔재는 제외함
				  {
					if (array_key_exists($item, $price_per_kg)) {
						$price = $price_per_kg[$item];
					} else {
						$price = $price_per_kg['etcsteel'];
					}

					$total_price = ($weight * $price * (int)$steelnum * 7.93);
					$formatted_total_sum = number_format($total_price, 0, '.', ',');
				  }
				   else
				   {
					   $total_price = 0 ;
					   $formatted_total_sum = number_format($total_price, 0, '.', ',');
					   $spec = '잔재활용';
				   }
				  
				?>
                    <tr>
                        <td class=" text-center"><?= htmlspecialchars($row['outdate']) ?></td>
					    <td class=" text-center"><?= htmlspecialchars($row['bad_choice']) ?></td>
                        <td class=" text-start"><?= htmlspecialchars($row['outworkplace']) ?></td>
                        <td class=" text-center"><?= htmlspecialchars($row['item']) ?></td>
                        <td class=" text-center"><?= htmlspecialchars($spec) ?></td>
                        <td class=" text-center"><?= htmlspecialchars($steelnum) ?></td>
                        <td class="text-end" ><?= $formatted_total_sum . '원' ?></td>
                      
                    </tr>
                <?php } ?>
            </tbody>
        </table>
		
<?php
} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}			


?>		
    </div>
        </div>			
				
	 
	 </div>
</form>
</body>
</html>
<script>


var dataTable; // DataTables 인스턴스 전역 변수
var errorstatpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('errorstatpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var errorstatpageNumber = dataTable.page.info().page + 1;
        setCookie('errorstatpageNumber', errorstatpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('errorstatpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('errorstatpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}


$(document).ready(function(){	

      // PHP에서 계산된 월별/년도별 합계를 JavaScript로 전달
        const monthly_totals = <?php echo json_encode($monthly_totals); ?>;

        // x축 라벨 및 데이터 시리즈 준비
        const categories = Object.keys(monthly_totals);
        const data = Object.values(monthly_totals).map(total => parseFloat(total.toFixed(2)));

        // 그래프 생성
        Highcharts.chart('mychart', {
            chart: {
                type: 'column'
            },
            title: {
                text: '원자재 월별 부적합 비용'
            },
            xAxis: {
                categories: categories,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: '비용 (원)'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:15px">{point.key}</span><table>',
					pointFormatter: function() {
						return '<tr><td style="color:' + this.series.color + ';padding:0">' + this.series.name + ': </td>' +
							'<td style="padding:0"><b>' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' 원</b></td></tr>';
					},
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
                name: '부적합 비용',
                data: data
            }]
        });

      const tabName =  '<?php echo $tabName; ?>';  
	  
	  console.log(tabName);

	  openTab('qualityIssues');

});

function updateAllmonth() {
    let isChecked = document.getElementById('Allmonth').checked;
    document.cookie = "Allmonth=" + isChecked + ";path=/";    
	document.getElementById('board_form').submit(); 
}

function updateGraph() {
    let isChecked = document.getElementById('exclude_bad_choice').checked;
    document.cookie = "exclude_bad_choice=" + isChecked + ";path=/";    
	document.getElementById('board_form').submit(); 
}

function openTab(tabName) {
	var i, tabcontent, tablinks;
	tabcontent = document.getElementsByClassName("tabcontent");
	for (i = 0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	}
	tablinks = document.getElementsByClassName("tablinks");
	for (i = 0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	}
	document.getElementById(tabName).style.display = "block";
	// currentTarget.className += " active";
	
	$('#tabName').val(tabName);	
	
}


   </script> 
  
  
  </body>

  
<script> 
function comma(str) { 
    str = String(str); 
    return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'); 
} 
function uncomma(str) { 
    str = String(str); 
    return str.replace(/[^\d]+/g, ''); 
}

function List_name(worker)
{	
		var worker; 				
		var name='<?php echo $user_name; ?>' ;
		 
			$("#search").val(worker);	
			$('#board_form').submit();		// 검색버튼 효과
}
</script>
  </html>
  
<script>
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

$('input[name="item_sel"]').change(function() {
    // 모든 radio를 순회한다.
    $('input[name="item_sel"]').each(function() {
        var value = $(this).val();              // value
        var checked = $(this).prop('checked');  // jQuery 1.6 이상 (jQuery 1.6 미만에는 prop()가 없음, checked, selected, disabled는 꼭 prop()를 써야함)
        // var checked = $(this).attr('checked');   // jQuery 1.6 미만 (jQuery 1.6 이상에서는 checked, undefined로 return됨)
        // var checked = $(this).is('checked');
        var $label = $(this).next(); 
        if(checked)  {
           $("#item_sel").val(value);
	       document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 	
		}

    });
});
					
    var item_arr = <?php echo json_encode($item_arr);?> ;
    var work_sum = <?php echo json_encode($work_sum);?> ;
    var month_sum = <?php echo json_encode($month_sum);?> ;
    var year_sum = <?php echo json_encode($year_sum);?> ;
	var ctx = document.getElementById('myChart');
    var chart_type = document.getElementById('display_sel').value;
    var item_type = document.getElementById('item_sel').value;

    if(item_type=='종류별비교') 
					var myChart = new Chart(ctx, {
						type: chart_type,
						data: {
							labels: [item_arr[0], item_arr[1], item_arr[2]],
							datasets: [{
								label: '#쟘 제작수량 합계 ',
								data: [work_sum[0], work_sum[1], work_sum[2]],
								backgroundColor: [
									'rgba(255, 99, 132, 0.2)',
									'rgba(54, 162, 235, 0.2)',
									'rgba(255, 206, 86, 0.2)',
									'rgba(75, 192, 192, 0.2)',
									'rgba(153, 102, 255, 0.2)',
									'rgba(205, 100, 25, 0.2)',
									'rgba(25, 66, 200, 0.2)',
									'rgba(95, 452, 60, 0.2)',
									'rgba(113, 62, 55, 0.2)',
									'rgba(255, 99, 132, 0.2)',
									'rgba(54, 162, 235, 0.2)',					
									'rgba(255, 159, 64, 0.2)'					
								],
								borderColor: [
									'rgba(255, 99, 132, 1)',
									'rgba(54, 162, 235, 1)',
									'rgba(255, 206, 86, 1)',
									'rgba(75, 192, 192, 1)',
									'rgba(153, 102, 255, 1)',
									'rgba(205, 100, 25, 1)',
									'rgba(25, 66, 200, 1)',
									'rgba(95, 452, 60, 1)',
									'rgba(113, 62, 55, 1)',
									'rgba(255, 99, 132, 1)',
									'rgba(54, 162, 235, 1)',
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
					

    if(item_type=='월별비교') 
	{
		item_arr[0] = '1월';
		item_arr[1] = '2월';
		item_arr[2] = '3월';
		item_arr[3] = '4월';
		item_arr[4] = '5월';
		item_arr[5] = '6월';
		item_arr[6] = '7월';
		item_arr[7] = '8월';
		item_arr[8] = '9월';
		item_arr[9] = '10월';
		item_arr[10] = '11월';
item_arr[11] = '12월';


		var myChart = new Chart(ctx, {
			type: chart_type,
			data: {
				labels: [item_arr[0], item_arr[1], item_arr[2],item_arr[3], item_arr[4], item_arr[5],item_arr[6], item_arr[7], item_arr[8],item_arr[9], item_arr[10],item_arr[11] ],
				datasets: [{
					label: '#쟘 제작수량 합계 ',
					data: [ month_sum[0], month_sum[1], month_sum[2],month_sum[3], month_sum[4], month_sum[5],month_sum[6], month_sum[7], month_sum[8],month_sum[9], month_sum[10],month_sum[11] ],
					backgroundColor: [
						'rgba(255, 99, 132, 0.2)',
						'rgba(54, 162, 235, 0.2)',
						'rgba(255, 206, 86, 0.2)',
						'rgba(75, 192, 192, 0.2)',
						'rgba(153, 102, 255, 0.2)',
						'rgba(205, 100, 25, 0.2)',
						'rgba(25, 66, 200, 0.2)',
						'rgba(95, 452, 60, 0.2)',
						'rgba(113, 62, 55, 0.2)',
						'rgba(255, 99, 132, 0.2)',
						'rgba(54, 162, 235, 0.2)',					
						'rgba(255, 159, 64, 0.2)'					
					],
					borderColor: [
						'rgba(255, 99, 132, 1)',
						'rgba(54, 162, 235, 1)',
						'rgba(255, 206, 86, 1)',
						'rgba(75, 192, 192, 1)',
						'rgba(153, 102, 255, 1)',
						'rgba(205, 100, 25, 1)',
						'rgba(25, 66, 200, 1)',
						'rgba(95, 452, 60, 1)',
						'rgba(113, 62, 55, 1)',
						'rgba(255, 99, 132, 1)',
						'rgba(54, 162, 235, 1)',
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
	}
	
	
function generateChartColors() {
  const baseColors = [
		'rgba(128, 128, 128, 0.2)',
		'rgba(54, 162, 235, 0.2)',
		'rgba(130, 130, 130, 0.2)',
		'rgba(75, 192, 192, 0.2)',
		'rgba(132, 132, 132, 0.2)',
		'rgba(205, 100, 25, 0.2)',
		'rgba(134, 134, 134, 0.2)',
		'rgba(95, 452, 60, 0.2)',
		'rgba(136, 136, 136, 0.2)',
		'rgba(255, 99, 132, 0.2)',
		'rgba(138, 138, 138, 0.2)',				
		'rgba(255, 159, 64, 0.2)' ,					
		'rgba(126, 126, 126, 0.2)',
		'rgba(54, 162, 235, 0.2)',
		'rgba(128, 128, 128, 0.2)',
		'rgba(75, 192, 192, 0.2)',
		'rgba(130, 130, 130, 0.2)',
		'rgba(205, 100, 25, 0.2)',
		'rgba(132, 132, 132, 0.2)',
		'rgba(95, 452, 60, 0.2)',
		'rgba(134, 134, 134, 0.2)',
		'rgba(255, 99, 132, 0.2)',
		'rgba(136, 136, 136, 0.2)',				
		'rgba(255, 159, 64, 0.2)'	
  ];

  // borderColor는 같은 색상이지만 투명도가 다릅니다.
  const borderColors = baseColors.map(color => color.replace(/0.2\)$/, '1)'));

  // backgroundColors와 borderColors를 순서대로 번갈아 가며 확장합니다.
  const backgroundColors = [], borderColorsExtended = [];
  for (let i = 0; i < baseColors.length; i++) {
    backgroundColors.push(baseColors[i], baseColors[i]); // 같은 색상을 두 번 추가
    borderColorsExtended.push(borderColors[i], borderColors[i]); // 같은 색상을 두 번 추가
  }

  return { backgroundColors, borderColors: borderColorsExtended };
}

if (item_type == '년도비교') {
  const { backgroundColors, borderColors } = generateChartColors();

  var myChart = new Chart(ctx, {
    type: chart_type,
    data: {
      labels: [ 
		'전년1월',
		'1월',
		'전년2월',
		'2월',
		'전년3월',
		'3월',
		'전년4월',
		'4월',
		'전년5월',
		'5월',
		'전년6월',
		'6월',
		'전년7월',
		'7월',
		'전년8월',
		'8월',
		'전년9월',
		'9월',
		'전년10월',
		'10월',
		'전년11월',
		'11월',
		'전년12월',
		'12월'
      ],
      datasets: [{
        label: '# 쟘 전년도 제작수량 합계 , #금년도 제작수량',
        data: [ year_sum[0], year_sum[12], year_sum[1],year_sum[13], year_sum[2], year_sum[14],year_sum[3], year_sum[15], year_sum[4],year_sum[16], year_sum[5],year_sum[17], year_sum[6], year_sum[18], year_sum[7],year_sum[19], year_sum[8], year_sum[20],year_sum[9], year_sum[21], year_sum[10],year_sum[22], year_sum[11],year_sum[23] 
        ],
        backgroundColor: backgroundColors,
        borderColor: borderColors,
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
}
	
	

</script>  