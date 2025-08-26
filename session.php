<?php
if (!isset($_SESSION)) session_start();

$level = $_SESSION["level"] ?? '';
$user_name = $_SESSION["name"] ?? '';
$name = $_SESSION["name"] ?? '';
$user_id = $_SESSION["userid"] ?? '';
$DB = $_SESSION["DB"] ?? 'dbchandj';
$part = $_SESSION["part"] ?? '';
$eworks_level = $_SESSION["eworks_level"] ?? '';
$WebSite = "https://dh2024.co.kr/";
$root_dir = "https://dh2024.co.kr";

// 단일 로그인 검사 (관리자만 대상)
if ($level !== '9' && $user_id) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
    $pdo = db_connect();

    $stmt = $pdo->prepare("SELECT session_id FROM {$DB}.member WHERE id = ?");
    $stmt->execute([$user_id]);
    $db_session_id = $stmt->fetchColumn();

    if ($db_session_id !== session_id()) {
        session_destroy();

        // ✅ Ajax 요청일 경우 JSON 반환
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode([
                "error" => "세션이 만료되었습니다. 다시 로그인 해주세요.",
                "sessionExpired" => true
            ]);
            exit;
        }

        // ✅ 일반 요청일 경우 HTML 반환
        echo "<!DOCTYPE html>
        <html><head><meta charset='utf-8'></head><body>
        <script>
            alert('다른 곳에서 접속하여 현재 세션은 종료됩니다.');
            location.href = '../login/login_form.php';
        </script>
        </body></html>";
        exit;
    }
}
?>
