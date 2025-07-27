<?php
      session_start();
 $user_name= $_SESSION["name"];
 $level= $_SESSION["level"];	  
 ?> 
  
 <?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>
  
  
<title> 미래기업 jamb 시공소장 VOC </title> 
 
    <style>
        .table-hover tbody tr:hover {
            cursor: pointer;
        }
    </style> 
 
 </head>

<body>

<? require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<form id="board_form" name="board_form"  method="post" action="list.php?mode=search&search=<?=$search?>&find=<?=$find?>">

<div class="container  justify-content-center">  

<div class="card justify-content-center" > 	   
   <div class="card-body"> 



<?php         
 
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

 if(isset($_REQUEST["page"])) // $_REQUEST["page"]값이 없을 때에는 1로 지정 
    $page=$_REQUEST["page"];  // 페이지 번호
  else
    $page=1;

 if(isset($_REQUEST["choice"])) // $_REQUEST["choice"]값이 없을 때에는 1로 지정 
    $choice=$_REQUEST["choice"];  // 페이지 번호
  else
    $choice=1;	 

  $scale = 50;       // 한 페이지에 보여질 게시글 수
  $page_scale = 10;   // 한 페이지당 표시될 페이지 수
  $first_num = ($page-1) * $scale;  // 리스트에 표시되는 게시글의 첫 순번.
	 
	 
  if(isset($_REQUEST["mode"]))
     $mode=$_REQUEST["mode"];
  else 
     $mode="";

       if(isset($_REQUEST["search"]))   // search 쿼리스트링 값 할당 체크
         $search=$_REQUEST["search"];
       else 
         $search="";
     
       if(isset($_REQUEST["find"]))   //목록표에 제목,이름 등 나오는 부분
         $find=$_REQUEST["find"];
       else
         $find="";
      
   if($mode=="search"){
         if(!$search){      
	          $sql = "select * from mirae8440.voc where content<>'' order by num desc "; 
	          $sqlcon = "select * from mirae8440.voc where content<>'' order by num desc "; 
			  
          }
          $sql="select * from mirae8440.voc where (content<>'') and (( content like '%$search%') or (name like '%$search%') or (subject like '%$search%')) order by num desc  limit $first_num, $scale";
          $sqlcon="select * from mirae8440.voc where (content<>'') and (( content like '%$search%') or (name like '%$search%') or (subject like '%$search%')) order by num desc ";				
       } 
	   else
		   {
				$sql="select * from mirae8440.voc where content<>'' order by num desc limit $first_num, $scale";
				$sqlcon="select * from mirae8440.voc where content<>'' order by num desc";
			}
			
switch($choice) {
			case '2' :
               $sql="select * from mirae8440.voc where content<>''  and is_html='1' order by num desc limit $first_num, $scale";
				$sqlcon="select * from mirae8440.voc where content<>'' and is_html='1' order by num desc";
			break;	
			case '3' :
               $sql="select * from mirae8440.voc where content<>''  and is_html='2' order by num desc limit $first_num, $scale";
				$sqlcon="select * from mirae8440.voc where content<>''  and is_html='2' order by num desc";
			break;	
			
}			
				
  // 전체 레코드수를 파악한다.
	 try{  
	  $allstmh = $pdo->query($sqlcon);         // 검색 조건에 맞는 쿼리 전체 개수
      $temp2=$allstmh->rowCount();  
	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $temp1=$stmh->rowCount();
	      
	  $total_row = $temp2;     // 전체 글수	  		
         					 
     $total_page = ceil($total_row / $scale); // 검색 전체 페이지 블록 수
	 $current_page = ceil($page/$page_scale); //현재 페이지 블록 위치계산		 
			 
?>  	 
	<input type="hidden" id="voc_alert" name="voc_alert" value="<?=$voc_alert?>" size="5" > 	
	<input type="hidden" id="ma_alert" name="ma_alert" value="<?=$ma_alert?>" size="5" > 	
	<input type="hidden" id="order_alert" name="order_alert" value="<?=$order_alert?>" size="5" > 					
	<div id="vacancy" style="display:none">  </div>	
				
  
 <div class="d-flex mt-3 mb-3 justify-content-center">  
    <span class="text-secondary fs-5 " > &nbsp;&nbsp;  잠 시공소장 VOC &nbsp;&nbsp;</span>
  </div>					
				
	  
	  <input type="hidden" id="page" name="page" value="<?=$page?>"  > 
	  
      <div class="d-flex mt-2 mb-2 justify-content-center"> 
			▷ 총 <?= $total_row ?> 개 자료.   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    

		<button type="button" class="btn btn-outline-primary  btn-sm" onclick="location.href='list.php?choice=1'" > 전체  </button> &nbsp;  
		<button type="button" class="btn btn-outline-primary  btn-sm" onclick="location.href='list.php?choice=2'" > 접수중  </button> &nbsp;  
		<button type="button" class="btn btn-outline-primary  btn-sm" onclick="location.href='list.php?choice=3'" > 확인완료  </button> &nbsp;  
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    
          &nbsp;
          <input type="text" id="search" name="search" style="height:30px;" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();"  >
          
		  &nbsp;
		  <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" >  <ion-icon name="search"></ion-icon>  </button> 
      </div> 
 
 <div class=" d-flex  justify-content-center"  >
<table class="table table-hover">
   <thead class="table-primary" >
	    <tr>
			 <th class="text-center" > 번호    </th>
			 <th class="text-center" > 현장명   </th>
			 <th class="text-center" > 협의 내용   </th>
			 <th class="text-center" > 작성자   </th>
			 <th class="text-center" > 등록일 </th>   			 
			 <th class="text-center" > 처리상황 </th>   			 
		 </tr>
       </thead>
	<tbody>  


<?php  
  if ($page==1)  
    $start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
  else 
    $start_num=$total_row-($page-1) * $scale;
			 
 while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
  $item_num=$row["num"];
  $item_id=$row["id"];
  $item_name=$row["name"];
  $is_html=$row["is_html"];
  $item_date=$row["regist_day"];
  $item_date=substr($item_date, 0, 10);
  $item_subject=iconv_substr($row["subject"],0,30,"utf-8");
  $item_content=iconv_substr($row["content"],0,30,"utf-8");
   
require_once("../lib/mydb.php");
  $pdo = db_connect();   
   
  $sql="select * from mirae8440.voc_ripple where parent=$item_num";
  $stmh1 = $pdo->query($sql); 
  $num_ripple=$stmh1->rowCount(); 
  
 ?>
 
 	<tr onclick="redirectToView('<?=$item_num?>', '<?=$page?>')">  
	  <td class="text-center" >  <?= $start_num ?>      </td>
	  <td class="text-center" >   <?= $item_subject ?>    </td>
	  <td>  <?= $item_content ?>   </td>
	  <td class="text-center" >  <?= $item_name ?>      </td>
	  <td class="text-center" >  <?= $item_date ?>      </td>     	  
	  <td class="text-center" > 
		<?php 
		  if($is_html=='1')
				print '<span class="blinking" style="color:red" >' .  '접수 중' . '</span>' ; 
			  else
				print "확인완료";	
		?>
      </td>     	  
	</tr>  
     	
<?php
	$start_num--;
    }
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  }  

   // 페이지 구분 블럭의 첫 페이지 수 계산 ($start_page)
      $start_page = ($current_page - 1) * $page_scale + 1;
   // 페이지 구분 블럭의 마지막 페이지 수 계산 ($end_page)
      $end_page = $start_page + $page_scale - 1;
  
 ?>
  	  </tbody>
	  </table>  
</div>
<div class="row row-cols-auto mt-5 justify-content-center align-items-center mt-2 mb-5"> 
 <?php
	if($page!=1 && $page>$page_scale){
              $prev_page = $page - $page_scale;    
              // 이전 페이지값은 해당 페이지 수에서 리스트에 표시될 페이지수 만큼 감소
              if($prev_page <= 0) 
              $prev_page = 1;  // 만약 감소한 값이 0보다 작거나 같으면 1로 고정
		      print '<button class="btn btn-outline-secondary btn-sm" type="button" id=previousListBtn  onclick="javascript:movetoPage(' . $prev_page . ')"> ◀ </button> &nbsp;' ;              
            }
            for($i=$start_page; $i<=$end_page && $i<= $total_page; $i++) {        // [1][2][3] 페이지 번호 목록 출력
              if($page==$i) // 현재 위치한 페이지는 링크 출력을 하지 않도록 설정.
                print '<span class="text-secondary" >  ' . $i . '  </span>'; 
              else 
                   print '<button class="btn btn-outline-secondary btn-sm" type="button" id=moveListBtn onclick="javascript:movetoPage(' . $i . ')">' . $i . '</button> &nbsp;' ;     			
            }

            if($page<$total_page){
              $next_page = $page + $page_scale;
              if($next_page > $total_page) 
                     $next_page = $total_page;
                // netx_page 값이 전체 페이지수 보다 크면 맨 뒤 페이지로 이동시킴
				  print '<button class="btn btn-outline-secondary btn-sm" type="button" id=nextListBtn onclick="javascript:movetoPage(' . $next_page . ')"> ▶ </button> &nbsp;' ; 
            }
            ?>         
   </div>
		
   
  </div> 
  </div> 
  </div> <!-- end of container -->

  </form> <!-- end of form -->
  
  </body>
  
  
  
  </html>
  
<script>


function redirectToView(num, page) {
    popupCenter("view.php?num=" + num,'현장소장 VOC', 1400, 800) ;
}

function blinker() {
	$('.blinking').fadeOut(700);
	$('.blinking').fadeIn(700);
}
setInterval(blinker, 1200);


function SearchEnter(){
	
    $("#page").val('1');
    if(event.keyCode == 13){
		document.getElementById('board_form').submit(); 
    }
}


$(document).ready(function(){
	

$("#searchBtn").click(function(){ 	
	  // page 1로 초기화 해야함
     $("#page").val('1');
	 document.getElementById('board_form').submit();    
 
 });		
	
movetoPage = function(page){ 	  
	  $("#page").val(page); 
	 $("#board_form").submit();  
	}			
});	
	

	
</script>