 <?php
 session_start();

   $level= $_SESSION["level"];
 if(!isset($_SESSION["level"]) || $level>5) {
          /*   alert("관리자 승인이 필요합니다."); */
		 sleep(2);
         header ("Location:http://8440.co.kr/login/logout.php");
         exit;
   }
   
$DB = "p_qccontrol";
   
 ?>
 <!DOCTYPE HTML>
 <html>
 <head>
 <meta charset="UTF-8">
 <link rel="stylesheet" type="text/css" href="../css/common.css"> 
 <!-- JavaScript -->
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/alertify.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<!-- CSS -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/css/alertify.min.css"/>
<!-- Default theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/css/themes/default.min.css"/>
<!-- Semantic UI theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/css/themes/semantic.min.css"/>
<!-- Bootstrap theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/css/themes/bootstrap.min.css"/>	
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">   <!--날짜 선택 창 UI 필요 -->


<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
 
<!-- 최초화면에서 보여주는 상단메뉴 -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" >
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.1/font/bootstrap-icons.css">
<style>
a { text-decoration:none;
  color:gray;
}
</style>
  
<title> 미래기업 QC 공정표 </title> 
 
 </head>
 
<?php
  
require_once("../lib/mydb.php");
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
     
       if(isset($_REQUEST["find"]))   //목록표에 제목,이름 등 나오는 부분
         $find=$_REQUEST["find"];
       else
         $find="";
	  

  $scale = 10;       // 한 페이지에 보여질 게시글 수
  $page_scale = 10;   // 한 페이지당 표시될 페이지 수
  $first_num = ($page-1) * $scale;  // 리스트에 표시되는 게시글의 첫 순번.	 
	 
      
   if($mode=="search"){
         if(!$search) {
				$sql ="select * from mirae8440." . $DB . " order  by num desc  limit $first_num, $scale"; 
				$sqlcon ="select * from mirae8440." . $DB . " order  by num desc" ;
             }
              $sql="select * from mirae8440." . $DB . " where name like '%$search%' or subject like '%$search%'  or nick like '%$search%'  or regist_day like '%$search%'  order by num desc  limit $first_num, $scale";
              $sqlcon="select * from mirae8440." . $DB . " where name like '%$search%' or subject like '%$search%'  or nick like '%$search%'  or regist_day like '%$search%'  order by num desc";
       } else {
              $sql="select * from mirae8440." . $DB . " order  by num desc limit $first_num, $scale";
              $sqlcon="select * from mirae8440." . $DB . " order  by num desc ";
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
 
	 
<body>

<div class="container-fluid">  
<div class="d-flex mb-1 justify-content-center">    
  <a href="../index.php"><img src="../img/toplogo.jpg" style="width:100%;" ></a>	
</div>

<? include '../myheader.php'; ?>   

</div>  



<form name="board_form" id="board_form"  method="post" action="list.php?mode=search&search=<?=$search?>">

<div class="container justify-content-center">  

  <input type="hidden" id="page" name="page" value="<?=$page?>"  > 
  
  
 <div class="d-flex mt-3 mb-1 justify-content-center">  
    <img src="../img/qc_control.jpg" style="width:80%;">
  </div>	 
 <div class="d-flex mt-3 mb-1 justify-content-center">  
  <h3> QC 공정표 </h3>  
  </div>	 
  
 <div class="d-flex mt-3 mb-1 justify-content-center">  
 
    <div class="input-group p-2 mb-2 justify-content-center">	  
	   <button type="button"   class="btn btn-dark btn-sm" onclick="location.href='write_form.php?DB=<?=$DB?>'" > QC 공정표 등록 </button>		&nbsp;&nbsp;
	   <input type="text" name="search" id="search" value="<?=$search?>" size="30" onkeydown="JavaScript:SearchEnter();" placeholder="검색어 입력"> 
		<button type="button" id="searchBtn" class="btn btn-dark"  > 검색 </button>	
		
		</div>
       </div>
 
<div class="row d-flex  p-2 m-2 mt-1 mb-1 justify-content-center bg-secondary text-white"> 		
	   
			  <div class="col-1" > 번호  </div>
			  <div class="col-5" > QC공정표명  </div>
			  <div class="col-1" > 등록인 </div>

			  <div class="col-2" > 등록일자 </div>     
			  <div class="col-1" > 조회수 </div>     
       
</div>
	
<?php  
  if ($page==1)  
    $start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
  else 
    $start_num=$total_row-($page-1) * $scale;
			 
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
 
   <div class="row d-flex  p-1 m-1 mt-1 mb-1 justify-content-center "> 	
      
			  <div class="col-1" >  <a href="view.php?num=<?=$item_num?>&page=<?=$page?>&DB=<?=$DB?>"> <?= $start_num ?> </a> </div>
			  <div class="col-5" >  <a href="view.php?num=<?=$item_num?>&page=<?=$page?>&DB=<?=$DB?>"> <?= $item_subject ?>  </a>   </div>
			  <div class="col-1" >  <?= $item_nick ?> </div>

			  <div class="col-2" > <?= $item_date ?> </div>     
			  <div class="col-1" > <?= $item_hit ?> </div>    
  </div>   
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
  

<div class="row row-cols-auto mt-5 justify-content-center align-items-center"> 
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
   

</form>   

</body>
</html>

<script>
$(document).ready(function(){
	
$("#searchBtn").click(function(){ 	
	  // page 1로 초기화 해야함
     $("#page").val('1');
	 document.getElementById('board_form').submit();    
 
 });	
		
	
	movetoPage = function(page){ 	  
	  $("#page").val(page); 
      // var echo="<?php echo $partOpt; ?>"; 
      // var searchOpt="<?php echo $searchOpt; ?>"; 
      // var search="<?php echo $search; ?>"; 

     // $("#partOpt").val(partOpt);
     // $("#searchOpt").val(searchOpt);
     // $("#search").val(search);
	 $("#board_form").submit();  
	}			
});	
	
function SearchEnter(){

    if(event.keyCode == 13){
	
    $("#page").val('1');		
	document.getElementById('board_form').submit(); 
    }
}
	
</script>