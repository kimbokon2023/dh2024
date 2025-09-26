<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$title_message = '구매 발주(중국) 리스트';

// 권한 체크 - session.php에서 $WebSite 변수가 정의된 후 실행
if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    sleep(1);
    header("Location: " . ($WebSite ?? '/') . "login/login_form.php");
    exit;
}
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>
<title> <?=$title_message?> </title>
<link href="css/style.css" rel="stylesheet" >   
<style>
.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #0c63e4;
}
.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
.accordion-item {
    border: 1px solid rgba(0, 0, 0, 0.125);
    margin-bottom: 0.5rem;
}
.accordion-body {
    padding: 0;
}
.badge {
    font-size: 0.75em;
}
#myTable th, #myTable td {
  border: 1px solid #333 !important;
}
</style>
</head>
<body>    

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<?php
$tablename = 'm_order'; 

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$china_vendor = isset($_REQUEST['china_vendor']) ? $_REQUEST['china_vendor'] : '';

// JSON에서 카테고리 데이터 로드하는 함수
function loadVendorCategories() {
    $jsonFilePath = __DIR__ . '/vendor_categories.json';

    if (file_exists($jsonFilePath)) {
        $content = file_get_contents($jsonFilePath);
        $data = json_decode($content, true);

        if ($data !== null) {
            return $data;
        }
    }

    // 기본 카테고리 반환
    return [
        "default" => [
            "name" => "기본",
            "categories" => ["모터", "연동제어기", "원단", "부속자재", "운송비"]
        ]
    ];
}

// 구매처에 맞는 카테고리 목록 가져오기
function getCategoriesForVendor($vendorName, $chinaItem) {
    $vendorCategories = loadVendorCategories();
    $searchText = $chinaItem ?: $vendorName;

    // 정확한 키 매칭 먼저 시도
    if (isset($vendorCategories[$searchText])) {
        return $vendorCategories[$searchText]['categories'];
    }

    // 부분 매칭으로 검색
    foreach ($vendorCategories as $key => $vendor) {
        if ($key !== 'default' && strpos($searchText, $key) !== false) {
            return $vendor['categories'];
        }
    }

    // 기본 카테고리 반환
    return $vendorCategories['default']['categories'];
}

function decodeList($jsonData, $categoryList = null) {
    $decoded = json_decode($jsonData, true);
    if (is_array($decoded)) {
        // 카테고리 목록이 제공되지 않으면 기본 카테고리 사용
        if ($categoryList === null) {
            $categoryList = ["모터", "연동제어기", "원단", "부속자재", "운송비"];
        }

        // 동적 카테고리별 데이터 그룹화
        $categories = [];
        foreach ($categoryList as $cat) {
            $categories[$cat] = ['items' => [], 'totalQty' => 0, 'totalAmount' => 0];
        }
        
        $totalQuantity = 0;
        $totalAmount = 0;
        
        // 데이터를 카테고리별로 분류
        foreach ($decoded as $item) {
            $category = isset($item['col0']) ? trim($item['col0']) : '모터';
            $col1 = isset($item['col1']) ? trim($item['col1']) : '';
            $col2 = isset($item['col2']) ? trim($item['col2']) : '';
            $col3 = isset($item['col3']) ? trim($item['col3']) : '';
            $col4 = isset($item['col4']) ? trim($item['col4']) : '';
            $col5 = isset($item['col5']) ? trim($item['col5']) : '';
            $col6 = isset($item['col6']) ? trim($item['col6']) : '';
            
            // 콤마 제거 후 숫자 변환
            $col3_clean = str_replace(',', '', $col3);
            $col4_clean = str_replace(',', '', $col4);
            $col6_clean = str_replace(',', '', $col6);
            
            $quantity = is_numeric($col3_clean) ? (float)$col3_clean : 0;
            $amount = is_numeric($col6_clean) ? (float)$col6_clean : 0;
            
            // 카테고리가 정의되지 않은 경우 첫 번째 카테고리로 분류
            if (!isset($categories[$category])) {
                $category = $categoryList[0];
            }
            
            $categories[$category]['items'][] = [
                'col1' => $col1,
                'col2' => $col2,
                'col3' => $col3,
                'col4' => $col4,
                'col5' => $col5,
                'col6' => $col6,
                'quantity' => $quantity,
                'amount' => $amount
            ];
            
            $categories[$category]['totalQty'] += $quantity;
            $categories[$category]['totalAmount'] += $amount;
            
            $totalQuantity += $quantity;
            $totalAmount += $amount;
        }
        
        $table = '<div class="accordion" id="categoryAccordion">';
        
        foreach ($categories as $categoryName => $categoryData) {
            if (count($categoryData['items']) > 0) {
                $categoryId = str_replace([' ', '-'], '', $categoryName);
                $table .= '<div class="accordion-item">';
                $table .= '<h2 class="accordion-header" id="heading' . $categoryId . '">';
                $table .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $categoryId . '" aria-expanded="false" aria-controls="collapse' . $categoryId . '">';
                $table .= '<strong>' . $categoryName . '</strong> ';
                $table .= '<span class="badge bg-primary ms-2">수량: ' . number_format($categoryData['totalQty']) . '</span>';
                $table .= '<span class="badge bg-success ms-2">금액: ' . number_format($categoryData['totalAmount']) . ' 위엔</span>';
                $table .= '</button>';
                $table .= '</h2>';
                $table .= '<div id="collapse' . $categoryId . '" class="accordion-collapse collapse" aria-labelledby="heading' . $categoryId . '" data-bs-parent="#categoryAccordion">';
                $table .= '<div class="accordion-body p-0">';
                $table .= '<table class="table table-sm table-bordered mb-0" style="font-size: 11px;">';
                $table .= '<thead class="table-light">';
                $table .= '<tr>';
                $table .= '<th class="text-center" style="width: 20%;">품명</th>';
                $table .= '<th class="text-center" style="width: 20%;">품목코드</th>';
                $table .= '<th class="text-center" style="width: 5%;">수량</th>';
                $table .= '<th class="text-center" style="width: 5%;">단가(위엔)</th>';
                $table .= '<th class="text-center" style="width: 5%;">금액(위엔)</th>';
                $table .= '<th class="text-center" style="width: 30%;">비고</th>';
                $table .= '</tr>';
                $table .= '</thead>';
                $table .= '<tbody>';
                
                foreach ($categoryData['items'] as $item) {
                    $table .= '<tr>';
                    $table .= '<td class="text-start">' . htmlspecialchars($item['col1']) . '</td>';
                    $table .= '<td class="text-center">' . htmlspecialchars($item['col2']) . '</td>';
                    $table .= '<td class="text-end">' . (is_numeric($item['col3']) ? number_format($item['col3']) : $item['col3']) . '</td>';
                    $table .= '<td class="text-end">' . (is_numeric($item['col4']) ? number_format($item['col4']) : $item['col4']) . '</td>';
                    $table .= '<td class="text-end">' . (is_numeric($item['col6']) ? number_format($item['col6']) : $item['col6']) . '</td>';
                    $table .= '<td class="text-start">' . htmlspecialchars($item['col5']) . '</td>';
                    $table .= '</tr>';
                }
                
                // 카테고리별 합계 행
                $table .= '<tr class="table-warning fw-bold">';
                $table .= '<td class="text-center" colspan="2">' . $categoryName . ' 합계</td>';
                $table .= '<td class="text-end">' . number_format($categoryData['totalQty']) . '</td>';
                $table .= '<td class="text-end">-</td>';
                $table .= '<td class="text-end">' . number_format($categoryData['totalAmount']) . '</td>';
                $table .= '<td class="text-center">-</td>';
                $table .= '</tr>';
                
                $table .= '</tbody>';
                $table .= '</table>';
                $table .= '</div>';
                $table .= '</div>';
                $table .= '</div>';
            }
        }
        
        // 전체 합계 섹션
        if (count($decoded) > 0) {
            $table .= '<div class="mt-2 p-2 bg-light border rounded">';
            $table .= '<div class="d-flex justify-content-between align-items-center">';
            $table .= '<strong>전체 합계</strong>';
            $table .= '<div>';
            $table .= '<span class="badge bg-primary me-2">총 수량: ' . number_format($totalQuantity) . '</span>';
            $table .= '<span class="badge bg-success">총 금액: ' . number_format($totalAmount) . ' 위엔</span>';
            $table .= '</div>';
            $table .= '</div>';
            $table .= '</div>';
        }
        
        $table .= '</div>';
        
        return $table;
    }
    return '';
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    // $fromdate = date("Y-m-d", strtotime("-4 weeks", strtotime($currentDate))); // 4주 전 날짜
    $fromdate = date("2025-01-01"); 
    $todate = date("Y-m-d", strtotime("+2 months", strtotime($currentDate))); // 2개월 후 날짜
    $Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}

$sql="SELECT * FROM {$tablename}";

$sum=array();
$now = date("Y-m-d");     // 현재 날짜와 크거나 같으면 출하예정으로 구분

$orderby=" ORDER BY num DESC";
$attached=''; 
$whereattached = '';
$titletag = '';
        
$SettingDate=" m.orderDate ";
$common= $SettingDate . " BETWEEN '$fromdate' AND '$Transtodate' AND m.is_deleted IS NULL ";
$andPhrase= " AND " . $common;
$wherePhrase= " WHERE " . $common;

// 검색을 위해 모든 검색변수 공백제거
$search = str_replace(' ', '', $search);

// 중국 발주업체 필터링 추가
$vendor_filter = '';
if (!empty($china_vendor)) {
    // 업체명으로 직접 필터링 (새 컬럼 사용)
    $vendor_filter = " AND m.vendor_name = '" . addslashes($china_vendor) . "' ";
}

if($search==""){
    if($whereattached!=='')
        $sql="SELECT m.*, p.image_base64 as pb_image_base64
              FROM " . $tablename . " m
              LEFT JOIN {$DB}.phonebook_buy p ON m.vendor_code = p.num " . $whereattached . $andPhrase . $vendor_filter . $orderby;
    else
        $sql="SELECT m.*, p.image_base64 as pb_image_base64
              FROM " . $tablename . " m
              LEFT JOIN {$DB}.phonebook_buy p ON m.vendor_code = p.num " . $wherePhrase . $vendor_filter . $orderby;
}
else {
    $sql ="SELECT m.*, p.image_base64 as pb_image_base64
           FROM " . $tablename . " m
           LEFT JOIN {$DB}.phonebook_buy p ON m.vendor_code = p.num
           WHERE (REPLACE(m.searchtag,' ','') LIKE '%$search%' ) " . $attached . " AND m.is_deleted IS NULL " . $vendor_filter . $orderby;
}

try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져오기
    $total_row = count($rows); // 가져온 데이터의 행 수 계산
    $start_num = $total_row;
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}
?>

<form id="board_form" name="board_form" method="get">
    <input type="hidden" name="china_vendor" id="selected_china_vendor" value="<?=htmlspecialchars($china_vendor)?>">
<div class="container">
    <div class="d-flex justify-content-center">
    <div class="card mb-2 mt-2 w-75">
        <div class="card-body">
            <div class="card-header d-flex justify-content-center align-items-center mb-2">   
                <span class="text-center fs-5">  <?=$title_message?>   </span>     
				<button type="button" class="btn btn-dark btn-sm mx-1" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      						 
				<small class="ms-5 text-muted"> 중국측 구매를 위한 발주서 (발주서 작성 후 중국측 발송)</small>              
				<button type="button" class="btn btn-primary btn-sm mx-1"  onclick='location.href="list_input.php";' title="발주 입고창 이동" >  <i class="bi bi-list-columns"></i> </button>
				<?php if(intval($level) === 1) : ?>
					<button type="button" class="btn btn-danger btn-sm mx-1"  onclick='location.href="list_account.php";' title="송금액 이동" >  <i class="bi bi-currency-dollar"></i> </button>
					<button type="button" class="btn btn-success btn-sm mx-1"  onclick='openCategoryMapping();' title="카테고리 연결 관리" >  <i class="bi bi-link-45deg"></i> 카테고리 연결 </button>
				<?php endif; ?>					
            </div>

            <!-- 중국 발주업체 선택 라디오 버튼 -->
            <?php
            // 중국발주 업체 목록 조회 - china_sort_order 순서로 정렬
            $china_vendors_sql = "SELECT num, vendor_name, image_base64, item FROM {$DB}.phonebook_buy WHERE is_china_vendor = 1 AND is_deleted IS NULL ORDER BY china_sort_order ASC, vendor_name ASC";
            $china_vendors = [];
            try {
                $china_stmh = $pdo->query($china_vendors_sql);
                $china_vendors = $china_stmh->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $Exception) {
                echo "중국업체 조회 오류: " . $Exception->getMessage();
            }

            if (!empty($china_vendors)) :
            ?>
            <div class="d-flex mt-2 mb-3 justify-content-center align-items-center flex-wrap">
                <span class="me-3 fw-bold text-primary">중국 발주업체:</span>

                <!-- 전체 라디오 버튼 -->
                <div class="form-check form-check-inline me-3">
                    <input class="form-check-input" type="radio" name="china_vendor" id="vendor_all" value="" <?= empty($china_vendor) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold text-secondary" for="vendor_all">
                        <div class="d-flex align-items-center">
                            <div class="vendor-image-placeholder me-2">
                                <i class="bi bi-building text-muted" style="font-size: 20px;"></i>
                            </div>
                            <span>전체</span>
                        </div>
                    </label>
                </div>
 
                <?php foreach ($china_vendors as $vendor) :
                    $vendor_id = "vendor_" . $vendor['num'];
                    $image_src = '';
                    if (!empty($vendor['image_base64'])) {
                        $image_src = (strpos($vendor['image_base64'], 'data:') === 0) ?
                                    $vendor['image_base64'] :
                                    'data:image/png;base64,' . $vendor['image_base64'];
                    }
                ?>
                <div class="form-check form-check-inline me-3 mb-2">
                    <input class="form-check-input" type="radio" name="china_vendor" id="<?=$vendor_id?>" value="<?=htmlspecialchars($vendor['vendor_name'])?>" <?= $china_vendor === $vendor['vendor_name'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?=$vendor_id?>" style="cursor: pointer;">
                        <div class="d-flex align-items-center vendor-option" data-vendor="<?=htmlspecialchars($vendor['vendor_name'])?>">
                            <div class="vendor-image me-2">
                                <?php if (!empty($image_src)) : ?>
                                    <img src="<?=$image_src?>" style="width: 25px; height: 25px; object-fit: contain; border: 1px solid #ddd; border-radius: 3px;" alt="<?=htmlspecialchars($vendor['vendor_name'])?>" />
                                <?php else : ?>
                                    <div style="width: 25px; height: 25px; border: 1px solid #ddd; border-radius: 3px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                        <i class="bi bi-building text-muted" style="font-size: 12px;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="vendor-name" style="font-size: 0.9rem; white-space: nowrap;"><?=htmlspecialchars($vendor['item'] ?? $vendor['vendor_name'])?></span>
                        </div>
                    </label>
                </div>
                <?php endforeach; ?> 
            </div>

            <style>
            .vendor-option:hover {
                background-color: #f8f9fa;
                border-radius: 5px;
                padding: 2px 5px;
                transition: background-color 0.2s ease;
            }

            .form-check-input:checked + .form-check-label .vendor-option {
                background-color: #e3f2fd;
                border-radius: 5px;
                padding: 2px 5px;
                border: 1px solid #2196f3;
            }

            .vendor-image-placeholder {
                width: 25px;
                height: 25px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* 라디오 버튼과 내용의 세로 가운데 정렬 */
            .form-check-inline {
                display: flex;
                align-items: center;
            }

            .form-check-input {
                margin-right: 8px;
                margin-top: 0;
                vertical-align: middle;
            }

            .form-check-label {
                display: flex;
                align-items: center;
                margin-bottom: 0;
                padding-top: 0;
            }

            .vendor-option {
                display: flex;
                align-items: center;
            }

            .vendor-image {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .vendor-name {
                display: flex;
                align-items: center;
            }
            </style>
            <?php endif; ?>

            <div class="d-flex mt-1 mb-1 justify-content-center align-items-center">
                ▷  <?= $total_row ?> &nbsp;
                <input type="date" id="fromdate" name="fromdate" class="form-control" style="width:100px;" value="<?=$fromdate?>">  &nbsp;   ~ &nbsp;  
                <input type="date" id="todate" name="todate" class="form-control me-1" style="width:100px;" value="<?=$todate?>">  &nbsp;  
            
                <div class="inputWrap">
                    <input type="text" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off" class="form-control" style="width:150px;"> &nbsp;            
                    <button class="btnClear"></button>
                </div>                                
                &nbsp;
                <button id="searchBtn" type="button" class="btn btn-dark  btn-sm"> <i class="bi bi-search"></i> 검색 </button>          
                &nbsp;&nbsp;&nbsp;                        
                <button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규 </button> 
            </div>               
        </div> <!--card-body-->
    </div> <!--card -->   
    </div> <!--d-flex justify-content-center-->
</div> <!--container-fluid -->   
</form>     

<!-- 테이블을 화면 중앙에 정렬하기 위한 스타일 추가 -->
<style>
.center-table-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 420px; /* 필요시 높이 조정 */
    width: 100%;
} 
@media (max-width: 991px) {
    .center-table-wrap {
        min-height: unset;
        padding: 0 5px;
    }
}
</style>
<div class="container mt-1 mb-3">
    <div class="center-table-wrap">
        <div class="table-responsive" style="width: 100%; max-width: 1100px;">
            <table class="table table-bordered table-hover mx-auto" id="myTable" style="border-collapse:collapse;">
                <thead class="table-info">
                <tr>
                    <th class="text-center">발주일자</th>
                    <th class="text-center">중국회사</th>
                    <th class="text-center">품목</th>
                    <th class="text-center">발주수량</th>
                    <th class="text-center">발주금액(CNY)</th>
                    <th class="text-center">누적금액(CNY)</th>
                    <th class="text-center">비고</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($rows as $row) {
                    $orderDate = $row['orderDate'];
                    $orderlist = json_decode($row['orderlist'], true);
                    $num = $row['num'];

                    // 중국회사 정보
                    $vendor_name = $row['vendor_name'] ?? '';
                    $vendor_image = $row['pb_image_base64'] ?? '';
                    $vendor_item = $row['china_item'] ?? $vendor_name;  // 중국 품목명이 없으면 업체명 사용
                    $memo = $row['memo'] ?? '';

                    // 해당 구매처에 맞는 카테고리 목록 가져오기
                    $categoryList = getCategoriesForVendor($vendor_name, $vendor_item);

                    $catSum = [];
                    foreach ($categoryList as $cat) {
                        $catSum[$cat] = ['qty' => 0, 'amount' => 0];
                    }
                    if (is_array($orderlist)) {
                        foreach ($orderlist as $item) {
                            $cat = isset($item['col0']) ? $item['col0'] : $categoryList[0];
                            $qty = isset($item['col3']) ? floatval(str_replace(',', '', $item['col3'])) : 0;
                            $amt = isset($item['col6']) ? floatval(str_replace(',', '', $item['col6'])) : 0;
                            if (!isset($catSum[$cat])) $cat = $categoryList[0];
                            $catSum[$cat]['qty'] += $qty;
                            $catSum[$cat]['amount'] += $amt;
                        }
                    }
                    // 발주일자별 누적합
                    $rowCumulative = 0;
                    $first = true;
                    $index = 0; // $index 선언 및 초기화
                    $rowspan = count($categoryList); // 동적 rowspan 계산
                    foreach ($categoryList as $cat) {
                        $rowCumulative += $catSum[$cat]['amount'];
                        echo '<tr onclick="redirectToView(\'' . $num . '\', \'$tablename\')">';
                        if ($first) {
                            echo '<td rowspan="' . $rowspan . '" class="text-center" style="vertical-align:middle;">' . htmlspecialchars($orderDate) . '</td>';

                            // 중국회사 열 추가
                            echo '<td rowspan="' . $rowspan . '" class="text-center" style="vertical-align:middle;">';
                            if (!empty($vendor_image)) {
                                $image_src = (strpos($vendor_image, 'data:') === 0) ?
                                            $vendor_image :
                                            'data:image/png;base64,' . $vendor_image;
                                echo '<div class="d-flex flex-column align-items-center">';
                                echo '<img src="' . $image_src . '" style="width: 30px; height: 30px; object-fit: contain; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 3px;" alt="' . htmlspecialchars($vendor_item) . '" />';
                                echo '<small class="text-muted" style="font-size: 0.75rem;">' . htmlspecialchars($vendor_item) . '</small>';
                                echo '</div>';
                            } else if (!empty($vendor_item)) {
                                echo '<div class="d-flex flex-column align-items-center">';
                                echo '<div style="width: 30px; height: 30px; border: 1px solid #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; margin-bottom: 3px;">';
                                echo '<i class="bi bi-building text-muted" style="font-size: 14px;"></i>';
                                echo '</div>';
                                echo '<small class="text-muted" style="font-size: 0.75rem;">' . htmlspecialchars($vendor_item) . '</small>';
                                echo '</div>';
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            echo '</td>';

                            $first = false;
                        }
                        echo '<td class="text-center">' . $cat . '</td>';
                        echo '<td class="text-end">' . ($catSum[$cat]['qty'] ? number_format($catSum[$cat]['qty']) : '') . '</td>';
                        echo '<td class="text-end">' . ($catSum[$cat]['amount'] ? number_format($catSum[$cat]['amount'], 2) : '') . '</td>';
                        echo '<td class="text-end fw-bold">' . ($rowCumulative ? number_format($rowCumulative, 2) : '') . '</td>';
                        if($index == 0) {
                            echo '<td rowspan="' . $rowspan . '" class="text-start">' . (isset($memo) ? $memo : '') . '</td>';
                        }
                        echo '</tr>';
                        $index++; // $index 증가
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="container mt-3 mb-3">
    <? include '../footer_sub.php'; ?>
</div>

<script>
function redirectToView(num, tablename) {
    var url = "write_form.php?mode=view&num=" + num + "&tablename=" + tablename;
    customPopup(url, '', 1850, 900);
}

function openCategoryMapping() {
    var url = "category_mapping.php";
    customPopup(url, '카테고리 연결 관리', 1200, 800);
}    

function SearchEnter(){
    if(event.keyCode == 13){        
        saveSearch();
    }
}

function saveSearch() {    
    $('#board_form').submit();
}



// 숫자를 콤마 형식으로 변환하는 함수
function formatNumber(num) {
    if (isNaN(num) || num === '') return '';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


// Document ready - consolidated initialization
$(document).ready(function(){
    // Hide loading overlay
    var loader = document.getElementById('loadingOverlay');
    if(loader) loader.style.display = 'none';

    // Button event handlers
    $("#writeBtn").click(function(){
        var tablename = '<?php echo $tablename; ?>';
        var url = "write_form.php?tablename=" + tablename;
        customPopup(url, '', 1850, 900);
    }); 
  
    $("#searchBtn").off('click').on('click', function(){
        saveSearch();
    });

    // Analytics logging
    saveLogData('구매서 작성 리스트');

    // China vendor radio button auto-search - GET 방식으로 변경
    $('input[name="china_vendor"]').off('change').on('change', function() {
        // try {
        //     var selectedVendor = $(this).val();
        //     console.log('라디오 버튼 변경됨:', selectedVendor);

        //     // 현재 URL 파라미터들을 유지하면서 china_vendor 추가/변경
        //     var url = new URL(window.location);
        //     if (selectedVendor) {
        //         url.searchParams.set('china_vendor', selectedVendor);
        //     } else {
        //         url.searchParams.delete('china_vendor');
        //     }
          // $('#board_form').submit();
        // } catch (error) {
        //     console.error('라디오 버튼 변경 오류:', error);            
        // }
        var selectedVendor = $(this).val();
        try {
            var url = new URL(window.location.href);
            if (selectedVendor) {
                url.searchParams.set('china_vendor', selectedVendor);
            } else {
                url.searchParams.delete('china_vendor');
            }
            window.location.href = url.toString();
        } catch (error) {
            console.error('라디오 버튼 변경 오류:', error);
            $("#selected_china_vendor").val(selectedVendor);
            $("#board_form").attr('method', 'get').submit();
        }
    });
}); 

</script>
</body>
</html> 