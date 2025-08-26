<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '직원관리'

?>
  
<title> <?=$title_message?> </title>  
	 
<body>

<? include $_SERVER['DOCUMENT_ROOT'] . '/myheader.php'; ?>   

<style>
	.table-hover tbody tr:hover {
		cursor: pointer;
	}
</style>  
 </head> 
<?php     
$tablename = "member";

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
	 
 if(isset($_REQUEST["page"])) // $_REQUEST["page"]값이 없을 때에는 1로 지정 
    $page=(int)$_REQUEST["page"];  // 페이지 번호
  else
    $page=1;	 
	 
  if(isset($_REQUEST["mode"]))
     $mode=$_REQUEST["mode"];
  else 
     $mode="";

       if(isset($_REQUEST["search"]))   // search 쿼리스트링 값 할당 체크
         $search=$_REQUEST["search"];
       else 
         $search="";

      // 재직/퇴직 필터 상태 (기본: 재직자만)
      if(isset($_REQUEST["empStatus"]))
        $empStatus = $_REQUEST["empStatus"];
      else
        $empStatus = "active";

      // 조건 구성: 재직/퇴직 + 검색어
      $conditions = array();
      if($empStatus === "retired"){
          $conditions[] = "(quitDate IS NOT NULL AND quitDate <> '0000-00-00')";
      } else {
          $conditions[] = "(quitDate IS NULL OR quitDate = '0000-00-00')";
      }

      if($mode==="search" && $search !== ''){
          $conditions[] = "(id like '%$search%' or name like '%$search%')";
      }

      $sql = "select * from " . $DB . "." . $tablename;
      if(count($conditions) > 0){
          $sql .= " where " . implode(" and ", $conditions);
      }
      $sql .= " order  by num desc";

?>
 

<form name="board_form" id="board_form"  method="post" action="list.php?mode=search&search=<?=$search?>">
<input type="hidden" name="empStatus" id="empStatus" value="<?= isset($empStatus) ? $empStatus : 'active' ?>">

<div class="container-fluid justify-content-center">  
    
 <div class="d-flex mt-3 mb-3 justify-content-center">  
       <span class="badge bg-primary fs-5 " > &nbsp;&nbsp; <?=$title_message?>  &nbsp;&nbsp;</span>
  </div>	 
 
 <div class="d-flex mt-2 mb-2 justify-content-center align-items-center">       
       <div class="form-check form-switch me-3" style="font-size: 1.3em;">
         <input class="form-check-input" type="checkbox" id="empStatusToggle" style="transform: scale(1.2);" <?= (isset($empStatus) && $empStatus === 'retired') ? 'checked' : '' ?>>
         <label class="form-check-label me-3" for="empStatusToggle" style="font-size: 1.2em;">
           <span id="empStatusLabel"><?= (isset($empStatus) && $empStatus === 'retired') ? '퇴직' : '재직' ?></span>
         </label>
       </div>
	   <button type="button" class="btn btn-dark btn-sm me-2" onclick="popupCenter('write_form.php', '등록', 800, 750);return false;" > <ion-icon name="pencil-outline"></ion-icon> 신규  </button>		   
	   <button type="button" class="btn btn-dark btn-sm me-2" onclick="popupCenter('setline.php', '결재라인 등록', 600, 400);return false;" > 결재라인 </button>	
	   <input type="text" name="search" id="search" value="<?=$search?>" class="form-control me-1" style="width:200px;" onkeydown="JavaScript:SearchEnter();" placeholder="검색어"> 
		<button type="button" id="searchBtn" class="btn btn-dark"  > <i class="bi bi-search"></i> </button>	
</div>
 
<div class="row d-flex"  >
 <table class="table table-hover" id="myTable">
   <thead class="table-primary" >
	    <tr>
			 <th class="text-center" > 번호   </th>
			 <th class="text-center" > 재직/퇴직  </th>
			 <th class="text-center" > ID    </th>
			 <th class="text-center" > P/W   </th>
			 <th class="text-center" > 이름   </th>
			 <th class="text-center" > 전번   </th>
			 <th class="text-center" > 레벨   </th>
			 <th class="text-center" > 회사 </th>   		
			 <th class="text-center" > 부서 </th>   		
			 <th class="text-center" > numorder </th>   		
			 <th class="text-center" > position </th>   		
			 <th class="text-center" > eworks_level </th>   		
			 <th class="text-center" > 작업일지 </th>   		
			 <th class="text-center" > 주소 </th>   		
		 </tr>
       </thead>
	<tbody>  
	
<?php  
  
  try{  

	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $total_row=$stmh->rowCount();
	  
	  $start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
			 
 while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {  
        include '_row.php';
 ?>
	<tr onclick="redirectToView('<?=$num?>')">  
	  <td class="text-center"> <?= $num ?> </td>
	  <td class="text-center text-danger fw-bold"> <?= ($quitDate === '0000-00-00' || $quitDate === '') ? '' : '퇴직' ?> </td>
	  <td class="text-center fw-bold"> <?= $id ?> </td>	  
	  <td class="text-center"> <input type="password" name="password" value="<?= $pass ?>" disabled>    </td>
	   <td class="text-center text-primary fw-bold"> <?= $name ?>   </td>
	   <td class="text-center">  <?= $hp ?>   </td>
	  <td class="text-center">  <?= $userlevel ?>      </td>
	  <td class="text-center">  <?= $company ?>      </td>     	  
	  <td class="text-center">  <?= $part ?>      </td>     	  
	  <td class="text-center">  <?= $numorder ?>      </td>     	  
	  <td class="text-center">  <?= $position ?>      </td>     	  
	  <td class="text-center">  <?= $eworks_level ?>      </td>     	  
	  <td class="text-center fw-bold">  <?= $dailyworkcheck ?>      </td>     	  
	  <td class="text-start ">  <?= $address ?>      </td>     	  
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
   

</form>   


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
var noticepageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('memberpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var noticepageNumber = dataTable.page.info().page + 1;
        setCookie('memberpageNumber', noticepageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('memberpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('memberpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}

function redirectToView(num) {
	var title = '<?php echo $title_message; ?>';
	popupCenter('write_form.php?mode=modify&num=' + num,  title , 800, 800);    
}

$(document).ready(function(){		
	$("#searchBtn").click(function(){ 		  
		 document.getElementById('board_form').submit();    
	 
	 });	

    // 재직/퇴직 토글
    $('#empStatusToggle').on('change', function(){
        var isRetired = $(this).is(':checked');
        $('#empStatus').val(isRetired ? 'retired' : 'active');
        $('#empStatusLabel').text(isRetired ? '퇴직' : '재직');
        document.getElementById('board_form').submit();
    });
});	
	
function SearchEnter(){
    if(event.keyCode == 13){	
		$("#page").val('1');		
		document.getElementById('board_form').submit(); 
    }
}

$(document).ready(function(){
	saveLogData('회원관리'); 
});
	
</script>

</body>
</html>