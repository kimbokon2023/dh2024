<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
if(!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; 
$title_message = '중국발주 원단가';   
?>  
<title>  <?=$title_message?>  </title> 
</head>
<body> 

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/mymodal.php'); ?>       	 
<?php
$tablename = 'price'; 
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();  

// 검색 조건 설정
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$SettingDate = isset($_REQUEST['SettingDate']) ? $_REQUEST['SettingDate'] : "registedate";  // 기본 날짜 설정: registedate

$separate_date = isset($_REQUEST["separate_date"]) ? $_REQUEST["separate_date"] : "";
$existing_status = isset($_REQUEST["status_option"]) ? $_REQUEST["status_option"] : '전체';    

$currentDate = date("Y-m-d");

if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    $fromdate = date("Y-m-d", strtotime("-5 years", strtotime($currentDate))); 
    $todate =  date("Y-m-d", strtotime("+3 months", strtotime($currentDate)));
    $Transtodate = $todate;
} else {
    $Transtodate = $todate;
}

$SettingDate = "registedate";

$orderby = " order by " . $SettingDate . " desc, num desc";   

if ($existing_status == '전체') {    
    $where = " where " . $SettingDate . " between date('$fromdate') and date('$Transtodate') and is_deleted = '0' " . $orderby;
    $searchwhere = " where is_deleted = '0' and searchtag like '%$search%'" . $orderby;
} else {    
    $where = " where " . $SettingDate . " between date('$fromdate') and date('$Transtodate') and is_deleted = '0' and regist_state = '$existing_status'" . $orderby;
    $searchwhere = " where is_deleted = '0' and regist_state = '$existing_status' and searchtag like '%$search%'" . $orderby;
}

$sql = ($search == "") ? 
    "select * from $DB.$tablename " . $where : 
    "select * from $DB.$tablename " . $searchwhere;

$today = date("Y-m-d");

// print $sql;

try {   
    $stmh = $pdo->query($sql);           
    $total_row = $stmh->rowCount();
} catch (PDOException $Exception) {
    print "오류: " . $Exception->getMessage();
}           
?>
<form id="board_form" name="board_form" method="post" action="list.php?mode=search">      
    <input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
    <input type="hidden" id="num" name="num"> 
    <input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>">                 
    <input type="hidden" id="header" name="header" value="<?=$header?>">   
<div class="container">  
    <div class="card mb-2 mt-2">  
        <div class="card-body">  
            <div class="row">             
                <div class="col-sm-12">             
                    <div class="d-flex p-1 m-1 mt-1 justify-content-center align-items-center ">             
                        <h5>  <?=$title_message?>  </h5>  &nbsp;&nbsp;&nbsp;&nbsp;    
                        <button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  
                        <small class="ms-5 text-muted"> 중국발주 품목/품목코드/원단가를 설정합니다. </small>  
                    </div>    
                </div>    
            </div>                 
            <div class="row">              
                <div class="col-sm-12">              

                    <div class="d-flex p-1 m-1 mt-1 mb-1 justify-content-center align-items-center"> 
                        <ion-icon name="caret-forward-outline"></ion-icon> <?= $total_row ?> &nbsp;                     
                        <input type="date" id="fromdate" name="fromdate" class="form-control" style="width:100px;" value="<?=$fromdate?>">  &nbsp;   ~ &nbsp;  
                        <input type="date" id="todate" name="todate" class="form-control me-1" style="width:100px;" value="<?=$todate?>">  &nbsp;                        

						<div class="inputWrap">
							<input type="text" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off"  class="form-control" style="width:150px;" > &nbsp;			
							<button class="btnClear"></button>
							</div>	
							 &nbsp;
					  <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> 검색 </button> 	
					  	  &nbsp;&nbsp;&nbsp;		    
						<button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규  </button> 			 
                </div>                  
                </div>                  
            </div>                  
        </div> <!--card-body-->
    </div> <!--card -->   
    <div class="d-flex justify-content-center align-items-center">         
        <table class="table table-hover" id="myTable">
            <thead class="table-primary">
                <tr>
                    <th class="text-center" style="width:5%;"> 번호 </th>
                    <th class="text-center" style="width:10%;"> 등록일자 </th>
                    <th class="text-center" style="width:65%;"> 업데이트 로그 </th>
                    <th class="text-center" style="width:20%;"> 비고 </th>
					
                </tr>
            </thead>
            <tbody>
                <?php  
                $start_num = $total_row;          

                while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
					
					include '_row.php';	
					
                    ?>
                    <tr onclick="redirectToView('<?= $num ?>', '<?= $tablename ?>')">
                        <td class="text-center"><?= $start_num ?></td>
                        <td class="text-center"><?= $registedate ?></td>
                        <td class="text-start"><?= $update_log ?></td>
                        <td class="text-start"><?= $memo ?></td>						
                    </tr>
                    <?php
                    $start_num--;  
                } 
                ?>
            </tbody>
        </table>
    </div>
</div> <!--container-->
</form>

<script>
// 페이지 로딩
$(document).ready(function(){  
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});

function redirectToView(num, tablename) {   
    var url = "write_form.php?mode=view&num=" + num + "&tablename=" + tablename;          
    customPopup(url, '단가 단가', 1850, 900);          
}

$(document).ready(function(){	
	$("#writeBtn").click(function(){ 		
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '단가 단가', 1600, 800); 	
	 });	  
});	  

function submitForm(status) {
    $('input[name=status_option]').val(status);
    document.getElementById('board_form').submit();
}

$(document).ready(function(){
	saveLogData('모터 원가 산출'); 
}); 

</script>
</body>
</html>
