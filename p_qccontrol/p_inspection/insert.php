 <?php session_start(); ?>
 
 
 <meta charset="utf-8">
 <?php
  if(!isset($_SESSION["userid"])) {
 ?>
  <script>
        alert('로그인 후 이용해 주세요.');
        history.back();
  </script>
 <?php
  }
  
// 임시저장된 첨부파일을 확정하기 위해 검사하기  
isset($_REQUEST["timekey"])  ? $timekey=$_REQUEST["timekey"] :  $timekey='';   // 신규데이터에 생성할때 임시저장키  
  
  if(isset($_REQUEST["page"]))
    $page=$_REQUEST["page"];
  else 
    $page=1;   // 1로 설정해야 함
 if(isset($_REQUEST["mode"]))  //modify_form에서 호출할 경우
    $mode=$_REQUEST["mode"];
 else 
    $mode="";
 
 if(isset($_REQUEST["num"]))
    $num=$_REQUEST["num"];
 else 
    $num="";

 if(isset($_REQUEST["DB"]))
    $DB=$_REQUEST["DB"];
 else 
    $DB="";
       
 if(isset($_REQUEST["is_html"]))  //checkbox는 체크해야 변수명 전달됨.
    $is_html=$_REQUEST["is_html"];
  else
    $is_html="";  

 if(isset($_REQUEST["noticecheck"])) 
    $noticecheck=$_REQUEST["noticecheck"];
  else
    $noticecheck="";
   
  $subject=$_REQUEST["subject"];
  $content=$_REQUEST["content"];
   
        
require_once("../lib/mydb.php");
 $pdo = db_connect();
    
 if ($mode=="modify"){
     $num_checked = count($_REQUEST['del_file']);
     $position = $_REQUEST['del_file'];
 
     for($i=0; $i<$num_checked; $i++)      // delete checked item
     {
	 $index = $position[$i];
	 $del_ok[$index] = "y";
     }
      
     try{
        $sql = "select * from mirae8440." . $DB . " where num=?";  // get target record
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1,$num,PDO::PARAM_STR); 
        $stmh->execute(); 
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
     } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
     } 
              
     try{
        $pdo->beginTransaction();   
        $sql = "update mirae8440." . $DB . " set subject=?, content=?, is_html=? where num=?";
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $subject, PDO::PARAM_STR);  
        $stmh->bindValue(2, $content, PDO::PARAM_STR);  
        $stmh->bindValue(3, $is_html, PDO::PARAM_STR);             
        $stmh->bindValue(4, $num, PDO::PARAM_STR);   
        $stmh->execute();
        $pdo->commit(); 
        } catch (PDOException $Exception) {
           $pdo->rollBack();
           print "오류: ".$Exception->getMessage();
       }                         
       
 } else	{
    if ($is_html =="y"){
	         $content = htmlspecialchars($content);
       }
   try{
     $pdo->beginTransaction();
     $sql = "insert into mirae8440." . $DB . " (id, name, nick, subject, content, regist_day, hit, is_html) ";     
     $sql .= "values(?, ?, ?, ?, ?, now(), 0, ?)";
     $stmh = $pdo->prepare($sql); 
     $stmh->bindValue(1, $_SESSION["userid"], PDO::PARAM_STR);  
     $stmh->bindValue(2, $_SESSION["name"], PDO::PARAM_STR);  
     $stmh->bindValue(3, $_SESSION["nick"], PDO::PARAM_STR);   
     $stmh->bindValue(4, $subject, PDO::PARAM_STR);  
     $stmh->bindValue(5, $content, PDO::PARAM_STR);  
     $stmh->bindValue(6, $is_html, PDO::PARAM_STR);   // 8-2 = 6ea 
     
     $stmh->execute();
     $pdo->commit(); 
     } catch (PDOException $Exception) {
          $pdo->rollBack();
       print "오류: ".$Exception->getMessage();
     }   
   }
if ($mode=="modify"){
	header("Location:http://8440.co.kr/" . $DB . "/view.php?num=$num&page=$page&DB=$DB");
}
else
{
// 신규데이터인경우 num을 추출한 후 view로 보여주기
 $sql="select * from mirae8440." . $DB . " order by num asc"; 					

  try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $rowNum = $stmh->rowCount();  
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {	   
 			  $num=$row["num"];			   			 
	 } 	 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}    

// 신규데이터인 경우 첨부파일/첨부이미지 추가한 것이 있으면 parentid 변경해줌
// 신규데이터인경우 num을 추출한 후 view로 보여주기
 
 $id = $num;
 
  try{
        $pdo->beginTransaction();   
        $sql = "update mirae8440.fileuploads set parentid=? where parentid=?";
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $id, PDO::PARAM_STR);  
        $stmh->bindValue(2, $timekey, PDO::PARAM_STR);   
        $stmh->execute();
        $pdo->commit(); 
        } catch (PDOException $Exception) {
           $pdo->rollBack();
           print "오류: ".$Exception->getMessage();
       }                         
      	
   header("Location:http://8440.co.kr/" . $DB . "/view.php?num=$num&page=$page&DB=$DB");		
}
 ?>

