<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';


   // 첫 화면 표시 문구
 $title_message = '전산 개발일지';   

 ?>
<title>  <?=$title_message?>  </title> 
</head>  
<body> 
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   
<?php
// Check if 'navibar' and 'menu' are set in the request and assign their values; if not, set them to an empty string
$navibar = isset($_REQUEST['navibar']) ? $_REQUEST['navibar'] : '';
$menu = isset($_REQUEST['menu']) ? $_REQUEST['menu'] : '';
?> 
<?php
$tablename = "rnd";

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
				$sql ="select * from ".$DB."." . $tablename . " order  by num desc   "; 				
             }
              $sql="select * from ".$DB."." . $tablename . " where name like '%$search%' or subject like '%$search%'  or nick like '%$search%'  or regist_day like '%$search%'   or searchtext like '%$search%'  order by num desc   ";              
       } else {
              $sql="select * from ".$DB."." . $tablename . " order  by num desc  ";
       }

  // 전체 레코드수를 파악한다.
	 try{  

	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $total_row=$stmh->rowCount();
         					 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  
	 
// var_dump($sql);
	 
 try{  
		$stmh = $pdo->query($sql); 
         ?>



<form name="board_form" id="board_form"  method="post" action="list.php?mode=search&search=<?=$search?>">

<div class="container justify-content-center">  

  <input type="hidden" id="page" name="page" value="<?=$page?>"  > 
  <input type="hidden" id="scale" name="scale" value="<?=$scale?>"  > 
  
  
	<div class="card mt-2 mb-4">  
	<div class="card-body">  
		<div class="d-flex mt-3 mb-2 justify-content-center">  
				<h5>  <?=$title_message?> </h5> 
		</div>	
  
 
 <div class="d-flex mt-3 mb-1 justify-content-center  align-items-center"> 
 
    <div class="input-group p-1 mb-1 justify-content-center">	  
			<button type="button" class="btn btn-dark  btn-sm me-2" id="writeBtn"> <ion-icon name="pencil-outline"></ion-icon> 신규  </button> 			   
		   <input type="text" name="search" id="search" value="<?=$search?>" size="25" onkeydown="JavaScript:SearchEnter();" placeholder="검색어"> 
			<button type="button" id="searchBtn" class="btn btn-dark"  > <i class="bi bi-search"></i> 검색 </button>				
	</div>
</div>
	   
	   
 <div class="row d-flex"  >
 <table class="table table-hover" id="myTable">
   <thead class="table-primary" >	    
            <tr>
			 <th class="text-center" > 번호    </th>
			 <th class="text-center" > 글제목   </th>
			 <th class="text-center" > 작성자   </th>
			 <th class="text-center" > 등록일자 </th>   
			 <th class="text-center" > 조회수   </th>   
			 </tr>
       </thead>
	<tbody>  
  
	
<?php  
 $start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번 
			 
 while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
  $item_num=$row["num"];
  $item_id=$row["id"];
  $item_name=$row["name"];
  $item_nick=$row["nick"];
  $item_hit=$row["hit"];
  $item_date=$row["regist_day"];
  $item_date=substr($item_date, 0, 10);
  $item_subject=str_replace(" ", "&nbsp;", $row["subject"]);
   
  $sql="select * from ".$DB.".notice_ripple where parent=$item_num";
  $stmh1 = $pdo->query($sql); 
  $num_ripple=$stmh1->rowCount(); 
 ?>
 
   <tr onclick="redirectToView('<?=$item_num?>', '<?=$tablename?>')">
  
				  <td class="text-center" >  <?= $start_num ?>      </td>
				  <td>  <?= $item_subject ?>   </td>
				  <td class="text-center" >  <?= $item_nick ?>      </td>
				  <td class="text-center" >  <?= $item_date ?>      </td>     
				  <td class="text-center" >  <?= $item_hit ?>       </td>    
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
    loader.style.display = 'none';
});
</script>

<script>

var dataTable; // DataTables 인스턴스 전역 변수
var qnapageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('qnapageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var qnapageNumber = dataTable.page.info().page + 1;
        setCookie('qnapageNumber', qnapageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('qnapageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('qnapageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}


function redirectToView(num, tablename) {
    var page = qnapageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)
    	
    var url = "view.php?num=" + num + "&tablename=" + tablename;          

	customPopup(url, '자료실', 1200, 900); 		    
}

$(document).ready(function(){
	
	$("#writeBtn").click(function(){ 
		var page = qnapageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)	
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '자료실', 1300, 850); 	
	 });			 
		
});	

$(document).ready(function(){
	saveLogData('전산 개발일지'); 
});
</script>