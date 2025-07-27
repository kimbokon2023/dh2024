<?php
if(!isset($_SESSION))      
	session_start(); 
if(isset($_SESSION["DB"]))
	$DB = $_SESSION["DB"] ;	
$level= $_SESSION["level"];
$user_name= $_SESSION["name"];
$user_id= $_SESSION["userid"];	

$WebSite = "http://8440.co.kr/";	

 if(!isset($_SESSION["level"]) || $level>5) {	     
		 sleep(1);
         header ("Location:" . $WebSite . "login/logout.php");         
         exit;
   }       
$title_message = '원자재 입출고';      

?>  

<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?> 
<title> <?=$title_message?> </title>  

<style>

th, td {
    vertical-align: middle !important;
}
</style>
</head>
 
<body>  

<?php	      
include "../load_company.php";
	  
	if(isset($_REQUEST["mode"]))  //수정 버튼을 클릭해서 호출했는지 체크
		$mode=$_REQUEST["mode"];
	else
		$mode="";
  
	if(isset($_REQUEST["num"]))  //수정 버튼을 클릭해서 호출했는지 체크
		$num=$_REQUEST["num"];
	else
		$num="";
   
$fromdate=$_REQUEST["fromdate"];	 
$todate=$_REQUEST["todate"];
  
 // 철판종류에 대한 추출부분
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	
   
$sql="select * from mirae8440.steelsource order by sortorder asc, item asc, spec asc"; 	// 정렬순서 정함.				

$sum_title=array(); 
$sum= array();
$company_arr = array();

 try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $rowNum = $stmh->rowCount();  
   $counter=0;
   $steelsource_num=array();
   $steelsource_item=array();
   $steelsource_spec=array();
   $steelsource_take=array();   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {	   
 			  $steelsource_num[$counter]=$row["num"];			  
 			  $steelsource_item[$counter]=trim($row["item"]);
 			  $steelsource_spec[$counter]=trim($row["spec"]);
		      $steelsource_take[$counter]=trim($row["take"]);  
              array_push($sum_title, $steelsource_item[$counter] . $steelsource_spec[$counter]. $steelsource_take[$counter]) ;
              array_push($company_arr, $steelsource_take[$counter]) ;
	   $counter++;
	 } 	 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  

$sum_title = array_unique($sum_title);  // 고유번호이름만 살리기
sort($sum_title);  // 고유번호이름만 살리기

$steelsource_item = array_values(array_filter($steelsource_item, 'strlen'));
$steelsource_item = array_values(array_unique($steelsource_item));
sort($steelsource_item);
$sumcount = count($steelsource_item);

 // 전체합계(입고/출고)를 산출하는 부분 
$sql="select * from mirae8440.steel order by outdate";
 
$tmpsum = 0; 

 try{  
// 레코드 전체 sql 설정
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {

			  $outdate=$row["outdate"];			  
			  $item=trim($row["item"]);			  
			  $spec=trim($row["spec"]);
			  $steelnum=$row["steelnum"];			  
			  $company=$row["company"];
			  $comment=$row["comment"];
			  $which=$row["which"];	 				  
	
			  $tmp=$item . $spec . $company;
	
        for($i=0;$i<count($sum_title) ; $i++) {  	          
			  if($which=='1' and $tmp==$sum_title[$i])
				     $sum[$i]= $sum[$i] + (int)$steelnum;		// 입고숫자 더해주기 합계표	
			  if($which=='2' and $tmp==$sum_title[$i])
				    $sum[$i] =  $sum[$i] - (int)$steelnum;
		           }
			}		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  
      

// var_dump( $sum); 
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
  
// // json파일로 만들기 시작
// // 현재 날짜
// $today = new DateTime();

// // 한 달 전의 첫 날짜를 계산 (예: 2024년 2월 21일인 경우 2024년 1월 1일을 얻음)
// $firstDayOfLastMonth = new DateTime('first day of last month');

// // 해당 달의 마지막 날짜를 계산 (예: 2024년 1월 31일을 얻음)
// $lastDayOfLastMonth = new DateTime('last day of last month');

// // 매월 1일이 되면 전전달까지의 데이터 계산 로직
// if ($today->format('j') === '1') {
    // // 이 경우에는 전전달의 마지막 날짜를 계산합니다.
    // // 예: 2024년 2월 1일인 경우 2023년 12월 31일을 얻습니다.
    // $lastDayOfLastLastMonth = new DateTime('last day of -2 month');
    // // 이 날짜를 기준으로 데이터 계산 로직을 실행합니다.
    // // ...
// }

// // 날짜 형식에 따른 문자열 출력 (디버깅 용도)
// // echo "한 달 전의 첫 날짜: " . $firstDayOfLastMonth->format('Y-m-d') . "<br>";
// // echo "한 달 전의 마지막 날짜: " . $lastDayOfLastMonth->format('Y-m-d') . "<br>";
// if (isset($lastDayOfLastLastMonth)) {
   // //  echo "전전달의 마지막 날짜: " . $lastDayOfLastLastMonth->format('Y-m-d') . "<br>";
    // $oneMonthAgo =  $lastDayOfLastLastMonth->format('Y-m-d') ;
// }

// // $oneMonthAgo = new DateTime('first day of last month');

// $jsonFileName = "../steel_monthly_summary.json"; 

// if ($today->format('j') === '1') {
    // // 매월 첫째 날에는 전달 데이터를 계산하여 저장
    // $monthlySummary = calculateMonthlySummary($pdo, $oneMonthAgo);
    // file_put_contents($jsonFileName, json_encode($monthlySummary, JSON_PRETTY_PRINT));
// } else {
    // // 그 외 날짜에는 기존의 JSON 파일을 사용
    // if (file_exists($jsonFileName)) {
        // $jsonData = file_get_contents($jsonFileName);
        // $monthlySummary = json_decode($jsonData, true);
    // } else {
        // // JSON 파일이 없는 경우 새로 계산
        // $monthlySummary = calculateMonthlySummary($pdo, $oneMonthAgo);
        // file_put_contents($jsonFileName, json_encode($monthlySummary, JSON_PRETTY_PRINT));
    // }
// }

// // $monthlySummary에서 $sum_title과 $sum을 사용할 수 있습니다.
// // 예: $sum_title = $monthlySummary['sum_title'];
// //     $sum = $monthlySummary['sum'];

// function calculateMonthlySummary($pdo, $date) {

	// require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
	// $pdo = db_connect();

	 // // 전체합계(입고부분)를 산출하는 부분 
	// $sum_title=array(); 
	// $sum= array();

	// $tmpsum = 0; 

	// $sql="select * from mirae8440.steel ";

	 // try{  
	// // 레코드 전체 sql 설정
	   // $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
	   // while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
		   
				  // $outdate=$row["outdate"];			  
				  // $item=$row["item"];			  
				  // $spec=$row["spec"];
				  // $steelnum=$row["steelnum"];			  
				  // $company=$row["company"];
				  // $comment=$row["comment"];
				  // $which=$row["which"];	 	

				  // $tmp=$item . $spec . $company;
		
			// for($i=0;$i<=$sumcount;$i++) {  
				  // $sum_title[$i]= trim($steelsource_item[$i]) . trim($steelsource_spec[$i]) . trim($steelsource_take[$i]) ;	          
				  // if($which=='1' and $tmp==$sum_title[$i])
						  // $sum[$i]= $sum[$i] + intval($steelnum);			// 입고숫자 더해주기 합계표	
					   // }
				// }		 
	   // } catch (PDOException $Exception) {
		// print "오류: ".$Exception->getMessage();
	// }  

	 // // 전체합계(출고부분)를 처리하는 부분 
	// $sql="select * from mirae8440.steel " ; 
	 // try{  
	// // 레코드 전체 sql 설정
	   // $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
	   // while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
		   
				  // $outdate=$row["outdate"];				  
				  // $item=$row["item"];			  
				  // $spec=$row["spec"];
				  // $steelnum=$row["steelnum"];			  
				  // $company=$row["company"];
				  // $comment=$row["comment"];
				  // $which=$row["which"];	 	
				  
				  // $tmp= $item . $spec . $company;
				  
			// for($i=0;$i<$sumcount;$i++) {	
					// $sum_title[$i]= trim($steelsource_item[$i]) . trim($steelsource_spec[$i]) . trim($steelsource_take[$i]) ;	          
					// $company_arr[$i] = $steelsource_take[$i] ;	
				// if($which=='2' and $tmp==$sum_title[$i])
					// $sum[$i]= $sum[$i] - intval($steelnum);						
				// }	
			// }		 
	   // } catch (PDOException $Exception) {
		// print "오류: ".$Exception->getMessage();
	// }  

    // return [
        // 'sum_title' => $sum_title,
        // 'sum' => $sum
    // ];
// }


  if ($mode=="modify"){
    try{
      $sql = "select * from mirae8440.steel where num = ? ";
      $stmh = $pdo->prepare($sql); 

      $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();            
	  $row = $stmh->fetch(PDO::FETCH_ASSOC);  // $row 배열로 DB 정보를 불러온다.
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
            include '_row.php';  
	  
			 if($indate!="0000-00-00") $indate = date("Y-m-d", strtotime( $indate) );
					else $indate="";	 
			 if($outdate!="0000-00-00") $outdate = date("Y-m-d", strtotime( $outdate) );
					else $outdate="";	 					
			  
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
  }  

  if ($mode=="copy"){
    try{
      $sql = "select * from mirae8440.steel where num = ? ";
      $stmh = $pdo->prepare($sql); 

      $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();            
	  $row = $stmh->fetch(PDO::FETCH_ASSOC);  // $row 배열로 DB 정보를 불러온다.
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
            include '_row.php';  
	  
			 if($indate!="0000-00-00") $indate = date("Y-m-d", strtotime( $indate) );
					else $indate="";	 
			 if($outdate!="0000-00-00") $outdate = date("Y-m-d", strtotime( $outdate) );
					else $outdate="";	 					
			  
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
	 
	 $num=0;
	 $title_message = '(데이터 복사) 원자재 입출고';  
  }   
    
  if ($mode!="modify" && $mode!="copy" ){    // 수정모드가 아닐때 신규 자료일때는 변수 초기화 한다.          
			  $outdate=date("Y-m-d");
			  $indate=date("Y-m-d");
			  $outworkplace=$row["outworkplace"];			  
			  $model=null;			  
			  $item=null;			  
			  $spec=null;
			  $steelnum=null;			  
			  $company="";
			  $comment=null;
			  $which="2";	
              $update_log=""; 			  
              $search_opt=""; 			  
              $bad_choice=""; 	
			  $which="2";	  
  } 
  
 if( $mode=="copy" )  
	 		$which="2";	  
 
?>


<form id="board_form" name="board_form" method="post"  onkeydown="return captureReturnKey(event)"  >	
    
	<!-- 전달함수 설정 input hidden -->
	<input type="hidden" id="id" name="id" value="<?=$id?>" >			  								
	<input type="hidden" id="num" name="num" value="<?=$num?>" >			  								
	<input type="hidden" id="parentid" name="parentid" value="<?=$parentid?>" >			  								
	<input type="hidden" id="fileorimage" name="fileorimage" value="<?=$fileorimage?>" >			  									
	<input type="hidden" id="upfilename" name="upfilename" value="<?=$upfilename?>" >			  								
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>" >			  								
	<input type="hidden" id="savetitle" name="savetitle" value="<?=$savetitle?>" >			  								
	<input type="hidden" id="pInput" name="pInput" value="<?=$pInput?>" >			  								
	<input type="hidden" id="mode" name="mode" value="<?=$mode?>" >		
	<input type="hidden" id="timekey" name="timekey" value="<?=$timekey?>" >  <!-- 신규데이터 작성시 parentid key값으로 사용 -->	
	<input type="hidden" id="first_writer" name="first_writer" value="<?=$first_writer?>"  >
	<input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>"  >	
	<input type="hidden" id="steelitem" name="steelitem" >
	<input type="hidden" id="steelspec" name="steelspec" >
	<input type="hidden" id="steeltake" name="steeltake" >		
    
<div class="container">
    <div class="card mt-2 mb-3">
       <div class="card-body">
 	    <div class="d-flex mb-2 mt-2 justify-content-center align-items-center fs-4">        
	           <?=$title_message?>   &nbsp; &nbsp; &nbsp; &nbsp;					
		</div>  
		
       <div class="row">
		   <div class="col-sm-9">		   
				<div class="d-flex  mb-1 justify-content-start  align-items-center"> 			   	
				   <button id="saveBtn" type="button" class="btn btn-dark  btn-sm me-2"  > <i class="bi bi-floppy"></i> 저장  </button> 				   	
				</div> 	   	
				</div> 	   	
	   <div class="col-sm-3">	
				<div class="d-flex  mb-1 justify-content-end"> 	
				   <button class="btn btn-secondary btn-sm" onclick="self.close();"  > <i class="bi bi-x-lg"></i> 창닫기 </button>&nbsp;					
				</div> 			
		 </div> 			
	 </div> 	   	
  <div class="row mt-1 mb-3">
  <?php if($chkMobile===false)  
       echo '<div class="col-sm-6 " >';
	  else
		  echo '<div class="col-lg-12" >';
	  ?>
	 <div class="card" >
		<div class="card-body mt-2 mb-2" >
	 
      <table class="table table-bordered">        
        <tr>
           <td class="text-center fw-bold " style="width:22%;">
            <label>구분</label>
          </td>
          <td>
			 <?php	 		  
					 $aryreg=array();
					 $aryitem=array();
					 if($which=='') $which='2';
					 switch ($which) {
						case   "1"             : $aryreg[0] = "checked" ; break;
						case   "2"             :$aryreg[1] =  "checked" ; break;
						default: break;
					}		 
						// 검색 선택 쟘 or 천장
					 if($search_opt=='') $search_opt='1';
					 switch ($search_opt) {
						case   "1"             : $aryitem[0] = "checked" ; break;
						case   "2"             :$aryitem[1] =  "checked" ; break;
						default: break;
					}		
				   ?>		  
					  
				 <span class="text-primary">  입고 </span> &nbsp;  <input  type="radio" <?=$aryreg[0]?> name=which value="1"> </span>  
				&nbsp; &nbsp;  <span class="text-danger">  출고         <input  type="radio" <?=$aryreg[1]?>  name=which value="2">  </span>  
          </td>
        </tr>
        <tr>
           <td class="text-center fw-bold ">
            <label  for="indate">접수일</label>
          </td>
          <td>
		     <input    type="date" id="indate" name="indate" class="form-control" style="width:100px;" value="<?=$indate?>" >
			 
          </td>
        </tr>        
        <tr>
           <td class="text-center fw-bold ">
            <label for="outdate">입출고일</label>
          </td>
          <td>
			<div class="d-flex justify-content-start align-items-center">  		  			  
				<input type="date" id="outdate" name="outdate"  class="form-control" style="width:100px;" value="<?=$outdate?>"  > &nbsp;&nbsp;      				
			</div>
          </td>
        </tr> 
        <tr>
           <td class="text-center fw-bold ">
            <label for="outworkplace">현장명</label>
          </td>
          <td>     
			<div class="d-flex mb-1 mt-2 justify-content-start align-items-center">  		  
				쟘(jamb)    &nbsp;     <input  type="radio" <?=$aryitem[0]?> name=search_opt value="1"> &nbsp; &nbsp; 
				천장   &nbsp;       <input  type="radio"  <?=$aryitem[1]?>  name=search_opt value="2"> &nbsp; &nbsp; 
			</div>	
			<div class="d-flex justify-content-start align-items-center">  		  			  
			 <input type="text" id="outworkplace" name="outworkplace" onkeydown="JavaScript:Enter_Check();" value="<?=$outworkplace?>"  class="form-control" style="width:300px;" placeholder="현장명"> 	 &nbsp; 
			 <button type="button" class="btn btn-dark btn-sm" onclick="JavaScript:Choice_search();">   <i class="bi bi-search"></i> </button> 
			 </div>
			 <div class="d-flex justify-content-start align-items-center">  		  			  
				<div id="displaycode" style="display:none"> 	 
				  &nbsp;&nbsp;  천장 Code   <input type="text" id="ceilingcode" name="ceilingcode"  class="form-control" style="width:80px;" value="<?=$ceilingcode?>" >	  
				</div>	 
			</div>	 
			<div class="d-flex mb-1 mt-1 justify-content-center align-items-center">  
			 <div id="displaysearch" style="display:none"> 	 
			 </div>	 	 
			</div>  
          </td>
        </tr>
        <tr>
           <td class="text-center fw-bold ">
            <label for="model">모델</label>
          </td>
          <td>
		  	<div class="d-flex justify-content-start align-items-center"> 
				<input type="text" name="model" value="<?=$model?>"  class="form-control" style="width:150px;" placeholder="모델명" />	 &nbsp;
			</div>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="text-center text-danger fw-bold">
             [주의] 미래기업 구매 자재는 '사급자재'가 아님. 업체 제공 자재만 '사급'.
          </td>
        </tr>
        <tr>
           <td class="text-center fw-bold ">
            <label for="company">사급업체</label>
          </td>
          <td>				
			<div class="d-flex justify-content-start align-items-center"> 
				<select name="company" id="company"  class="form-control" style="width:150px;" >
				   <?php		 
						for($i=0;$i<count($suply_company_arr);$i++) {
							 if(trim($company) == $suply_company_arr[$i])
										print "<option selected value='" . $suply_company_arr[$i] . "'> " . $suply_company_arr[$i] .   "</option>";
								 else   
										print "<option value='" . $suply_company_arr[$i] . "'> " . $suply_company_arr[$i] .   "</option>";
						} 		   
						?>	  
				</select> 		
				&nbsp;
				<button  type="button" id="registcompanyBtn"  class="btn btn-outline-dark btn-sm"  >  <ion-icon name="settings-outline"></ion-icon>  </button> 
			</div>
          </td>
        </tr>
        <tr>
		  <td class="text-center fw-bold ">
            <label for="supplier">공급(제조사)</label>
          </td>
          <td>				
			 <input name="supplier" id="supplier"  value="<?=$supplier?>" class="form-control" style="width:150px;" placeholder="입고시 기록"  >	
          </td>
        </tr>	
        <tr>
		  <td class="text-center fw-bold ">
            <label for="method">샤링여부</label>
          </td>
          <td>
			<select name="method" id="method" class="form-control" style="width:60px;" >
			   <?php	
                    $method_arr = array('','샤링');
					for($i=0;$i<count($method_arr);$i++) {
						 if(trim($method) == $method_arr[$i])
									print "<option selected value='" . $method_arr[$i] . "'> " . $method_arr[$i] .   "</option>";
							 else   
									print "<option value='" . $method_arr[$i] . "'> " . $method_arr[$i] .   "</option>";
					} 		   
					?>	  
			</select> 	
          </td>
        </tr>	
        <tr>
          <td class="text-center fw-bold ">
            <label for="comment">비고</label>
          </td>
          <td>
				<textarea class="form-control" rows="5" id="comment" name="comment" placeholder="기타사항 입력"><?=$comment?></textarea>
          </td>
        </tr>
        <tr>
         <td class=" align-middle text-center fw-bold">
			 <span class="text-danger" > 불량 구분 </span> 
		 </td>					 
		 <td>		
				<div class="d-flex mb-2 mt-2 justify-content-center align-items-center">   	  			
					   <?php			 
							 $arybad=array();
							 if($bad_choice=="")
								   $bad_choice="해당없음";
							 switch ($bad_choice) {
								case   "해당없음"       : $arybad[0] =  "checked" ; break;
								case   "설계"           : $arybad[1] =  "checked" ; break;
								case   "레이져"     : $arybad[2] =  "checked" ; break;
								case   "V컷"           : $arybad[3] =  "checked" ; break;
								case   "절곡"           : $arybad[4] =  "checked" ; break;
								case   "운반중"           : $arybad[5] =  "checked" ; break;
								case   "소장"           : $arybad[6] =  "checked" ; break;
								case   "업체"           : $arybad[7] =  "checked" ; break;
								case   "기타"           : $arybad[8] =  "checked" ; break;
								default: break;
							}		 
						?>			
								
					   <input type="radio" <?=$arybad[0]?> name="bad_choice" value="해당없음"    >	해당없음  &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[1]?> name="bad_choice" value="설계"    >	설계  &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[2]?> name="bad_choice" value="레이져"   > 레이져 &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[3]?> name="bad_choice" value="V컷"    >  V컷 &nbsp;&nbsp;
					</div>  
					<div class="d-flex mb-2 mt-2 justify-content-center align-items-center">  
					   <input type="radio" <?=$arybad[4]?> name="bad_choice" value="절곡"    > 절곡 &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[5]?> name="bad_choice" value="운반중"   > 운반중 &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[6]?> name="bad_choice" value="소장"    > 소장 &nbsp;&nbsp;				  
					   <input type="radio" <?=$arybad[7]?> name="bad_choice" value="업체"    > 업체 &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[8]?> name="bad_choice" value="기타"    > 기타 &nbsp;&nbsp;
								
					</div>    
				  
          </td>
        </tr>
      </table>

		</div>
		</div>
    </div>
  
  <?php if($chkMobile===false)  
       echo '<div class="col-sm-6" >';
	  else
		  echo '<div class="col-lg-12" >';
	  ?>
	  <div class="card" >
		<div class="card-body mt-2 mb-2" >
      <table class="table table-bordered">
        <tr>
           <td class="text-center fw-bold ">
            <label for="item">종류</label>			
          </td>
          <td>
            <div class="d-flex align-items-center">			  
				<select id="item"  name="item" class="form-control me-1" style="width:250px;"  >
				   <?php
						for ($i =0 ; $i < count($steelsource_item); $i++) {
							$currentItem = $steelsource_item[$i] ;   // 공백 제거

							if (trim($item) == $currentItem) {
								print "<option selected value='" . $currentItem . "'> " . $currentItem . "</option>";
							} else {
								print "<option value='" . $currentItem . "'> " . $currentItem . "</option>";
							}
						}		   
					?>	  
				</select> 
				<button  type="button" id="registsteelitem"  class="btn btn-outline-dark btn-sm"  > <ion-icon name="settings-outline"></ion-icon> </button> &nbsp;
			 </div>
          </td>
        </tr>
        <tr>
           <td class="text-center fw-bold ">
            <label for="spec">규격</label>
          </td>
          <td>
            <div class="d-flex align-items-center">			  
				   <select name="spec" id="spec" class="form-control me-1" style="width:150px;" >
				   <?php		 

				   for($i=0;$i<$spec_counter;$i++) {
						   if(trim($spec) == $spec_arr[$i])
								   print "<option selected value='" . $spec_arr[$i] . "'> " . $spec_arr[$i] .   "</option>";
							   else
									print "<option value='" . $spec_arr[$i] . "'> " . $spec_arr[$i] .   "</option>";
				   } 		   
						?>	   
				</select>
				<button  type="button" id="registspecBtn"  class="btn btn-outline-dark btn-sm me-2"  > <ion-icon name="settings-outline"></ion-icon>  </button> 								  
				 재고량 &nbsp; 
				 <input disabled type="text" name="stock" id="stock" value="<?=$stock?>" size="2"  /> &nbsp; 				 
				 <button  type="button" id="searchStockBtn"  class="btn btn-outline-dark btn-sm" >  <ion-icon name="search-outline"></ion-icon> 재고 </button> 
			 </div>
          </td>
        </tr>
        <tr>
           <td class="text-center fw-bold ">	  
            <label for="steelnum">수량</label>
          </td>
          <td>
            <div class="d-flex align-items-center">			  
				<input type="text" id="steelnum" name="steelnum" class="form-control me-1" style="width:50px;"  value="<?=$steelnum?>" size="3" placeholder="수량" autocomplete="off">
				&nbsp;    <button type="button" id="saveBtn2"  class="btn btn-dark btn-sm">  <i class="bi bi-floppy"></i> 저장 </button>	&nbsp;           
			</div>
          </td>
        </tr>		
		
        <tr>
		 <td colspan="2">
			<div class="p-1 m-1">
				<button type="button" class="btn btn-primary btn-sm" onclick="HL304_click(); searchStock();"> 304 HL </button> &nbsp;
				<button type="button" class="btn btn-success btn-sm" onclick="MR304_click(); searchStock();"> 304 MR </button> &nbsp;
				<button type="button" class="btn btn-secondary btn-sm" onclick="VB_click(); searchStock();"> VB </button> &nbsp;
				<button type="button" class="btn btn-warning btn-sm" onclick="EGI_click(); searchStock();"> EGI </button> &nbsp;
				<button type="button" class="btn btn-danger btn-sm" onclick="PO_click(); searchStock();"> PO </button> &nbsp;
				<button type="button" class="btn btn-dark btn-sm" onclick="CR_click(); searchStock();"> CR </button> &nbsp;
				<button type="button" class="btn btn-success btn-sm" onclick="MR201_click(); searchStock();"> 201 2B MR </button> &nbsp;
			</div>
			<div class="p-1 m-1">
				<span class="text-success "> <strong> 쟘 1.2T &nbsp; </strong> </span>
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size1000_1950_click(); searchStock();"> 1000x1950  </button> &nbsp;
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size1000_2150_click(); searchStock();"> 1000x2150  </button> &nbsp;
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size42150_click(); searchStock();">  4'X2150 </button> &nbsp;
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size1000_8_click(); searchStock();"> 1000x8' </button> &nbsp;
			</div>
			<div class="p-1 m-1">
				&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size4_8_click(); searchStock();"> 4'x8' </button> &nbsp;
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size1000_2700_click(); searchStock();"> 1000x2700 </button> &nbsp;
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size4_2700_click(); searchStock();"> 4'x2700 </button> &nbsp;
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size4_3200_click(); searchStock();"> 4'x3200  </button> &nbsp;
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size4_4000_click(); searchStock();"> 4'x4000 </button> &nbsp;
			</div>
			<div class="p-1 m-1">
				<span class="text-success "> <strong> 신규쟘 1.5T(HL) &nbsp; </strong> </span>
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size15_4_2150_click(); searchStock();"> 4'x2150 </button> &nbsp;
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size15_4_8_click(); searchStock();"> 4'x8' </button> &nbsp;
				<span class="text-success "> <strong> 신규쟘 2.0T(EGI) &nbsp; </strong> </span>
				<button type="button" class="btn btn-outline-success btn-sm" onclick="size20_4_8_click(); searchStock();"> 4'x8'  </button> &nbsp;
			</div>

			<div class=" p-1 m-1">
				천장 1.2T(CR) &nbsp;
				<button type="button" class="btn btn-outline-danger btn-sm" onclick="size12_4_1680_click(); searchStock();"> 4'x1680 </button> &nbsp;
				<button type="button" class="btn btn-outline-danger btn-sm" onclick="size12_4_1950_click(); searchStock();"> 4'x1950 </button> &nbsp;
				<button type="button" class="btn btn-outline-danger btn-sm" onclick="size12_4_8_click(); searchStock();"> 4'x8' </button> &nbsp;
			</div>
			<div class=" p-1 m-1">
				천장 1.6T(CR) &nbsp;
				<button type="button" class="btn btn-outline-primary btn-sm" onclick="size16_4_1680_click(); searchStock();"> 4'x1680 </button> &nbsp;
				<button type="button" class="btn btn-outline-primary btn-sm" onclick="size16_4_1950_click(); searchStock();"> 4'x1950 </button> &nbsp;
				<button type="button" class="btn btn-outline-primary btn-sm" onclick="size16_4_8_click(); searchStock();"> 4'x8' </button> &nbsp;
			</div>
			<div class=" p-1 m-1">
				천장 2.3T(PO) &nbsp;
				<button type="button" class="btn btn-outline-secondary btn-sm" onclick="size23_4_1680_click(); searchStock();"> 4'x1680 </button> &nbsp;
				<button type="button" class="btn btn-outline-secondary btn-sm" onclick="size23_4_1950_click(); searchStock();"> 4'x1950 </button> &nbsp;
				<button type="button" class="btn btn-outline-secondary btn-sm" onclick="size23_4_8_click(); searchStock();"> 4'x8' </button> &nbsp;
				천장 3.2T(PO) &nbsp;
				<button type="button" class="btn btn-outline-secondary btn-sm" onclick="size32_4_1680_click(); searchStock();"> 4'x1680 </button> &nbsp;
			</div>
		</td>

        </tr>
     <tr>
    <td colspan="2">                  
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <div class="d-flex mb-1 mt-1 justify-content-center align-items-center">
                <span class="text-primary me-2">
                    잔재<?php echo $i; ?>
                </span>                
                    세로(폭) x 가로(길이) :&nbsp;
                    <input type="number" id="used_width_<?php echo $i; ?>" name="used_width_<?php echo $i; ?>" value="<?php echo ${"used_width_$i"} ?>" min="0" max="9999"  class="form-control me-1" style="width:100px; height:50px;" >&nbsp; x &nbsp; 
                    <input type="number" id="used_length_<?php echo $i; ?>" name="used_length_<?php echo $i; ?>" value="<?php echo ${"used_length_$i"} ?>" min="0" max="9999"  class="form-control me-1" style="width:100px;height:50px;" >
                    &nbsp;&nbsp; 수량&nbsp;&nbsp; 
                    <input type="number" id="used_num_<?php echo $i; ?>" name="used_num_<?php echo $i; ?>" value="<?php echo ${"used_num_$i"} ?>" min="0" max="99"  class="form-control me-1" style="width:100px;height:50px;" >                                         
            </div>
            <?php endfor; ?>        
    </td>
</tr>

		
      </table>	  
    </div>
    </div>
    </div>
  </div>	
  </div>	
  </div>	
  </div>	
  </div>	
  </div>	
	</form>
	
<script>

$(document).ready(function(){	
	$('#closeBtn').click(function() {
		window.close(); // 현재 창 닫기
	});
		
 $("#saveBtn").click(function(){ 
    // 필요한 값 설정
    $('#steelitem').val($('#steel_item').val()); 
    $('#steelspec').val($('#spec').val()); 
    $('#steeltake').val($('#company').val()); 
	
    // 조건 확인
    if($("#outworkplace").val() === '' || $("#steelnum").val()  === '' ) {
        showWarningModal();
    } else {
		
		Toastify({
			text: "변경사항 저장중...",
			duration: 2000,
			close:true,
			gravity:"top",
			position: "center",
			style: {
				background: "linear-gradient(to right, #00b09b, #96c93d)"
			},
		}).showToast();	
		setTimeout(function(){
		         saveData();
		}, 1000);
      
    }
});

	function showWarningModal() {
		Swal.fire({                                    
			title: '등록 오류 알림',
			text: '현장명, 수량은 필수입력 요소입니다.',
			icon: 'warning',
			// ... 기타 설정 ...
		}).then(result => {
			if (result.isConfirmed) { 
				return; // 사용자가 확인 버튼을 누르면 아무것도 하지 않고 종료
			} else {
				saveData(); // 그렇지 않으면 데이터 저장 함수 실행
			}               
		});
	}

	function saveData() {	
	
		if (ajaxRequest !== null) {
			ajaxRequest.abort();
		}	

		ajaxRequest = $.ajax({
				url: "/request/update_steelsource.php",  // request의 함수 사용하기
				type: "post",        
				data: $("#board_form").serialize(),
				dataType:"json",
				success : function( data ){
					console.log( data);	
					saveData1();
					
				},
				error : function( jqxhr , status , error ){
					console.log( jqxhr , status , error );
				}                     
			}); // end of ajax
	}	

	function saveData1() {		
		var num = $("#num").val();  
		
		// 결재상신이 아닌경우 수정안됨     
		if(Number(num) < 1) 				
				$("#mode").val('insert');     			  			
			
		//  console.log($("#mode").val());    
		// 폼데이터 전송시 사용함 Get form         
		var form = $('#board_form')[0];  	    	
		var datasource = new FormData(form); 

		// console.log(data);
		if (ajaxRequest !== null) {
			ajaxRequest.abort();
		}		 
		ajaxRequest = $.ajax({
			enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
			processData: false,    
			contentType: false,      
			cache: false,           
			timeout: 600000, 			
			url: "insert.php",
			type: "post",		
			data: datasource,			
			dataType: "json", 
			success : function(data){
				  // console.log('data :' , data);
				  // Swal.fire(
					  // '자료등록 완료',
					  // '데이터가 성공적으로 등록되었습니다.',
					  // 'success'
					// );
				setTimeout(function(){									
							if (window.opener && !window.opener.closed) {
								// 부모 창에 restorePageNumber 함수가 있는지 확인
								if (typeof window.opener.restorePageNumber === 'function') {
									window.opener.restorePageNumber(); // 함수가 있으면 실행
								}

								window.opener.location.reload(); // 부모 창 새로고침
							}	
					}, 1000);		
					
				setTimeout(function(){															
						 location.href = "view.php?num=" + data["num"];
				}, 1000);						
					
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
						} 			      		
		   });		
			
	}	
	 
	$("#saveBtn2").click(function(){   					    						    
		$("#saveBtn").click(); 								 
	 });	
		 
	$("#closeModalBtn").click(function(){ 
		$('#myModal').modal('hide');
	});			
     // 원자재 종류 관리 등록수정삭제 
	 $("#registsteelitem").click(function(){
        href = '../standard_material/list.php?item=item';				         
		popupCenter(href, '원자재 종류 관리', 600, 700);
	 });		
     // 규격 등록 수정 삭제 
	 $("#registspecBtn").click(function(){   	 
        href = '../standard/list.php';		
		popupCenter(href, '규격(spec) 관리', 600, 700);
	 });		
	 
     // 업체등록 수정 삭제 
	 $("#registcompanyBtn").click(function(){   
        href = '../standard_outsourcing/list.php';				
		popupCenter(href, '납품회사 등록', 600, 700);
	 });			
     
	 $("#searchSimilarStockBtn").click(function(){   
	    searchSimilarStock();
      }); 
	  
	 $("#searchStockBtn").click(function(){   
	    searchStock();
      });
	  
     // // 자제 종류를 누르면 재고 나오게 만듬
     // $("#item").bind( "change", function() {		
		 // searchSimilarStock();
		 // });	
		 
	 // 자제사이즈를 누르면 수량 나오게 만듬
     $("#spec").bind( "change", function() {		
		 searchStock();
		 });	
   		
});	
		
function searchSimilarStock(){
	
	   // 한글,소문자대문자영어,숫자만 읽는 정규식
	   // const regex = /^[ㄱ-ㅎ|가-힣|a-z|A-Z|0-9|]+$/;
	   // 영어와 숫자만 읽는 정규식	   
	   
	   var arr1 = <?php echo json_encode($sum_title);?> ;
	   var arr2 = <?php echo json_encode($sum);?> ;	   
	   var arr3 = <?php echo $sumcount; ?> ; 
	   var arr4 = <?php echo json_encode($company_arr);?> ;
	  	   
		  var a = $('#item').val();
		  var b = $('#spec').val();
		  var c = $('#company').val();
		  
		console.log(a);
		
		let tmp = '';
		let temptext='';		
		for(i=0;i<arr3;i++)
		{
			temptext = arr1[i];		
			if(temptext.includes(a))
			{
			   
			   if(arr2[i] > 0)
			   {
			     tmp += arr1[i] + '     수량 ' + arr2[i] + "<br>";
			     console.log(temptext.includes(a));				
			   }
			}
		}
		  if(tmp=='')
		     {
                 tmp='자재 없음';
				 $('#stock').val(0);  
			 }
		  $('#alertmsg').html(tmp); 
		  
		  $('#myModal').modal('show'); 
}

function searchStock(){
	 // 한글,소문자대문자영어,숫자만 읽는 정규식
	   // const regex = /^[ㄱ-ㅎ|가-힣|a-z|A-Z|0-9|]+$/;
	   // 영어와 숫자만 읽는 정규식	   
	   var arr1 = <?php echo json_encode($sum_title);?> ;
	   var arr2 = <?php echo json_encode($sum);?> ;	   
	   var arr3 = <?php echo $sumcount; ?> ; 
	   var arr4 = <?php echo json_encode($company_arr);?> ;
	   
	  // console.log('원자재 Full name '  + arr1);
	   console.log(arr3);
	   
		  var a = $('#item').val();
		  var b = $('#spec').val();
		  var c = $('#company').val();
		  
		var title = a + b + c;
		let tmp = '';
		let temptext='';
		console.log('arr1.length '  + arr1.length);		 		
		console.log('a+b+c title ', title);
		console.log('sum_title ', arr1);		 
		console.log('sum ', arr2);		 
		console.log('sumcount ', arr3);		 
				
		// 제거할 단어 목록
		var wordsToRemove = ['미래기업', '윤스틸', '현진스텐'];		 		 
		 
		for(i=0;i<arr1.length;i++)
		{
			temptext = arr1[i];			


			// 제거할 단어를 제외한 문자열 생성 함수
			function removeWordsFromString(text, wordsToRemove) {
			  var result = text;
			  for (var i = 0; i < wordsToRemove.length; i++) {
				var wordToRemove = wordsToRemove[i];
				result = result.replace(wordToRemove, '');
			  }
			  return result.trim();
			}

			// 문자열에서 단어 제거 후 비교
			var removedTitle = removeWordsFromString(title, wordsToRemove);
			var removedTempText = removeWordsFromString(temptext, wordsToRemove);

			// 수정된 문자열 비교
			if (removedTempText === removedTitle) {
					
				console.log('removedTempText '  + removedTempText);
			    console.log('removedTitle '  + removedTitle);
			    console.log('sum '  + arr2[i]);
				   // console.clear();
				   if(arr2[i] > 0)
				   {
					 tmp += a + ' ' + b + ' ' + arr4[i] + ' 수량 ' + arr2[i] + "<br>";
					 console.log(arr4[i]);
					$('#stock').val(arr2[i]);  
				   }
				}
		}
		  if(tmp=='')
		     {
                 tmp='자재 없음';
				 $('#stock').val(0);  
			 }
		  $('#alertmsg').html(tmp); 
		  
		  // steel에는 모달창 뜨지 않게 하기
		  // steel에는 모달창 뜨지 않게 하기
		  // $('#myModal').modal('show'); 
}


function captureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
}

function recaptureReturnKey(e) {
    if (e.keyCode==13)
        exe_search();
}

function Enter_Check(){
var tmp = $('input[name=search_opt]:checked').val();	
	
        // 엔터키의 코드는 13입니다.
    if(event.keyCode == 13 && tmp== 1 )
      search_jamb();  // 잠 현장검색
	  
    if(event.keyCode == 13 && tmp== 2 )
      search_ceiling();  // 천장 현장 검색	      
}

function Choice_search() {
var tmp = $('input[name=search_opt]:checked').val();	
	if(tmp =='1' )
      search_jamb();  // 잠 현장검색	  
    if(tmp == '2' )
      search_ceiling();  // 천장 현장 검색	      
  
 // alert(tmp);
  }
  
function Enter_CheckTel(){
        // 엔터키의 코드는 13입니다.
    if(event.keyCode == 13){
      exe_searchTel();  // 실행할 이벤트
    }
}

function deldate(){	
	document.getElementById("indate").value  = "";
	var _today = new Date();   
	printday=_today.format('yyyy-MM-dd');   
	document.getElementById("outdate").value  = printday;
	$("input[name='which']:radio[value='1']").attr("checked", true) ;

}  

function search_jamb()
{
	  var ua = window.navigator.userAgent;
      var postData; 	 
	  var text1= document.getElementById("outworkplace").value;
	
	     if (ua.indexOf('MSIE') > 0 || ua.indexOf('Trident') > 0) {
                postData = encodeURI(text1);
            } else {
                postData = text1;
            }

      $("#displaysearch").show();
      $("#displaysearch").load("./search.php?mode=search&search=" + postData);
} 

function search_ceiling()
{
	  var ua = window.navigator.userAgent;
      var postData; 	 
	  var text1= document.getElementById("outworkplace").value;
	
	     if (ua.indexOf('MSIE') > 0 || ua.indexOf('Trident') > 0) {
                postData = encodeURI(text1);
            } else {
                postData = text1;
            }
			
			
	  $("#displaycode").show();			

      $("#displaysearch").show();
      $("#displaysearch").load("./search_ceiling.php?mode=search&search=" + postData);
} 

// 새로운 사급업체가 추가된 것을 update함
function updateOptions(item, newValue) {
	var select = document.getElementById(item);

	var option = document.createElement("option");
	option.value = newValue;
	option.text = newValue;

	select.add(option);
	select.value = newValue; // 선택된 옵션을 새 값으로 설정
}

</script> 
</body>
</html>
