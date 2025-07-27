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
	
	// 증상들과 발생일을 수집할 배열
	$symptoms_with_dates = [];
	
	while($data = $stmh->fetch(PDO::FETCH_ASSOC)) {
		$aslist = trim($data['aslist']);
		$asproday = $data['asproday']; // 처리예정일을 발생일로 사용
		
		// 빈 값이 아니고 유효한 증상인 경우만 추가
		if (!empty($aslist) && $aslist !== '' && $aslist !== null) {
			// 발생일 포맷팅
			$formatted_date = '';
			if ($asproday != '0000-00-00' && !empty($asproday)) {
				$date = new DateTime($asproday);
				$formatted_date = $date->format('Y-m-d');
			}
			
			// 증상과 발생일을 함께 저장
			$symptom_with_date = $aslist;
			if (!empty($formatted_date)) {
				$symptom_with_date .= ' (발생일: ' . $formatted_date . ')';
			}
			
			// 중복 제거를 위해 배열에 추가
			if (!in_array($symptom_with_date, $symptoms_with_dates)) {
				$symptoms_with_dates[] = $symptom_with_date;
			}
		}
	}
	
	// 증상들을 " "로 연결
	$symptoms_text = implode(' ", "', $symptoms_with_dates);
	
	// AI 프롬프트 생성
	$ai_prompt = '"' . $symptoms_text . '" 이 증상들을 이용해서 불량에 대한 상세한 내용을 사장에게 보고하는 형식으로 부탁해요. 불량원인에 대한 분석을 위한 자료입니다.';
	
	// 파일명 생성
	$filename = 'as_report.txt';
	$filepath = $_SERVER['DOCUMENT_ROOT'] . '/temp/' . $filename;
	
	// temp 디렉토리가 없으면 생성
	if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/temp/')) {
		mkdir($_SERVER['DOCUMENT_ROOT'] . '/temp/', 0777, true);
	}
	
	// 텍스트 파일 생성
	$file = fopen($filepath, 'w');
	
	// UTF-8 BOM 추가 (한글 깨짐 방지)
	fwrite($file, "\xEF\xBB\xBF");
	
	// AI 프롬프트 작성
	fwrite($file, $ai_prompt);
	
	fclose($file);
	
	// JSON 응답
	header('Content-Type: application/json');
	echo json_encode([
		'success' => true,
		'file' => '/temp/' . $filename,
		'message' => 'AI 프롬프트 파일이 성공적으로 생성되었습니다.',
		'symptom_count' => count($symptoms_with_dates),
		'prompt' => $ai_prompt
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
		'message' => 'AI 프롬프트 파일 생성 오류: ' . $e->getMessage()
	]);
}
?> 