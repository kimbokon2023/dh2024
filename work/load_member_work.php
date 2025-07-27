<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// POST로 전송된 이름을 가져옴
$member_name = $_POST['member_name'] ?? '';

if (!empty($member_name)) {
    // 해당 사용자의 작업일정 가져오기
    $first_day_of_month = date("Y-m-01");
    $last_day_of_month = date("Y-m-t");

    $sql = "SELECT orderdate, title, title_after, first_writer  FROM todos_work WHERE orderdate BETWEEN :first_day AND :last_day AND first_writer = :first_writer AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(':first_day', $first_day_of_month, PDO::PARAM_STR);
    $stmh->bindValue(':last_day', $last_day_of_month, PDO::PARAM_STR);
    $stmh->bindValue(':first_writer', $member_name, PDO::PARAM_STR);
    $stmh->execute();

    while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        $orderdate = htmlspecialchars($row['orderdate']);
        $first_writer = htmlspecialchars($row['first_writer']);
        $title = htmlspecialchars($row['title']);
        $title_after = htmlspecialchars($row['title_after']);

        echo '<div class="col-sm-12 mb-1">';
        echo '<div class="d-flex justify-content-start align-items-center fs-6">';
        echo '<span class="badge bg-secondary me-2">' . $first_writer  . '</span>';
        echo '<span class="badge bg-success me-2">' . $orderdate . '</span>';
        echo '<span>오전: ' . $title . ' / 오후: ' . $title_after . '</span>';
        echo '</div>';
        echo '</div>';
    }
}
?>
