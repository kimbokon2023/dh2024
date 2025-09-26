<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

// 권한 체크
if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

header('Content-Type: application/json; charset=utf-8');

$pdo = db_connect();
$action = $_REQUEST['action'] ?? '';

try {
    error_log('API 요청 - Action: ' . $action . ', Method: ' . $_SERVER['REQUEST_METHOD']);

    switch($action) {
        case 'list':
            handleList($pdo);
            break;
        case 'create':
            handleCreate($pdo);
            break;
        case 'update':
            handleUpdate($pdo);
            break;
        case 'delete':
            handleDelete($pdo);
            break;
        case 'delete_multiple':
            handleDeleteMultiple($pdo);
            break;
        case 'save_all':
            handleSaveAll($pdo);
            break;
        case 'history':
            handleHistory($pdo);
            break;
        default:
            throw new Exception('유효하지 않은 액션입니다: ' . $action);
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    $errorFile = $e->getFile();
    $errorLine = $e->getLine();

    error_log("API 오류: $errorMessage in $errorFile:$errorLine");
    error_log("요청 액션: $action");
    error_log("요청 데이터: " . print_r($_REQUEST, true));

    // JSON 디코딩 에러인 경우 더 자세한 정보 제공
    if (strpos($errorMessage, 'JSON') !== false) {
        $inputData = file_get_contents('php://input');
        error_log("원본 입력 데이터: " . $inputData);
        error_log("JSON 에러 코드: " . json_last_error());
        error_log("JSON 에러 메시지: " . json_last_error_msg());
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $errorMessage,
        'action' => $action,
        'debug' => [
            'file' => basename($errorFile),
            'line' => $errorLine,
            'json_error' => json_last_error_msg()
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Error $e) {
    $errorMessage = $e->getMessage();
    $errorFile = $e->getFile();
    $errorLine = $e->getLine();

    error_log("PHP Fatal Error: $errorMessage in $errorFile:$errorLine");

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "PHP Fatal Error: $errorMessage",
        'debug' => [
            'file' => basename($errorFile),
            'line' => $errorLine
        ]
    ], JSON_UNESCAPED_UNICODE);
}

// 단가 변경 이력 저장 함수
function saveUnitpriceHistory($pdo, $originalNum, $oldData, $newData, $changeType, $changeReason = null) {
    global $DB;

    error_log("=== 히스토리 저장 시도 ===");
    error_log("originalNum: " . $originalNum);
    error_log("changeType: " . $changeType);
    error_log("changeReason: " . $changeReason);

    // 테이블 존재 여부 확인
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'unitprice_history'");
        if ($checkTable->rowCount() == 0) {
            error_log('unitprice_history 테이블이 존재하지 않습니다.');
            return false;
        }
        error_log('unitprice_history 테이블 존재 확인됨');
    } catch (Exception $e) {
        error_log('테이블 확인 실패: ' . $e->getMessage());
        return false;
    }

    try {
        $sql = "INSERT INTO {$DB}.unitprice_history (
                    original_num, item_code, item_name, category, vendor_code, vendor_name,
                    unit_price_cny, exchange_rate, min_quantity, unit, effective_date, memo,
                    change_type, change_reason,
                    old_unit_price_cny, new_unit_price_cny,
                    old_exchange_rate, new_exchange_rate,
                    changed_by,
                    original_created_by, original_created_date,
                    original_updated_by, original_updated_date, original_is_deleted
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    ?,
                    ?, ?,
                    ?, ?, ?
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $originalNum,
            $newData['item_code'] ?? $oldData['item_code'] ?? null,
            $newData['item_name'] ?? $oldData['item_name'] ?? '',
            $newData['category'] ?? $oldData['category'] ?? '모터',
            $newData['vendor_code'] ?? $oldData['vendor_code'] ?? null,
            $newData['vendor_name'] ?? $oldData['vendor_name'] ?? null,
            $newData['unit_price_cny'] ?? $oldData['unit_price_cny'] ?? null,
            $newData['exchange_rate'] ?? $oldData['exchange_rate'] ?? null,
            $newData['min_quantity'] ?? $oldData['min_quantity'] ?? 1,
            $newData['unit'] ?? $oldData['unit'] ?? 'EA',
            $newData['effective_date'] ?? $oldData['effective_date'] ?? date('Y-m-d'),
            $newData['memo'] ?? $oldData['memo'] ?? null,
            $changeType,
            $changeReason,
            $oldData['unit_price_cny'] ?? null,
            $newData['unit_price_cny'] ?? null,
            $oldData['exchange_rate'] ?? null,
            $newData['exchange_rate'] ?? null,
            $_SESSION['name'],
            $oldData['created_by'] ?? null,
            $oldData['created_date'] ?? null,
            $oldData['updated_by'] ?? null,
            $oldData['updated_date'] ?? null,
            $oldData['is_deleted'] ?? 0
        ]);

        error_log("히스토리 저장 성공! history_id: " . $pdo->lastInsertId());
        return true;
    } catch (Exception $e) {
        error_log('단가 이력 저장 실패: ' . $e->getMessage());
        error_log('SQL: ' . $sql);
        return false;
    }
}

function handleList($pdo) {
    global $DB;

    // unitprice 테이블이 존재하지 않는 경우 빈 배열 반환
    try {
        $check_table = $pdo->query("SHOW TABLES LIKE 'unitprice'");
        if ($check_table->rowCount() == 0) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            return;
        }
    } catch (Exception $e) {
        echo json_encode([], JSON_UNESCAPED_UNICODE);
        return;
    }

    $sql = "SELECT u.*,
                   p.vendor_name as pb_vendor_name,
                   p.image_base64 as pb_image_base64
            FROM {$DB}.unitprice u
            LEFT JOIN {$DB}.phonebook_buy p ON u.vendor_code = p.num
            WHERE u.is_deleted = 0
            ORDER BY u.num DESC";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 업체명 통합 (vendor_name이 없으면 phonebook_buy에서 가져온 값 사용)
    foreach ($rows as &$row) {
        if (empty($row['vendor_name']) && !empty($row['pb_vendor_name'])) {
            $row['vendor_name'] = $row['pb_vendor_name'];
        }

        // KRW 단가 계산
        if ($row['unit_price_cny'] && $row['exchange_rate']) {
            $row['unit_price_krw'] = round($row['unit_price_cny'] * $row['exchange_rate'], 0);
        }

        // 불필요한 필드 제거
        unset($row['pb_vendor_name'], $row['pb_image_base64']);
    }

    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
}

function handleCreate($pdo) {
    global $DB;

    $input = json_decode(file_get_contents('php://input'), true);
    $data = $input['data'] ?? [];

    // 필수 필드 검증
    if (empty($data['item_name'])) {
        throw new Exception('품목명은 필수입니다.');
    }

    // vendor_code 찾기 (vendor_name이 있는 경우)
    if (!empty($data['vendor_name']) && empty($data['vendor_code'])) {
        $stmt = $pdo->prepare("SELECT num FROM {$DB}.phonebook_buy WHERE vendor_name = ? AND is_deleted IS NULL LIMIT 1");
        $stmt->execute([$data['vendor_name']]);
        $vendor_code = $stmt->fetchColumn();
        if ($vendor_code) {
            $data['vendor_code'] = $vendor_code;
        }
    }

    $sql = "INSERT INTO {$DB}.unitprice (
                item_code, item_name, category, vendor_code, vendor_name,
                unit_price_cny, sell_price_krw, exchange_rate, min_quantity, unit,
                effective_date, memo, created_by, updated_by
            ) VALUES (
                :item_code, :item_name, :category, :vendor_code, :vendor_name,
                :unit_price_cny, :sell_price_krw, :exchange_rate, :min_quantity, :unit,
                :effective_date, :memo, :created_by, :updated_by
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':item_code' => $data['item_code'] ?? null,
        ':item_name' => $data['item_name'],
        ':category' => $data['category'] ?? '모터',
        ':vendor_code' => $data['vendor_code'] ?? null,
        ':vendor_name' => $data['vendor_name'] ?? null,
        ':unit_price_cny' => $data['unit_price_cny'] ?? null,
        ':sell_price_krw' => $data['sell_price_krw'] ?? null,
        ':exchange_rate' => $data['exchange_rate'] ?? null,
        ':min_quantity' => $data['min_quantity'] ?? 1,
        ':unit' => $data['unit'] ?? 'EA',
        ':effective_date' => $data['effective_date'] ?? date('Y-m-d'),
        ':memo' => $data['memo'] ?? null,
        ':created_by' => $_SESSION['name'],
        ':updated_by' => $_SESSION['name']
    ]); 

    $newId = $pdo->lastInsertId();

    // 단가 생성 이력 저장
    $changeReason = $input['change_reason'] ?? '신규 단가 등록';
    saveUnitpriceHistory($pdo, $newId, [], $data, 'INSERT', $changeReason);

    echo json_encode([
        'success' => true,
        'num' => $newId,
        'message' => '저장되었습니다.'
    ]);
}

function handleUpdate($pdo) {
    global $DB;

    $input = json_decode(file_get_contents('php://input'), true);
    $data = $input['data'] ?? [];

    if (empty($data['num'])) {
        throw new Exception('수정할 데이터의 번호가 없습니다.');
    }

    // 필수 필드 검증
    if (empty($data['item_name'])) {
        throw new Exception('품목명은 필수입니다.');
    }

    // 기존 데이터 조회 (이력 저장용)
    $stmt = $pdo->prepare("SELECT * FROM {$DB}.unitprice WHERE num = ? AND is_deleted = 0");
    $stmt->execute([$data['num']]);
    $oldData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldData) {
        throw new Exception('수정할 데이터를 찾을 수 없습니다.');
    }

    // vendor_code 찾기 (vendor_name이 있는 경우)
    if (!empty($data['vendor_name']) && empty($data['vendor_code'])) {
        $stmt = $pdo->prepare("SELECT num FROM {$DB}.phonebook_buy WHERE vendor_name = ? AND is_deleted IS NULL LIMIT 1");
        $stmt->execute([$data['vendor_name']]);
        $vendor_code = $stmt->fetchColumn();
        if ($vendor_code) {
            $data['vendor_code'] = $vendor_code;
        }
    }

    $sql = "UPDATE {$DB}.unitprice SET
                item_code = :item_code,
                item_name = :item_name,
                category = :category,
                vendor_code = :vendor_code,
                vendor_name = :vendor_name,
                unit_price_cny = :unit_price_cny,
                sell_price_krw = :sell_price_krw,
                exchange_rate = :exchange_rate,
                min_quantity = :min_quantity,
                unit = :unit,
                effective_date = :effective_date,
                memo = :memo,
                updated_by = :updated_by,
                updated_date = NOW()
            WHERE num = :num AND is_deleted = 0";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':item_code' => $data['item_code'] ?? null,
        ':item_name' => $data['item_name'],
        ':category' => $data['category'] ?? '모터',
        ':vendor_code' => $data['vendor_code'] ?? null,
        ':vendor_name' => $data['vendor_name'] ?? null,
        ':unit_price_cny' => $data['unit_price_cny'] ?? null,
        ':sell_price_krw' => $data['sell_price_krw'] ?? null,
        ':exchange_rate' => $data['exchange_rate'] ?? null,
        ':min_quantity' => $data['min_quantity'] ?? 1,
        ':unit' => $data['unit'] ?? 'EA',
        ':effective_date' => $data['effective_date'] ?? date('Y-m-d'),
        ':memo' => $data['memo'] ?? null,
        ':updated_by' => $_SESSION['name'],
        ':num' => $data['num']
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('수정할 데이터를 찾을 수 없습니다.');
    }

    // 단가 변경 이력 저장
    $changeReason = $input['change_reason'] ?? '단가 정보 수정';
    saveUnitpriceHistory($pdo, $data['num'], $oldData, $data, 'UPDATE', $changeReason);

    echo json_encode([
        'success' => true,
        'message' => '수정되었습니다.'
    ]);
}

function handleDelete($pdo) {
    global $DB;

    // FormData와 JSON 방식 모두 지원
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // FormData 방식
        $num = $_POST['num'] ?? 0;
        error_log("Delete - FormData Input, num: " . $num);
    } else {
        // JSON 방식
        $input = json_decode(file_get_contents('php://input'), true);
        $num = $input['num'] ?? 0;
        error_log("Delete - JSON Input, num: " . $num);
    }

    if (empty($num)) {
        throw new Exception('삭제할 데이터의 번호가 없습니다.');
    }

    // 기존 데이터 조회 (이력 저장용)
    $stmt = $pdo->prepare("SELECT * FROM {$DB}.unitprice WHERE num = ? AND is_deleted = 0");
    $stmt->execute([$num]);
    $oldData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldData) {
        throw new Exception('삭제할 데이터를 찾을 수 없습니다.');
    }

    $sql = "UPDATE {$DB}.unitprice SET
                is_deleted = 1,
                updated_by = :updated_by,
                updated_date = NOW()
            WHERE num = :num";
 
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':updated_by' => $_SESSION['name'],
        ':num' => $num
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('삭제할 데이터를 찾을 수 없습니다.');
    }

    // 단가 삭제 이력 저장
    $changeReason = $input['change_reason'] ?? '단가 정보 삭제';
    $deletedData = $oldData;
    $deletedData['is_deleted'] = 1;
    saveUnitpriceHistory($pdo, $num, $oldData, $deletedData, 'DELETE', $changeReason);

    echo json_encode([
        'success' => true,
        'message' => '삭제되었습니다.'
    ]);
}

function handleDeleteMultiple($pdo) {
    global $DB;

    // FormData와 JSON 방식 모두 지원
    if (isset($_POST['action']) && $_POST['action'] === 'delete_multiple') {
        // FormData 방식
        $nums = isset($_POST['nums']) ? json_decode($_POST['nums'], true) : [];
        error_log("Delete Multiple - FormData Input, nums: " . $_POST['nums']);
    } else {
        // JSON 방식
        $rawInput = file_get_contents('php://input');
        error_log("Delete Multiple - JSON Raw Input: " . $rawInput);

        $input = json_decode($rawInput, true);
        $nums = $input['nums'] ?? [];
        error_log("Delete Multiple - JSON Decoded Input: " . print_r($input, true));
    }

    error_log("Delete Multiple - Final nums array: " . print_r($nums, true));

    if (empty($nums) || !is_array($nums)) {
        throw new Exception('삭제할 데이터의 번호 목록이 없습니다.');
    }

    // 배열 내 모든 값이 숫자인지 검증
    foreach ($nums as $num) {
        if (!is_numeric($num) || $num <= 0) {
            throw new Exception('유효하지 않은 데이터 번호입니다: ' . $num);
        }
    }

    $pdo->beginTransaction();

    try {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($nums as $num) {
            try {
                // 기존 데이터 조회 (이력 저장용)
                $stmt = $pdo->prepare("SELECT * FROM {$DB}.unitprice WHERE num = ? AND is_deleted = 0");
                $stmt->execute([$num]);
                $oldData = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$oldData) {
                    $errors[] = "번호 {$num}: 삭제할 데이터를 찾을 수 없습니다.";
                    $errorCount++;
                    continue;
                }

                // 삭제 처리
                $sql = "UPDATE {$DB}.unitprice SET
                            is_deleted = 1,
                            updated_by = ?,
                            updated_date = NOW()
                        WHERE num = ?";

                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$_SESSION['name'], $num]);

                if ($stmt->rowCount() > 0) {
                    // 단가 삭제 이력 저장
                    $changeReason = $input['change_reason'] ?? '단가 일괄 삭제';
                    $deletedData = $oldData;
                    $deletedData['is_deleted'] = 1;
                    saveUnitpriceHistory($pdo, $num, $oldData, $deletedData, 'DELETE', $changeReason);

                    $successCount++;
                } else {
                    $errors[] = "번호 {$num}: 삭제 처리 실패";
                    $errorCount++;
                }

            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "번호 {$num}: " . $e->getMessage();
            }
        }

        $pdo->commit();

        $message = "성공: {$successCount}건";
        if ($errorCount > 0) {
            $message .= ", 실패: {$errorCount}건";
        }

        echo json_encode([
            'success' => $successCount > 0,
            'message' => $message,
            'details' => [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function handleSaveAll($pdo) {
    global $DB;

    if (!$pdo) {
        throw new Exception('데이터베이스 연결 실패');
    }

    if (empty($DB)) {
        throw new Exception('데이터베이스명이 설정되지 않음');
    }

    // FormData 방식과 JSON 방식 모두 지원
    if (isset($_POST['data'])) {
        // FormData 방식
        $jsonData = $_POST['data'];
        error_log('FormData 방식으로 데이터 수신: ' . strlen($jsonData) . ' bytes');
        $dataList = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON 파싱 오류: ' . json_last_error_msg());
        }
    } else {
        // JSON 방식
        $input = json_decode(file_get_contents('php://input'), true);
        $dataList = $input['data'] ?? [];
        error_log('JSON 방식으로 데이터 수신');
    }

    // 디버깅: 받은 데이터 로그
    error_log('받은 데이터 수: ' . count($dataList));
    if (!empty($dataList)) {
        error_log('첫 번째 데이터: ' . print_r($dataList[0], true));
    }

    if (empty($dataList)) {
        throw new Exception('저장할 데이터가 없습니다.');
    }

    $pdo->beginTransaction();

    try {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($dataList as $index => $data) {
            try {
                // 빈 행 스킵
                if (empty($data['item_name'])) {
                    continue;
                }

                // vendor_code 찾기
                if (!empty($data['vendor_name']) && empty($data['vendor_code'])) {
                    $stmt = $pdo->prepare("SELECT num FROM {$DB}.phonebook_buy WHERE vendor_name = ? AND is_deleted IS NULL LIMIT 1");
                    $stmt->execute([$data['vendor_name']]);
                    $vendor_code = $stmt->fetchColumn();
                    if ($vendor_code) {
                        $data['vendor_code'] = $vendor_code;
                    }
                }

                if (!empty($data['num'])) {
                    // 기존 데이터 조회 (히스토리 저장용)
                    $oldStmt = $pdo->prepare("SELECT * FROM {$DB}.unitprice WHERE num = ? AND is_deleted = 0");
                    $oldStmt->execute([$data['num']]);
                    $oldData = $oldStmt->fetch(PDO::FETCH_ASSOC);

                    // 업데이트
                    $sql = "UPDATE {$DB}.unitprice SET
                                item_code = ?, item_name = ?, category = ?, vendor_code = ?, vendor_name = ?,
                                unit_price_cny = ?, sell_price_krw = ?, exchange_rate = ?, min_quantity = ?, unit = ?,
                                effective_date = ?, memo = ?, updated_by = ?, updated_date = NOW()
                            WHERE num = ? AND is_deleted = 0";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $data['item_code'] ?? null,
                        $data['item_name'],
                        $data['category'] ?? '모터',
                        $data['vendor_code'] ?? null,
                        $data['vendor_name'] ?? null,
                        $data['unit_price_cny'] ?? null,
                        $data['sell_price_krw'] ?? null,
                        $data['exchange_rate'] ?? null,
                        $data['min_quantity'] ?? 1,
                        $data['unit'] ?? 'EA',
                        $data['effective_date'] ?? date('Y-m-d'),
                        $data['memo'] ?? null,
                        $_SESSION['name'],
                        $data['num']
                    ]);

                    // 히스토리 저장 (업데이트)
                    if ($oldData) {
                        saveUnitpriceHistory($pdo, $data['num'], $oldData, $data, 'UPDATE', '단가 일괄 수정');
                    }
                } else {
                    // 삽입
                    $sql = "INSERT INTO {$DB}.unitprice (
                                item_code, item_name, category, vendor_code, vendor_name,
                                unit_price_cny, sell_price_krw, exchange_rate, min_quantity, unit,
                                effective_date, memo, created_by, updated_by
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $data['item_code'] ?? null,
                        $data['item_name'],
                        $data['category'] ?? '모터',
                        $data['vendor_code'] ?? null,
                        $data['vendor_name'] ?? null,
                        $data['unit_price_cny'] ?? null,
                        $data['sell_price_krw'] ?? null,
                        $data['exchange_rate'] ?? null,
                        $data['min_quantity'] ?? 1,
                        $data['unit'] ?? 'EA',
                        $data['effective_date'] ?? date('Y-m-d'),
                        $data['memo'] ?? null,
                        $_SESSION['name'],
                        $_SESSION['name']
                    ]);

                    // 새로 생성된 ID 가져오기
                    $newId = $pdo->lastInsertId();

                    // 히스토리 저장 (삽입)
                    saveUnitpriceHistory($pdo, $newId, [], $data, 'INSERT', '단가 일괄 등록');
                }

                $successCount++;

            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "행 " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        $pdo->commit();

        $message = "성공: {$successCount}건";
        if ($errorCount > 0) {
            $message .= ", 실패: {$errorCount}건";
        }

        echo json_encode([
            'success' => true,
            'message' => $message,
            'details' => [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function handleHistory($pdo) {
    global $DB;

    $originalNum = $_GET['original_num'] ?? '';
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);

    $whereClauses = [];
    $params = [];

    // 특정 단가 항목의 이력만 조회
    if (!empty($originalNum)) {
        $whereClauses[] = "h.original_num = ?";
        $params[] = $originalNum;
    }

    $whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

    // 이력 데이터 조회 (뷰 사용)
    $sql = "SELECT h.*,
                   pb.vendor_name as pb_vendor_name,
                   pb.image_base64 as pb_image_base64,
                   -- CNY 단가 변동율 계산
                   CASE
                       WHEN h.old_unit_price_cny IS NOT NULL AND h.old_unit_price_cny > 0
                       THEN ROUND(((h.new_unit_price_cny - h.old_unit_price_cny) / h.old_unit_price_cny) * 100, 2)
                       ELSE NULL
                   END as price_change_percent,
                   -- KRW 환산 단가 계산
                   CASE
                       WHEN h.new_unit_price_cny IS NOT NULL AND h.new_exchange_rate IS NOT NULL
                       THEN ROUND(h.new_unit_price_cny * h.new_exchange_rate, 0)
                       ELSE NULL
                   END as new_unit_price_krw,
                   CASE
                       WHEN h.old_unit_price_cny IS NOT NULL AND h.old_exchange_rate IS NOT NULL
                       THEN ROUND(h.old_unit_price_cny * h.old_exchange_rate, 0)
                       ELSE NULL
                   END as old_unit_price_krw
            FROM {$DB}.unitprice_history h
            LEFT JOIN {$DB}.phonebook_buy pb ON h.vendor_code = pb.num
            {$whereClause}
            ORDER BY h.changed_date DESC
            LIMIT {$limit} OFFSET {$offset}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 총 개수 조회
    $countSql = "SELECT COUNT(*) FROM {$DB}.unitprice_history h {$whereClause}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'data' => $history,
        'total_count' => $totalCount,
        'limit' => $limit,
        'offset' => $offset
    ], JSON_UNESCAPED_UNICODE);
}
?>