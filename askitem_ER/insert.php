<?php include $_SERVER['DOCUMENT_ROOT'] . '/session.php';   

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문  
  
  // 임시저장된 첨부파일을 확정하기 위해 검사하기  
isset($_REQUEST["timekey"])  ? $timekey=$_REQUEST["timekey"] :  $timekey='';   // 신규데이터에 생성할때 임시저장키  

$mode = $_REQUEST["mode"] ?? 'insert';
$num = $_REQUEST["num"] ?? '';

include $_SERVER['DOCUMENT_ROOT'] . '/eworks/_request.php';

$expense_data_json = isset($_POST['expense_data']) ? $_POST['expense_data'] : null;

// 전자결재의 변수에 매칭 (저장변수가 다른경우 선언)
$expense_data = null;
if ($expense_data_json) {
    try {
        $expense_data = json_decode($expense_data_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data');
        }
    } catch (Exception $e) {
        echo json_encode([
            "error" => true,
            "message" => "지출 데이터 처리 중 오류가 발생했습니다: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 첫 번째 expense_item 추출 및 전체 건수 계산
if (!empty($expense_data) && isset($expense_data[0]['expense_item'])) {
    $firstItem = $expense_data[0]['expense_item'];
    $total = count($expense_data);

    if ($total > 1) {
        // 첫 번째 항목 외 나머지 건수 표시
        $e_title = $firstItem . ' 외 ' . ($total - 1) . '건';
    } else {
        // 단건일 때는 항목명만
        $e_title = $firstItem;
    }
} else {
    $e_title = '';
}

$eworks_item = '지출결의서';

// 전자 결재에 보여질 내용 data 수정 update       
$data = array(    
	"e_title" => $e_title,
	"indate" => $indate,
	"author" => $author,	
	"suppliercost" => $suppliercost,  // 비용 총액 저장
    "paymentdate" => $paymentdate, // 결재일자
    "requestpaymentdate" => $requestpaymentdate, // 지출요청일자
    "expense_data" => $expense_data // 지출결의서 내역(JSON)
);

$contents = json_encode($data, JSON_UNESCAPED_UNICODE);
  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 전자결재 이름찾아 결재 아이디 찾아내기 대한은 강제설정

$first_approval_id = 'chandj';
$first_approval_name = '신지환 대표이사';
     
 if ($mode=="modify"){
    $data=date("Y-m-d H:i:s") . " - "  . $_SESSION["name"] . "  " ;	
	$update_log = $data . $update_log . "&#10";  // 개행문자 Textarea      

    // 결재금액이 10만원 미만인 경우 결재라인과 아이디를 공백으로 설정
    $numeric_cost = (int)str_replace(',', '', $suppliercost);
    if ($numeric_cost < 100000) {
        $e_line_id = '';
        $e_line = '';
        $status = 'end';
    } else {
        $e_line_id = $first_approval_id;
        $e_line = $first_approval_name;
        if($done=='done'){  // 결재완료일때는 그대로 기록되게 수정
            $status = 'end';
        } else {
            $status = 'send';
        }
    }    
    
    try {
        $pdo->beginTransaction();   
        
        // UPDATE 문 (변경된 컬럼명 반영)
        $sql = "UPDATE {$DB}.eworks SET 
                    indate = ?, 
                    outworkplace = ?, 
                    request_comment = ?, 
                    first_writer = ?, 
                    author = ?, 
                    update_log = ?, 
                    contents = ?, 
                    e_title = ?, 
                    eworks_item = ?,
					al_content=?, 
					suppliercost=?,  
					store=?,
                    paymentdate=?,
                    requestpaymentdate=?,
                    expense_data=?,
                    companyCard=?,
                    status=?,
                    e_line_id=?,
                    e_line=? ,
                    al_company=?  
                WHERE num = ? 
                LIMIT 1";

        $stmh = $pdo->prepare($sql);      

        // 바인딩된 값
        $stmh->bindValue(1, $indate, PDO::PARAM_STR);  
        $stmh->bindValue(2, $outworkplace, PDO::PARAM_STR);  
        $stmh->bindValue(3, $request_comment, PDO::PARAM_STR);  
        $stmh->bindValue(4, $first_writer, PDO::PARAM_STR);  
        $stmh->bindValue(5, $author, PDO::PARAM_STR);  
        $stmh->bindValue(6, $update_log, PDO::PARAM_STR);  
        $stmh->bindValue(7, $contents, PDO::PARAM_STR);  
        $stmh->bindValue(8, $e_title, PDO::PARAM_STR);  
        $stmh->bindValue(9, $eworks_item, PDO::PARAM_STR);  
        $stmh->bindValue(10, $al_content, PDO::PARAM_STR);  
        $stmh->bindValue(11, $suppliercost, PDO::PARAM_STR);  
        $stmh->bindValue(12, $store, PDO::PARAM_STR);  
        $stmh->bindValue(13, $paymentdate, PDO::PARAM_STR);  
        $stmh->bindValue(14, $requestpaymentdate, PDO::PARAM_STR);  
        $stmh->bindValue(15, json_encode($expense_data), PDO::PARAM_STR);  
        $stmh->bindValue(16, $companyCard, PDO::PARAM_STR);   
        $stmh->bindValue(17, $status, PDO::PARAM_STR);   
        $stmh->bindValue(18, $e_line_id, PDO::PARAM_STR);   
        $stmh->bindValue(19, $e_line, PDO::PARAM_STR);   
        $stmh->bindValue(20, $al_company, PDO::PARAM_STR);   
        $stmh->bindValue(21, $num, PDO::PARAM_STR);        

        $stmh->execute();
        $pdo->commit(); 
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }                         
}

else if ($mode == "copy" || $mode == "insert") {
    // 데이터 신규 등록
    $registdate = date("Y-m-d H:i:s");

    // 결재 상태 설정
    $status = 'send';
    $author_id = $user_id;
    $author = $user_name;

    // 최초 등록자 정보
    $first_writer = $_SESSION["name"] . " _" . date("Y-m-d H:i:s");

    // 결재금액이 10만원 미만인 경우 결재라인과 아이디를 공백으로 설정
    $numeric_cost = (int)str_replace(',', '', $suppliercost);
    if ($numeric_cost < 100000) {
        $e_line_id = '';
        $e_line = '';
        $status = 'end';
    } else {
        $e_line_id = $first_approval_id;
        $e_line = $first_approval_name;
    }
    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO {$DB}.eworks (
                    indate, outworkplace, request_comment, first_writer, author,
                    update_log, contents, e_title, eworks_item, registdate, 
                    author_id, status, e_line_id, e_line, al_content, 
                    suppliercost, store, paymentdate, requestpaymentdate, expense_data, 
                    companyCard, al_company
                ) VALUES (?, ?, ?, ?, ?,     ?, ?, ?, ?, ?,     ?, ?, ?, ?, ?,      ?, ?, ?, ?, ?,    ?, ?)";

        $stmh = $pdo->prepare($sql);

        // 바인딩된 값 설정
        $stmh->bindValue(1, $indate, PDO::PARAM_STR);
        $stmh->bindValue(2, $outworkplace, PDO::PARAM_STR);
        $stmh->bindValue(3, $request_comment, PDO::PARAM_STR);
        $stmh->bindValue(4, $first_writer, PDO::PARAM_STR);
        $stmh->bindValue(5, $author, PDO::PARAM_STR);
        $stmh->bindValue(6, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(7, $contents, PDO::PARAM_STR);
        $stmh->bindValue(8, $e_title, PDO::PARAM_STR);
        $stmh->bindValue(9, $eworks_item, PDO::PARAM_STR);
        $stmh->bindValue(10, $registdate, PDO::PARAM_STR);
        $stmh->bindValue(11, $author_id, PDO::PARAM_STR);
        $stmh->bindValue(12, $status, PDO::PARAM_STR);
        $stmh->bindValue(13, rtrim($e_line_id, '!'), PDO::PARAM_STR);
        $stmh->bindValue(14, rtrim($e_line, '!'), PDO::PARAM_STR);
        $stmh->bindValue(15, $al_content, PDO::PARAM_STR);
        $stmh->bindValue(16, $suppliercost, PDO::PARAM_STR);
        $stmh->bindValue(17, $store, PDO::PARAM_STR);
        $stmh->bindValue(18, $paymentdate, PDO::PARAM_STR);
        $stmh->bindValue(19, $requestpaymentdate, PDO::PARAM_STR);
        $stmh->bindValue(20, json_encode($expense_data), PDO::PARAM_STR);
        $stmh->bindValue(21, $companyCard, PDO::PARAM_STR);
        $stmh->bindValue(22, $al_company, PDO::PARAM_STR);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode([
            "error" => true,
            "message" => $Exception->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 신규 레코드 번호 가져오기
    try {
        $sql = "SELECT num FROM {$DB}.eworks ORDER BY num DESC LIMIT 1";
        $stmh = $pdo->prepare($sql);
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        $num = $row["num"];
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }

    // 첨부파일의 임시 키를 정상적인 번호로 업데이트
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE {$DB}.fileuploads SET parentid = ? WHERE parentid = ?";  // fileuploads 테이블의 parentid이다. picuploads 테이블의 parentnum이다. 
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_STR);
        $stmh->bindValue(2, $timekey, PDO::PARAM_STR);
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

else if ($mode == "delete") {	
    try {
        // update_log에 삭제자 기록 추가
        $logEntry = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " 삭제됨\n";
        $update_log = $logEntry . ($update_log ?? '');

        $pdo->beginTransaction();
        $sql = "UPDATE {$DB}.eworks
                SET is_deleted = 1,
                    update_log = ?
                WHERE num = ?
                LIMIT 1";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(2, $num,        PDO::PARAM_INT);
        $stmh->execute();
        $pdo->commit();

        echo json_encode(["num"=>$num, "mode"=>$mode], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["error"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
    }    
	exit;	
}

$data = array(
	"num" =>  $num,
	"mode" =>  $mode
);

//json 출력
echo(json_encode($data, JSON_UNESCAPED_UNICODE));     