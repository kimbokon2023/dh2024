<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/session.php';
header("Content-Type: application/json");

$mode = $_REQUEST["mode"] ?? 'insert';
$num = $_REQUEST["num"] ?? '';
$indate = $_REQUEST["indate"] ?? '';
$mytitle = $_REQUEST["mytitle"] ?? '';
$content = $_REQUEST["content"] ?? '';
$content_reason = $_REQUEST["content_reason"] ?? '';
$author = $_REQUEST["author"] ?? '';
$first_writer = $_REQUEST["first_writer"] ?? '';
$update_log = $_REQUEST["update_log"] ?? '';
$timekey = $_REQUEST["timekey"] ?? '';

$outworkplace = $mytitle;
$al_content = $content;
$request_comment = $content_reason;
$e_title = '연구노트';
$eworks_item = '연구노트';

$data = [
    "e_title" => $e_title,
    "indate" => $indate,
    "author" => $author,
    "outworkplace" => $outworkplace,
    "al_content" => $al_content
];

$contents = json_encode($data, JSON_UNESCAPED_UNICODE);

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if ($mode === "modify") {
    // 결재라인 설정 (askitem_ER 방식 적용)
    $first_approval_id = 'chandj';
    $first_approval_name = '신지환 대표이사';

    // 연구노트는 항상 결재라인 적용
    $e_line_id = $first_approval_id;
    $e_line = $first_approval_name;
    $status = 'send';

    try {
        $pdo->beginTransaction();
        $data = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . "  ";
        $update_log = $data . $update_log . "&#10";

        $sql = "UPDATE {$DB}.eworks SET
                    indate = ?,
                    outworkplace = ?,
                    author = ?,
                    update_log = ?,
                    contents = ?,
                    e_title = ?,
                    eworks_item = ?,
                    al_content = ?,
                    request_comment = ?,
                    e_line_id = ?,
                    e_line = ?,
                    status = ?
                WHERE num = ? LIMIT 1";

        $stmh = $pdo->prepare($sql);
        $stmh->execute([
            $indate, $outworkplace, $author, $update_log, $contents,
            $e_title, $eworks_item, $al_content, $request_comment,
            $e_line_id, $e_line, $status, $num
        ]);
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["error" => $e->getMessage()]);
        exit;
    }
} else {
    $registdate = date("Y-m-d H:i:s");
    $first_writer = $_SESSION["name"] . " _" . $registdate;
    $author_id = $user_id;
    $status = 'send';
    // 결재라인 설정 (askitem_ER 방식 적용)
    $first_approval_id = 'chandj';
    $first_approval_name = '신지환 대표이사';

    // 연구노트는 항상 결재라인 적용
    $e_line_id = $first_approval_id;
    $e_line = $first_approval_name;
	

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO {$DB}.eworks (
                    indate, outworkplace, first_writer, author,
                    update_log, contents, e_title, eworks_item,
                    registdate, author_id, status, al_content, e_line_id, e_line, request_comment
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);
        $stmh->execute([
            $indate, $outworkplace, $first_writer, $author,
            $update_log, $contents, $e_title, $eworks_item,
            $registdate, $author_id, $status, $al_content, $e_line_id, $e_line, $request_comment
        ]);
        $pdo->commit();

        $stmh = $pdo->prepare("SELECT num FROM {$DB}.eworks ORDER BY num DESC LIMIT 1");
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        $num = $row["num"] ?? '';

        // 첨부파일의 parentid 업데이트 (tablename 조건 추가)
        $pdo->beginTransaction();
        $sql = "UPDATE {$DB}.fileuploads SET parentid = ? WHERE parentid = ? AND tablename = 'eworks'";
        $stmh = $pdo->prepare($sql);
        $stmh->execute([$num, $timekey]);
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["error" => $e->getMessage()]);
        exit;
    } 
}

echo json_encode(["num" => $num, "mode" => $mode], JSON_UNESCAPED_UNICODE);
