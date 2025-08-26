<?php
ob_start(); // 출력 버퍼링
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($level) || intval($level) > 7 || empty($user_name)) {
    header("Location:https://dh2024.co.kr/login/login_form.php");
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// // 세션의 만료 시간을 확인합니다.
// $expiryTime = ini_get('session.gc_maxlifetime');
// $remainingTime = 0;

// // 세션의 만료 시간과 현재 시간을 비교하여 남은 시간을 계산합니다.
// if (isset($_SESSION['LAST_ACTIVITY'])) {
//   $lastActivity = $_SESSION['LAST_ACTIVITY'];
//   $currentTime = time();
//   $elapsedTime = $currentTime - $lastActivity;
  
//   if ($elapsedTime < $expiryTime) {
//     $remainingTime = $expiryTime - $elapsedTime;
//   }
// }

// 세션의 남은 시간을 반환합니다.
// echo $expiryTime;
$today = date("Y-m-d");

require_once($_SERVER['DOCUMENT_ROOT'] . "/load_header.php");

// DH모터 자료 (접수/출고 등) 가져오기
include "load_motor_info.php";
?>

<script src="<?$root_dir?>/js/todolist.js?v=<?=time()?>"></script> 
 
<title> (주)대한 DH모터 </title> 
  
<!-- Favicon-->	
<link rel="icon" type="image/x-icon" href="favicon.ico">   <!-- 33 x 33 -->
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">    <!-- 144 x 144 -->
<link rel="apple-touch-icon" type="image/x-icon" href="favicon.ico">
 
 <style>

.shop-header {
    background-image: linear-gradient(to right, #0090f7, #ba62fc, #f2416b);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}	
/* 모바일 레이아웃 스타일 */
@media (max-width: 65px) {
    .mobile-layout .btn {
        font-size: 5rem;
        padding: 0.75rem;
    }
    .mobile-layout .radio-label .badge {
        font-size: 5rem;
        padding: 2rem;
    }
}
    /* 라디오 버튼 크기를 10배로 키움 */
    .filter-radio {
        width: 18px; /* 기본 크기 지정 */
        height: 18px; /* 기본 크기 지정 */
        transform: scale(1.1); /* 크기를 10배로 확대 */
        transform-origin: 0 0; /* 좌측 상단을 기준으로 확대 */
        margin-right: 5px; /* 확대된 크기에 맞게 여백 조정 */
    }

    /* 라디오 버튼이 너무 커지면 상하좌우 여백이 부족해지므로 조정 */
    .radio-label {
        display: flex;
        align-items: center;
        margin-bottom: 10px; /* 각 라디오 버튼 사이에 적당한 여백 추가 */
    }
	
#todo-list td {
    vertical-align: top;
	font-size : 13px;
	padding : 2px;
}	

</style> 
</head> 
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>
	
 <?php if($chkMobile==false) { ?>
	<div class="container">     
 <?php } else { ?>
 	<div class="container-fluid">     
	<?php } ?>	 

<?php
    $tabs = array(
		"알림" => 0,
		"작성" => 1,
		"상신" => 2,
		"미결" => 3,
		"진행" => 4,
		"결재" => 5
    );

?>

<div class="sideBanner">
    <span class="text-center text-dark">&nbsp; 전자결재 </span>
     
	<?php	
		// print $eworks_level  ;		
		foreach ($tabs as $label => $tabId) {
			$badgeId = "badge" . $tabId;	
			
    ?>
	<div class="mb-1 mt-1">
	 <?php if ($label !== "알림") 
		{
			
			if($eworks_level && ($tabId>=3) )
			{
			  print '<button type="button" class="btn btn-dark rounded-pill" onclick="seltab(' . $tabId . '); "> ';
			  echo $label; 
			  print '<span class="badge badge-pill badge-dark" id="' . $badgeId . '"></span>';				  
			} 
			else if (!$eworks_level)  // 일반결재 상신하는 그룹
			{				
			  print '<button type="button" class="btn btn-dark rounded-pill" onclick="seltab(' . $tabId . '); "> ';
			  echo $label; 
			  print '<span class="badge badge-pill badge-dark" id="' . $badgeId . '"></span>';				  
			} 
			
		}
		else 
		{		
			   print '<div id="bellIcon"> 🔔결재 </div>';					
		}
		
		?>
	</button>
	</div>
<?php
}
?>
</div>
  
</div>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data" >	

<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>" >
<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>" >

<!-- todo모달 컨테이너 -->
<div class="container-fluid">
	<!-- Modal -->
	<div id="todoModal" class="modal">
		<div class="modal-content"  style="width:800px;">
			<div class="modal-header">
				<span class="modal-title">할일</span>
				<span class="todo-close">&times;</span>
			</div>
			<div class="modal-body">
				<div class="custom-card"></div>
			</div>
		</div>
	</div>
</div>

<?php include 'mymodal.php'; ?>  

 <?php if($chkMobile==false) { ?>
	<div class="container">        
 <?php } else { ?>
 	<div class="container-fluid">     
	<?php } ?>

<div class="row d-flex mb-1 mt-1">		
	<div class="col-sm-2">
		<button  type="button" id="board_view" class="btn btn-info btn-sm me-2 fw-bold"> <i class="bi bi-chevron-down"></i> </button>             
	</div>		
	<div class="col-sm-8">
		<div class="d-flex justify-content-center align-items-center"> 	
			<span class="fw-bold shop-header fs-5">
				<?php
				// title.txt 파일에서 목표 문구 읽기
				$title_file = __DIR__ . '/title/title.txt';
				$goal_text = '2025년 목표';
				
				if (file_exists($title_file)) {
					$content = file_get_contents($title_file);
					$content = trim($content);
					if (!empty($content)) {
						$goal_text = $content;
					}
				}
				echo htmlspecialchars($goal_text);
				?>
			</span> 	
		</div>
	</div>		
	<div class="col-sm-2">
	  <div class="d-flex justify-content-end" > 
	  (주) DH대한
	  </div>
	</div>
</div>
<div class="row d-flex board_list"  >	
		
	<!-- 전일 경영 Report -->
	<div class="col-sm-3 board_list" >

		 <!-- 금일 현황 -->	
		 <div class="card justify-content-center  my-card-padding">
			<div class="card-header text-center  my-card-padding fs-6 fw-bold ">
				     금일 현황
				</div>
				<div class="card-body  my-card-padding">	
				<table class="table table-bordered table-hover table-sm">									
					<thead class="align-middle">	
						<tr>									
					<th class="text-center w-25 fs-6"> 접수 </th>									
					<th class="text-center w-25 fs-6"> 출고예정 </th>									
					<th class="text-center w-25 fs-6"> 출고완료 </th>													
					<th class="text-center w-25 fs-6"> 회수예정 </th>													
						</tr>
					</thead>					
					<tbody class="align-middle">					 
							<tr onclick="window.location.href='./motor/list.php'" style="cursor:pointer;">
								<td class="text-center">                                                                                   
								<span class="text-muted  fs-6"> <span class="text-center badge text-bg-dark" id="motor_registedate" >  </span>  </span>                                            
								</td>
								<td class="text-center">                                                                                    
								<span class="text-muted  fs-6"> <span class="badge text-bg-primary" id="motor_duedate" >  </span>  </span>                                            
								</td>
								<td class="text-center">                                                                                   
								<span class="text-muted  fs-6"> <span class="badge text-bg-danger" id="motor_outputdonedate" >  </span>  </span>                                            
								</td>
								<td class="text-center">                                                                                   
								<span class="text-muted  fs-6"> <span class="badge text-bg-warning" id="motor_returndue" >  </span>  </span>                                            
								</td>
							</tr>
					</tbody>
				</table>												   
			</div> 
		</div>				            	
							
		<div class="card justify-content-center  my-card-padding" >
			<div class="card-header text-center  my-card-padding">
				<a href="/motor/statistics.php?header=header"> 매출 통계 </a>
			</div>
			<div class="card-body  my-card-padding">	
				<?php include 'load_statistics.php'; ?>
			</div>
		</div>			
	</div>  <!-- end of col-sm-4 -->
	
	<div class="col-sm-3 board_list">   
		<!-- 업무요청사항 -->	
		<div class="card justify-content-center">		
			<div class="card-header  text-center fs-6 badge bg-danger" >
				<a class="text-white" href="./workprocess/list.php"> 업무요청사항(진행중) </a>
			</div>
		<div class="card-body  my-card-padding" >					
				<?php   
				// 주요안건
				$now = date("Y-m-d",time()) ;				  
				$a=" order by num desc ";  				  
				$sql="select * from dbchandj.workprocess " . $a; 		
				$stmh = $pdo->query($sql);
				$total_row = $stmh->rowCount();
				
				// 현재 날짜를 DateTime 객체로 가져옵니다.
				$currentDate = new DateTime();
				
				if($total_row > 0) {
					echo '<table class="table table-hover table-sm">';
					
					while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
						
						// 처리완료는 제외시킴
				     if($row['doneDate'] == '0000-00-00' or $row['doneDate'] == '')
					   {
						// 데이터의 등록 날짜를 DateTime 객체로 가져옵니다.
						$dataDate = new DateTime($row["regist_day"]);
						
						// 날짜 차이를 계산합니다.
						$interval = $currentDate->diff($dataDate)->days;

						// 이미지 태그 초기화
						$newImage = '';

						// 7일 이내면 이미지를 추가합니다.
						if($interval < 10) {
							$newImage = '<img src="./img/new-gif.gif" style="width:8%;" alt="New" /> &nbsp;';
						}
						 
						  $item_num = $row["num"]; 
						  $sqlsub="select * from dbchandj.workprocess_ripple where parent=$item_num";
						  $stmh1 = $pdo->query($sqlsub); 
						  $num_ripple=$stmh1->rowCount(); 
						  
						  if($row['doneDate'] !== '0000-00-00' and $row['doneDate'] !== '')
								$resultComment ='<span class="text-danger">처리완료</span>';
							else
								$resultComment ='<span class="text-success">진행중</span>';
						
						$chargedPerson =  '&nbsp; &nbsp; <span class="text-primary"> 담당: '. $row['chargedPerson'] . ' </span> ';

						// 데이터-속성 추가하여 공지의 ID 또는 필요한 정보를 저장
						print '<td class="text-start" style="cursor:pointer;" onclick="viewBoard(\'workprocess\', ' .  $item_num  . ');return false;"> &nbsp;  ' . $newImage . $row["subject"] ;
						   if($num_ripple>0)
								echo ' &nbsp; <span class="badge bg-dark "> ' . $num_ripple . ' </span> ' . $chargedPerson . ' (' . $resultComment . ') </td> ';						
							  else
								  echo  ' ' . $chargedPerson . ' (' . $resultComment . ')  </td> ';

							echo '</tr>'; // 테이블 행 종료
						     }
					      }
						echo '</table>';
					} else {
						echo '<span> &nbsp; </span>';
					}
					?>  
				
			</div>   
		</div> 			  				
		<!-- 전체 공지 -->	
		<div class="card justify-content-center">		
			<div class="card-header  text-center  my-card-padding">
				<a href="./notice/list.php"> 전체 공지 </a>
			</div>
			 <div class="card-body  my-card-padding" >					
				<?php   
				//전체 공지사항
				$now = date("Y-m-d",time()) ;				  
				$a="   where noticecheck='y' order by num desc ";  				  
				$sql="select * from dbchandj.notice " . $a; 		
				$stmh = $pdo->query($sql);
				$total_row = $stmh->rowCount();
				
				// 현재 날짜를 DateTime 객체로 가져옵니다.
				$currentDate = new DateTime();
				
				if($total_row > 0) {
					echo '<table class="table table-hover">';
					
					while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
						// 데이터의 등록 날짜를 DateTime 객체로 가져옵니다.
						$dataDate = new DateTime($row["regist_day"]);
						
						// 날짜 차이를 계산합니다.
						$interval = $currentDate->diff($dataDate)->days;

						// 이미지 태그 초기화
						$newImage = '';

						// 7일 이내면 이미지를 추가합니다.
						if($interval < 7) {
							$newImage = '<img src="./img/new-gif.gif" style="width:10%;" alt="New" /> &nbsp;';
						}
						 
						  $item_num = $row["num"]; 
						  $sqlsub="select * from dbchandj.notice_ripple where parent=$item_num";
						  $stmh1 = $pdo->query($sqlsub); 
						  $num_ripple=$stmh1->rowCount(); 

						// 데이터-속성 추가하여 공지의 ID 또는 필요한 정보를 저장
						print '<td class="text-start" style="cursor:pointer;" onclick="viewBoard(\'notice\', ' .  $item_num  . ');return false;"> &nbsp;  ' . $newImage . $row["subject"] ;

						   if($num_ripple>0)
								echo ' &nbsp; <span class="badge bg-dark "> ' . $num_ripple . ' </span> </td> ';						
							  else
								  echo  '</td> ';

							echo '</tr>'; // 테이블 행 종료
						}

						echo '</table>';
					} else {
						echo '<span> &nbsp; </span>';
					}
					?>  
				
	</div>   
	</div> 
	
	<!-- 새소식 -->	
	<div class="card justify-content-center">		
		<div class="card-header  text-center  my-card-padding">
			<a href="./notice/list.php"> 새소식 </a>
		</div>
	<div class="card-body  my-card-padding">	
	<table class="table table-bordered table-hover ">
		<tbody>				     
		<?php   
		//공지사항
		$now = date("Y-m-d", time());

		// 2주일 전 날짜 계산
		$oneWeekAgo = date("Y-m-d", strtotime("-20 week", strtotime($now)));			// 20주전 정보		
		$endOfDay = date("Y-m-d 23:59:59", time());
		// 전체 공지된 내용은 제외한다.
		$a = " WHERE regist_day BETWEEN '$oneWeekAgo' AND '$endOfDay' AND noticecheck<>'y' ORDER BY num DESC limit 5";
		$sql = "SELECT * FROM dbchandj.notice" . $a;

		$stmh = $pdo->query($sql);
		$total_row = $stmh->rowCount();


		// 현재 날짜를 DateTime 객체로 가져옵니다.
		$currentDate = new DateTime();					
		if($total_row > 0) {						
		print '<tr>';				
		print '<td class="align-middle" rowspan="' . ($total_row) . '" style="width:20%;" onmouseover="this.style.backgroundColor=\'initial\';" onmouseout="this.style.backgroundColor=\'initial\';"> 공지<br> 사항 </td> ';

		while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			// 데이터의 등록 날짜를 DateTime 객체로 가져옵니다.
			$dataDate = new DateTime($row["regist_day"]);
			
			// 날짜 차이를 계산합니다.
			$interval = $currentDate->diff($dataDate)->days;

			// 이미지 태그 초기화
			$newImage = '';

			// 7일 이내면 이미지를 추가합니다.
			if($interval < 7) {
				$newImage = '<img src="./img/new-gif.gif" style="width:10%;" alt="New" /> &nbsp;';
			}
			// 데이터-속성 추가하여 공지의 ID 또는 필요한 정보를 저장
			print '<td class="text-start" ';
			
			  $item_num = $row["num"]; 
			  $sqlsub="select * from dbchandj.notice_ripple where parent=$item_num";
			  $stmh1 = $pdo->query($sqlsub); 
			  $num_ripple=$stmh1->rowCount(); 

			// 데이터-속성 추가하여 공지의 ID 또는 필요한 정보를 저장
			print '<span   style="cursor:pointer;" onclick="viewBoard(\'notice\', ' .  $item_num  . ');"> &nbsp;  ' . $newImage . $row["subject"] . '</span> ';
			   if($num_ripple>0)
					echo '<span class="badge bg-dark "> '.$num_ripple.' </span> ';						
			print '</span> </td> </tr>';
		}
		} 

		//자료실
		$now = date("Y-m-d", time());

		// // 1주일 전 날짜 계산
		$oneWeekAgo = date("Y-m-d", strtotime("-3 week", strtotime($now)));			// 3주전 정보		
		$endOfDay = date("Y-m-d 23:59:59", time());
		$a = " WHERE regist_day BETWEEN '$oneWeekAgo' AND '$endOfDay' ORDER BY num DESC";

		$sql = "SELECT * FROM dbchandj.qna" . $a;

		$stmh = $pdo->query($sql);
		$total_row = $stmh->rowCount();


		// 현재 날짜를 DateTime 객체로 가져옵니다.
		$currentDate = new DateTime();					
		if($total_row > 0) {						
		print '<tr>';				
		print '<td class="align-middle no-hover" rowspan="' . ($total_row) . '" style="width:20%;"  onmouseover="this.style.backgroundColor=\'initial\';" onmouseout="this.style.backgroundColor=\'initial\';"> 자료실 </td> ';					
		while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			// 데이터의 등록 날짜를 DateTime 객체로 가져옵니다.
			$dataDate = new DateTime($row["regist_day"]);
			
			// 날짜 차이를 계산합니다.
			$interval = $currentDate->diff($dataDate)->days;

			// 이미지 태그 초기화
			$newImage = '';

			// 7일 이내면 이미지를 추가합니다.
			if($interval < 7) {
				$newImage = '<img src="./img/new-gif.gif" style="width:10%;" alt="New" /> &nbsp;';
			}
			// 데이터-속성 추가하여 공지의 ID 또는 필요한 정보를 저장
			print '<td class="text-start" ';
			print ' onclick="viewBoard(\'qna\', ' . $row["num"] . ');">' . $newImage . $row["subject"] . '</td>';
			print '</tr>';
		}
		} 
		?>  
			</tbody>
			</table>
		</div>   
	</div>               	
	          						
	</div>	<!-- end of col-sm-4 -->

	<div class="col-sm-3 board_list">	


	<!-- 지출결의서 -->
	<?php
		$title_message = '지출결의서';
		$tablename     = 'eworks';

		// 사용자 레벨과 이름 확인
		$user_level = $_SESSION["level"] ?? '';
		$user_name = $_SESSION["name"] ?? '';
		$is_admin = ($user_level == "1" || $user_level == 1);

		// 법인카드 목록 가져오기
		$jsonFile = $_SERVER['DOCUMENT_ROOT'] . '/account/cardlist.json';
		$cards = [];
		if (file_exists($jsonFile)) {
			$jsonContent = file_get_contents($jsonFile);
			$cards = json_decode($jsonContent, true);
			if (!is_array($cards)) {
				$cards = [];
			}
		}

		// 사용자 이름으로 카드번호 찾기 함수
		function getUserCards($userName, $cardList) {
			$userCards = [];
			foreach ($cardList as $card) {
				if (isset($card['user']) && strpos($card['user'], $userName) !== false) {
					$userCards[] = $card['number'];
				}
			}
			return $userCards;
		}

		// 사용자 카드번호 목록 가져오기 (관리자가 아닌 경우)
		$userCardNumbers = [];
		if (!$is_admin && !empty($user_name)) {
			$userCardNumbers = getUserCards($user_name, $cards);
		}

		// 사용자 레벨에 따른 추가 조건
		$userCondition = "";
		if (!$is_admin && !empty($user_name)) {
			if (!empty($userCardNumbers)) {
				$cardConditions = [];
				foreach ($userCardNumbers as $cardNo) {
					$cardConditions[] = "companyCard LIKE '%" . trim($cardNo) . "%'";
				}
				if (!empty($cardConditions)) {
					$userCondition = " AND (" . implode(" OR ", $cardConditions) . ")";
				}
			} else {
				// 사용자 카드가 없으면 빈 결과
				$userCondition = " AND 1=0";
			}
		}

		// 오늘 날짜, 3개월 전
		$now            = date("Y-m-d");
		$threeMonthsAgo = date("Y-m-d", strtotime("-3 months", strtotime($now)));
		$endOfDay       = $now;

		// 최근 3개월·삭제되지 않고 eworks_item='지출결의서'인 데이터 7건
		$where = " WHERE indate BETWEEN '$threeMonthsAgo' AND '$endOfDay'
				AND (is_deleted IS NULL OR is_deleted='0')
				AND eworks_item='지출결의서' 
				" . $userCondition . "
				ORDER BY indate DESC
				LIMIT 15";

		$sql       = "SELECT * FROM {$DB}.{$tablename}" . $where;
		$stmh      = $pdo->query($sql);
		$total_row = $stmh->rowCount();
		?>
		<!-- 지출결의서 -->
		<div class="card justify-content-center">
		<div class="card-header text-center my-card-padding">
			<a href="./askitem_ER/list.php?header=header"><?=$title_message?></a>
			<?php if (!$is_admin): ?>
				<span class="text-warning small ms-2">🔒 본인 카드만</span>
			<?php endif; ?>
		</div>

		<table class="table table-bordered table-hover table-sm">
			<tbody>
			<?php if ($total_row > 0): ?>
			<thead class="table-secondary">
				<tr>
				<th class="text-center">작성일</th>
				<th class="text-center">제목</th>
				<th class="text-center">금액</th>
				<th class="text-center">결재완료</th>
				</tr>
			</thead>
			<?php while ($row = $stmh->fetch(PDO::FETCH_ASSOC)):
				// 작성일
				$indate = $row['indate'] ?? '';
				$status      = $row['status']          ?? '';
				$e_confirm   = $row['e_confirm']       ?? '';
				$e_line_id   = $row['e_line_id']       ?? '';
				$formattedDate = explode('-', $indate, 2)[1] ?? $indate;

				// expense_data JSON 파싱
				$expenseData = json_decode($row['expense_data'] ?? '[]', true);
				if (!is_array($expenseData)) $expenseData = [];

				// 제목(첫 항목 + 외 N건)
				$items = [];
				$totalAmount = 0;
				foreach ($expenseData as $exp) {
				if (!empty($exp['expense_item'])) {
					$items[] = $exp['expense_item'];
				}
				if (!empty($exp['expense_amount'])) {
					$totalAmount += intval(str_replace(',', '', $exp['expense_amount']));
				}
				}
				if (count($items) > 1) {
				$titleShort = $items[0] . ' 외 ' . (count($items) - 1) . '건';
				} elseif (count($items) === 1) {
				$titleShort = $items[0];
				} else {
				$titleShort = '';
				}

				// 결재 완료 표시
				$approvedMark = ( ($status === 'end' && !empty($e_confirm)) || empty($e_line_id) ) ? '✅' : '';
			?>
			<tr onclick="viewBoard('지출결의서', <?=$row['num']?>); return false;" style="cursor:pointer;">
				<td class="text-center"><?=$formattedDate?></td>
				<td class="text-center"><?=htmlspecialchars(mb_substr($titleShort, 0, 10))?></td>
				<td class="text-end"><?=number_format($totalAmount)?></td>
				<td class="text-center"><?=$approvedMark?></td>
			</tr>
			<?php endwhile; ?>
			<?php else: ?>
			<tr>
				<td colspan="4" class="text-center text-muted">
					<?php if (!$is_admin): ?>
						본인 카드의 지출결의서가 없습니다.
					<?php else: ?>
						지출결의서가 없습니다.
					<?php endif; ?>
				</td>
			</tr>
			<?php endif; ?>
			</tbody>
		</table>
		</div> 		<!-- end of 지출결의서 -->	

	</div>    <!-- end of col-sm-3 -->	
	
	<div class="col-sm-3 board_list">	
		<!-- 모터 개발일지 -->	
		<div class="card justify-content-center">		
			<div class="card-header  text-center  my-card-padding">
				<a href="./motor_rnd/list.php"> 모터 개발일지 </a>
			</div>
		<div class="card-body  my-card-padding">	
		<table class="table table-bordered table-hover ">
			<tbody>				     
			<?php   
			// 모터 개발일지
			$now = date("Y-m-d", time());

			// // 1주일 전 날짜 계산
			$oneWeekAgo = date("Y-m-d", strtotime("-20 week", strtotime($now)));			// 20주전 정보		
			$endOfDay = date("Y-m-d 23:59:59", time());
			$a = " WHERE regist_day BETWEEN '$oneWeekAgo' AND '$endOfDay' ORDER BY num DESC limit 5";

			$sql = "SELECT * FROM motor_rnd" . $a;

			$stmh = $pdo->query($sql);
			$total_row = $stmh->rowCount();


			// 현재 날짜를 DateTime 객체로 가져옵니다.
			$currentDate = new DateTime();					
			if($total_row > 0) {								
				while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
					// 데이터의 등록 날짜를 DateTime 객체로 가져옵니다.
					$dataDate = new DateTime($row["regist_day"]);
					
					// 날짜 차이를 계산합니다.
					$interval = $currentDate->diff($dataDate)->days;

					// 이미지 태그 초기화
					$newImage = '';
					print '<tr>';						

					// 7일 이내면 이미지를 추가합니다.
					if($interval < 7) {
						$newImage = '<img src="./img/new-gif.gif" style="width:7%;" alt="New" /> &nbsp;';
					}
					// 데이터-속성 추가하여 공지의 ID 또는 필요한 정보를 저장
					print '<td class="text-start" ';
					print ' onclick="viewBoard(\'motor_rnd\', ' . $row["num"] . ');">' . $newImage . $row["subject"] . '</td>';
					print '</tr>';
				}
			} 
			?>  
				</tbody>
				</table>
			</div>   
		</div>    	
				
		<!-- 개발일지 -->	
		<div class="card justify-content-center">		
			<div class="card-header  text-center  my-card-padding">
				<a href="./rnd/list.php"> 전산 개발일지 </a>
			</div>
		<div class="card-body  my-card-padding">	
		<table class="table table-bordered table-hover ">
			<tbody>				     
			<?php   
			// 개발일지
			$now = date("Y-m-d", time());

			// // 1주일 전 날짜 계산
			$oneWeekAgo = date("Y-m-d", strtotime("-20 weeks", strtotime($now)));			// 20주전 정보		
			$endOfDay = date("Y-m-d 23:59:59", time());
			$a = " WHERE regist_day BETWEEN '$oneWeekAgo' AND '$endOfDay' ORDER BY num DESC limit 5";

			$sql = "SELECT * FROM dbchandj.rnd" . $a;

			$stmh = $pdo->query($sql);
			$total_row = $stmh->rowCount();


			// 현재 날짜를 DateTime 객체로 가져옵니다.
			$currentDate = new DateTime();					
			if($total_row > 0) {								
				while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
					// 데이터의 등록 날짜를 DateTime 객체로 가져옵니다.
					$dataDate = new DateTime($row["regist_day"]);
					
					// 날짜 차이를 계산합니다.
					$interval = $currentDate->diff($dataDate)->days;

					// 이미지 태그 초기화
					$newImage = '';
					print '<tr>';						

					// 7일 이내면 이미지를 추가합니다.
					if($interval < 7) {
						$newImage = '<img src="./img/new-gif.gif" style="width:7%;" alt="New" /> &nbsp;';
					}
					// 데이터-속성 추가하여 공지의 ID 또는 필요한 정보를 저장
					print '<td class="text-start" ';
					print ' onclick="viewBoard(\'rnd\', ' . $row["num"] . ');">' . $newImage . $row["subject"] . '</td>';
					print '</tr>';
				}
			} 
			?>  
				</tbody>
				</table>
			</div>   
		</div>    	
	</div>    <!-- end of col-sm-3 -->	
</div>    <!-- end of row -->           	    
</div>               	    
</div>               
</div>
</div>

<!-- 오늘의 할일 -->
<?php if($chkMobile==false) { ?>
    <div class="container">     
<?php } else { ?>
    <div class="container-fluid">      
<?php } ?>     
<div class="card mt-1">
<div class="card-body">
    <div class="row d-flex ">
        <!-- Calendar Controls -->
        <div class="col-sm-5">
		  <div class="d-flex justify-content-start align-items-center ">
            <button  type="button" id="employee_tasks_view" class="btn btn-info btn-sm me-2 fw-bold"> <i class="bi bi-chevron-down"></i> </button>			
			<h5> <오늘 할일> </h5>
		  </div>
        </div>
        <div class="col-sm-7">
        </div>               		
    </div>	
    <div id="employee_tasks-calendar-container">	
		<?php
		// 오늘 날짜의 직원 할일 목록 표시
		$pdo = db_connect();
		
		// 오늘 날짜
		$today = date('Y-m-d');
		
		// 세션에서 사용자 정보 가져오기
		$current_user = $_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? '';
		$current_department = $_SESSION['part'] ?? $_SESSION['department'] ?? $_SESSION['dept'] ?? $_SESSION['user_department'] ?? '';
		
		// SQL 조건 구성
		$where_conditions = ["(is_deleted = 'N' OR is_deleted IS NULL)"];
		$params = [];
		
		// 오늘 날짜 조건 추가
		$where_conditions[] = "task_date = :today";
		$params[':today'] = $today;
		
		// 현재 사용자가 특정 부서에 속해있다면 해당 부서의 할일만 표시
		// if (!empty($current_department)) {
		// 	$where_conditions[] = "department = :current_department";
		// 	$params[':current_department'] = $current_department;
		// }
		
		$where_clause = implode(' AND ', $where_conditions);
		
		// 오늘 할일 데이터 조회
		$sql = "SELECT * FROM {$DB}.employee_tasks WHERE {$where_clause} ORDER BY created_at DESC";
		$stmt = $pdo->prepare($sql);
		foreach ($params as $key => $value) {
			$stmt->bindValue($key, $value);
		}
		$stmt->execute();
		$today_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
		?>
		
		<div class="card shadow-sm mt-2 mb-2">
			<div class="card-header bg-info text-white">
				<h5 class="mb-0">
					<i class="bi bi-calendar-check"></i> 목록 (<?= date('Y-m-d') ?>)
				</h5>
			</div>
			<div class="card-body">
				<?php if (empty($today_tasks)): ?>
					<div class="text-center py-4">
						<i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
						<p class="text-muted mt-2">오늘 등록된 할일이 없습니다.</p>
					</div>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead class="table-light">
								<tr>
									<th>직원명</th>
									<th>부서</th>
									<th>할일 개수</th>
									<th>완료율</th>
									<th>지연 할일</th>
									<th>메모</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($today_tasks as $task): 
									// JSON 데이터 파싱
									$task_items = json_decode($task['tasks'], true) ?? [];
									$total_tasks = count($task_items);
									$completed_tasks = 0;
									$elapsed_tasks = 0;
									
									foreach ($task_items as $item) {
										if ($item['is_completed'] ?? false) {
											$completed_tasks++;
										}
										// 경과일이 있는 할일 카운트
										if (!empty($item['original_date'])) {
											$original_date = new DateTime($item['original_date']);
											$today_date = new DateTime();
											$elapsed = $today_date->diff($original_date)->days;
											if ($elapsed > 0) {
												$elapsed_tasks++;
											}
										}
									}
									
									$completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
								?>
								<tr class="task-row-clickable" data-task-num="<?= $task['num'] ?>" style="cursor: pointer;">
									<td class="text-center"><?= htmlspecialchars($task['employee_name']) ?></td>
									<td class="text-center">
										<span class="badge bg-secondary"><?= htmlspecialchars($task['department'] ?? '-') ?></span>
									</td>
									<td class="text-center">
										<span class="badge bg-primary"><?= $total_tasks ?>개</span>
									</td>
									<td class="text-center">
										<?php if ($completion_rate == 100): ?>
											<span class="badge bg-success"><?= $completion_rate ?>%</span>
										<?php elseif ($completion_rate >= 50): ?>
											<span class="badge bg-warning"><?= $completion_rate ?>%</span>
										<?php else: ?>
											<span class="badge bg-danger"><?= $completion_rate ?>%</span>
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php if ($elapsed_tasks > 0): ?>
											<span class="badge bg-danger"><?= $elapsed_tasks ?>개</span>
										<?php else: ?>
											<span class="badge bg-success">0개</span>
										<?php endif; ?>
									</td>
									<td class="text-start">
										<?php if (!empty($task['memo'])): ?>
											<span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?= htmlspecialchars($task['memo']) ?>">
												<?= htmlspecialchars($task['memo']) ?>
											</span>
										<?php else: ?>
											<span class="text-muted">-</span>
										<?php endif; ?>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
				
				<div class="mt-3">
					<button type="button" class="btn btn-primary" onclick="openTaskList()">
						<i class="bi bi-list"></i> 전체 할일 목록(이동)
					</button>
					<!-- <button type="button" class="btn btn-success" onclick="openNewTask()">
						<i class="bi bi-plus-circle"></i> 새 할일 등록
					</button> -->
				</div>
			</div>
		</div>
	</div>	
	</div>	
	</div>
</div>
 

<!-- todo Calendar -->
<?php if($chkMobile==false) { ?>
    <div class="container">     
<?php } else { ?>
    <div class="container-fluid">      
<?php } ?>     
<div class="card mt-1">
<div class="card-body">
    <div class="row d-flex ">
        <!-- Calendar Controls -->
        <div class="col-sm-5">
		  <div class="d-flex justify-content-start align-items-center ">
            <button  type="button" id="todo_view" class="btn btn-info btn-sm me-2 fw-bold"> <i class="bi bi-chevron-down"></i> </button>            
            <button  type="button" id="calendar_view" class="btn btn-info btn-sm me-2" > <i class="bi bi-calendar-event"></i> </i> </button>            
			<h5> <월간상세일정> ※일지작성은 [업무일지]메뉴 </h5>
		  </div>
        </div>
        <div class="col-sm-7">
            <div class="d-flex justify-content-start align-items-center mb-2">
                <button type="button" id="todo-prev-month" class="btn btn-info  btn-sm me-2"><i class="bi bi-arrow-left"></i></button>
                 <span id="todo-current-period" class="text-dark fs-6 me-2"></span>
                <button  type="button" id="todo-next-month" class="btn btn-info btn-sm me-2"> <i class="bi bi-arrow-right"></i></button>
                <button  type="button" id="todo-current-month" class="btn btn-outline-info fw-bold btn-sm me-5"> <?php echo date("m",time()); ?> 월</button>                
            </div>  
		 <!-- 라디오 버튼 추가 -->
				<div class="d-flex justify-content-end align-items-center">
					<label class="radio-label">
						<input type="radio" name="filter" id="filter_all" class="filter-radio " checked>
						<span class="checkmark"></span> <span class="badge bg-dark me-2" > 전체 </span>
					</label>
					<label class="radio-label">
						<input type="radio" name="filter" id="filter_al" class="filter-radio">
						<span class="checkmark"></span> <span class="text-dark me-2" > 연차 </span>
					</label>
					<!-- <label class="radio-label">
						<input type="radio" name="filter" id="filter_workrecord" class="filter-radio">
						<span class="checkmark"></span> <span class="text-dark me-2" > 업무일지</span>
					</label> -->
					<label class="radio-label">
						<input type="radio" name="filter" id="filter_as" class="filter-radio">
					   <span class="checkmark"></span> <span class="badge bg-warning me-2" > AS </span>
					</label>
					<!-- <label class="radio-label">
						<input type="radio" name="filter" id="filter_etc" class="filter-radio">
						<span class="checkmark"></span> <span class="text-secondary me-2" > 해야 할일 </span>
					</label> -->
					<label class="radio-label">
						<input type="radio" name="filter" id="filter_meeting" class="filter-radio">
						<span class="checkmark"></span> <span class="text-secondary me-2" > 회의록 </span>
					</label>
				</div>			
        </div>               		
    </div>	
    <div id="todo-calendar-container"></div>	
</div>
</div>
</div>
 
 <?php if($chkMobile==false) { ?>
	<div class="container">     
 <?php } else { ?>
 	<div class="container-fluid">     
	<?php } ?>
    
<?php     
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	
// AS
   $a = " where (asendday='0000-00-00' or asendday IS NULL) and is_deleted IS NULL order by num desc ";    
   $sql="select * from dbchandj.as " . $a; 					
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $total_row=$stmh->rowCount();
   if($total_row>0) 	  
      include "./load_AS.php";
	
   // 금일출고
   $a = " where deadline='$now' order by num desc ";    
   $sql="select * from dbchandj.motor " . $a; 					
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $total_row=$stmh->rowCount();
   if($total_row>0) 
        include "./load_motor.php";
	else
		include "./load_null.php";

   // 로트번호 미등록	   
   include "./load_lotnum.php";	
 ?>
    
</div>
 
 <?php if($chkMobile==false) { ?>
	<div class="container">     
 <?php } else { ?>
 	<div class="container-fluid">     
	<?php } ?>

<?php include 'footer.php'; ?>

</div> 
</div>
</div> <!-- container-fulid end -->
</form> 

<script>
$(function() {
    // 로딩 오버레이 숨기기
    $('#loadingOverlay').hide();

    // viewBoard 함수
    window.viewBoard = function(sel, num) {
        var url, title, w, h;
        switch(sel) {
            case 'notice':
                url   = "./notice/view.php?num=" + num + "&tablename=notice";
                title = '공지사항'; w = 1300; h = 850;
                break;
            case 'qna':
                url   = "./qna/view.php?num=" + num + "&menu=no&page=1&tablename=qna";
                title = '자료실'; w = 1500; h = 900;
                break;
            case 'motor_rnd':
                url   = "./motor_rnd/view.php?num=" + num + "&menu=no&tablename=motor_rnd";
                title = '모터 개발일지'; w = 1300; h = 900;
                break;
            case 'rnd':
                url   = "./rnd/view.php?num=" + num + "&menu=no&tablename=rnd";
                title = '개발일지'; w = 1300; h = 900;
                break;
            case 'workprocess':
                url   = "./workprocess/view.php?num=" + num + "&tablename=workprocess";
                title = ''; w = 1400; h = 900;
                break;
            case '지출결의서':
                url   = "./askitem_ER/write_form.php?mode=view&num=" + num + "&menu=no&tablename=eworks";
                title = '지출결의서'; w = 800; h = 850;
                break;
            default:
                return;
        }
        popupCenter(url, title, w, h);
    };

    // DH모터 금일 정보 셋팅
    var motorInfo = {
        registedate: '<?php echo isset($motor_registedate) ? $motor_registedate : ''; ?>',
        duedate:     '<?php echo isset($motor_duedate) ? $motor_duedate : ''; ?>',
        outputdonedate: '<?php echo isset($motor_outputdonedate) ? $motor_outputdonedate : ''; ?>',
        returndue:   '<?php echo isset($motor_returndue) ? $motor_returndue : ''; ?>'
    };
    $.each(motorInfo, function(key, val){
        var $el = $('#motor_' + key);
        if ($el.length && val) {
            $el.text(val);
        }
    });

    // 순차적으로 파트 보이기
    var parts     = $('.part'),
        descParts = $('.desc-part'),
        idx       = 0;
    function showNext() {
        if (idx < parts.length) {
            parts.eq(idx++).addClass('show');
        } else if (idx - parts.length < descParts.length) {
            descParts.eq(idx++ - parts.length).addClass('show');
        } else {
            return;
        }
        setTimeout(showNext, 500);
    }
    showNext();

    // 결재창 1.5초마다 확인
    var level = "<?php echo isset($_SESSION['level']) ? $_SESSION['level'] : ''; ?>";
    if (level) {
        setInterval(alert_eworkslist, 1500);
    }

    // employee_tasks_view 토글
    $('#employee_tasks_view').on('click', function() {
        var state = getCookie("showEmployeeTasksView") === "show" ? "hide" : "show";
        $("#employee_tasks-calendar-container").toggle();
        setCookie("showEmployeeTasksView", state, 10);
    });

    // todo_view 토글
    $('#todo_view').on('click', function() {
        var state = getCookie("showTodoView") === "show" ? "hide" : "show";
        $("#todo-list").toggle();
        setCookie("showTodoView", state, 10);
    });

    // calendar_view
    $('#calendar_view').on('click', function() {
        location.href = '/motor/month_schedule.php';
    });

    // board_view 토글 및 초기 상태
    function toggleBoard() {
        var state = getCookie("showBoardView") === "show";
        $('.board_list')[ state ? 'show' : 'hide' ]();
    }
    $('#board_view').on('click', function() {
        var next = getCookie("showBoardView") === "show" ? "hide" : "show";
        setCookie("showBoardView", next, 10);
        toggleBoard();
    });
    // 초기 실행
    toggleBoard();

    // 기타 팝업/다이얼로그 함수
    window.closeMsg     = function() { $('#myMsgDialog')[0].close(); };
    window.closeDialog  = function() { $('#closeDialog')[0].close(); };
    window.sendMsg      = function() { $('#myMsgDialog')[0].close(); };
    window.restorePageNumber = function() { location.reload(); };

    // 모터 수주내역 보기
    window.redirectToView_motor = function(num) {
        popupCenter(
            "./motor/write_form.php?mode=view&num=" + num,
            "DH모터 수주내역",
            1850,
            900
        );
    };

    window.openTaskList = function() {
        popupCenter(
            "./todo_task/task_list.php?header=no",
            "오늘의 할일",
            1920,
            1080
        );
    };

    window.openNewTask = function() {
        popupCenter(
            "./employee_tasks/write_form.php?mode=insert&tablename=employee_tasks",
            "새 할일 등록",
            1200,
            800
        );
    };

   $(".task-row-clickable").on("click", function() {
		var taskNum = $(this).data("task-num");
        popupCenter(
            "./todo_task/task_list.php?header=no&task_num=" + taskNum,
            "오늘의 할일",
            1920,
            1080
        );
   });

//    $(".task-row-clickable").on("click", function() {
// 		var taskNum = $(this).data("task-num");
// 		popupCenter(
// 			"./employee_tasks/write_form.php?mode=view&num=" + taskNum + "&tablename=employee_tasks",
// 			"할일 상세",
// 			1200,
// 		800
// 		);
//    });

});
</script>
</body>
</html>