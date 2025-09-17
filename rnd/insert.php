<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문 받는측에서 필요한 정보임 ajax로 보내는 쪽에서 type : json

// 임시저장된 첨부파일을 확정하기 위해 검사하기  
isset($_REQUEST["timekey"])  ? $timekey=$_REQUEST["timekey"] :  $timekey='';   // 신규데이터에 생성할때 임시저장키  
  
$page = $_REQUEST["page"] ?? 1;
$mode = $_REQUEST["mode"] ?? "";
$tablename = $_REQUEST["tablename"] ?? "rnd";
$id = $_REQUEST["id"] ?? "";
$num = $_REQUEST["num"] ?? "";
$is_html = $_REQUEST["is_html"] ?? "";
$noticecheck = $_REQUEST["noticecheck"] ?? "";
   
$subject = $_REQUEST["subject"] ?? '';
$content = $_REQUEST["content"] ?? '';
$content_b64 = $_REQUEST["content_b64"] ?? '';
if ($content_b64 !== '') {
    // base64로 온 경우 우선 디코드하여 content로 사용
    $decoded = base64_decode($content_b64, true);
    if ($decoded !== false) {
        $content = $decoded;
    }
}
$searchtext = $_REQUEST["searchtext"] ?? '';
        
// 필수값 검증: tablename 없거나 형식이 잘못된 경우 중단
if ($tablename === '' || !preg_match('/^[A-Za-z0-9_]+$/', $tablename)) {
    http_response_code(400);
    echo json_encode(["error" => "invalid_tablename"], JSON_UNESCAPED_UNICODE);
    exit;
}
        
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
 $pdo = db_connect();
    	
	
 if ($mode=="modify"){ 
     	          
     try{
        $sql = "select * from ".$DB."." . $tablename . " where num=?";  // get target record
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1,$num,PDO::PARAM_STR); 
        $stmh->execute(); 
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
    } 
               
     try{
        $pdo->beginTransaction();   
        $sql = "update ".$DB."." . $tablename . " set subject=?, content=?, is_html=?, searchtext=? where num=?";
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $subject, PDO::PARAM_STR);  
        $stmh->bindValue(2, $content, PDO::PARAM_STR);  
        $stmh->bindValue(3, $is_html, PDO::PARAM_STR);             
        $stmh->bindValue(4, $searchtext, PDO::PARAM_STR);             
        $stmh->bindValue(5, $num, PDO::PARAM_STR);   
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
     $sql = "insert into ".$DB."." . $tablename . " (id, name, nick, subject, content, regist_day, hit, is_html, searchtext) ";     
     $sql .= "values(?, ?, ?, ?, ?, now(), 0, ?, ?)";
     $stmh = $pdo->prepare($sql); 
     $stmh->bindValue(1, $_SESSION["userid"], PDO::PARAM_STR);  
     $stmh->bindValue(2, $_SESSION["name"], PDO::PARAM_STR);  
     $stmh->bindValue(3, $_SESSION["name"], PDO::PARAM_STR);   
     $stmh->bindValue(4, $subject, PDO::PARAM_STR);  
     $stmh->bindValue(5, $content, PDO::PARAM_STR);  
     $stmh->bindValue(6, $is_html, PDO::PARAM_STR);  
     $stmh->bindValue(7, $searchtext, PDO::PARAM_STR);  
     
    $stmh->execute();
    $num = $pdo->lastInsertId();
    $pdo->commit(); 
     } catch (PDOException $Exception) {
          $pdo->rollBack();
       print "오류: ".$Exception->getMessage();
     }   
   }
   
   
   
if ($mode!=="modify"){
    
    // 신규데이터의 num은 lastInsertId()로 확보됨. 혹시 비어 있으면 보정
    if (empty($num)) {
        try {
            $sql = "select num from ".$DB."." . $tablename . " order by num desc limit 1";
            $stmh = $pdo->query($sql);
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
            if ($row && isset($row["num"])) {
                $num = $row["num"];
            }
        } catch (PDOException $Exception) {
            print "오류: ".$Exception->getMessage();
        }
    }

    // 신규데이터인 경우 첨부파일/첨부이미지 추가한 것이 있으면 parentid 변경해줌
    $id = $num;
    
    try{
        $pdo->beginTransaction();   
        $sql = "update ".$DB.".fileuploads set parentid=? where parentid=?";
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $id, PDO::PARAM_STR);  
        $stmh->bindValue(2, $timekey, PDO::PARAM_STR);   
        $stmh->execute();
        $pdo->commit(); 
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
    }
}

 $data = [   
	 'num' => $num,	 
	 'tablename' => $tablename
 ]; 
 
 echo json_encode($data, JSON_UNESCAPED_UNICODE);


 ?>

