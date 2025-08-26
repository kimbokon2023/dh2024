<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/php_errors.log');

header('Content-Type: application/json');

error_log('[toggle_favorite.php] 스크립트 시작');
error_log('[toggle_favorite.php] REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
error_log('[toggle_favorite.php] CONTENT_TYPE: ' . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

try {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
} catch (Exception $e) {
    error_log('[toggle_favorite.php] include 오류: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "필수 파일 로드 실패", "details" => $e->getMessage()]);
    exit;
}

if (!isset($_SESSION["level"])) {
    error_log('[toggle_favorite.php] 세션 없음');
    http_response_code(401);
    echo json_encode(["error" => "로그인이 필요합니다."]);
    exit;
}

// ---- 입력 파싱 (폼 우선 → 없으면 raw JSON) ----
$raw = '';
$input_method = '';

if (isset($_POST['data']) && $_POST['data'] !== '') {
    $raw = $_POST['data'];
    $input_method = 'POST[data]';
} else {
    $raw = file_get_contents('php://input');
    if ($raw !== '') {
        $input_method = 'php://input';
    }
}

error_log("[toggle_favorite.php] input_method={$input_method}");
error_log('[toggle_favorite.php] Raw input (head): ' . substr($raw, 0, 300));

if ($raw === '') {
    http_response_code(400);
    echo json_encode(["error" => "요청 데이터가 비어있습니다."]);
    exit;
}

$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "잘못된 JSON 형식", "details" => json_last_error_msg()]);
    exit;
}

if (empty($data['secondordnum']) || !is_numeric($data['secondordnum'])) {
    http_response_code(400);
    echo json_encode(["error" => "secondordnum 값이 유효하지 않습니다."]);
    exit;
}

// 안전 추출
$secondordnum  = (int)$data['secondordnum'];
$deliverymethod= $data['deliverymethod'] ?? '';
$address       = $data['address'] ?? '';
$receiver      = $data['receiver'] ?? '';
$tel           = $data['tel'] ?? '';
$carinfo       = $data['carinfo'] ?? '';
$delcompany    = $data['delcompany'] ?? '';
$chargedman    = $data['chargedman'] ?? '';
$chargedmantel = $data['chargedmantel'] ?? '';
$delcaritem    = $data['delcaritem'] ?? '';
$delbranch     = $data['delbranch'] ?? '';

$fav_item = [
    'secondordnum'  => $secondordnum,
    'deliverymethod'=> $deliverymethod,
    'address'       => $address,
    'receiver'      => $receiver,
    'tel'           => $tel,
    'carinfo'       => $carinfo,
    'delcompany'    => $delcompany,
    'chargedman'    => $chargedman,
    'chargedmantel' => $chargedmantel,
    'delcaritem'    => $delcaritem,
    'delbranch'     => $delbranch
];

try {
    $pdo = db_connect();
    if (!isset($DB) || empty($DB)) {
        $DB = $_SESSION["DB"] ?? 'dbchandj';
    }

    // 선택
    $stmt = $pdo->prepare("SELECT favorites_list FROM {$DB}.delivery_favorites WHERE secondordnum = :secondordnum");
    $stmt->bindValue(':secondordnum', $secondordnum, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $favorites = $row ? (json_decode($row['favorites_list'], true) ?: []) : [];

    // 존재하면 제거, 없으면 추가
    $foundIndex = -1;
    foreach ($favorites as $i => $fav) {
        if ($fav == $fav_item) { $foundIndex = $i; break; }
    }

    if ($foundIndex !== -1) {
        unset($favorites[$foundIndex]);
        $action = 'removed';
    } else {
        $favorites[] = $fav_item;
        $action = 'added';
    }

    $favorites = array_values($favorites);
    $favorites_json = json_encode($favorites, JSON_UNESCAPED_UNICODE);

    if ($row) {
        $stmt = $pdo->prepare("UPDATE {$DB}.delivery_favorites SET favorites_list = :favorites_list WHERE secondordnum = :secondordnum");
    } else {
        $stmt = $pdo->prepare("INSERT INTO {$DB}.delivery_favorites (secondordnum, favorites_list) VALUES (:secondordnum, :favorites_list)");
    }
    $stmt->bindValue(':secondordnum', $secondordnum, PDO::PARAM_INT);
    $stmt->bindValue(':favorites_list', $favorites_json, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "action" => $action,
        "favorites_count" => count($favorites),
        "secondordnum" => $secondordnum
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "데이터베이스 오류", "details" => $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "서버 오류", "details" => $e->getMessage()]);
    exit;
}
