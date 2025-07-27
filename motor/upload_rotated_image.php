<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/session.php');

// 오류 리포팅 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// JSON 응답 설정
header('Content-Type: application/json');

// 사용자 정의 오류 핸들러를 설정하여 모든 오류를 JSON 형식으로 반환
set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "PHP Error: $message in $file on line $line"
    ]);
    exit;
});

// 예외 핸들러를 설정하여 모든 예외를 JSON 형식으로 반환
set_exception_handler(function ($exception) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Uncaught Exception: " . $exception->getMessage()
    ]);
    exit;
});

// 이미지 파일을 저장할 디렉토리 지정
$targetDir = "uploads/";

// 클라이언트에서 전송된 이미지 파일 받기
if (isset($_FILES['rotatedImage'])) {
    $file = $_FILES['rotatedImage'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];

    $tmpNm = explode('.', basename($fileName));
    $ext = strtolower(end($tmpNm));

    $new_file_name = date("Y_m_d_H_i_s");
    $newfilename1 = $new_file_name . "_update." . $ext;      

    // 파일 저장 경로 설정
    $targetFilePath = $targetDir . $newfilename1;  // 새로운 이름 부여

    // sql DB 내용도 수정해야 한다.
    require_once("../lib/mydb.php");
    $pdo = db_connect();

    // update
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " . $DB . ".picuploads SET picname = ? WHERE picname = ? LIMIT 1";    
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $newfilename1, PDO::PARAM_STR);          
        $stmh->bindValue(2, $fileName, PDO::PARAM_STR);          
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Database update error: " . $Exception->getMessage()]);
        exit;
    }

    // 파일을 지정된 경로에 저장
    if (move_uploaded_file($fileTmpName, $targetFilePath)) {
        // 파일을 올렸으면 기존 파일은 삭제한다.
        $oldFilePath = $targetDir . $fileName;
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        echo json_encode([
            "status" => "success",
            "message" => "File uploaded successfully",
            "rotatedImageFilename" => $newfilename1,
            "targetFilePath" => $targetFilePath
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "There was an error uploading your file"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No file uploaded"]);
}
?>
