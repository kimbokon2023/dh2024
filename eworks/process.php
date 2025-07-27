<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
include $_SERVER['DOCUMENT_ROOT'] . '/common.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$pdo = db_connect();

function getPosition($userId, $pdo) {
    $query = "SELECT position FROM {$GLOBALS['DB']}.member WHERE id = ?"; // Assuming 'id' is the field for user ID and 'position' for the job title
    $position = '';

    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $position = $row['position']; // Assuming 'position' is the field that contains the job title
    }

    return $position; // Returns the position as a string
}

function getRippleData($rippleId, $pdo) {
    $query = "SELECT * FROM {$GLOBALS['DB']}.eworks_ripple WHERE num = ?";
    $rippleData = array();

    $stmt = $pdo->prepare($query);
    $stmt->execute([$rippleId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Assuming these are the fields in your e_works_ripple table
        $rippleData = [
            'num' => $row['num'],
            'content' => $row['content'],
            'author_id' => $row['author_id'],
            'author' => $row['author'], 
            'regist_day' => $row['regist_day'],               
            'parent' => $row['parent']                
        ];
    }

    return $rippleData; // Returns an associative array with ripple data
}

$e_num = isset($_REQUEST["e_num"]) ? $_REQUEST["e_num"] : ''; 
$ripple_num = isset($_REQUEST["ripple_num"]) ? $_REQUEST["ripple_num"] : ''; 
$SelectWork = isset($_REQUEST["SelectWork"]) ? $_REQUEST["SelectWork"] : "insert"; 
$e_line = isset($_REQUEST["e_line"]) ? $_REQUEST["e_line"] : ""; 
$e_line_id = isset($_REQUEST["e_line_id"]) ? $_REQUEST["e_line_id"] : ""; 
$e_confirm = isset($_REQUEST["e_confirm"]) ? $_REQUEST["e_confirm"] : ""; 
$eworks_item = isset($_REQUEST["eworks_item"]) ? $_REQUEST["eworks_item"] : ""; 
$author = isset($_REQUEST["author"]) ? $_REQUEST["author"] : ""; 		
$author_id = isset($_REQUEST["author_id"]) ? $_REQUEST["author_id"] : ""; 	
$recent_num = $e_num;  // 마지막 번호 임시 저장

$arr = explode("!", $e_line_id);		
$e_line_count = count($arr);
// 결재시간 추출해서 조합하기
$approval_time = explode("!", $e_confirm);	
$e_confirm_count = count($approval_time);

include "_request.php";

if($status == null) $status = 'draft';   // 최초 작성으로 설정함

$date = date('Y-m-d H:i:s'); // 현재 시간

if ($SelectWork == "update") {
    $query = "UPDATE {$DB}.eworks SET eworks_item=?, e_title=?, contents=?, registdate=?, status=?, e_line=?, e_line_id=?, e_confirm=?, e_confirm_id=?, r_line=?, r_line_id=?, recordtime=?, author=?, author_id=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$eworks_item, $e_title, $contents, $date, $status, $e_line, $e_line_id, $e_confirm, $e_confirm_id, $r_line, $r_line_id, $recordtime, $author, $author_id, $e_num]);
} 

if ($SelectWork == "insert") {
    $query = "INSERT INTO {$DB}.eworks (eworks_item, e_title, contents, registdate, status, e_line, e_line_id, e_confirm, e_confirm_id, r_line, r_line_id, recordtime, author, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$eworks_item, $e_title, $contents, $date, $status, $e_line, $e_line_id, $e_confirm, $e_confirm_id, $r_line, $r_line_id, $recordtime, $author, $author_id]);
    
    $recent_num = $pdo->lastInsertId();
}

if ($SelectWork == "send") {
    $status = 'send';
    $query = "UPDATE {$DB}.eworks SET status=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$status, $e_num]);
}

if ($SelectWork == "approval") {
    $e_confirm_value = ($e_confirm === '' || $e_confirm === null) ? $user_name . " " . getPosition($user_id, $pdo) . " " . $date : $e_confirm . '!' . $user_name . " " . getPosition($user_id, $pdo) . " " . $date;
    $e_confirm_id_value = ($e_confirm_id === '' || $e_confirm_id === null) ? $user_id : $e_confirm_id . '!' . $user_id;

    $query = "UPDATE {$DB}.eworks SET e_confirm=?, e_confirm_id=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$e_confirm_value, $e_confirm_id_value, $e_num]);

    $arr = explode("!", $e_line_id);
    $approval_time = explode("!", $e_confirm_id_value);
    $e_line_count = count($arr);
    $e_confirm_count = count($approval_time);

    if ($e_line_count > $e_confirm_count) {
        $status = 'ing';
    } else if ($e_line_count == $e_confirm_count) {
        $status = 'end';
        $done = 'done';
        $query = "UPDATE {$DB}.eworks SET done=? WHERE num=?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$done, $e_num]);
    }

    $query = "UPDATE {$DB}.eworks SET status=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$status, $e_num]);
}

if ($SelectWork == "restore") {
    $idArray = explode('!', $e_viewexcept_id);
    if (($key = array_search($user_id, $idArray)) !== false) {
        unset($idArray[$key]);
    }
    $e_viewexcept_id = implode('!', $idArray);

    $query = "UPDATE {$DB}.eworks SET e_viewexcept_id=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$e_viewexcept_id, $e_num]);
}

if ($SelectWork == "except") {
    $e_viewexcept_id = ($e_viewexcept_id === '' || $e_viewexcept_id === null) ? $user_id : $e_viewexcept_id . '!' . $user_id;

    $query = "UPDATE {$DB}.eworks SET e_viewexcept_id=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$e_viewexcept_id, $e_num]);
}

if ($SelectWork == "recall") {
    $status = 'draft';

    $query = "UPDATE {$DB}.eworks SET status=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$status, $e_num]);
}

if ($SelectWork == "reject") {
    $status = 'reject';

    $query = "UPDATE {$DB}.eworks SET status=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$status, $e_num]);

    $e_confirm_value = ($e_confirm === '' || $e_confirm === null) ? $user_name . " " . getPosition($user_id, $pdo) . " " . $date : $e_confirm . '!' . $user_name . " " . getPosition($user_id, $pdo) . " " . $date;
    $e_confirm_id_value = ($e_confirm_id === '' || $e_confirm_id === null) ? $user_id : $e_confirm_id . '!' . $user_id;

    $query = "UPDATE {$DB}.eworks SET e_confirm=?, e_confirm_id=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$e_confirm_value, $e_confirm_id_value, $e_num]);
}

if ($SelectWork == "wait") {
    $status = 'wait';

    $query = "UPDATE {$DB}.eworks SET status=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$status, $e_num]);

    $e_confirm_value = ($e_confirm === '' || $e_confirm === null) ? $user_name . " " . getPosition($user_id, $pdo) . " " . $date : $e_confirm . '!' . $user_name . " " . getPosition($user_id, $pdo) . " " . $date;
    $e_confirm_id_value = ($e_confirm_id === '' || $e_confirm_id === null) ? $user_id : $e_confirm_id . '!' . $user_id;

    $query = "UPDATE {$DB}.eworks SET e_confirm=?, e_confirm_id=? WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$e_confirm_value, $e_confirm_id_value, $e_num]);
}

if ($SelectWork == "delete_ripple") {
    $query = "UPDATE {$DB}.eworks_ripple SET is_deleted=1 WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$ripple_num]);
}

if ($SelectWork == "insert_ripple") {
    $ripple_content = $_REQUEST['ripple_content'];
    $ripple_author = $user_name;
    $ripple_author_id = $user_id;
    $parent_id = $e_num;
    $regist_day = date('Y-m-d H:i:s');

    $query = "INSERT INTO {$DB}.eworks_ripple (content, author, author_id, parent, regist_day) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$ripple_content, $ripple_author, $ripple_author_id, $parent_id, $regist_day]);

    $last_id = $pdo->lastInsertId();
    $ripple_data = getRippleData($last_id, $pdo);

    echo json_encode($ripple_data, JSON_UNESCAPED_UNICODE);
}

if ($SelectWork == "deldata") {
    $query = "UPDATE {$DB}.eworks SET is_deleted=1 WHERE num=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$e_num]);
}

if ($SelectWork !== "insert_ripple") {
    $data = ['e_num' => $recent_num];
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}
?>
