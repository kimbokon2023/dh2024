<?php
include $_SERVER['DOCUMENT_ROOT'] . '/session.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = db_connect();
    
    // DB 변수 확인
    if (!isset($DB)) {
        $DB = $_SESSION['DB'] ?? 'dbchandj';
    }
    
    // POST 데이터 받기
    $action = $_POST['action'] ?? '';
    
    if ($action === 'saveRotation') {
        $fileId = $_POST['fileId'] ?? '';
        $rotation = $_POST['rotation'] ?? 0;
        $tablename = $_POST['tablename'] ?? '';
        $parentid = $_POST['parentid'] ?? '';
        
        // 필수 파라미터 검증
        if ($fileId === '' || empty($tablename) || empty($parentid)) {
            throw new Exception('필수 파라미터가 누락되었습니다.');
        }
        
        // 회전 각도 유효성 검증 (0, 90, 180, 270만 허용)
        if (!in_array($rotation, [0, 90, 180, 270])) {
            throw new Exception('유효하지 않은 회전 각도입니다.');
        }
        
        // fileuploads 테이블에서 해당 파일 찾기
        $sql = "SELECT * FROM {$DB}.fileuploads WHERE tablename = ? AND parentid = ? AND item = 'image'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tablename, $parentid]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($files)) {
            throw new Exception('해당하는 파일을 찾을 수 없습니다.');
        }
        
        // fileId가 숫자인 경우 인덱스로 사용, 문자열인 경우 파일명으로 검색
        $targetFile = null;
        if (is_numeric($fileId)) {
            // fileId가 실제 파일 ID인지 먼저 확인
            foreach ($files as $file) {
                if ($file['id'] == $fileId) {
                    $targetFile = $file;
                    break;
                }
            }
            
            // 파일 ID로 찾지 못한 경우 인덱스로 사용
            if (!$targetFile) {
                $index = intval($fileId);
                if (isset($files[$index])) {
                    $targetFile = $files[$index];
                }
            }
        } else {
            // fileId가 파일명인 경우
            foreach ($files as $file) {
                if ($file['savename'] === $fileId) {
                    $targetFile = $file;
                    break;
                }
            }
        }
        
        if (!$targetFile) {
            throw new Exception('지정된 파일을 찾을 수 없습니다.');
        }
        
        // 회전 정보 업데이트
        $updateSql = "UPDATE {$DB}.fileuploads SET rotate = ? WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $result = $updateStmt->execute([$rotation, $targetFile['id']]);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => '회전 정보가 성공적으로 저장되었습니다.',
                'fileId' => $targetFile['id'],
                'rotation' => $rotation
            ]);
        } else {
            throw new Exception('회전 정보 저장에 실패했습니다.');
        }
        
    } else {
        throw new Exception('지원하지 않는 액션입니다.');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => '데이터베이스 오류가 발생했습니다: ' . $e->getMessage()
    ]);
}
?>  
  
  
 

