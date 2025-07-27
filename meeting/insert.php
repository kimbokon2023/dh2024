<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문 받는측에서 필요한 정보임 ajax로 보내는 쪽에서 type : json

// 임시저장된 첨부파일을 확정하기 위해 검사하기  
isset($_REQUEST["timekey"])  ? $timekey=$_REQUEST["timekey"] :  $timekey='';   // 신규데이터에 생성할때 임시저장키  

 if(isset($_REQUEST["mode"]))  //modify_form에서 호출할 경우
    $mode=$_REQUEST["mode"];
 else 
    $mode="";

 if(isset($_REQUEST["tablename"]))
    $tablename=$_REQUEST["tablename"];
 else 
    $tablename="";

include '_request.php';
        
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
 $pdo = db_connect();
 
 $error_message = "";
 $success = true;
 
  if ($mode == "modify") {

    try {
        $pdo->beginTransaction();
        $sql = "update " . $DB . "." . $tablename . " set registration_date=?, subject=?, content=?, is_html=?, suggestioncheck=?, searchtext=? where num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $registration_date, PDO::PARAM_STR);
        $stmh->bindValue(2, $subject, PDO::PARAM_STR);
        $stmh->bindValue(3, $content, PDO::PARAM_LOB);
        $stmh->bindValue(4, $is_html, PDO::PARAM_STR);
        $stmh->bindValue(5, $suggestioncheck, PDO::PARAM_STR);
        $stmh->bindValue(6, $searchtext, PDO::PARAM_STR);
        $stmh->bindValue(7, $num, PDO::PARAM_INT);
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        $error_message = "오류: " . $Exception->getMessage();
        $success = false;
    }

} else {
    if ($is_html == "y") {
        $content = htmlspecialchars($content);
    }

    try {
        $pdo->beginTransaction();
        $sql = "insert into " . $DB . "." . $tablename . " (registration_date, id, name, subject, content, regist_day, hit, is_html, suggestioncheck, searchtext) ";
        $sql .= "values(?, ?, ?, ?, ?, now(), 0, ?, ?, ?)";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $registration_date, PDO::PARAM_STR);
        $stmh->bindValue(2, $_SESSION["userid"], PDO::PARAM_STR);
        $stmh->bindValue(3, $_SESSION["name"], PDO::PARAM_STR);
        $stmh->bindValue(4, $subject, PDO::PARAM_STR);
        $stmh->bindValue(5, $content, PDO::PARAM_LOB);
        $stmh->bindValue(6, $is_html, PDO::PARAM_STR);
        $stmh->bindValue(7, $suggestioncheck, PDO::PARAM_STR);
        $stmh->bindValue(8, $searchtext, PDO::PARAM_STR);
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        $error_message = "오류: " . $Exception->getMessage();
        $success = false;
    }
}

if ($success && $mode != "modify") {
    // 신규데이터인경우 num을 추출한 후 view로 보여주기
    $sql = "select * from " . $DB . "." . $tablename . " order by num desc limit 1";

    try {
        $stmh = $pdo->query($sql);
        $rowNum = $stmh->rowCount();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        $num = $row["num"];
    } catch (PDOException $Exception) {
        $error_message = "오류: " . $Exception->getMessage();
        $success = false;
    }

    if ($success) {
        // 신규데이터인 경우 첨부파일/첨부이미지 추가한 것이 있으면 parentid 변경해줌
        // 신규데이터인경우 num을 추출한 후 view로 보여주기
         
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
                   $error_message = "오류: " . $Exception->getMessage();
                   $success = false;
               }                         
    }
}

if ($success) {
    $data = [   
        'success' => true,
        'num' => $num, 
        'tablename' => $tablename
    ]; 
} else {
    $data = [
        'success' => false,
        'error' => $error_message
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);

?> 