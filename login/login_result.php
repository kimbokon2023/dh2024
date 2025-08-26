<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/session.php'); 

$id=$_REQUEST["uid"];
$pw=$_REQUEST["upw"];

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

try {
	// 퇴직자 접속 금지 코드 추가
    $sql = "select * from " . $DB . ".member where id=? AND (quitDate='0000-00-00' OR quitDate IS NULL)" ;
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $id, PDO::PARAM_STR);
    $stmh->execute();
    $count = $stmh->rowCount();
    $row = $stmh->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $Exception) {
    print "오류: " . $Exception->getMessage();
    exit;
}

if($count>0) {	

    if ($pw != $row["pass"]) {  // 여기서 password_verify($pw, $row["pass"])를 사용해야 할 수도 있음
        echo '<script>
        alert("비밀번호가 틀립니다!");
        history.back();		
        </script>';
		exit;
    }

    $_SESSION["userid"]=$row["id"] ?? '';
    $_SESSION["name"]=$row["name"] ?? '';    
    $_SESSION["user_name"]=$row["name"] ?? '';    
    $_SESSION["level"]=$row["userlevel"] ?? '';
    $_SESSION["ecountID"]=$row["ecountID"] ?? '';
    $_SESSION["part"]=$row["part"] ?? '';	
    $_SESSION["eworks_level"]=$row["eworks_level"] ?? '';	
    $_SESSION["position"]=$row["position"] ?? '';	
    $_SESSION["hp"]=$row["hp"] ?? '';	
    $_SESSION["DB"]=$DB ?? '';

    // ✅ 세션 ID 저장 (단일 로그인 유지용)
    $currentSessionId = session_id();
    $update = $pdo->prepare("UPDATE {$DB}.member SET session_id = ? WHERE id = ?");
    $update->execute([$currentSessionId, $row["id"]]);	
			
	$data=date("Y-m-d H:i:s") . " - " . $_SESSION["userid"] . " - " . $_SESSION["name"] ;	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
	$pdo = db_connect();
	$pdo->beginTransaction();
	$sql = "insert into ".$DB.".logdata(data) values(?) " ;
	$stmh = $pdo->prepare($sql); 
	$stmh->bindValue(1, $data, PDO::PARAM_STR);   
	$stmh->execute();
	$pdo->commit(); 

	// 로그인 성공 시 쿠키 설정
	setcookie("showTodoView", "show", time() + 86400, "/"); // 1일 동안 유효
	setcookie("showBoardView", "show", time() + 86400, "/"); // 1일 동안 유효

	if(intval($_SESSION["level"]) == '10' ) {
		header('Location: ' . '../motor/del_list.php');
		exit;	
	}

	if(isset($_SESSION["url"])) {
		$redirectUrl = $_SESSION["url"];
		unset($_SESSION["url"]); // 리디렉션 후 세션에서 URL 제거
		header('Location: ' . $redirectUrl);
		exit;
	}
	
   header ("Location:../index.php");
   exit;  	
	
}
else 
{
	// 거래처 아이디인지 판단한다.		
	try{
		$sql="select * from " . $DB . ".phonebook where pid=?";
		$stmh=$pdo->prepare($sql);
		$stmh->bindValue(1,$id,PDO::PARAM_STR);
		$stmh->execute();
		$pidcount=$stmh->rowCount();
		$row = $stmh->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $Exception) {
		print "오류: ".$Exception->getMessage();
	}
	
	if($pidcount>0)
	{
	   if ($pw != $row["ppw"]) { 
			echo '<script>
			alert("비밀번호가 틀립니다!");
			history.back();		
			</script>';
			exit;
		}
		
		echo '<script>
		alert("협력사 로그인 성공");		
		</script>';
			
		$_SESSION["userid"]=$row["num"];
		$_SESSION["name"]=$row["vendor_name"];    
		$_SESSION["secondordnum"]=$row["secondordnum"];    
		$_SESSION["part"]=$row["part"];	
		$_SESSION["level"]= '9';						
		$_SESSION["DB"]=$DB ;

		// 로그인 성공 시 쿠키 설정
		setcookie("showTodoView", "show", time() + 86400, "/"); // 1일 동안 유효
		setcookie("showBoardView", "show", time() + 86400, "/"); // 1일 동안 유효
		
		header('Location: ' . '../motor/plist.php');
		exit;		
	}
	else
	{		    
        echo '<script>
        alert("ID와 비밀번호를 확인바랍니다!");
        history.back();		
        </script>';
		exit;
    }
}  

?>
