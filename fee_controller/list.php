<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

// 첫 화면 표시 문구
$title_message = "DH모터 연동제어기 (원가 및 단가)" ;
 ?>

<?php 

 if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
		 sleep(1);
		  header("Location:" . $WebSite . "login/login_form.php"); 
         exit;
   }    
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
   
 ?>

<title> <?=$title_message?> </title>

<link href="css/style.css" rel="stylesheet" >   

</head>

<body>	
	 
<?php
$tablename = 'fee_controller'; 

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
// 메뉴를 표현할지 판단하는 header
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  
if($header !== 'noheader')
	require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');


require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();



// 부자재에 대한 배열 가져오기
$sql = "select * from " . $DB . ".{$tablename} order by basicdate desc limit 1";

try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져옴
    $total_row = count($rows); // 가져온 행의 수를 계산
    $rows = array_reverse($rows); // 배열을 역순으로 정렬
    foreach ($rows as $row) {
        // 각 행에 대한 JSON 데이터를 디코드하고 필요에 따라 필터링
        $sub_item = array_filter(json_decode($row['item'], true) ?? [], function ($value) {
            return trim($value) !== '';
        });
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}

array_unshift($sub_item, ''); // 배열의 맨 앞에 빈 문자열 추가

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    $fromdate = date("Y-m-d", strtotime('2024-01-01'));
    $todate = $currentDate; // 현재 날짜
	$Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}
		
$sql=" select * from " . $DB . "." . $tablename ;

$sum=array();
  	
$now = date("Y-m-d");	 // 현재 날짜와 크거나 같으면 출고예정으로 구분

$orderby="order by basicdate desc ";
 
$attached=''; 

$whereattached = '';
		
$SettingDate=" basicdate "; 
	 
$common= $SettingDate . " between '$fromdate' and '$Transtodate' and is_deleted IS NULL ";
		
$andPhrase= " and " . $common  . $orderby ;
$wherePhrase= " where " . $common  . $orderby ;


// 검색을 위해 모든 검색변수 공백제거
$search = str_replace(' ', '', $search);  

if($search==""){
	if($whereattached!=='')
		$sql="select * from " . $DB . "." . $tablename. $whereattached . $andPhrase; 					                 
	else
		$sql="select * from " . $DB . "." . $tablename. $wherePhrase ;					                 
	}	
	elseif($search!="" ) {
		$sql ="select * from " . $DB . "." . $tablename . " where (replace(searchtag,' ','') like '%$search%' ) " . $attached . $andPhrase; 					                       
	}


try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져오기
    $total_row = count($rows); // 가져온 데이터의 행 수 계산
    // var_dump($total_row);
    $start_num = $total_row;
    
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}
		 
 ?>

<form id="board_form" name="board_form" method="post" >  		
	
<div class="container-fluid">  
	<div class="card mb-2 mt-2">  
	<div class="card-body">  	 
			 
	<div class="d-flex  p-1 m-1 mt-1 justify-content-center align-items-center "> 		
		<h5>  <?=$title_message?> </h5>		
		<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  		
	</div>	

	    <div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center"> 	  
			▷  <?= $total_row ?> &nbsp; 		
		   <input type="date" id="fromdate" name="fromdate"   class="form-control"   style="width:100px;" value="<?=$fromdate?>" >  &nbsp;   ~ &nbsp;  
		   <input type="date" id="todate" name="todate"  class="form-control"   style="width:100px;" value="<?=$todate?>" >  &nbsp;     </span> 
		   &nbsp;&nbsp;		 

		<div class="inputWrap">
				<input type="text" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off"  class="form-control" style="width:150px;" > &nbsp;			
				<button class="btnClear"></button>
		</div>				
		<div id="autocomplete-list">				
		</div>	
		  &nbsp;
		  <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> 검색 </button> 		  
		  &nbsp;&nbsp;&nbsp;		    
				 <button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규  </button> 
				 <button type="button" class="btn btn-info  btn-sm me-1" id="guideBtn"> <i class="bi bi-question-circle"></i> 새로 추가할 경우 간단 설명서  </button> 
				 <?php
				 if($header == 'noheader')
				 {
				 ?>
					<button type="button" class="btn btn-dark  btn-sm me-1" id="reflectBtn"> <i class="bi bi-database-fill-down"></i> 적용하기  </button> 				

				 <?php } ?>					 
					 
					 
				 
         </div> 	 

		 
   </div> <!--card-body-->
   </div> <!--card -->   
</div> <!--container-fluid -->   
<div class="container-fluid">  
<div class="d-flex justify-content-center align-items-center"> 		
<table class="table table-hover" id="myTable">
  <thead class="table-primary">
    <tr>
      <th class="text-center " style="width:50px;" >번호</th>      
      <th class="text-center " style="width:100px;" >기준일</th>
	  <th class="text-center " style="width:600px;">내용</th>      
	  <th class="text-center " style="width:400px;">메모</th>      
    </tr>        
  </thead>	  
  <tbody>
<?php
try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져오기
    $total_row = count($rows); // 가져온 데이터의 행 수 계산
    // var_dump($total_row);
    $start_num = $total_row;


	$contentlist ='연동제어기 원가 및 단가 지정';
	
    foreach ($rows as $row) {
        // HTML 출력 부분
		include '_row.php';		
		
        ?>
        <tr onclick="redirectToView('<?= $row['num'] ?>', '<?= $tablename ?>')">
            <td class="text-center"><?= $start_num ?></td>
            <td class="text-center"><?= $row['basicdate'] ?></td>            
            <td class="text-center"><?= $contentlist ?></td>            
            <td class="text-start "><?= $row['memo'] ?></td>
        </tr>
        <?php
        $start_num--;
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}
?>
  
  
     <!-- Table body 부분은 아래에 추가 -->
    </tbody>  
    </table>  
</div>
      
   </div> <!--container-->
</form>	
	<div class="container-fluid mt-3 mb-3">
		<? 
		
		if($header !== 'noheader')	
			include '../footer_sub.php'; 
		
		?>
	</div>

<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader) 
		loader.style.display = 'none';
});
</script>

<script> 

var dataTable; // DataTables 인스턴스 전역 변수
var feecontrollerpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {			
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 25,
        "lengthMenu": [25, 50, 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[0, 'desc']]
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('feecontrollerpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var feecontrollerpageNumber = dataTable.page.info().page + 1;
        setCookie('feecontrollerpageNumber', feecontrollerpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('feecontrollerpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('feecontrollerpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
	location.reload(true);
}

function redirectToView(num, tablename) {	
    var url = "write_form.php?mode=view&num=" + num + "&tablename=" + tablename;          
	customPopup(url, '', 1200, 900); 		    
}

$(document).ready(function(){	
	$("#writeBtn").click(function(){ 		
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '', 1200, 900); 	
	 });			 
	
	$("#guideBtn").click(function(){ 		
		var url = "guide.php"; 				
		customPopup(url, '', 800, 600); 	
	 });			 
});	


function SearchEnter(){

    if(event.keyCode == 13){		
		document.getElementById('board_form').submit();
    }
}

$(document).ready(function(){	
	$("#searchBtn").click(function(){ 	
		 document.getElementById('board_form').submit();
	 });		
});


</script>
    
</body>
</html>