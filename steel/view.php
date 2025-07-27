<?php
if(!isset($_SESSION))      
	session_start(); 
if(isset($_SESSION["DB"]))
	$DB = $_SESSION["DB"] ;	
	$level= $_SESSION["level"];
	$user_name= $_SESSION["name"];
	$user_id= $_SESSION["userid"];	

$WebSite = "http://8440.co.kr/";	

 if(!isset($_SESSION["level"]) || $level>5) {	     
		 sleep(1);
         header ("Location:" . $WebSite . "login/logout.php");         
         exit;
  }
   
$title_message = '원자재 입출고';      

?> 
 
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>
 
<title> <?=$title_message?> </title> 
 
<style>
th, td {
    vertical-align: middle !important;
}
</style> 
 
 </head>
 
 <body>
  
  <?php
  if(isset($_REQUEST["mode"]))  //수정 버튼을 클릭해서 호출했는지 체크
   $mode=$_REQUEST["mode"];
  else
   $mode="";
  
  if(isset($_REQUEST["num"]))  //수정 버튼을 클릭해서 호출했는지 체크
   $num=$_REQUEST["num"];
  else
   $num="";
   

 // 철판종류에 대한 추출부분
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	
  
// JSON 파일 읽기
$jsonData = file_get_contents("../steelsourcejson.json");

// JSON을 PHP 배열로 변환
$data = json_decode($jsonData, true);

// 데이터 배열에서 각각의 부분을 추출
$steelsource_num = $data["steelsource_num"];
$steelsource_item = $data["steelsource_item"];
$steelsource_spec = $data["steelsource_spec"];
$steelsource_take = $data["steelsource_take"];
$steelitem_arr = $data["steelitem_arr"];  

$steelsource_item = array_values(array_filter($steelsource_item, 'strlen'));
$steelsource_item = array_values(array_unique($steelsource_item));
sort($steelsource_item);
$sumcount = count($steelsource_item);
// array_unshift($steelsource_item, " "); // 앞에 공백을 추가하는 공식
// var_dump($steelsource_item); 

     try{
      $sql = "select * from mirae8440.steel where num = ? ";
      $stmh = $pdo->prepare($sql); 

      $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();            
	  $row = $stmh->fetch(PDO::FETCH_ASSOC);  // $row 배열로 DB 정보를 불러온다.
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{

			include '_row.php'		  ;
	  
			 if($indate!="0000-00-00") $indate = date("Y-m-d", strtotime( $indate) );
					else $indate="";	 
			 if($outdate!="0000-00-00") $outdate = date("Y-m-d", strtotime( $outdate) );
					else $outdate="";	 							
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
?>

<style>
input {
    border: 3px solid #f5f5f5;
}
</style>

</head> 
    
<form id="board_form" name="board_form" method="post"  onkeydown="return captureReturnKey(event)" > 

  <!-- 전달함수 설정 input hidden -->
	<input type="hidden" id="id" name="id" value="<?=$id?>" >			  								
	<input type="hidden" id="parentid" name="parentid" value="<?=$parentid?>" >			  								
	<input type="hidden" id="fileorimage" name="fileorimage" value="<?=$fileorimage?>" >			  								
	<input type="hidden" id="item" name="item" value="<?=$item?>" >			  								
	<input type="hidden" id="upfilename" name="upfilename" value="<?=$upfilename?>" >			  								
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>" >			  								
	<input type="hidden" id="savetitle" name="savetitle" value="<?=$savetitle?>" >			  								
	<input type="hidden" id="pInput" name="pInput" value="<?=$pInput?>" >			  								
	<input type="hidden" id="mode" name="mode" value="<?=$mode?>" >			
	<input type="hidden" id="num" name="num" value="<?=$num?>" >			
	<input type="hidden" id="first_writer" name="first_writer" value="<?=$first_writer?>"  >
	<input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>"  >	
    <input type="hidden" id="steelitem" name="steelitem" >
    <input type="hidden" id="steelspec" name="steelspec" >
    <input type="hidden" id="steeltake" name="steeltake" >	
	
<div class="container"> 
    <div class="card mt-2 mb-3">
       <div class="card-body">
 	    <div class="d-flex mb-1 mt-2 justify-content-center align-items-center fs-4">        
	        원자재 입출고 &nbsp;       &nbsp;    
		 </div> 
		 
       <div class="row">
		<?php if($chkMobile) { ?>	
		  <div class="col-sm-12">
		<?php } if(!$chkMobile) { ?>	
		  <div class="col-sm-8">
		<?php  } ?>			 		   
			<div class="d-flex  justify-content-start">      		  				
				<button type="button"   class="btn btn-dark btn-sm" onclick="location.href='write_form.php?mode=modify&num=<?=$num?>'" > <i class="bi bi-pencil-square"></i> 수정 </button> &nbsp;
				<button type="button"   class="btn btn-danger btn-sm" onclick="javascript:del('delete.php?num=<?=$num?>&page=<?=$page?>')" > <i class="bi bi-trash"></i> 삭제  </button>	 &nbsp;									
				<button type="button"   class="btn btn-primary btn-sm" onclick="location.href='write_form.php?mode=copy&num=<?=$num?>'" > <i class="bi bi-copy"></i> 복사</button>	&nbsp;
				<button type="button"   class="btn btn-dark btn-sm" onclick="location.href='write_form.php'" > <i class="bi bi-file-plus"></i> 신규 </button>		&nbsp;														
					</div> 			
			 </div> 			
		<?php if($chkMobile) { ?>	
		  <div class="col-sm-12">
		<?php } if(!$chkMobile) { ?>	
		  <div class="col-sm-4 text-end">
		<?php  } ?>	
			<div class="d-flex  mb-1 justify-content-end"> 	
				<button class="btn btn-secondary btn-sm" id="closeBtn" >  <i class="bi bi-x-lg"></i> 창닫기 </button>&nbsp;									
			</div> 					 
	 </div> 
	 </div> 
	   	
  <div class="row mt-3 mb-3">
  <?php if($chkMobile===false)  
       echo '<div class="col-sm-6 " >';
	  else
		  echo '<div class="col-lg-12" >';
	  ?>
	 <div class="card" >
		<div class="card-body mt-2 mb-2" >
	 
      <table class="table table-bordered">        
        <tr>
          <td class="text-center fw-bold " style="width:22%;">
            <label>구분</label>
          </td>
          <td>
			<?php	 		  
			 $aryreg=array();
			 $aryitem=array();
			 if($which=='') $which='2';
			 switch ($which) {
				case   "1"             : $aryreg[0] = "checked" ; break;
				case   "2"             :$aryreg[1] =  "checked" ; break;
				default: break;
			}		 
				// 검색 선택 쟘 or 천장
			 if($search_opt=='') $search_opt='1';
			 switch ($search_opt) {
				case   "1"             : $aryitem[0] = "checked" ; break;
				case   "2"             :$aryitem[1] =  "checked" ; break;
				default: break;
			}		
			?>		  					  
			<span class="text-primary">  입고 </span> &nbsp;  <input  type="radio" <?=$aryreg[0]?> name=which value="1"> </span>  
				&nbsp; &nbsp;  <span class="text-danger">  출고         <input  type="radio" <?=$aryreg[1]?>  name=which value="2">  </span>  
          </td>
        </tr>
        <tr>
          <td class="text-center fw-bold " >
            <label for="indate">접수일</label>
          </td>
          <td>
		     <input    type="date" id="indate" name="indate" value="<?=$indate?>" >
			 
          </td>
        </tr>        
        <tr>
		<td class="text-center fw-bold " >
            <label for="outdate">입출고일</label>
          </td>
          <td>
             <input type="date" id="outdate" name="outdate" value="<?=$outdate?>"  > &nbsp;&nbsp;			
          </td>
        </tr> 
        <tr>
          <td class="text-center fw-bold " >
            <label for="outworkplace">현장명</label>
          </td>
          <td>             
				쟘(jamb) &nbsp; <input  type="radio" <?=$aryitem[0]?> name=search_opt value="1"> &nbsp; &nbsp; 
			    천장 &nbsp;      <input  type="radio"  <?=$aryitem[1]?>  name=search_opt value="2"> &nbsp; &nbsp; 
			 <input type="text" id="outworkplace" name="outworkplace" onkeydown="JavaScript:Enter_Check();" value="<?=$outworkplace?>" size="70" > 	 &nbsp; 			 			 
          </td>
        </tr>
        <tr>
          <td class="text-center fw-bold " >
            <label for="model">모델</label>
          </td>
          <td>
            <input type="text" name="model" value="<?=$model?>" size="20" placeholder="모델명" />	 &nbsp;
          </td>
        </tr>
        <tr>
          <td colspan="2" class="text-center fw-bold  text-danger">
             [주의] 미래기업 구매 자재는 '사급자재'가 아님. 업체 제공 자재만 '사급'.
          </td>
        </tr>
        <tr>
          <td class="text-center fw-bold " >
            <label for="company">사급업체</label>
          </td>
          <td>				
			<input name="company" id="company" value="<?=$company?>">			
          </td>
        </tr>
        <tr>
          <td class="text-center fw-bold " >
            <label for="supplier">공급(제조사)</label>
          </td>
          <td>				
			 <input name="supplier" id="supplier" value="<?=$supplier?>">
          </td>
        </tr>				
        <tr>
          <td class="text-center fw-bold " >
            <label for="comment">샤링여부</label>
          </td>
          <td>
             <input name="method" id="method" value="<?=$method?>">
          </td>
        </tr>					
        <tr>
          <td class="text-center fw-bold " >
            <label for="comment">비고</label>
          </td>
          <td>
            <textarea class="form-control" rows="4" id="comment" name="comment" placeholder="기타사항 입력"><?=$comment?></textarea>
          </td>
        </tr>
		
      </table>
	
	</div>	
	  
	</div>				 
    </div>
  
  <?php if($chkMobile===false)  
       echo '<div class="col-sm-6" >';
	  else
		  echo '<div class="col-lg-12" >';
	  ?>
	  <div class="card" >
		<div class="card-body mt-2 mb-2" >
      <table class="table table-bordered">
        <tr>
          <td class="text-center fw-bold " style="width:200px;">
            <label for="item">종류</label>
          </td>
          <td>
			<input name="item" id="item"  value="<?=$item?>" class="form-control">		
          </td>
        </tr>
        <tr>
          <td class="text-center fw-bold ">
            <label for="spec">규격</label>
          </td>
          <td>
           <input name="spec" id="spec"   value="<?=$spec?>" class="form-control">	
          </td>
        </tr>       
        <tr>
           <td class="text-center fw-bold ">
            <label for="steelnum">수량</label>
          </td>
          <td>
            <input type="text" id="steelnum" name="steelnum" value="<?=$steelnum?>" size="3" placeholder="수량" autocomplete="off">			
          </td>
        </tr>		
        <tr>         
			<td colspan="2">                  
				<?php for ($i = 1; $i <= 5; $i++): 
					if((int)${"used_width_$i"} > 0 ) { ?>
						<div class="d-flex mb-1 mt-1 justify-content-center align-items-center">
							<span class="text-primary me-2">
								잔재<?php echo $i; ?>
							</span>                
							세로(폭) x 가로(길이) :&nbsp;
							<input type="number" id="used_width_<?php echo $i; ?>" name="used_width_<?php echo $i; ?>" value="<?php echo ${"used_width_$i"} ?>" min="0" max="9999"  class="form-control me-1" style="width:50px;" >&nbsp; x &nbsp; 
							<input type="number" id="used_length_<?php echo $i; ?>" name="used_length_<?php echo $i; ?>" value="<?php echo ${"used_length_$i"} ?>" min="0" max="9999"  class="form-control me-1" style="width:50px;" >
							&nbsp;&nbsp; 수량&nbsp;&nbsp; 
							<input type="number" id="used_num_<?php echo $i; ?>" name="used_num_<?php echo $i; ?>" value="<?php echo ${"used_num_$i"} ?>" min="0" max="99"  class="form-control me-1" style="width:50px;" >                                         
						</div>
					<?php } endfor; ?>        
			</td>
					  
		  
        </tr>
        <tr>
          <td class=" align-middle text-center fw-bold ">
			 <span class="text-danger" > 불량 구분   </span> 
		 </td>					 
		 <td>		
				<div class="d-flex mb-2 mt-3 justify-content-center align-items-center">   	  			
					   <?php			 
							 $arybad=array();
							 if($bad_choice=="")
								   $bad_choice="해당없음";
							 switch ($bad_choice) {
								case   "해당없음"       : $arybad[0] =  "checked" ; break;
								case   "설계"           : $arybad[1] =  "checked" ; break;
								case   "레이져"     : $arybad[2] =  "checked" ; break;
								case   "V컷"           : $arybad[3] =  "checked" ; break;
								case   "절곡"           : $arybad[4] =  "checked" ; break;
								case   "운반중"           : $arybad[5] =  "checked" ; break;
								case   "소장"           : $arybad[6] =  "checked" ; break;
								case   "업체"           : $arybad[7] =  "checked" ; break;
								case   "기타"           : $arybad[8] =  "checked" ; break;
								default: break;
							}		 
						?>			
								
					   <input type="radio" <?=$arybad[0]?> name="bad_choice" value="해당없음"    >	해당없음  &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[1]?> name="bad_choice" value="설계"    >	설계  &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[2]?> name="bad_choice" value="레이져"   > 레이져 &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[3]?> name="bad_choice" value="V컷"    >  V컷 &nbsp;&nbsp;
					</div>  
					<div class="d-flex mb-3 mt-1 justify-content-center align-items-center">  
					   <input type="radio" <?=$arybad[4]?> name="bad_choice" value="절곡"    > 절곡 &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[5]?> name="bad_choice" value="운반중"   > 운반중 &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[6]?> name="bad_choice" value="소장"    > 소장 &nbsp;&nbsp;				  
					   <input type="radio" <?=$arybad[7]?> name="bad_choice" value="업체"    > 업체 &nbsp;&nbsp;
					   <input type="radio" <?=$arybad[8]?> name="bad_choice" value="기타"    > 기타 &nbsp;&nbsp;
								
					</div>    				  
          </td>
        </tr>	
		
        <tr>
          <td class="text-center fw-bold " colspan="2">			 
			 등록 : <?=$first_writer?>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
					  <?php
						      $update_log_extract = substr($update_log, 0, 31);  // 이미래....
					  ?>
					<br> 최종수정 : <?=$update_log_extract?> &nbsp;&nbsp;&nbsp;
					
							<button type="button" class="btn btn-outline-dark btn-sm" id="showlogBtn"   >								
								Log 기록
							</button>	
		  
          </td>
        </tr>	

		
		
      </table>
	  
	 </div>
    
	
	</div>
    </div>
  </div>	
  </div>	
  </div>	
  </div>	
  </div>	
  </div>	
  </div>	
  </div>					
	</form>	

<script>


$(document).ready(function(){
	
	$('#closeBtn').click(function() {
		window.close(); // 현재 창 닫기
	});	
	
    // Select input, textarea, and radio elements
    $("input, textarea, input[type='radio']").each(function() {
        // Set the readonly attribute
        $(this).prop('readonly', true);

        // If it's a radio button, disable it instead (readonly doesn't work for radio buttons)
        // if ($(this).attr('type') == 'radio') {
            // $(this).prop('disabled', true);
        // }

        // Change the background color to lightgray
        $(this).css('background-color', '#f5f5f5');
    });

			// Log 파일보기
		$("#showlogBtn").click( function() {     	
		    var num = '<?php echo $num; ?>' 
			// table 이름을 넣어야 함
		    var workitem =  'steel' ;
			// 버튼 비활성화
			var btn = $(this);						
			    popupCenter("../Showlog.php?num=" + num + "&workitem=" + workitem , '로그기록 보기', 500, 500);									 
			btn.prop('disabled', false);					 					 
		});	
});


function captureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
}

function recaptureReturnKey(e) {
    if (e.keyCode==13)
        exe_search();
}
function Enter_Check(){
        // 엔터키의 코드는 13입니다.
    if(event.keyCode == 13){
      exe_search();  // 실행할 이벤트
    }
}
function Enter_CheckTel(){
        // 엔터키의 코드는 13입니다.
    if(event.keyCode == 13){
      exe_searchTel();  // 실행할 이벤트
    }
}

function exe_search()
{
      var postData = changeUri(document.getElementById("outworkplace").value);
      var sendData = $(":input:radio[name=root]:checked").val();

      $("#displaysearch").show();
	 if(sendData=='주일')
         $("#displaysearch").load("./search.php?mode=search&search=" + postData);
	 if(sendData=='경동') 
         $("#displaysearch").load("./searchkd.php?mode=search&search=" + postData);	  
}
function exe_searchTel()
{
	  var postData =  changeUri(document.getElementById("receiver").value);
      $("#displaysearchworker").show();
      $("#displaysearchworker").load("./workerlist.php?mode=search&search=" + postData);
}


function del(href) {    
	var user_name = '<?php echo $user_name; ?>';
	var first_writer = '<?php echo $first_writer; ?>';
	var admin = '<?php echo $admin; ?>';

if (!first_writer.includes(user_name) && admin !== '1') 
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
				$.ajax({
					url: "delete.php",
					type: "post",		
					data: $("#board_form").serialize(),
					dataType:"json",
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
								window.opener.restorePageNumber(); // 부모 창에서 페이지 번호 복원
								window.opener.location.reload(); // 부모 창 새로고침
								window.close();
							}							
							
						}, 1000);	
					},
					error : function( jqxhr , status , error ){
						console.log( jqxhr , status , error );
					} 			      		
				   });	
                    

            }
        });
    }
}
 
</script>


	</body>
 </html>
