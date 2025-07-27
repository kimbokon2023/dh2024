<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/session.php'); 

$id = $_POST["uid"];
$pw = $_POST["upw"];

header("Content-Type: application/json");  // JSON 응답을 위해 필요

$DB = "dbchandj";

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$error = '';
$msg =  '';

try {
    // 회원 테이블에서 사용자 검색
    $sql = "SELECT * FROM {$DB}.member WHERE id=? AND (quitDate='0000-00-00' OR quitDate IS NULL ) ";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $id, PDO::PARAM_STR);
    $stmh->execute();
    $count = $stmh->rowCount();
    $row = $stmh->fetch(PDO::FETCH_ASSOC);

    if ($count < 1) {
        // 회원 정보가 없으면 협력사 테이블에서 검색
        $sql = "SELECT * FROM {$DB}.phonebook WHERE pid=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $id, PDO::PARAM_STR);
        $stmh->execute();
        $count = $stmh->rowCount();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        
        if ($count > 0 && $pw == $row["ppw"]) {
            // 협력사 사용자 로그인 성공
            $_SESSION["userid"] = $row["num"];
            $_SESSION["name"] = $row["vendor_name"];
			$_SESSION["secondordnum"]=$row["secondordnum"];   
            $_SESSION["level"] = '9';  // 예를 들어, 협력사의 경우 userlevel을 9로 설정
            $_SESSION["DB"] = $DB;
            $error = '';  // 오류 없음
        } else {
            $error = "ID와 비밀번호를 확인해주세요!";
			$msg =  $row["representative_name"] . $row["pid"] . $row["ppw"] ;
        }
    } elseif ($pw != $row["pass"]) {
        $error = "비밀번호가 틀립니다!";
    } else {
        // 자체 맴버 로그인 성공
        $_SESSION["userid"] = $row["id"];
        $_SESSION["name"] = $row["name"];
        $_SESSION["user_name"] = $row["name"];
        $_SESSION["level"] = $row["userlevel"];
        $_SESSION["ecountID"] = $row["ecountID"];
        $_SESSION["part"] = $row["part"];
        $_SESSION["eworks_level"] = $row["eworks_level"];
        $_SESSION["position"] = $row["position"];
        $_SESSION["hp"] = $row["hp"];
        $_SESSION["DB"] = $DB;
        $error = '';  // 오류 없음

        // ✅ 세션 ID 저장 (단일 로그인 유지용)
        $currentSessionId = session_id();
        $update = $pdo->prepare("UPDATE {$DB}.member SET session_id = ? WHERE id = ?");
        $update->execute([$currentSessionId, $row["id"]]);        
				
		if (intval($_SESSION["level"]) == '10') {
			// 기존의 리다이렉트 코드 대신에
			$redirect_url = '../motor/del_list.php';
		} else {
			$redirect_url = '';
		}
		
    }
} catch (PDOException $Exception) {
    $error = "오류: " . $Exception->getMessage();
}

if(empty($error))
{
	$logdata=date("Y-m-d H:i:s") . " - " . $_SESSION["userid"] . " - " . $_SESSION["name"] ;	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
	$pdo = db_connect();
	$pdo->beginTransaction();
	$sql = "insert into ".$DB.".logdata(data) values(?) " ;
	$stmh = $pdo->prepare($sql); 
	$stmh->bindValue(1, $logdata, PDO::PARAM_STR);   
	$stmh->execute();
	$pdo->commit(); 
}

$data = array(
    "id" => $id,
    "error" => $error,
    "redirect" => $redirect_url,
    "session_msg" => $msg	
);

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
