<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

function checkNull($strtmp) {
    return !($strtmp === null || trim($strtmp) === '');
}

$search = isset($_POST['search']) ? $_POST['search'] : '';  
$tablename = 'as';

$pdo = db_connect();
 
$order_by = "ORDER BY num DESC";
	
// AS 자료만 필터링 (itemcheck가 'AS'인 경우만)
if (checkNull($search)) {
	$sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL AND itemcheck='AS' AND ((payment='paid' And paydate IS NOT NULL) or (payment='free' And asendday IS NOT NULL))  " . $order_by;	
} else {
	$sql = "SELECT * FROM ".$DB.".".$tablename." WHERE is_deleted IS NULL AND itemcheck='AS' AND ((payment='paid' And paydate IS NOT NULL) or (payment='free' And asendday IS NOT NULL)) " . $order_by;
}

try {
	$stmh = $pdo->query($sql); 
	$total_row = $stmh->rowCount();
	
	// 파일명 생성
	$filename = 'AS_완료_리포트_' . date('Y-m-d_H-i-s') . '.csv';
	$filepath = $_SERVER['DOCUMENT_ROOT'] . '/temp/' . $filename;
	
	// temp 디렉토리가 없으면 생성
	if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/temp/')) {
		mkdir($_SERVER['DOCUMENT_ROOT'] . '/temp/', 0777, true);
	}
	
	// CSV 파일 생성
	$file = fopen($filepath, 'w');
	
	// UTF-8 BOM 추가 (한글 깨짐 방지)
	fwrite($file, "\xEF\xBB\xBF");
	
	// 헤더 작성
	$headers = [
		'번호',
		'처리예정일',
		'AS완료일',
		'요청업체',
		'요청인',
		'비용부담업체',
		'비용',
		'청구일자',
		'현장주소',
		'증상',
		'유상처리결과',
		'처리방법 및 결과(구체적)'
	];
	
	fputcsv($file, $headers);
	
	// 데이터 입력
	$start_num = $total_row;
	
	while($data = $stmh->fetch(PDO::FETCH_ASSOC)) {
		// _row.php에서 사용하는 변수들을 설정
		$num = $data['num'];
		$asproday = $data['asproday'];
		$asendday = $data['asendday'];
		$as_step = $data['as_step'];
		$asorderman = $data['asorderman'];
		$asfee_estimate = $data['asfee_estimate'];
		$asfee = $data['asfee'];
		$demandDate = $data['demandDate'];
		$address = $data['address'];
		$aslist = $data['aslist'];
		$asresult = $data['asresult'];
		$note = $data['note'];
		
		// 처리예정일 포맷팅
		$formatted_asproday = '';
		if ($asproday != '0000-00-00' && !empty($asproday)) {
			$date = new DateTime($asproday);
			$formatted_asproday = $date->format('Y-m-d');
		}
		
		// AS완료일 포맷팅
		$formatted_asendday = ($asendday == '0000-00-00') ? '' : $asendday;
		
		// 비용 포맷팅
		$formatted_asfee = '';
		if ($asfee !== '' && $asfee !== null && strpos($asfee, ',') === false) {
			$formatted_asfee = number_format($asfee);
		} else {
			$formatted_asfee = $asfee;
		}
		
		// CSV에 데이터 입력
		$row_data = [
			$start_num,
			$formatted_asproday,
			$formatted_asendday,
			$as_step,
			$asorderman,
			$asfee_estimate,
			$formatted_asfee,
			$demandDate,
			$address,
			$aslist,
			$asresult,
			$note
		];
		
		fputcsv($file, $row_data);
		$start_num--;
	}
	
	fclose($file);
	
	// JSON 응답
	header('Content-Type: application/json');
	echo json_encode([
		'success' => true,
		'file' => '/temp/' . $filename,
		'message' => 'CSV 파일이 성공적으로 생성되었습니다.'
	]);
	
} catch (PDOException $Exception) {
	header('Content-Type: application/json');
	echo json_encode([
		'success' => false,
		'message' => '데이터베이스 오류: ' . $Exception->getMessage()
	]);
} catch (Exception $e) {
	header('Content-Type: application/json');
	echo json_encode([
		'success' => false,
		'message' => 'CSV 파일 생성 오류: ' . $e->getMessage()
	]);
}
?> 