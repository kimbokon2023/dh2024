<?php
// 소장들 보는 모바일 화면 구성
// 소장들 선택할때 관리자도 선택가능하게 제작함.
 session_start();

   $level= $_SESSION["level"];
   $id_name= $_SESSION["name"];   
   $user_name= $_SESSION["name"];   
   
 if(!isset($_SESSION["level"]) || $level>8) {
          /*   alert("관리자 승인이 필요합니다."); */
		 sleep(2);
         header ("Location:http://8440.co.kr/login/logout.php");
         exit;
   }  

$workername = $_REQUEST["workername"];

if($workername !== '' and  $workername !== null )
{
	$id_name=$workername;	 
	$user_name=$workername;	
}
 else
 {
   $level= $_SESSION["level"];
   $id_name= $_SESSION["name"];   
   $user_name= $_SESSION["name"];  
 }

 ?>


<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>
  
 <title> 미래기업 쟘공사 </title>
 
 <style>
 
 th {
	 
	 font-size:23px;
 }
 
 </style>

 </head>  

<body>


 <?php
 
if(isset($_REQUEST["search"]))   //목록표에 제목,이름 등 나오는 부분
	 $search=$_REQUEST["search"];
	  
 if(isset($_REQUEST["check"])) 
	 $check=$_REQUEST["check"]; // request 사용 페이지 이동버튼 누를시`
   else
     $check=$_POST["check"]; //  POST사용 

if($check==null) $check=0;	 
    
$sum=array();

// 금일 출고리스트 배열 추출

 require_once("../lib/mydb.php");
 $pdo = db_connect();	

$sql="select * from mirae8440.work where worker='$id_name' and (deadline !='' and deadline IS Not NULL)  and (doneday ='' OR doneday IS NULL) and (madeconfirm ='' OR madeconfirm IS NULL)   ";

$numArr = array();
$workplacenameArr = array();

 try{  

	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
	     while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			   
			  $num=$row["num"];			  			  
			  $workplacename=$row["workplacename"];
			  array_push($numArr, $num);
			  array_push($workplacenameArr, $workplacename);
				
			 } 
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  } 				
				

	 
  if(isset($_REQUEST["mode"]))
     $mode=$_REQUEST["mode"];
  else 
     $mode="";       
   
 switch($check) {  
  case '1' :  // 출고예정 체크된 경우
				$attached=" and (date(endworkday)>=date(now()))  ";
				$orderby=" order by endworkday asc ";						
				break;		
   case '2' :  // 시공완료 체크된 경우 (사진여부)
				$attached=" and (filename2<>'')  ";				
                $orderby=" order by workday desc  ";							
				break;
   case '3' :  // 미시공 체크된 경우
 	         	$attached=" and ((doneday='') or (doneday='0000-00-00'))  ";
			    $orderby=" order by orderday desc  ";											
				break;
   case '4' :  // 미실측 체크된 경우
				$attached=" and ((measureday='') or (measureday='0000-00-00')) ";
			    $orderby=" order by assigndate asc  ";											
				break;				
	default:	
	  	    $orderby=" order by assigndate desc  ";															
		}		 
	 
$a= " " . $orderby . " ";  
$b=  " " . $orderby;

		  if($search==""){
			    if($check=='1')
				{
					 $sql="select * from mirae8440.work where   ( worker='$id_name') " . $attached . $orderby; 					
				}	
		    elseif($check=='2')
				{
				$sql="select * from mirae8440.work where   ( worker='$id_name') " . $attached . $orderby ; 					
				}
		    elseif($check=='3')
				{
					 $sql="select * from mirae8440.work where worker='$id_name' " . $attached . $orderby ; 					
				}				
		    elseif($check=='4')
				{
				$sql="select * from mirae8440.work where   ( worker='$id_name') " . $attached . $orderby ; 				 
				}				
				else
					{
					 $sql="select * from mirae8440.work where worker='$id_name' " . $a; 					
	                 $sqlcon = "select * from mirae8440.work where worker='$id_name' "  . $b;   // 전체 레코드수를 파악하기 위함.					 
				}
		  }
			else						 
		  			 
        { // 필드별 검색하기
					  $sql ="select * from mirae8440.work where ((workplacename like '%$search%' ) or (firstordman like '%$search%' )  or (secondordman like '%$search%' )  or (chargedman like '%$search%' ) ";
					  $sql .="or (delicompany like '%$search%' ) or (hpi like '%$search%' ) or (firstord like '%$search%' ) or (secondord like '%$search%' ) or (worker like '%$search%' ) or (memo like '%$search%' ) ) and (worker='$id_name') " . $a;
					  
                      $sqlcon ="select * from mirae8440.work where ((workplacename like '%$search%' )  or (firstordman like '%$search%' )  or (secondordman like '%$search%' )  or (chargedman like '%$search%' ) ";
					  $sqlcon .="or (delicompany like '%$search%' ) or (hpi like '%$search%' ) or (firstord like '%$search%' ) or (secondord like '%$search%' ) or (worker like '%$search%' ) or (memo like '%$search%' ))  and (worker='$id_name') " . $b;
				  } 

	 try{  

	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $temp1=$stmh->rowCount();
	      
	  $total_row = $temp1;     // 전체 글수	   
			 
	  $message = '';			 
	  
			?>
		 
<body>
<form id="board_form" name="board_form" method="post" action="index.php?mode=search&search=<?=$search?>&check=<?=$check?>&workername=<?=$workername?>">  
 <div  class="container-fluid">
	 <br>
	 <br>
		<div id="top-menu">
		<?php
			if(!isset($_SESSION["userid"]))
			{
		?>
				  <a href="../login/login_form.php">로그인</a> | <a href="../member/insertForm.php">회원가입</a>
		<?php
			}
			else
			 {
		?>
		<div class="row">
		   <div class="col-6"> 
			 <h3 class="font-center text-left"> 
					<?=$user_name?> | 
						<a href="../login/logout.php">로그아웃</a> | <a href="../member/updateForm.php?id=<?=$_SESSION["userid"]?>">정보수정</a>
						
				<?php
					 }
				?>
			</h3>
		</div> 
</div> 
	
	<div class="d-flex mt-4">
	   <H1> &nbsp;&nbsp;  쟘(Jamb) 현장 </H1> &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;  
	   <button type="button"  class="btn btn-outline-primary btn-lg" onclick="window.open('./request.php','VB 자재 협조 요청사항','top=10, left=10, height=600, width=800, menubar=no, toolbar=no,');">  (바이브)VB 자재사용시 요청사항 </button>  &nbsp;  
	   <button type="button"  id="showhpiBtn" class="btn btn-outline-success btn-lg" >  HPI(타공) </button>
   </div>
	 
	<?php if($message!=='') { ?>
		<div class="d-flex">
		  <div class="p-2 flex-fill ">			  
		  <h2 class="display-4  bg-dark text-light ">공지</h2></div>
		  <div class="p-2 flex-fill ">			  
		  <?php print '<h2 class="display-4  bg-dark text-light">' . $message . ' </h2></div>' ; ?>
		  
	 </div> 
	<? } ?>
			
			<br>
<?php
   if(count($numArr)>0)			
   {
	   ?>
   
	<div class="d-flex" id="dp_outputlist">
	<div class="col-sm-3">
	  <div class="card p-1 mt-2 mb-5">
		<div class="card-body bg-primary text-white">	
			<div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center ">   
					<h3> 생산완료확인 ☞ </H3> 
			</div>
			<div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center ">   
					<h4> '확인' 버튼 후 리스트에서 사라짐  </h4> 
			</div>
		
		</div>
	  </div>
	  </div>
	  
	<div class="col-sm-9 mb-3">
		  <div class="card mt-1 mb-1">		  
				<div class="card-body ">	
				<?php
				   for($i=0;$i<count($numArr);$i++)
				   {  
					  ?>
				
							 <div class="d-flex justify-content-start align-items-center mt-1">   						
								
								<span class="badge bg-secondary text-white fs-4 " > <?=$workplacenameArr[$i]?>		 </span> 
								&nbsp;&nbsp;
								<button type="button" class="btn btn-dark " onclick="donecheck('<?=$numArr[$i]?>')"> 확인 </button> 
								
							</div>			
				   <?php } ?>
				  </div>
		  </div>
	  </div>
	  
   </div>
		
		
<?php    } 	   ?>		

			<div class="d-flex justify-content-center mb-3 mt-3">				  
					  <H2> <input type="text" id="search" name="search" value="<?=$search?>" >				  
						<button type="button"  class="btn btn-dark" onclick="document.getElementById('board_form').submit();"> <ion-icon name="search"></ion-icon>   </button>					
						 </h2>
			</div>				
	<div class="d-flex mt-3 mb-3">		
		<button type="button" id="showall" class="btn btn-dark btn-lg " onclick="location.href='index.php?mode=search&search=<?=$search?>&check=0&workername=<?=$workername ?>'"> 전체   </button>  &nbsp;&nbsp;&nbsp;&nbsp;
		<button id="showNomeasure"  type="button" class="btn btn-secondary btn-lg btn-lg " onclick="location.href='index.php?mode=search&search=<?=$search?>&check=4&workername=<?=$workername ?>'"> 미실측  </button> &nbsp;&nbsp;&nbsp;&nbsp;
		<button id="showNowork" type="button" class="btn btn-secondary btn-lg " onclick="location.href='index.php?mode=search&search=<?=$search?>&check=3&workername=<?=$workername ?>'"> 미시공   </button>  &nbsp;&nbsp;&nbsp;&nbsp;
		<button type="button" id="outputplan" class="btn btn-secondary btn-lg " onclick="location.href='index.php?mode=search&search=<?=$search?>&check=1&workername=<?=$workername ?>'"> 생산예정   </button>  &nbsp;&nbsp;&nbsp;&nbsp;
		<button type="button" class="btn btn-secondary btn-lg " onclick="location.href='index.php?mode=search&search=<?=$search?>&check=2&workername=<?=$workername ?>'"> 시공 사진   </button>  &nbsp;&nbsp;&nbsp;&nbsp;
		<button type="button" class="btn btn-secondary btn-lg" onclick="location.href='workfee.php?mode=search&search=<?=$search?>&worker=<?=$user_name?>&workername=<?=$workername ?>'"> 시공내역 </button>
	</div>
		<input type="hidden" id="check" name="check" value="<?=$check?>" size="5" > 						
		<input type="hidden" id="sqltext" name="sqltext" value="<?=$sqltext?>" > 			
		<input type="hidden" id="voc_alert" name="voc_alert" value="<?=$voc_alert?>" size="5" > 	
		<input type="hidden" id="ma_alert" name="ma_alert" value="<?=$ma_alert?>" size="5" > 	
	    <div id="vacancy" style="display:none">  </div>			                
       
	  <?php 
	    if($check==3) // 미시공 클릭
		{
			
		print '		       
		<div class="d-flex">        
			<h1 class="font-center text-center text-danger mt-4 mb-4 "> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 미시공리스트는 (시공완료버튼)을 누르시면 없어집니다. </h1>
        </div> ';			
		}
		
	  ?>
	  
	<div class="table table-responsive">	  
     <table class="table table-bordered table-hover">        
	   <thead class="table-dark">
        <tr>
           <th class="text-center "> No </th>
        
		<?php
		  switch($check) {
			  case '1' :
			   			     print ' <th class="text-center" > 착공 </th>';
							 print ' <th class="text-center" > 생산예정 </th> ';
							break;
			  case '2' :
			   			     print ' <th class="text-center" > 출고 </th> ';
							break;							
			  default : 
			     print ' <th class="text-center" >  접수</th> ';
							break;														
		  }	  			?>							
		<?php
		switch($check) {
			case '0' :
			print ' <th class="text-center" > 외주 </th> ';
			print ' <th class="text-center" > 출고 </th> ';
			break;
			default : 
			print ' <th class="text-center" > 외주 </th> ';
			print ' <th class="text-center" > 검사</th> ';
			break;														
						}	
	  ?>
	  	<th class="text-center" > 실측 </th>
        <th class="text-center" > 도면 </th>
        <th class="text-center" > 시/완</th>
        <th class="text-center" > <ion-icon name="images"></ion-icon> </th>
        <th class="text-center" > 현장명</th>        
	  
    <?php  
	
			$start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
			
	       while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			
			 include "../work/_row.php";
			
			  $imgurl1="../imgwork/" . $filename1;
			  $imgurl2="../imgwork/" . $filename2;			  
			  
			  
			  $sum[0] = $sum[0] + (int)$widejamb;
			  $sum[1] += (int)$normaljamb;
			  $sum[2] += (int)$smalljamb;
			  $sum[3] += (int)$widejamb + (int)$normaljamb + (int)$smalljamb;
			  
			  $dis_text = "막판 : " . $sum[0] . " 세트, 막판(無) : " . $sum[1] . " 세트, 쪽쟘 : "  . $sum[2] . " 세트, 합계 : " . $sum[3] . " 세트" ; 

		      if($orderday!="0000-00-00" and $orderday!="1970-01-01"  and $orderday!="") $orderday = date("Y-m-d", strtotime( $orderday) );
					else $orderday="";
		      if($measureday!="0000-00-00" and $measureday!="1970-01-01" and $measureday!="")   $measureday = date("Y-m-d", strtotime( $measureday) );
					else $measureday="";
		      if($drawday!="0000-00-00" and $drawday!="1970-01-01" and $drawday!="")  $drawday = date("Y-m-d", strtotime( $drawday) );
					else $drawday="";
		      if($deadline!="0000-00-00" and $deadline!="1970-01-01" and $deadline!="")  $deadline = date("Y-m-d", strtotime( $deadline) );
					else $deadline="";
		      if($workday!="0000-00-00" and $workday!="1970-01-01"  and $workday!="")  $workday = date("Y-m-d", strtotime( $workday) );
					else $workday="";					
		      if($endworkday!="0000-00-00" and $endworkday!="1970-01-01" and $endworkday!="")  $endworkday = date("Y-m-d", strtotime( $endworkday) );
					else $endworkday="";	
		      if($demand!="0000-00-00" and $demand!="1970-01-01" and $demand!="")  $demand = date("Y-m-d", strtotime( $demand) );
					else $demand="";						
		      if($startday!="0000-00-00" and $startday!="1970-01-01" and $startday!="")  $startday = date("Y-m-d", strtotime( $startday) );
					else $startday="";		  			  	  
		      if($testday!="0000-00-00" and $testday!="1970-01-01" and $testday!="")  $testday = date("Y-m-d", strtotime( $testday) );
					else $testday="";		  			  	  
		      if($assigndate!="0000-00-00" and $assigndate!="1970-01-01" and $assigndate!="")  $assigndate = date("Y-m-d", strtotime( $assigndate) );
					else $assigndate="";		  
			  	  				  
			  $state_work=0;
			  if($row["checkbox"]==0) $state_work=1;
			  if(substr($row["workday"],0,2)=="20") $state_work=2;
			  if(substr($row["endworkday"],0,2)=="20") $state_work=3;	
	 
              $measure_done="     ";			  
			  if(substr($row["measureday"],0,2)=="20") $measure_done = "OK";		    			  
              
			  if(substr($testday,0,2)=="20")  $testday = iconv_substr($testday,5,5,"utf-8");
			            else $testday="    ";	              
			  if(substr($row["doneday"],0,2)=="20")  $doneday = iconv_substr($doneday,5,5,"utf-8");
			            else $doneday="    ";			  
              if($filename2!="") $pic_done="등록";
			     else
				    $pic_done='';
				
				
// print_r($testday)				;
				
			 ?>
			 </tr>
		</thead>
		<tbody>
		  <tr onclick="redirectToView('<?=$num?>', '<?=$check?>', '<?=$workername?>')">
            <td class="text-center" >  <?=$start_num?> </td>        
		<?php
		  switch($check) {
			  case '1' :
							print ' <td class="text-center" > ' . iconv_substr($startday,5,5,"utf-8") . '</td> ';
							print ' <td class="text-center" > ' . iconv_substr($endworkday,5,5,"utf-8") . '</td> ';						
							break;
			  case '2' :
			   			    print ' <td class="text-center" > ' .  iconv_substr($workday,5,5,"utf-8")  . ' </td> ';
							break;							
			  default : 
							print ' <td class="text-center" > ' . iconv_substr($assigndate,5,5,"utf-8") . ' </td> ';
							break;														
				}					
			?>		
			
        <?php
		  switch($check) {
			  case '0' :
					print ' <td class="text-center text-danger" > <b>  ' . $outsourcing . ' </b>  </td> ';
					print ' <td class="text-center" > ' . iconv_substr($workday,5,5,"utf-8") . ' </td> ';							
					break;
			  default : 
					print ' <td class="text-center text-danger" > <b>  ' . $outsourcing . ' </b>  </td> ';
					print ' <td class="text-danger text-center" > <b> ' . iconv_substr($testday,0,5,"utf-8") . ' </b> </td> ';
					break;														
				}					
			?>		
		<td class="text-success text-center" > <b> <?=$measure_done?> </b> </td>
        <td class="text-center" > <?=$draw_done?>  </td>
        <td class="text-center" > <?=$doneday?>  </td>
        <td class="text-center text-primary " > <?=$pic_done?>  </td>
        <td class="text-start" > <?=$workplacename?>  </td>
        
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
	         
         </div> <!-- end of  container -->     
   </form>
  </body>  

  
  </html>
  
<!-- Custom Style to increase the font size -->
<style>
    .big-font-toast {
        font-size: 35px !important;  // 원하는 폰트 크기로 설정
    }
</style>  
  
  
 <script language="javascript">
 
 function redirectToView(num, check, workername) {
    var url = "view.php?num=" + num + "&check=" + check  + "&workername=" + workername ;        
    window.location.href = url;
}
 
 
$(document).ready(function(){
	
	$("#showhpiBtn").click(function(){ 
	var popup = window.open("hpi.php", "hpi 정보", "width=700,height=800");
    // Focus the popup window
    if (window.focus) {
        popup.focus();
    }
	});	         
});
 
 function donecheck(num) {
	 
	  $.ajax({
			url: "update_madeconfirm.php?num=" + num,
    	  	type: "post",		
   			data: '',
   			dataType:"json",
			success : function( data ){
					
			Toastify({
				text: "확인처리되었습니다.",
				duration: 3000,  // 2초 동안 표시
				close: true,
				gravity: "top", // `top` or `bottom`
				position: "center", // `left`, `center` or `right`
				backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
				stopOnFocus: true, // Prevents dismissing of toast on hover
				onClick: function(){}, // Callback after click
				className: "big-font-toast"  // 추가: Custom class for bigger font
			}).showToast();

			// 2초 후에 원하는 동작 수행
			setTimeout(function(){
				// 여기에 2초 후에 수행되어야 할 코드를 넣으세요.
				console.log("2초 후에 실행되는 코드입니다.");				
				console.log( data);
				location.reload();	
			}, 3000);
					
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
			} 			      		
		   });   
    
}
 
</script>
