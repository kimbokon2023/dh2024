<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
   
$title_message = '업무요청사항';   

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
$tablename = "workprocess";  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
	 
$mode=$_REQUEST["mode"] ?? '' ;
$search=$_REQUEST["search"] ?? '';
     
if($mode=="search"){
    if(!$search) {
		$sql ="select * from " . $DB . "." . $tablename . " order  by num desc "; 				
    }
        $sql="select * from " . $DB . "." . $tablename . " where name like '%$search%' or subject like '%$search%'  or regist_day like '%$search%'   or searchtext like '%$search%'  order by num desc ";              
    } else {
        $sql="select * from " . $DB . "." . $tablename . " order  by num desc";              
    }
	 	 
try{  
	$stmh = $pdo->query($sql); 
	$total_row=$stmh->rowCount();  	  
?>
	
<form name="board_form" id="board_form"  method="post">
   
<div class="container justify-content-center">  
<div class="card mt-2 mb-4">  
	<div class="card-body">  
		<div class="card-header">
			<div class="d-flex justify-content-center text-center align-items-center">										 
				<span class="text-center fs-5"> <?=$title_message?> </span>		
				<button type="button" class="btn btn-dark btn-sm mx-3" onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  		
			</div>
		</div>
		<div class="card-body">								
			<div class="d-flex justify-content-center text-center align-items-center mb-2">										 
				▷ <?= $total_row ?> &nbsp; 
				<div class="inputWrap30">			
					<input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" autocomplete="off" onKeyPress="if (event.keyCode==13){ enter(); }">
					<button class="btnClear"></button>
				</div>											
				<button class="btn btn-outline-dark btn-sm mx-1" type="button" id="searchBtn"> <i class="bi bi-search"></i> </button> 
				<button id="writeBtn" type="button" class="btn btn-dark btn-sm mx-1"> <i class="bi bi-pencil-square"></i> 신규 </button>				
			</div>		
			
			 <div class="row d-flex">
			 <table class="table table-hover" id="myTable">
			   <thead class="table-primary">
					<tr>
						 <th class="text-center" > 번호 </th>
						 <th class="text-center" > 요청사항 제목 th>						 
						 <th class="text-center" > 작성자 </th>
						 <th class="text-center" > 업무담당자   </th>
						 <th class="text-center" > 작성일 </th>   
						 <th class="text-center" > 처리기한 </th>   
						 <th class="text-center" > 처리완료일 </th>   
						 <th class="text-center" > 조회수   </th>   
						 </tr>
				   </thead>
				<tbody>  			  
			<?php  			  
			  $start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
			  
			 while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
				include '_row.php';			   
				$subject=str_replace(" ", "&nbsp;", $row["subject"]);
				$sql="select * from " . $DB . ".workprocess_ripple where parent=$num";
				$stmh1 = $pdo->query($sql); 
				$num_ripple=$stmh1->rowCount(); 
			 ?>			 
			   <tr onclick="redirectToView('<?=$num?>', '<?=$tablename?>')">			  
				  <td class="text-center" >  <?= $start_num ?>      </td>
				  <td>  <?= $subject ?>                     
					<?php
					   if($num_ripple>0)
							echo '<span class="badge bg-primary "> '.$num_ripple.' </span> ';
					?>
				  </td>
				  <td class="text-center" >  <?= $first_writer ?>      </td>
				  <td class="text-center" >  <?= $chargedPerson ?>      </td>				  
				  <td class="text-center" >  <?= $regist_day ?>      </td>     				  
				  <td class="text-center">
				    <?= ($dueDate == '0000-00-00' || empty($dueDate)) ? '' : $dueDate ?>
			      </td>
				  <td class="text-center">
				    <?= ($doneDate == '0000-00-00' || empty($doneDate)) ? '' : $doneDate ?>
			      </td>
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
   </div> <!--card-body-->
   </div> <!--card -->
   </div> <!--container-->
	</form>   
</body> 
</html>   

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
});

var dataTable; // DataTables 인스턴스 전역 변수
var workprocesspageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('workprocesspageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var workprocesspageNumber = dataTable.page.info().page + 1;
        setCookie('workprocesspageNumber', workprocesspageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('workprocesspageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('workprocesspageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}


function redirectToView(num, tablename) {
    var page = workprocesspageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)
    	
    var url = "view.php?num=" + num + "&tablename=" + tablename;          

	customPopup(url, '', 1400, 900); 		    
}

$(document).ready(function(){
	
	$("#writeBtn").click(function(){ 
		var page = workprocesspageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)	
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '', 1400, 900); 	
	 });			 
		
});	

$(document).ready(function(){
	saveLogData('주요안건 처리현황'); 
});
</script>