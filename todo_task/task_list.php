<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($_SESSION["level"]) || intval($_SESSION["level"]) > 7) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}
$Get_task_num = isset($_GET['task_num']) ? $_GET['task_num'] : '';
$debug = isset($_GET['debug']) ? $_GET['debug'] : '';
$today = date("Y-m-d");
require_once($_SERVER['DOCUMENT_ROOT'] . "/load_header.php");
$titlemessage = '오늘의 할일 달력';
$version = time();
$header = isset($_GET['header']) ? $_GET['header'] : 'yes';

?> 

<title> <?=$titlemessage?>  </title>
<!-- Favicon-->    
<link rel="icon" type="image/x-icon" href="favicon.ico">
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
<link rel="apple-touch-icon" type="image/x-icon" href="favicon.ico">
<style>
	.editable-item {
		cursor: pointer;
	}
	td {
    vertical-align: top;
}	
</style>
</head>

<?php 
if($header !== 'no') {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); 
}
?>
	
<!-- 작업일지 작성자 선택 -->
<div class="container w-100">
    <div class="card mt-2">
        <div class="card-body">
		  <div class="d-flex justify-content-center align-items-center mt-1 mb-3">         
			   <h5>오늘의 할일</h5>
			   <small class="ms-5 text-muted">금일 퇴근 전 완료된 업무 체크 후 명일 할 일을 미리 기재. (업무내용 자세히 기재해야 검색기능 사용시 수월함)</small>            
		  </div>
            <div class="row">
			<?php
                require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
                $pdo = db_connect();

                // dailyworkcheck가 '작성'이고 퇴직자가 아닌 회원들만 불러오기 (quitDate 기준)
                $sql = "SELECT name FROM member WHERE dailyworkcheck = '작성' AND (quitDate IS NULL OR quitDate = '0000-00-00')";
                $stmh = $pdo->prepare($sql);
                $stmh->execute();
                $members = $stmh->fetchAll(PDO::FETCH_ASSOC);

                array_unshift($members, array('name' => '전체'));
				echo '<div class="d-flex justify-content-center align-items-center">';
				foreach ($members as $member) {
					$name = htmlspecialchars($member['name']);					
					$id = 'member_' . htmlspecialchars($member['name']); // input과 label 연결을 위한 id 설정
					$checked = ($name === '전체') ? 'checked' : ''; // '전체'가 기본 선택되도록 설정										
					echo '<input type="radio" id="' . $id . '" name="selected_member" value="' . $name . '" class="form-check-input ms-3 me-1 member-radio" ' . $checked . ' data-member="' . $name . '">';
					echo '<label for="' . $id . '" class="form-check-label fs-6 ">' . $name . '</label>'; // label 태그로 감싸서 클릭 가능하게 변경
				}
				echo '</div>';
			?>
            </div>
        </div>
    </div>
</div>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/todo_task/load_task.php"); ?>
<div class="container-fluid">
    <?php include $_SERVER['DOCUMENT_ROOT'] .'/footer.php'; ?>
</div>
</body>
</html>