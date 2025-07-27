<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
   
   // 첫 화면 표시 문구
$title_message = '건의사항';
   

 if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
		 sleep(1);
		  header("Location:" . $WebSite . "login/login_form.php"); 
         exit;
   }   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   

 ?>
  
<title>  <?=$title_message?>  </title> 

    <style>
        .table-hover tbody tr:hover {
            cursor: pointer;
        }
    </style> 
 
 </head> 
 
<body>

  <?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<?php

$tablename = "suggestion";
  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
	 
	 
  if(isset($_REQUEST["mode"]))
     $mode=$_REQUEST["mode"];
  else 
     $mode="";

       if(isset($_REQUEST["search"]))   // search 쿼리스트링 값 할당 체크
         $search=$_REQUEST["search"];
       else 
         $search="";     
     
   if($mode=="search"){
         if(!$search) {
				$sql ="select * from " . $DB . "." . $tablename . " order  by num desc "; 				
             }
              $sql="select * from " . $DB . "." . $tablename . " where name like '%$search%' or subject like '%$search%'  or regist_day like '%$search%'   or searchtext like '%$search%'  order by num desc ";              
       } else {
              $sql="select * from " . $DB . "." . $tablename . " order  by num desc";              
       }

try{  

$stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
$total_row=$stmh->rowCount();  

   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  	 	 
try{  
	$stmh = $pdo->query($sql); 
	  
?>
<form name="board_form" id="board_form"  method="post" action="list.php?mode=search">
   
<div class="container">  
	<div class="card mt-1">
		<div class="card-body">
			<div class="d-flex mb-3 mt-2 justify-content-center align-items-center">  
				<h4> <?=$title_message?> </h4>  
				<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  	 			
			</div>	

			<div class="d-flex p-0 justify-content-center">
				<div class="alert alert-info" role="alert" style="font-size: 14px;">
					  제품 품질개선 해야 할 내용이나 회사에 대한 건의사항이 있으면 부담갖지 말고 올려 주세요!
				</div>
			</div>

			<div class="d-flex mb-1 mt-1 justify-content-center align-items-center">  													   
				<div class="inputWrap">
					<input type="text" id="search" name="search" value="<?=$search?>" autocomplete="off"  class="form-control w-auto mx-1" placeholder="검색어 입력" > &nbsp;			
					<button type="button" class="btnClear" title="검색어 지우기">×</button>
				</div>				
				<div id="autocomplete-list">
				</div>
				 &nbsp;												   			   
				<button type="button" id="searchBtn" class="btn btn-dark  btn-sm"> <i class="bi bi-search"></i>  </button>	&nbsp;&nbsp;
				<button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규  </button> 	    			 
			</div>
		</div>
	</div>
	   
<div class="card mb-2">
<div class="card-body">	  	  
   <div class="table-responsive"> 	
   <table class="table table-hover " id="myTable">
    <thead class="table-primary">
      <tr>
        <th class="text-center" scope="col" style="width:8%;">번호</th>
        <th class="text-center" scope="col" style="width:50%;">글제목</th>        
        <th class="text-center" scope="col" style="width:12%;">작성자</th>
        <th class="text-center" scope="col" style="width:15%;">등록일자</th>   
        <th class="text-center" scope="col" style="width:10%;">조회수</th>           
      </tr>
    </thead>	
    <tbody>
      <?php
  
  $start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
  			 
 while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
   include '_row.php';
   
  $subject=str_replace(" ", "&nbsp;", $row["subject"]);
   
  $sql="select * from " . $DB . ".suggestion_ripple where parent=$num";
  $stmh1 = $pdo->query($sql); 
  $num_ripple=$stmh1->rowCount(); 
  
  // 중요건의 여부 확인
  $is_important = ($row["suggestioncheck"] == "y") ? true : false;
 ?>
 
   <tr onclick="redirectToView('<?=$num?>', '<?=$tablename?>')">
  
				  <td class="text-center" >  <?= $start_num ?>      </td>
				  <td>  
					<?php if($is_important): ?>
						<span class="badge bg-danger me-1">중요</span>
					<?php endif; ?>
					<?= $subject ?>                     
					<?php
					   if($num_ripple>0)
						    echo '<span class="badge bg-primary ms-1"> '.$num_ripple.' </span> ';
					?>
				  </td>
				  <td class="text-center" >  <?= $name ?>      </td>
				  <td class="text-center" >  <?= $regist_day ?>      </td>     
				  <td class="text-center" >  <?= $hit ?>       </td>    
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
   </div> <!--container-fluid-->
	</form>   
</body> 
</html>   

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>
<script>

var dataTable; // DataTables 인스턴스 전역 변수
var suggestionpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('suggestionpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var suggestionpageNumber = dataTable.page.info().page + 1;
        setCookie('suggestionpageNumber', suggestionpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('suggestionpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('suggestionpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}


function redirectToView(num, tablename) {
    var page = suggestionpageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)
    	
    var url = "view.php?num=" + num + "&tablename=" + tablename;          

	customPopup(url, '건의사항', 1000, 800); 		    
}

$(document).ready(function(){
	
	$("#writeBtn").click(function(){ 
		var page = suggestionpageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)	
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '건의사항', 1000, 800); 	
	 });			 
		
});	
</script> 