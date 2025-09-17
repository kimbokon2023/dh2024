<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

$title_message = '연차 관리자모드';
$tablename = 'eworks';
 include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>   
 
 <?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 배열로 기본정보 불러옴
require_once($_SERVER['DOCUMENT_ROOT'] . "/almember/load_DB.php");
 ?>

<title>  <?=$title_message?>  </title> 

<body>

<?php  require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>    

 <?php 
 
// 변수 초기화
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

isset($_REQUEST["year"]) ? $year=$_REQUEST["year"] : $year = date("Y");
	  
$whereCon = " where  referencedate = '$year' AND is_deleted IS NULL   "; 
$andCon = " AND referencedate = '$year' AND is_deleted IS NULL "; 

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if($mode=="search"){
	  if($search==""){		
		$sql = "select * from ".$DB.".almember  " . $whereCon . " order by referencedate   desc,  dateofentry asc, num desc  " ;
		       }
	 else {
			  $sql ="select * from ".$DB.".almember where ((name like '%$search%') or (part like '%$search%') or (referencedate like '%$search%')) ";
			  $sql .=" " . $andCon . " order by referencedate desc,  dateofentry asc, num desc ";
			}				
	 }      
  if($mode=="") { 				
		$sql = "select * from ".$DB.".almember " . $whereCon . "order by referencedate desc,  dateofentry asc, num desc  " ;
                }	

// print $sql;

 try{  	  
	  $stmh = $pdo->query($sql);           
	  $total_row = $stmh->rowCount();  				 		 
	?>      
	
<form name="board_form" id="board_form"  method="post" action="admin.php?mode=search">    
 
<div class="container">  
	<div class="card mt-2 mb-4">  
	<div class="card-body"> 		        		    
	<div class="d-flex mt-3 mb-3 justify-content-center"> 				
		<span class="text-dark fs-5" >   <?=$title_message?> &nbsp;&nbsp;  </span>
			<button type="button" id="backBtn" class="btn btn-outline-primary btn-sm me-2"  >  <ion-icon name="caret-back-circle-outline"></ion-icon> 이전화면  </button> &nbsp;&nbsp;&nbsp;		  
	</div>
<div class="d-flex mt-3 mb-1 justify-content-center  align-items-center"> 	
			선택년도 &nbsp;
		<select name="year" id="year"  class="form-select w-auto me-1" style="font-size:0.8rem; height:32px;"  >
		   <?php 		    
			$current_year = date("Y"); // 현재 년도를 얻습니다.
			$year_arr = array(); // 빈 배열을 생성합니다.
			
			for ($i = 0; $i < 3; $i++) {
				$year_arr[] = $current_year - $i;
			}
		   for($i=0;$i<count($year_arr);$i++) {
				 if($year==$year_arr[$i])
						print "<option selected value='" . $year_arr[$i] . "'> " . $year_arr[$i] .   "</option>";
				 else   
						print "<option value='" . $year_arr[$i] . "'> " . $year_arr[$i] .   "</option>";
		   } 		   
				?> 	  
		</select> 		 			
			
		 <button type="button" class="btn btn-dark btn-sm" onclick="popupCenter('write_form.php', '신규', 600, 700);return false;" >  <ion-icon name="create-outline"></ion-icon> 신규 </button> &nbsp;&nbsp;		 

     &nbsp;&nbsp;&nbsp; ▷ 총 <?= $total_row ?>개  &nbsp;&nbsp;&nbsp; 

	   <input type="text" name="search" id="search" class="form-control me-1" style="width:120px;" value="<?=$search?>" autocomplete="off" onkeydown="JavaScript:SearchEnter();" placeholder="검색어"> 
	   &nbsp;
		<button type="button" id="searchBtn" class="btn btn-dark btn-sm"  > <i class="bi bi-search"></i> 검색 </button> 	
	
</div>  
<div class="row d-flex mt-3 mb-1 justify-content-center  align-items-center"> 	
	<table class="table table-hover" id="myTable">
      <thead class="table-primary">
        <tr>
          <th class="text-center">번호</th>
          <th class="text-center">구분</th>
          <th class="text-center">직원이름</th>
          <th class="text-center">부서</th>
          <th class="text-center">입사일</th>
          <th class="text-center">해당연도</th>
          <th class="text-center">근속년</th>
          <th class="text-center">근속월</th>
          <th class="text-center">연차 발생일수</th>
          <th class="text-center">연차 사용일수</th>
          <th class="text-center">연차 잔여일수</th>
        </tr>
      </thead>
  <tbody>               
	 <?php
		$start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
	    
	   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
		   include "_row.php";  
		   	   
        // var_dump($totalused_arr);
		$totalusedday = 0;
		$totalremainday = isset($availableday) ? $availableday : 0 ; 		
		
		 for($i=0;$i<count($totalname_arr);$i++)
		 {			 

             // 해당년도가 같고 이름이 같으면 계산			 
			 if(trim($name) == trim($totalname_arr[$i]) && $referencedate == $totalusedYear_arr[$i] )
			 { 		 
				$totalusedday = $totalused_arr[$i];
				$totalremainday = $availableday - $totalusedday; 				
			 } 			
		 }
		 
			if (isset($dateofentry) && isset($referencedate)) {
				// DateTime 객체로 변환
				$entryDate = new DateTime($dateofentry);
				$referenceDate = new DateTime($referencedate);
				
				// 두 날짜 간의 차이 계산
				$interval = $entryDate->diff($referenceDate);
				
				// 총 년수 계산
				$years = $interval->y;
				
				// 총 월수 계산
				$months = $interval->m;
				
				// 근속년수 계산 (년 + (월 / 12)), 소수점 첫째 자리까지 반올림
				$continueYear = round($years + ($months / 12), 1);
				
				// 단순 월 계산
				$continueMonth = intval($years) * 12 + $interval->m;
			} else {
				// 기본 값 설정
				$continueYear = 0;
				$continueMonth = 0;
			}
			
			?>
			
				
        <tr onclick="redirectToView('<?= $row['num'] ?>', '<?= $tablename ?>')">
		    <td class="text-center"><?=$start_num?>	</td>
            <td class="text-center"><?=$comment?>	</td>
            <td class="text-center"><?=$name?>	    </td>
            <td class="text-center"><?=$part?>   	</td>
            <td class="text-center"><?=$dateofentry?>	</td>
            <td class="text-center"><?=$referencedate?>	</td> 		
            <td class="text-center"><?=$continueYear?>	</td>
            <td class="text-center"><?=$continueMonth?>	</td> 			
            <td class="text-center text-primary"><b><?=$availableday?></b>	</td>            		
            <td class="text-center text-success"><b><?=$totalusedday?></b>	</td>            		
            <td class="text-center text-danger"><b> <?=$totalremainday?></b>	</td>            	         
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
   </div>	 
   </div>	 
  
  
  	</form>	   	
    
<br>
<br>
<div class="container">
<? include '../footer_sub.php'; ?>
</div>
  
</body>
</html>


<!-- 페이지로딩 -->
<script>
$(document).ready(function(){ 	
	var loader = document.getElementById('loadingOverlay');
	loader.style.display = 'none';
});
</script>

<script>
var dataTable; // DataTables 인스턴스 전역 변수
var aladminpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('aladminpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var aladminpageNumber = dataTable.page.info().page + 1;
        setCookie('aladminpageNumber', aladminpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('aladminpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('aladminpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}

function SearchEnter(){
    if(event.keyCode == 13){
		document.getElementById('board_form').submit(); 
    }
}
	


$(document).ready(function(){ 	

	$('#year').on('change', function(){
	   document.getElementById('board_form').submit(); 
	}); 	


	$("#closeModalBtn").click(function(){ 
		$('#myModal').modal('hide');
	});

	$("#searchBtn").click(function(){  
		document.getElementById('board_form').submit();   
	}); 		
	
	$("#backBtn").click(function(){  
		location.href='/annualleave/index.php'; 
	}); 			



});

function redirectToView(num, tablename) { 	
    var url = "write_form.php?mode=modifiy&num=" + num + "&tablename=" + tablename;          
	customPopup(url, '연차등록/변경', 500, 600); 	    
}

</script>

