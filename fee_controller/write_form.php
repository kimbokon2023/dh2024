<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
		 sleep(1);
	          header("Location:" . $WebSite . "login/login_form.php");  
         exit;
   }  

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : "";
$tablename = isset($_REQUEST["tablename"]) ? $_REQUEST["tablename"] : "";

$first_writer = '';

if($mode === 'copy')
	$title_message = "(데이터복사) DH모터 연동제어기 (원가,단가)" ;
else
	$title_message = "DH모터 연동제어기 (원가,단가)" ;
 ?> 
<link href="css/style.css?v=1" rel="stylesheet" >    
<title> <?=$title_message?> </title>
</head>
<body>
<?   
include $_SERVER['DOCUMENT_ROOT'] . '/mymodal.php';
  
// 첨부 이미지에 대한 부분
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	
 
$URLsave = "https://dh2024.co.kr/motorloadpic.php?num=" . $num;  

$today = date("Y-m-d"); // 현재일자 변수지정

// var_dump($num);
// var_dump($mode);
// var_dump($tablename);
  
  if ($mode=="modify" || $mode=="view"){
    try{
	    $sql = "select * from " . $DB . "." . $tablename . " where num = ? ";
	    $stmh = $pdo->prepare($sql); 
		$stmh->bindValue(1,$num,PDO::PARAM_STR); 
		$stmh->execute();
		$count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);	  
		include "_row.php";		
	 }	 
	 }catch (PDOException $Exception) {
	   print "오류: ".$Exception->getMessage();
	 }	 
}
  
if ($mode !== "modify" and $mode !== "copy"  and $mode !== "view"  ) {    
	include '_request.php';
	$first_writer = $user_name ;
	 $basicdate= $today;
}

  if ($mode=="copy" ){
    try{
      $sql = "select * from " . $DB . "." . $tablename . " where num = ? ";
      $stmh = $pdo->prepare($sql); 

    $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
		print "검색결과가 없습니다.<br>";
     }else{
		$row = $stmh->fetch(PDO::FETCH_ASSOC);
	 }  
		include '_row.php';
		
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
	// 자료번호 초기화 
	 $num = 0;		 
	 $basicdate = $today;	 
	 
  }
  
?>
<form id="board_form"  name="board_form" method="post" enctype="multipart/form-data"  >

<input type="hidden" id="mode" name="mode">
<input type="hidden" id="num" name="num" value="<?=$num?>" >			  								
<input type="hidden" id="level" name="level" value="<?=$level?>" >			  								
<input type="hidden" id="user_name" name="user_name" value="<?=$user_name?>" >			  								
<input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>"  > 					
<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>"  > 					
<input type="hidden" id="price" name="price"  >
<input type="hidden" id="originalcost" name="originalcost"  >
<input type="hidden" id="item" name="item"  >
<input type="hidden" id="yuan" name="yuan"  >

<div class="container"> 

		<div class="row d-flex justify-content-center align-items-center ">	        
			<div class="card align-middle " style="width: 55rem;">	
			<div class="card-body text-center">							
				<div class="row d-flex justify-content-center align-items-center mb-3" >					
				<div class="col-sm-10" >					
					<div class="d-flex p-1 mb-1  justify-content-center align-items-center ">	
				<h3>	<?=$title_message?>    </h3>   &nbsp; &nbsp;  &nbsp; &nbsp; 	
					<?php if($mode =='view') { ?>		
					<button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php?mode=modify&num=<?=$num?>&tablename=<?=$tablename?>';" >  <i class="bi bi-pencil-square"></i> 수정 </button>
					<?php } ?>		
				   <?php if($mode!=='view') { ?>
						<button id="saveBtn" class="btn  btn-dark btn-sm me-1 " type="button">
						<? if((int)$num>0) print ' <i class="bi bi-hdd-fill"></i> 저장';  else print ' <i class="bi bi-hdd-fill"></i> 저장'; ?></button>
				   <? } if($level=='1') {  ?>		
					<?php if($mode!=='copy' && $mode!=='modify') { ?>				   
						<button id="copyBtn" class="btn btn-primary  btn-sm me-1" type="button"><i class="bi bi-copy"></i> 복사</button>					
						<button id="deleteBtn" class="btn btn-danger  btn-sm me-1" type="button"><i class="bi bi-trash2"></i> 삭제</button>					
						<? }  ?>		
					
						</div>
					</div>
					
					<? } ?>
						<div class="col-sm-2" >					
							<button type="button" class="btn btn-outline-dark  btn-sm " onclick="self.close();" > &times; 닫기 </button>	&nbsp; 				
						</div>	
					</div>	

				<div class="d-flex p-1 mb-1  justify-content-center align-items-center ">					  
					<span class="form-control mb-2" style="width:810px;" >
						<div class="d-flex p-1 mb-1  justify-content-center align-items-center ">
							<span class="form-control text-center badge bg-primary fs-5"   style="width:150px;" >  작성 기준일  </span>	 	&nbsp;
							<input type="date" name="basicdate" id="basicdate"  class="form-control me-3"  style="width:120px;" value="<?=$basicdate?>"  > 		 &nbsp; &nbsp; 						 							 
							<span class="badge bg-secondary fs-6"> 메모 </span>	&nbsp;				
							<textarea name="memo" id="memo" class="form-control" placeholder="(메모)" style="width:400px;"><?=$memo?></textarea>				    								 
						</div> 
					</span>
				</div>
				
			<?php if($mode!=='view') { ?>	
				<div class="d-flex justify-content-center align-items-center">
					<span class="form-control" style="width:810px;">
							<div id="grid" style="width:800px;">  </div>									
					</span>	
				</div>		  
				<? } else { ?>			
				<div class="d-flex justify-content-center align-items-center"> 		
					<table class="table" id="myTable">
					  <thead class="table-primary">
						<tr>
						  <th class="text-center " >번호</th>      						  
						  <th class="text-center " style="width:400px;">품목(품목코드)</th>      						  						  
						  <th class="text-center " style="width:100px;">원가</th>      
						  <th class="text-center " style="width:100px;">단가</th>   
						  <th class="text-center " style="width:100px;">구매시 위엔화 </th>  						  
						</tr>        
					  </thead>	  
					<tbody>
						<?php
						
						 $sql = "select * from " . $DB . "." . $tablename .  " where num = $num ";
						 
						try {
							$stmh = $pdo->query($sql);
							$rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // Fetch all data at once
							$total_row = count($rows); // Count the number of rows fetched
							
							// Reverse the array of rows 역순으로 배열바꾸기
							$rows = array_reverse($rows);

							foreach ($rows as $row) {
								// Decode and filter JSON data for each column as needed								
								$item = array_filter(json_decode($row['item'], true) ?? [], function($value) {
									return trim($value) !== '';
								});								
								
								$originalcost = json_decode($row['originalcost'], true) ?? [];
								$price = json_decode($row['price'], true) ?? [];
								$yuan = json_decode($row['yuan'], true) ?? [];
						

								$minSize = count($item); // Recalculate minimum size based on filtered $item

								for ($i = 0; $i < $minSize; $i++) {
									?>
									<tr>
										<td class="text-center"><?= ($i+1) ?></td>
										<td class="text-center"><?= htmlspecialchars($item[$i] ?? '') ?></td>                                        										
										<td class="text-end"><?= htmlspecialchars(number_format(is_numeric($originalcost[$i]) ? $originalcost[$i] : 0)) ?></td>
										<td class="text-end"><?= htmlspecialchars(number_format(is_numeric($price[$i]) ? $price[$i] : 0)) ?></td>										
										<td class="text-end"><?= htmlspecialchars($yuan[$i] ?? '') ?></td>
									</tr>
									<?php
								}								
							}
						} catch (PDOException $Exception) {
							echo "오류: " . $Exception->getMessage();
						}
						?>
					</tbody>
						</table>  
					</div>
				<?php } ?>		             		  
			</div>
		</div>
	  </div>						
   </div>		  		
</form>	
</body>
</html> 
   
<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
});
</script>

<?php if($mode !== 'view') { ?>
<script>
var mode = '<?php echo $mode; ?>';

var ajaxRequest_write = null;

$(document).ready(function(){		      
    var price = JSON.parse('<?php echo isset($price) ? addslashes($price) : "[]"; ?>');
    var originalcost = JSON.parse('<?php echo isset($originalcost) ? addslashes($originalcost) : "[]"; ?>');
    var item = JSON.parse('<?php echo isset($item) ? addslashes($item) : "[]"; ?>');
	var yuan         = <?php echo json_encode(isset($yuan) ? json_decode($yuan) : []); ?> || [];	

	//  console.log('item',item);    
	
	let row_count = 50;				   
	
	const COL_COUNT = 4;
	
	const data = [];
	const columns = [];
	 
	for (let i = 0; i < row_count; i += 1) {
		const row = { name: i };		
		
		row[`yuan`]         = yuan[i] !== undefined ? yuan[i] : "";
		
		
		if (price[i] !== undefined && price[i] !== null && price[i] !== '') {
			row['price'] = comma(price[i]);
		} else {
			row['price'] = ''; // Assign an empty string or another default value if there is no price
		}

		if (originalcost[i] !== undefined && originalcost[i] !== null && originalcost[i] !== '') {
			row['originalcost'] = comma(originalcost[i]);
		} else {
			row['originalcost'] = ''; // Assign an empty string or another default value if there is no original cost
		}				 						
		row[`item`] = item[i] ;			
		data.push(row);
				
	  }	  

	   const grid = new tui.Grid({
		  el: document.getElementById('grid'),
		  data: data,
		  bodyHeight: 750,	
		   columns: [ 				   
			{
			  header: '품목(품목코드)',
			  name: 'item',
			  width:350,						  
			  editor: {			
				type: 'text',
				
			  }	,  
				align: 'left'
			  // sortingType: 'desc',
			  // sortable: true,          
			  // editingEvent :  'Click'		  
			},						
			{
			  header: '원가',
			  name: 'originalcost',
			  width:150,
			  // sortingType: 'desc',
			  // sortable: true,
			  editor: {
				type: 'text',
				
			  }	, 		  
			  align: 'right'
			},
			{
			  header: '단가',
			  name: 'price',
			  width:120,						  
			  // sortingType: 'desc',
			  // sortable: true,
			  editor: {
				type: 'text',
				
			  }	, 		  
			  align: 'right'
			} ,
			{
			  header: '구매시 위엔화',
			  name: 'yuan',
			  width:110,						  
			  // sortingType: 'desc',
			  // sortable: true,
			  editor: {
				type: 'text',
				
			  },
			  align: 'right'			  
			}	
		  ],
	// 	  draggable: true,
			columnOptions: {
				resizable: true
				  },
			  // rowHeaders: ['rowNum','checkbox'],
			  rowHeaders: ['rowNum'],
			  // pageOptions: {
				// useClient: false,
				// perPage: 20
		  // },
		  
		});	
		
	// grid 꾸미기
	var Grid = tui.Grid; // or require('tui-grid')
		Grid.applyTheme('striped', {
				selection: {
					background: '#7d7575',
					border: '#00000c'
				  },
				  scrollbar: {
					background: '#f5f5f5',
					thumb: '#d9d9d9',
					active: '#c1c1c1'
				  },
				  row: {
					even: {
					  background: '#EEFAEE'
					},
					hover: {
					  background: '#e8e8e8'
					}
				  },
				  cell: {
					normal: {
					  background: '#fbfbfb',
					  border: '#e0e0e0',
					  showVerticalBorder: true
					},
					header: {
					  background: '#eee',
					  border: '#ccc',
					  showVerticalBorder: true
					},
					rowHeader: {
					  border: '#ccc',
					  showVerticalBorder: true
					},
					editable: {
					  background: '#fbfbfb'
					},
					selectedHeader: {
					  background: '#d8d8d8'
					},
					focused: {
					  border: '#b0b0b0'
					},
					disabled: {
					  text: '#b0b0b0'
					}
				  }	
	});
	 
	 
		 // console에 이벤트를 출력한다. 
		grid.on('editingFinish', ev => {
			console.log('check!', ev);					  
		});

		grid.on('mouseout', ev => {
		
		});

		grid.on('focusChange', ev => {
		     console.log('change onGridUpdated cell!', ev);
		 }); 	
	
	   								
// grid 변경된 내용을 php 넘기기 위해 input hidden에 넣는다.
function savegrid() {    
    let price  =  new Array();  
    let originalcost  =  new Array();  
    let item  =  new Array();     
    let yuan  =  new Array();     
    
    const MAXcount = grid.getRowCount();
    for (let i = 0; i < MAXcount; i++) {        
        
        price.push(zero(uncomma(grid.getValue(i, 'price'))));
        originalcost.push(zero(uncomma(grid.getValue(i, 'originalcost'))));
        item.push(grid.getValue(i, 'item'));
        yuan.push(grid.getValue(i, 'yuan'));
    }

    // Set the value of hidden inputs        
    document.getElementById('price').value = JSON.stringify(price);
    document.getElementById('originalcost').value = JSON.stringify(originalcost);
    document.getElementById('item').value = JSON.stringify(item);
    document.getElementById('yuan').value = JSON.stringify(yuan);
}	
	   
 function zero(str) {
	if(str=='0')
		return '';	
	   else
		return str;			   
 }		 
 
   $("#deldataBtn").click(function(){  
    // 삭제시 삽입과 마찬가지로 데이터를 화면상 지워주고 rowKey를 제거하는 방식으로 진행한다.
	
	  var tmp = grid.getCheckedRowKeys();
	  tmp.forEach(function(e){
		 deleteRow(e);        // 함수를 만들어서 한줄삽입처리함.		
		  console.log(e);
		  
			});	
	});	
	
	 function deleteRow(index=null) {		
   
		let price  =  new Array();  
		let originalcost  =  new Array();  				
		let item  =  new Array(); 
		
		// console.log(grid.getRowCount());	//삭제시 숫자가 정상적으로 줄어든다.
		 let MAXcount = grid.getRowCount() ;  
         console.log(MAXcount);
		 
	     
		 // 화면 지워준다
		      for(i = 0; i < MAXcount ; i++) {														
					grid.setValue(i, 'price', '');
					grid.setValue(i, 'originalcost', '');
					grid.setValue(i, 'item', '');					
					grid.setValue(i, 'yuan', '');					
				}        


           // 실제 데이터를 지워준다.
		   //  grid.removeRow(index);	

 			MAXcount = grid.getRowCount()  ;  // 1+ 1개 데이터를 추가				 
		  
		  // 새로 삽입되는 행을 포함한 데이터를 새로 넣어준다
			for(i = 0; i < item.length ; i++) {									
					grid.setValue(i, 'price', price[i]);
					grid.setValue(i, 'originalcost', originalcost[i]);
					grid.setValue(i, 'item', item[i]);					
					grid.setValue(i, 'yuan', yuan[i]);					
				}
		  
		  
	  }	   	
	  
  // 행삽입	  
   $("#insertdataBtn").click(function(){  
      
		var tmp = grid.getCheckedRowKeys();
		tmp.forEach(function(e){
		 appendRow(e+1);        // 함수를 만들어서 한줄삽입처리함.
		  console.log(e);
								  
			});	
		  
	  });	   			   				 
	
		 
	function appendRow(index= null) {
		
		// 레코드를 중간에 삽입하면 rowKey가 적용안되는 문제를 해결하기 위해서 저장 후 다시 불러주는 루틴작성
		
		        // 마지막에 한줄 추가 후 삽입작업
				var newRow = {
					eventId: '',
					localEvent: '',
					copyControl: ''
						};
				grid.appendRow(newRow);
				
		let price  =  new Array();  
		let originalcost  =  new Array();  				
		let item  =  new Array(); 
		let yuan  =  new Array(); 
		
		// console.log(grid.getRowCount());	//삭제시 숫자가 정상적으로 줄어든다.
		 const MAXcount = grid.getRowCount() + 1 ;  // 1+ 1개 데이터를 추가	
         console.log(MAXcount);
		 
		 for(i=0; i < MAXcount; i++) {      // grid.value는 중간중간 데이터가 빠진다. rowkey가 삭제/ 추가된 것을 반영못함.    
			if( index != i  )  // 행삽입을 해준다.
				{				 								   
					
					price.push(grid.getValue(i, 'price' ));																 
					originalcost.push(grid.getValue(i, 'originalcost' ));																 
					item.push(grid.getValue(i, 'item' ));		
					yuan.push(grid.getValue(i, 'yuan' ));		
				} // end of else				
			 else  // 삽입행과 만나면 공백을 넣어준다.
			  {
				  
					
					price.push('');																 
					originalcost.push('');																 
					item.push('');					
					yuan.push('');					

					
					price.push(uncomma(grid.getValue(i, 'price' )));																 
					originalcost.push(uncomma(grid.getValue(i, 'originalcost' )));																 
					item.push(grid.getValue(i, 'item' ));		
					yuan.push(grid.getValue(i, 'yuan' ));		
					
			   }				 
			 }
         
		 // 화면 지워준다
		      for(i = 0; i < MAXcount ; i++) {									
					grid.setValue(i, 'price', '');
					grid.setValue(i, 'originalcost', '');
					grid.setValue(i, 'item', '');					
					grid.setValue(i, 'yuan', '');					
				}        
		  		 
		  
		  // 새로 삽입되는 행을 포함한 데이터를 새로 넣어준다
			for(i = 0; i < MAXcount ; i++) {									
					grid.setValue(i, 'price', price[i]);
					grid.setValue(i, 'originalcost', originalcost[i]);
					grid.setValue(i, 'item', item[i]);					
					grid.setValue(i, 'yuan', yuan[i]);					
				}         
		}	
			 
		$("#closeModalBtn").click(function(){ 
			$('#myModal').modal('hide');
		});

	$("#closeBtn").click(function(){    // 저장하고 창닫기	
		 });	


	$("#saveBtn").click(function(){      // DATA 저장버튼 누름
	   // 견적서 그리드 저장
		savegrid();
		var num = $("#num").val();  	
			   
		// 결재상신이 아닌경우 수정안됨	 
		if(Number(num)>0) 
			   $("#mode").val('modify');     
			  else
				  $("#mode").val('insert');     
			  
		// 자료 삽입/수정하는 모듈		  
		Fninsert();	
			
	 }); 
 

			 
}); // end of ready document

</script>

<?php } ?>
 
<script>
var dataTable; // DataTables 인스턴스 전역 변수
var feeviewpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

var ajaxRequest_write = null;

$(document).ready(function() {			
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 500,
        "lengthMenu": [25, 50, 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[0, 'asc']]
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('feeviewpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var feeviewpageNumber = dataTable.page.info().page + 1;
        setCookie('feeviewpageNumber', feeviewpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('feeviewpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('feeviewpageNumber');
    // if (savedPageNumber) {
        // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
	location.reload(true);
}


$(document).ready(function(){	

	// 마지막에 넣어줘야 전체를 적용할 수 있다.
	if (mode === 'view') {
		$('input, textarea, select').prop('disabled', true); // Disable all input, textarea, and select elements
		// $('.sub_add').prop('disabled', true); // Disable buttons with the class 'restrictbtn'
		// $('.add').prop('disabled', true); // Disable buttons with the class 'restrictbtn'
		// $('input[type=file]').prop('disabled', false); // file input elements
		// $('input[type=hidden]').prop('disabled', false); // file input elements
	}
	
	$("#copyBtn").click(function(){ 
		
		location.href = 'write_form.php?mode=copy&num='  + $("#num").val() + "&tablename="  + $("#tablename").val() ;
				
	 }); // end of function	
	 
	
	// 삭제버튼
	$("#deleteBtn").click( function() {  
	
		var update_log = $("#update_log").val();	
		var user_name = $("#user_name").val();	
		var level = $("#level").val();	

	if (!update_log.includes(user_name) && level !== '1') 
	   {	
			Swal.fire({
				title: '삭제불가',
				text: "작성자와 관리자만 삭제가능합니다.",
				icon: 'error',
				confirmButtonText: '확인'
			});
		} else {
		
		Swal.fire({
				title: '자료 삭제',
				text: "삭제는 신중! 정말 삭제하시겠습니까?",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: '삭제',
				cancelButtonText: '취소'
			}).then((result) => {
				
				if (result.isConfirmed) {
					
					$("#mode").val('delete'); // `mode` 값을 설정
					var form = $('#board_form')[0];
					var formData = new FormData(form); // `formData`를 여기에서 정의합니다.

					// `formData`에 필요한 추가 데이터를 수동으로 설정
					formData.set('mode', $("#mode").val());
					formData.set('num', $("#num").val());

					console.log('mode', $("#mode").val());
					console.log('num', $("#num").val());

					if ( (typeof ajaxRequest_write !== 'undefined' && ajaxRequest_write) || ajaxRequest_write!==null ) {
						ajaxRequest_write.abort();
					}				

					ajaxRequest_write = $.ajax({
						enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
						processData: false,    
						contentType: false,      
						cache: false,           
						timeout: 1000000, 			
						url: "insert.php",
						type: "post",		
						data: formData,		
						dataType: "json", 
						success : function( data ){
							
							console.log(data);
							Toastify({
								text: "파일 삭제완료 ",
								duration: 2000,
								close:true,
								gravity:"top",
								position: "center",
								style: {
									background: "linear-gradient(to right, #00b09b, #96c93d)"
								},
							}).showToast();	
							setTimeout(function(){
								if (window.opener && !window.opener.closed) {
									// 부모 창에 restorePageNumber 함수가 있는지 확인
									if (typeof window.opener.restorePageNumber === 'function') {
										window.opener.restorePageNumber(); // 함수가 있으면 실행
									}
									// window.opener.location.reload(); // 부모 창 새로고침
									window.close();
								}							
								
							}, 2000);	
							
						},
						error : function( jqxhr , status , error ){
							console.log( jqxhr , status , error );
						} 			      		
					   });	
						

				}
			});
		}
											 
	});	

   // 화면이 시작된 후    
   hideOverlay();	
	
});


	// 삽입/수정하는 모듈 
	function Fninsert() {	 

	// $("#mode").val('modify');

	// 폼데이터 전송시 사용함 Get form         
	var form = $('#board_form')[0];  	    
	// Create an FormData object          
	var data = new FormData(form); 

		// tmp='파일을 저장중입니다. 잠시만 기다려주세요.';		
		// $('#alertmsg').html(tmp); 			  
		// $('#myModal').modal('show'); 	
		
	$("#overlay").show(); // 오버레이 표시
	$("button").prop("disabled", true); // 모든 버튼 비활성화		
			
		Toastify({
				text: "저장중...",
				duration: 2000,
				close:true,
				gravity:"top",
				position: "center",
				style: {
					background: "linear-gradient(to right, #00b09b, #96c93d)"
				},
			}).showToast();	

		// console.log('formdata : ', data);		  

	if ( (typeof ajaxRequest_write !== 'undefined' && ajaxRequest_write) || ajaxRequest_write!==null ) {
		ajaxRequest_write.abort();
	}				

	ajaxRequest_write = $.ajax({	
		enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
		processData: false,    
		contentType: false,      
		cache: false,           
		timeout: 600000, 			
		url: "insert.php",
		type: "post",		
		data: data,			
		dataType:"json",  
		success : function(data){
			// console.log(data);				
			
			setTimeout(function() {
			Toastify({
					text: "저장완료",
					duration: 3000,
					close:true,
					gravity:"top",
					position: "center",
					style: {
						background: "linear-gradient(to right, #00b09b, #96c93d)"
					},
				}).showToast();				

				}, 1000);		
			
			setTimeout(function() {
				 // $('#myModal').modal('hide');  
				opener.location.reload();
				window.close();		 
				}, 2000);	

			hideOverlay();				
			   
		},
		error : function( jqxhr , status , error ){
			console.log( jqxhr , status , error );
					} 			      		
	   });		

		// else
		  // {
		  // tmp='보고자만 결재상신 상태가 아닌 경우 수정이 가능합니다.';		
		  // $('#alertmsg').html(tmp); 			  
			// $('#myModal').modal('show');  
		  // }
		  
	} 
		 


</script>


<!-- mode == 'view' 조회 화면일때 사용금지 시키는 구문 -->
<script>
$(document).ready(function(){	
	var mode = '<?php echo $mode; ?>';
	// 마지막에 넣어줘야 전체를 적용할 수 있다.
	if (mode === 'view') {
		$('input, textarea, select').prop('disabled', true); // Disable all input, textarea, and select elements
		$('input[type=file]').prop('disabled', false); 
		$('input[type=hidden]').prop('disabled', false); 
	}
	
});
</script>