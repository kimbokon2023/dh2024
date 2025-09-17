<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");

isset($_REQUEST["timekey"])  ? $timekey = $_REQUEST["timekey"] : $timekey = ''; 

$mode = $_REQUEST["mode"] ?? '';  
$tablename = $_REQUEST["tablename"] ?? '';

include '_request.php'; // 여기서 $subject 등 주요 변수도 가져오는 것으로 가정됨

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 담당자 상태 관리 함수
function updateChargedPersonStatus($chargedPerson, $oldStatusJson) {
    $persons = array_map('trim', explode(',', $chargedPerson));
    $oldStatus = $oldStatusJson ? json_decode($oldStatusJson, true) : [];
    $newStatus = [];
    foreach ($persons as $person) { 
        if (!$person) continue;
        if (isset($oldStatus[$person])) {
            $newStatus[$person] = $oldStatus[$person];
        } else {
            $newStatus[$person] = ['checked' => '', 'done' => ''];
        }
    }
    return json_encode($newStatus, JSON_UNESCAPED_UNICODE);
}

if ($mode == "modify") {
    // 기존 row에서 chargedPersonStatus 불러오기
    $sql = "SELECT chargedPersonStatus FROM {$DB}.{$tablename} WHERE num=?";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $num, PDO::PARAM_INT);
    $stmh->execute();
    $row = $stmh->fetch(PDO::FETCH_ASSOC);
    $oldStatusJson = $row['chargedPersonStatus'] ?? '';
    $chargedPersonStatus = updateChargedPersonStatus($chargedPerson, $oldStatusJson);
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE {$DB}.{$tablename} 
                SET subject=?, content=?, is_html=?, noticecheck=?, searchtext=?,
                    first_writer=?, chargedPerson=?, dueDate=?, doneDate=?, chargedPersonStatus=?
                WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $subject, PDO::PARAM_STR);
        $stmh->bindValue(2, $content, PDO::PARAM_LOB);
        $stmh->bindValue(3, $is_html, PDO::PARAM_STR);
        $stmh->bindValue(4, $noticecheck, PDO::PARAM_STR);
        $stmh->bindValue(5, $searchtext, PDO::PARAM_STR);
        $stmh->bindValue(6, $first_writer, PDO::PARAM_STR);
        $stmh->bindValue(7, $chargedPerson, PDO::PARAM_STR);
        $stmh->bindValue(8, $dueDate, PDO::PARAM_STR);
        $stmh->bindValue(9, $doneDate, PDO::PARAM_STR);
        $stmh->bindValue(10, $chargedPersonStatus, PDO::PARAM_STR);
        $stmh->bindValue(11, $num, PDO::PARAM_INT);
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }

} else {
    if ($is_html == "y") {
        $content = htmlspecialchars($content);
    }
    $chargedPersonStatus = updateChargedPersonStatus($chargedPerson, '');
    try {
        $pdo->beginTransaction();
        $sql = "INSERT INTO {$DB}.{$tablename} 
                (id, name, subject, content, regist_day, hit, is_html, noticecheck, searchtext, 
                 first_writer, chargedPerson, dueDate, doneDate, chargedPersonStatus)
                VALUES (?, ?, ?, ?, CURDATE(), 0, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $_SESSION["userid"], PDO::PARAM_STR);
        $stmh->bindValue(2, $_SESSION["name"], PDO::PARAM_STR);
        $stmh->bindValue(3, $subject, PDO::PARAM_STR);
        $stmh->bindValue(4, $content, PDO::PARAM_LOB);
        $stmh->bindValue(5, $is_html, PDO::PARAM_STR);
        $stmh->bindValue(6, $noticecheck, PDO::PARAM_STR);
        $stmh->bindValue(7, $searchtext, PDO::PARAM_STR);
        $stmh->bindValue(8, $first_writer, PDO::PARAM_STR);
        $stmh->bindValue(9, $chargedPerson, PDO::PARAM_STR);
        $stmh->bindValue(10, $dueDate, PDO::PARAM_STR);
        $stmh->bindValue(11, $doneDate, PDO::PARAM_STR);
        $stmh->bindValue(12, $chargedPersonStatus, PDO::PARAM_STR);
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode != "modify") {
    $sql = "SELECT * FROM {$DB}.{$tablename} ORDER BY num DESC LIMIT 1";

    try {
        $stmh = $pdo->query($sql);
        $rowNum = $stmh->rowCount();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        $num = $row["num"];
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }

    $id = $num;

    try {
        $pdo->beginTransaction();   
        $sql = "UPDATE {$DB}.fileuploads SET parentid=? WHERE parentid=?";
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $id, PDO::PARAM_STR);  
        $stmh->bindValue(2, $timekey, PDO::PARAM_STR);   
        $stmh->execute();
        $pdo->commit(); 
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }                         
}

$data = [   
    'num' => $num, 
    'tablename' => $tablename
]; 
 
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
