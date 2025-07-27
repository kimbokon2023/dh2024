<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$pdo = db_connect();

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';

if ($mode === 'fetch' && $num) {
    try {
        // 메모 관련 레코드 가져오기
        $sql = "SELECT * FROM recordlist WHERE num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

        // 레코드를 제대로 가져왔다면 메모 정보를 출력
        if ($row) {
            $comment = htmlspecialchars($row['comment'], ENT_QUOTES);
            $primisedate = ($row['primisedate'] === '0000-00-00') ? '' : htmlspecialchars($row['primisedate'], ENT_QUOTES);
            $vendor_name = htmlspecialchars($row['vendor_name'], ENT_QUOTES);
        }
    } catch (PDOException $Exception) {
        echo "오류: " . $Exception->getMessage();
        exit;
    }
}

// 메모를 입력할 form 구조를 반환
?>
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-center">
        <div class="card justify-content-center">
            <div class="card-header text-center">
                <span class="text-center fs-5">메모 수정</span>
            </div>
            <div class="card-body">
                <div class="row justify-content-center text-center">
                    <div class="d-flex align-items-center justify-content-center m-2">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">거래처명</td>
                                    <td class="text-center" style="width:500px;">
                                        <input type="text" class="form-control fs-6 me-1 w-90" id="vendor_name" name="vendor_name" value="<?= $vendor_name ?>" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">메모 내용</td>
                                    <td class="text-center" colspan="3">
                                        <textarea class="form-control fs-6" id="memo_comment" name="memo_comment" rows="5"><?= $comment ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">약속일</td>
                                    <td class="text-center">
                                        <input type="date" class="form-control fs-6" id="memo_primisedate" name="memo_primisedate" value="<?= $primisedate ?>">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-center">                        
                    <button type="button" id="saveMemoBtn" class="btn btn-outline-primary btn-sm me-2">저장</button>
                    <button type="button" id="closeMemoBtn" class="btn btn-outline-dark btn-sm me-2">닫기</button>
                </div>
            </div>
        </div>
    </div>
</div>
