<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
// 첫 화면 표시 문구
$title_message = 'DH 모터 수주'; 
?>
<?php   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
 ?>
<title> <?=$title_message?> </title>
<link href="css/style.css" rel="stylesheet" >   
</head>
<body>		 
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   
<?php
$tablename = 'motor';  

$check = isset($_REQUEST['check']) ? $_REQUEST['check'] : '';
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// /////////////////////////첨부파일 있는 것 불러오기 
$savefilename_arr=array(); 
$realname_arr=array(); 
$attach_arr=array(); 
$tablename='motor';
$item = 'motor';

$sql=" select * from " . $DB . ".fileuploads where tablename ='$tablename' and item ='$item' ";	

 try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			array_push($realname_arr, $row["realname"]);			
			array_push($savefilename_arr, $row["savename"]);			
			array_push($attach_arr, $row["parentid"]);			
        }		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
  }    

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    $fromdate = date("Y-m-d", strtotime("-2 weeks", strtotime($currentDate))); // 2주 이전 날짜
    $todate =  date("Y-m-d", strtotime("+2 months", strtotime($currentDate))); // 3개월 이전 날짜
	$Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}
		
$sql=" select * from " . $DB . ".motor " ;

$sum=array();
  	
$now = date("Y-m-d");	 // 현재 날짜와 크거나 같으면 출고예정으로 구분

$orderby="order by deadline desc, num desc ";
 
$attached=''; 
$whereattached = '';
$titletag = '';

if ($check=='1')  // 미출고 리스트 클릭
		{
				$attached=" and ((outputdate IS NULL OR outputdate='0000-00-00')) ";
				$whereattached=" where (outputdate IS NULL OR outputdate='0000-00-00')  ";
				$titletag = "(미출고)";
		}
if ($check=='2')  // 제작완료 미청구
		{				
				$attached=" and ((demand IS NULL OR demand='0000-00-00')) ";
				$whereattached=" where (demand IS NULL OR demand='0000-00-00')  ";
				$titletag = "(미청구)";
		}
if ($check=='3')  // 회수예정
		{				
				$attached=" and (returndue = '회수예정' ) ";
				$whereattached=" where (returndue ='회수예정')  ";
				$titletag = "(회수예정)";
		}
if ($check=='4')  // 회수완료
		{				
				$attached=" and (returncheck = '회수완료' ) ";
				$whereattached=" where (returncheck = '회수완료' )  ";
				$titletag = "(회수완료)";				
		}		
if ($check=='0' || $check==0)
	$whereattached = '';
		
$SettingDate=" deadline "; 
	 
$common= $SettingDate . " between '$fromdate' and '$Transtodate' and is_deleted IS NULL ";
		
$andPhrase= " and " . $common  . $orderby ;
$wherePhrase= " where " . $common  . $orderby ;


// 검색을 위해 모든 검색변수 공백제거
$search = str_replace(' ', '', $search);  

if($search==""){
	if($whereattached!=='')
		$sql="select * from " . $DB . ".motor " . $whereattached . $andPhrase; 					                 
		else
			$sql="select * from " . $DB . ".motor " . $wherePhrase ;					                 
	}     				 
	else {
		$sql ="select * from " . $DB . ".motor where (replace(searchtag,' ','') like '%$search%' ) " . $attached . " and is_deleted IS NULL "; 					                       
	}
		   
// print '$sql : ' . $sql;  
// print '$check : ' . $check;  
$current_condition = $check;

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

<form id="board_form" name="board_form" method="post" action="list.php?mode=search">  
	<input type="hidden" id="voc_alert" name="voc_alert" value="<?=$voc_alert?>"  > 	
	<input type="hidden" id="ma_alert" name="ma_alert" value="<?=$ma_alert?>" >
	<input type="hidden" id="check" name="check" value="<?=$check?>" >
	
<div class="container-fluid">  
	<div class="card mb-2 mt-2">  
	<div class="card-body">  	 
			 
	<div class="d-flex  p-1 m-1 mt-1 justify-content-center align-items-center "> 		
		   <h5>  <?=$title_message?>  <?=$titletag?> </h5>  
		<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  		   
	   <span id="showalign" class="btn btn-dark btn-sm me-2" > <i class="bi bi-card-list"></i> 정렬 </span>	
		<div id="showalignframe" class="card">
			<div class="card-header text-center " style="padding:2px;">
				화면정렬
			</div>					
				<div class="card-body">				
					<?php
					function printCheckbox($id, $value, $label, $checkedValue) {
						$isChecked = ($value == $checkedValue) ? "checked" : "";                                    
						echo "<input type='checkbox' class='search-condition' $isChecked id=$id value='$value'>&nbsp; <span class='badge bg-dark' style='font-size:13px;'> $label </span>  &nbsp;&nbsp;";
					}                                                        

					printCheckbox('all', '0', '전체', $current_condition);                                
					printCheckbox('without', '1', '미출고', $current_condition);
					printCheckbox('not_claimed', '2', '출고완료 미청구', $current_condition);
					?>									
				</div>
			</div>					

				
		</div>	

	<div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center"> 	  
			▷   <?= $total_row ?> &nbsp; 		

			<!-- 기간부터 검색까지 연결 묶음 start -->
				<span id="showdate" class="btn btn-dark btn-sm " > 기간 </span>	&nbsp; 
				
				<div id="showframe" class="card">
					<div class="card-header " style="padding:2px;">
						<div class="d-flex justify-content-center align-items-center">  
							기간 설정
						</div>
					</div>
					<div class="card-body">
						<div class="d-flex justify-content-center align-items-center">  	
							<button type="button" class="btn btn-outline-success btn-sm me-1 change_dateRange"   onclick='alldatesearch()' > 전체 </button>  
							<button type="button" id="preyear" class="btn btn-outline-primary btn-sm me-1 change_dateRange"   onclick='pre_year()' > 전년도 </button>  
							<button type="button" id="three_month" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='pre_month()' > 전월 </button>
							<button type="button" id="prepremonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='dayBeforeYesterday()' > 전전일 </button>	
							<button type="button" id="premonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='yesterday()' > 전일 </button> 						
							<button type="button" class="btn btn-outline-danger btn-sm me-1  change_dateRange"  onclick='this_today()' > 오늘 </button>
							<button type="button" id="thismonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='this_month()' > 당월 </button>
							<button type="button" id="thisyear" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='this_year()' > 당해년도 </button> 
						</div>
					</div>
				</div>		

	   <input type="date" id="fromdate" name="fromdate"   class="form-control"   style="width:100px;" value="<?=$fromdate?>" >  &nbsp;   ~ &nbsp;  
	   <input type="date" id="todate" name="todate"  class="form-control me-1"   style="width:100px;" value="<?=$todate?>" >  &nbsp;     </span> 
			
		<div class="inputWrap">
				<input type="text" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off"  class="form-control" style="width:150px;" > &nbsp;			
				<button class="btnClear"></button>
		</div>				
		
		<div id="autocomplete-list">						
		</div>	
		  &nbsp;
		  <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> 검색  </button> 		  
		  &nbsp;&nbsp;&nbsp;		    
				 <button type="button" class="btn btn-primary  btn-sm me-1" id="returndueBtn"> <i class="bi bi-backspace"></i>  회수예정  </button> 
				 <button type="button" class="btn btn-danger btn-sm me-1" id="returnBtn"> <i class="bi bi-arrow-return-left"></i> 회수완료  </button> 
				 <button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규  </button> 
				 <button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('register.php','일일접수',1500,900);">  <i class="bi bi-r-square-fill"></i> </ion-icon> 일일접수 </button>    							 
				 <button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('plan_done.php','출고완료',1500,900);">  <i class="bi bi-calendar-check"></i>  출고완료 </button>
				 <button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('plan_making_kd.php','경동입고',1500,900);"> <i class="bi bi-truck-flatbed"></i> 경동입고 </button>
				 <button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('delivery.php','화물택배',1500,900);">  <i class="bi bi-truck"></i> 화물택배 </button>    							 
				<button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('print_group.php','출고증 묵음',1500,900);">  <i class="bi bi-printer"></i> 출고증 묶음 </button>    							 				 
				 <!-- <button  type="button" id="rawmaterialBtn"  class="btn btn-dark btn-sm" > <i class="bi bi-list"></i> 재고 </button> &nbsp;	 -->
         </div> 	 		 
		<!-- <div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center"> 		 
			<div id="showstatusframe" class="card">			
				<div class="card-body">				
					 <button type="button" class="btn btn-dark btn-sm " onclick="popupCenter('call_csv.php','CSV 파일추출',1600,500);"> 엑셀CSV</button>  	
				</div>				
			</div>			 
		</div>			 
		-->		 
   </div> <!--card-body-->
   </div> <!--card -->   
</div> <!--container-fluid -->   
<div class="container-fluid">  
<div class="d-flex justify-content-center align-items-center"> 		
<table class="table table-hover" id="myTable">
  <thead class="table-primary">
    <tr>
      <th class="text-center " style="width:50px;" >번호</th>      
      <th class="text-center " style="width:90px;" >접수</th>
      <th class="text-center " style="width:90px;">출고예정</th>
      <th class="text-center " style="width:50px;">출고</th>
      <th class="text-center " style="width:50px;">청구</th>      	  
      <th class="text-center text-danger " style="width:40px;">
		   </div class="d-flex">
			  <span class="badge bg-primary"> 회수예정 </span>
			  <span class="badge bg-danger"> 회수완료 </span> 
		   </div>
	  </th>      	  
      <th class="text-center" style="width:80px;"> 진행 </th>		  
	  <th class="text-center " style="width:70px;"> <i class="bi bi-image"></i>  출하</th>		  
	  <th class="text-center text-danger" style="width:120px;">  로트번호</th>		  
      <th class="text-center " style="width:120px;">발주처</th>      
	  <th class="text-center " style="width:200px;">현장명</th>
      <th class="text-center " style="width:250px;">내역</th> 
	  <th class="text-center " style="width:100px;">상차지</th>
	  <th class="text-center " style="width:80px;">배송</th>    
	  <th class="text-center text-success" style="width:90px;"> <i class="bi bi-truck"></i> 배차</th>		  
	  <th class="text-center " style="width:180px;">배송지</th>
	  <th class="text-center " style="width:100px;">비고</th>    
      <th class="text-center " style="width:100px;">전달사항</th>	  
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
	
    foreach ($rows as $row) {
		
      include '_contentload.php';
	  // 사진등록 찾기
	  $registpicture = '' ;
	  $sqltmp=" select * from ".$DB.".picuploads where parentnum ='$num'";		  
	  try{  
		// 레코드 전체 sql 설정
		   $stmhtmp = $pdo->query($sqltmp);    
		   
		   while($rowtmp = $stmhtmp->fetch(PDO::FETCH_ASSOC)) {
				$registpicture = "등록" ;
				}		 
		   } catch (PDOException $Exception) {
			print "오류: ".$Exception->getMessage();		  			
		   }	
        // 로트번호 등록/미등록 가져오기
		include $_SERVER['DOCUMENT_ROOT'] . '/motor/load_lotnum.php';  
		
        ?>
        <tr onclick="redirectToView('<?= $row['num'] ?>', '<?= $tablename ?>')">
            <td class="text-center"><?= $start_num ?></td>
            <td class="text-center"><?= $row['orderdate'] ?></td>
			<td class="text-center" data-deadline="<?= $deadline ?>">
				<?= $deadline ?>
			</td>			
			<td class="text-center" data-deadline="<?= $outputdate ?>">
				<?= iconv_substr($outputdate, 5, 5, "utf-8") ?>
			</td>
			<td class="text-center" data-deadline="<?= $demand ?>">
				<?= iconv_substr($demand, 5, 5, "utf-8") ?>
			</td>
			<td class="text-center">
				<?php if (!empty($returncheck)): ?>
					<span class="badge bg-danger"><?= $returncheck ?></span>
				<?php elseif (!empty($returndue)): ?>
					<span class="badge bg-primary"><?= $returndue ?></span>
				<?php else: ?>
					<!-- 아무 값도 없을 경우 빈 공간을 표시하거나 원하는 내용을 추가할 수 있습니다. -->
					
				<?php endif; ?>
			</td>
			
			<td class="text-center align-middle">
				<?php				
				if ($status == '접수대기') {
					echo '<span class="badge bg-warning">' . $status . '</span>';
				} else if ($status == '접수확인') {
					echo '<span class="badge bg-success">' . $status . '</span>';
				} else if ($status == '준비중') {
					echo '<span class="badge bg-info">' . $status . '</span>';
				}else if ($status == '출고대기') {
					echo '<span class="badge bg-secondary">' . $status . '</span>';
				}else if ($status == '출고완료') {
					echo '<span class="badge bg-danger">' . $status . '</span>';
				}
				?>
			</td>
			<td class="text-center"><?=$registpicture?></td>			
			<td class="text-center text-danger fw-bold"><?=$registlotnum?></td>			
            <td class="text-start"><?= !empty($row['secondordnum']) ? '<span class="text-secondary">' . $row['secondord'] . '</span>' : '<span class="text-primary">' . $row['secondord'] . '</span>' ?></td>
            <td class="text-start "><?= $row['workplacename'] ?></td>
            <td class="text-start"><?= $contentslist ?></td>			
            <td class="text-start "><?= $loadplace ?></td>		
			<?php
				if($deliverymethod  == '선/대신화물')
					print ' <td class="text-center align-middle"> <span class="badge bg-danger"> ' . $deliverymethod . ' </span> </td>';	
				else if ($deliverymethod  == '선/경동화물' || $deliverymethod  == '착/경동화물')		
					print ' <td class="text-center align-middle"> <span class="badge bg-primary"> ' .  $deliverymethod . ' </span> </td>';			
				else if ($deliverymethod  == '배차')		
					print ' <td class="text-center align-middle"> <span class="badge bg-success"> ' .  $deliverymethod . '[' . $delcompany . '] </span> </td>';			
				  else
					print ' <td class="text-center align-middle">' .  $deliverymethod . ' </td>';			
			?>			
			<td class="text-center text-success"><?=$del_status?></td>				
            <td class="text-start "><?= $address ?></td>
            <td class="text-start "><?= $row['memo'] ?></td>
            <td class="text-start "><?= $row['comment'] ?></td>
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
	<? include '../footer_sub.php'; ?>
</div>

<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>

<script> 
var dataTable; // DataTables 인스턴스 전역 변수
var motorpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {			
    // DataTables 초기 설정
  dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 50,
        "lengthMenu": [50, 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[2, 'desc']], // 초기 정렬 열 인덱스와 방향
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('motorpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var motorpageNumber = dataTable.page.info().page + 1;
        setCookie('motorpageNumber', motorpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('motorpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('motorpageNumber');
    // if (savedPageNumber) {
        // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
	location.reload(true);
}

function redirectToView(num, tablename) {	
    var url = "write_form.php?mode=view&num=" + num + "&tablename=" + tablename;          
	customPopup(url, '수주내역', 1850, 900); 		    
}

$(document).ready(function(){	
	$("#writeBtn").click(function(){ 		
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '수주내역', 1850, 900); 	
	 });			 
	 
	// 회수예정 클릭시
	$("#returndueBtn").click(function(){ 
	    $("#check").val('3');
		document.getElementById('board_form').submit();    
	});		 
			
	// 회수완료 클릭시
	$("#returnBtn").click(function(){ 
	    $("#check").val('4');
		document.getElementById('board_form').submit();    
	});		 
			
		
});	


function SearchEnter(){

    if(event.keyCode == 13){		
		saveSearch();
    }
}

function saveSearch() {
    let searchInput = document.getElementById('search');
    let searchValue = searchInput.value;

    console.log('searchValue ' + searchValue);

    if (searchValue === "") {        
        document.getElementById('board_form').submit();
    } else {
        let now = new Date();
        let timestamp = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();

        let searches = getSearches();
        // 기존에 동일한 검색어가 있는 경우 제거
        searches = searches.filter(search => search.keyword !== searchValue);
        // 새로운 검색 정보 추가
        searches.unshift({ keyword: searchValue, time: timestamp });
        searches = searches.slice(0, 15);

        document.cookie = "searches=" + JSON.stringify(searches) + "; max-age=31536000";
		
		var motorpageNumber = 1;
		setCookie('motorpageNumber', motorpageNumber, 10); // 쿠키에 페이지 번호 저장		
		// Set dateRange to '전체' and trigger the change event
        $('#dateRange').val('전체').change();
        document.getElementById('board_form').submit();
    }
}

// 검색창에 쿠키를 이용해서 저장하고 화면에 보여주는 코드 묶음
	document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const autocompleteList = document.getElementById('autocomplete-list');  

    searchInput.addEventListener('input', function() {
	const val = this.value;
	let searches = getSearches();
	let matches = searches.filter(s => {
		if (typeof s.keyword === 'string') {
			return s.keyword.toLowerCase().includes(val.toLowerCase());
		}
		return false;
	});			
	   renderAutocomplete(matches);               
    });
	 
    
    searchInput.addEventListener('focus', function() {
        let searches = getSearches();
        renderAutocomplete(searches);   

        console.log(searches);				
    });
			
});

    var isMouseOverSearch = false;
    var isMouseOverAutocomplete = false;

    document.getElementById('search').addEventListener('focus', function() {
        isMouseOverSearch = true;
        showAutocomplete();
    });

	document.getElementById('search').addEventListener('blur', function() {        
		setTimeout(function() {
			if (!isMouseOverAutocomplete) {
				hideAutocomplete();
			}
		}, 100); // Delay of 100 milliseconds
	});


    function hideAutocomplete() {
        document.getElementById('autocomplete-list').style.display = 'none';
    }

    function showAutocomplete() {
        document.getElementById('autocomplete-list').style.display = 'block';
    }


function renderAutocomplete(matches) {
    const autocompleteList = document.getElementById('autocomplete-list');    

    // Remove all .autocomplete-item elements
    const items = autocompleteList.getElementsByClassName('autocomplete-item');
    while(items.length > 0){
        items[0].parentNode.removeChild(items[0]);
    }

    matches.forEach(function(match) {
        let div = document.createElement('div');
        div.className = 'autocomplete-item';
        div.innerHTML =  '<span class="text-primary">' + match.keyword + ' </span>';
        div.addEventListener('click', function() {
            document.getElementById('search').value = match.keyword;
            autocompleteList.innerHTML = '';            
			console.log(match.keyword);
            document.getElementById('board_form').submit();    
        });
        autocompleteList.appendChild(div);
    });
}


function getSearches() {
    let cookies = document.cookie.split('; ');
    for (let cookie of cookies) {
        if (cookie.startsWith('searches=')) {
            try {
                let searches = JSON.parse(cookie.substring(9));
                // 배열이 50개 이상의 요소를 포함하는 경우 처음 50개만 반환
                if (searches.length > 15) {
                    return searches.slice(0, 15);
                }
                return searches;
            } catch (e) {
                console.error('Error parsing JSON from cookies', e);
                return []; // 오류가 발생하면 빈 배열 반환
            }
        }
    }
    return []; // 'searches' 쿠키가 없는 경우 빈 배열 반환
}


$(document).ready(function(){	

		$("#denkriModel").hover(function(){
			$("#customTooltip").show();
		}, function(){
			$("#customTooltip").hide();
		});

		$("#searchBtn").click(function(){ 	
			  saveSearch(); 
		 });		



});

$(document).ready(function() {
    $('.search-condition').change(function() {
        // 모든 체크박스의 선택을 해제합니다.
        $('.search-condition').not(this).prop('checked', false);

        // 선택된 체크박스의 값으로 `check` 필드를 업데이트합니다.
        var condition = $(this).is(":checked") ? $(this).val() : '';
        $("#check").val(condition);

        // 검색 입력란을 비우고 폼을 제출합니다.
        // $("#search").val('');                                                      
        $('#board_form').submit();  
    });
});


$(document).ready(function(){	
	
    // showstatus 요소와 showstatusframe 요소가 페이지에 존재하는지 확인
    var showstatus = document.getElementById('showstatus');
    var showstatusframe = document.getElementById('showstatusframe');
    
    // 요소가 존재하지 않으면 나머지 코드는 실행하지 않음
    if (!showstatus || !showstatusframe) {
        return;
    }

    var hideTimeoutstatus; // 프레임을 숨기기 위한 타이머 변수 

    // 요소가 존재한다면 이벤트 리스너를 추가
    showstatus.addEventListener('mouseenter', function(event) {
        clearTimeout(hideTimeoutstatus);  // 이미 설정된 타이머가 있다면 취소
        showstatusframe.style.top = (showstatus.offsetTop + showstatus.offsetHeight) + 'px';
        showstatusframe.style.left = showstatus.offsetLeft + 'px';
        showstatusframe.style.display = 'block';
    });

    showstatus.addEventListener('mouseleave', startstatusHideTimer);

    showstatusframe.addEventListener('mouseenter', function() {
        clearTimeout(hideTimeoutstatus);  // 이미 설정된 타이머가 있다면 취소
    });

    showstatusframe.addEventListener('mouseleave', startstatusHideTimer);

    // 타이머를 시작하는 함수
    function startstatusHideTimer() {
        hideTimeoutstatus = setTimeout(function() {
            showstatusframe.style.display = 'none';
        }, 50);  // 300ms 후에 프레임을 숨깁니다.
    }
	
});

</script>

  
</body>
</html>
