<?php

if(!isset($_SESSION))      
		session_start(); 
if(isset($_SESSION["DB"]))
		$DB = $_SESSION["DB"] ;	
 $level= $_SESSION["level"];
 $user_name= $_SESSION["name"];
 $user_id= $_SESSION["userid"];	

  // 첫 화면 표시 문구
 $title_message = 'QC 공정표';
   
?>
   
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>

 
<title> <?=$title_message?> </title>  
 
 </head> 

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/common/modal.php"; ?>
   
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<?php

 if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
          /*   alert("관리자 승인이 필요합니다."); */
		 sleep(1);
         header("Location:".$_SESSION["WebSite"]."login/login_form.php"); 
         exit;
   }       
   
$tablename = "p_qccontrol";

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
				$sql ="select * from mirae8440." . $tablename . " order  by num desc   "; 				
             }
              $sql="select * from mirae8440." . $tablename . " where name like '%$search%' or subject like '%$search%'  or nick like '%$search%'  or regist_day like '%$search%'  order by num desc   ";              
       } else {
              $sql="select * from mirae8440." . $tablename . " order  by num desc  ";
       }


// 전체 레코드수를 파악한다.
try{  
	$stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
	$total_row=$stmh->rowCount();    		
			 
 ?>
 
<form name="board_form" id="board_form"  method="post" action="list.php?mode=search&search=<?=$search?>">

  <input type="hidden" id="page" name="page" value="<?=$page?>"  > 

  <div class="container justify-content-center">  
	<div class="card mt-2 mb-4">  
	<div class="card-body">  
 
 <div class="d-flex mt-3 mb-1 justify-content-center">  
    <img src="../img/qc_control.jpg" class="form-control">
  </div>	 
 <div class="d-flex mt-3 mb-1 justify-content-center">  
  <h3> <?=$title_message?> </h3>  
  </div>	 
  
 <div class="d-flex mt-3 mb-1 justify-content-center">  
 
    <div class="input-group p-2 mb-2 justify-content-center">	  
		<button type="button" class="btn btn-dark  btn-sm me-2" id="writeBtn"> <ion-icon name="pencil-outline"></ion-icon> 신규  </button> 	
			<input type="text" name="search" id="search" value="<?=$search?>" size="30" onkeydown="JavaScript:SearchEnter();" placeholder="검색어 입력"> 
		<button type="button" id="searchBtn" class="btn btn-dark"  > <ion-icon name="search-outline"></ion-icon> </button>						
		</div>
   </div>

<div class="row d-flex"  >
<table class="table table-hover" id="myTable">
		<thead class="table-primary" >
			<tr>
				 <th class="text-center" > 번호  </td>
				 <th class="text-center" > QC공정표명  </td>
				 <th class="text-center" > 등록인 </td>
				 <th class="text-center" > 등록일자 </td>      
				 <th class="text-center" > 조회수 </td>      
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
 ?>

    <tr onclick="redirectToView('<?=$item_num?>', '<?=$tablename?>')">
  
				  <td class="text-center" ><?= $start_num ?> </td>
				  <td class="text-start" > <?= $item_subject ?>  </td>
				  <td class="text-center" ><?= $item_nick ?> </td>
				  <td class="text-center" ><?= $item_date ?> </td>     
				  <td class="text-center" ><?= $item_hit ?> </td>     
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

</body>
</html>

<script>

var dataTable; // DataTables 인스턴스 전역 변수
var QCcontrolpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('QCcontrolpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var QCcontrolpageNumber = dataTable.page.info().page + 1;
        setCookie('QCcontrolpageNumber', QCcontrolpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('QCcontrolpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('QCcontrolpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}


function redirectToView(num, tablename) {
    var page = QCcontrolpageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)
    	
    var url = "view.php?num=" + num + "&tablename=" + tablename;          

	customPopup(url, 'QC 공정표', 1200, 900); 		    
}

$(document).ready(function(){
	
	$("#writeBtn").click(function(){ 
		var page = QCcontrolpageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)	
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, 'QC 공정표', 1300, 850); 	
	 });			 
		
});	

</script>
