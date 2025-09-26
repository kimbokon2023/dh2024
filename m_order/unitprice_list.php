<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$title_message = '구매(중국) 원단가';

// 권한 체크 - session.php에서 $WebSite 변수가 정의된 후 실행
if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    sleep(1);
    header("Location: " . ($WebSite ?? '/') . "login/login_form.php");
    exit;
}
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
?>
<title><?=$title_message?></title>
<link href="css/style.css" rel="stylesheet">

<!-- Tabulator CSS -->
<link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">

<style>
.tabulator {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    font-size: 14px; 
}

.tabulator .tabulator-header {
    background: #e7f1ff;
    border-bottom: 2px solid #0c63e4;
}

.tabulator .tabulator-header .tabulator-col {
    background: #e7f1ff;
    border-right: 1px solid #dee2e6;
}

.tabulator .tabulator-row {
    border-bottom: 1px solid #dee2e6;
}

.tabulator .tabulator-row:hover {
    background-color: #f8f9fa !important;
}

.tabulator .tabulator-cell {
    border-right: 1px solid #dee2e6;
    padding: 8px;
}

.tabulator-editing {
    background-color: #fff3cd !important;
}

.save-indicator {
    color: #28a745;
    font-weight: bold;
}

.error-indicator {
    color: #dc3545;
    font-weight: bold;
}

#toolbar {
    margin-bottom: 15px;
}

.vendor-select {
    max-width: 200px;
}
</style>
</head>
<body>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 구매(중국) 원단가 조회 - china_sort_order 순서로 정렬
$china_vendors_sql = "SELECT num, vendor_name, image_base64, item, china_sort_order FROM {$DB}.phonebook_buy WHERE is_china_vendor = 1 AND is_deleted IS NULL ORDER BY china_sort_order ASC, vendor_name ASC";
$china_vendors = [];

// 디버깅: SQL 쿼리 실행 전 상태
$debug_timestamp = date('Y-m-d H:i:s');
error_log("[$debug_timestamp] 중국업체 쿼리 실행 시작");
error_log("[$debug_timestamp] 실행 SQL: $china_vendors_sql");
error_log("[$debug_timestamp] 데이터베이스: $DB");

try {
    $china_stmh = $pdo->query($china_vendors_sql);
    $china_vendors = $china_stmh->fetchAll(PDO::FETCH_ASSOC);

    // 디버깅: 쿼리 결과 상세 분석
    $debug_timestamp = date('Y-m-d H:i:s');
    error_log("[$debug_timestamp] 쿼리 성공 - 조회된 데이터 개수: " . count($china_vendors));

    foreach ($china_vendors as $index => $vendor) {
        error_log("[$debug_timestamp] 업체[$index]: " . print_r($vendor, true));
        error_log("[$debug_timestamp] - 업체명: " . ($vendor['vendor_name'] ?? 'NULL'));
        error_log("[$debug_timestamp] - 이미지데이터: " . (empty($vendor['image_base64']) ? 'EMPTY' : 'EXISTS(' . strlen($vendor['image_base64']) . ' chars)'));
    }

} catch (PDOException $Exception) {
    $debug_timestamp = date('Y-m-d H:i:s');
    error_log("[$debug_timestamp] 쿼리 실패: " . $Exception->getMessage());
    error_log("[$debug_timestamp] 스택 트레이스: " . $Exception->getTraceAsString());
    echo "<div class='alert alert-danger'>구매(중국) 원단가 조회 오류: " . $Exception->getMessage() . "</div>";
}

// 디버깅: 데이터가 없는 경우 테스트 데이터 추가
if (empty($china_vendors)) {
    $debug_timestamp = date('Y-m-d H:i:s');
    error_log("[$debug_timestamp] 실제 데이터가 없어 테스트 데이터로 대체");

    $china_vendors = [
        ['num' => 1, 'vendor_name' => '테스트업체1', 'image_base64' => ''],
        ['num' => 2, 'vendor_name' => '테스트업체2', 'image_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='],
        ['num' => 3, 'vendor_name' => '테스트업체3', 'image_base64' => '']
    ];

    error_log("[$debug_timestamp] 테스트 데이터 생성 완료: " . count($china_vendors) . "개");
}

// 디버깅: JSON 인코딩 테스트
$json_result = json_encode($china_vendors, JSON_UNESCAPED_UNICODE);
if ($json_result === false) {
    $debug_timestamp = date('Y-m-d H:i:s');
    error_log("[$debug_timestamp] JSON 인코딩 실패: " . json_last_error_msg());
} else {
    $debug_timestamp = date('Y-m-d H:i:s');
    error_log("[$debug_timestamp] JSON 인코딩 성공 (길이: " . strlen($json_result) . " chars)");
    error_log("[$debug_timestamp] JSON 결과: " . $json_result);
}

// JSON에서 카테고리 데이터 로드
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

$vendorCategories = loadVendorCategories();
?>

<div class="container-fluid mt-2">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 me-2"><?=$title_message?></h5>
                <button type="button" class="btn btn-link p-0" id="help-btn" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="bi bi-info-circle text-primary" style="font-size: 1.2em;" title="도움말"></i>
                </button>
            </div>
            <div>
                <span id="save-status" class="me-3"></span>
                <button type="button" class="btn btn-primary btn-sm" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> 새로고침
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- 툴바 -->
            <div id="toolbar" class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <button id="add-row" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle"></i> 추가
                    </button>
                    <button id="delete-selected" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i>  삭제
                    </button>
                    <button id="save-all" class="btn btn-primary btn-sm">
                        <i class="bi bi-save"></i> 저장
                    </button>
                    <button id="cell-edit-mode" class="btn btn-info btn-sm">
                        <i class="bi bi-grid-3x3"></i> 셀편집모드
                    </button>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- 필터 영역 제거됨 -->
                </div>
            </div>

            <!-- 디버깅 정보 표시 -->
            <!-- <div id="debug-info" class="alert alert-warning mb-3">
                <i class="bi bi-bug"></i>
                <strong>디버깅 정보:</strong>
                <div id="debug-content">
                    <div>PHP 데이터 로딩: <span id="php-status">확인중...</span></div>
                    <div>JavaScript 데이터: <span id="js-status">확인중...</span></div>
                    <div>Tabulator 초기화: <span id="tabulator-status">확인중...</span></div>
                    <div>중국업체 개수: <span id="vendor-count">0</span></div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="refreshDebugInfo()">
                    <i class="bi bi-arrow-clockwise"></i> 디버그 새로고침
                </button>
            </div> -->

            <!-- 셀편집모드 안내 -->
            <div id="cell-edit-help" class="alert alert-info d-none mb-3">
                <i class="bi bi-info-circle"></i>
                <strong>셀편집모드 사용법:</strong>
                <br>1. 엑셀에서 데이터를 복사 (Ctrl+C)
                <br>2. 이 화면에서 Ctrl+V로 붙여넣기
                <br>3. 데이터 순서: 중국구매처, 카테고리, 품목코드, 품목명, 규격, 단위, 단가(위엔), 판매가(원화), 비고
            </div>

            <!-- Tabulator -->
            <div id="unitprice-table"></div>
        </div>
    </div>
</div>

<!-- 도움말 모달 -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    구매(중국) 원단가 관리 도움말
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="text-primary"><i class="bi bi-gear-fill me-2"></i>페이지 기능</h6>
                        <p>이 페이지는 중국 구매처별 원단가 정보를 관리하는 시스템입니다.</p>

                        <h6 class="text-primary mt-4"><i class="bi bi-list-check me-2"></i>주요 기능</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-plus-circle text-success me-2"></i>
                                        <strong>추가:</strong> 새로운 단가 정보 등록
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-pencil text-warning me-2"></i>
                                        <strong>수정:</strong> 셀을 클릭하여 직접 편집
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-trash text-danger me-2"></i>
                                        <strong>삭제:</strong> 체크박스 선택 후 일괄 삭제
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-save text-info me-2"></i>
                                        <strong>저장:</strong> 변경사항을 서버에 저장
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-grid-3x3 text-secondary me-2"></i>
                                        <strong>셀편집모드:</strong> 엑셀 데이터 붙여넣기
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-arrow-clockwise text-primary me-2"></i>
                                        <strong>새로고침:</strong> 최신 데이터로 갱신
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <h6 class="text-primary mt-4"><i class="bi bi-info-square me-2"></i>필드 정보</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>필드명</th>
                                        <th>설명</th>
                                        <th>입력방식</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>중국구매처</strong></td>
                                        <td>중국 업체명 (이미지 포함)</td>
                                        <td>드롭다운 선택</td>
                                    </tr>
                                    <tr>
                                        <td><strong>카테고리</strong></td>
                                        <td>구매처별 품목 분류</td>
                                        <td>자동 매핑 + 드롭다운</td>
                                    </tr>
                                    <tr>
                                        <td><strong>품목코드</strong></td>
                                        <td>고유 품목 식별 코드</td>
                                        <td>텍스트 입력 (대문자 변환)</td>
                                    </tr>
                                    <tr>
                                        <td><strong>품목명</strong></td>
                                        <td>품목의 명칭</td>
                                        <td>텍스트 입력 (필수)</td>
                                    </tr>
                                    <tr>
                                        <td><strong>규격</strong></td>
                                        <td>품목의 상세 규격</td>
                                        <td>텍스트 입력</td>
                                    </tr>
                                    <tr>
                                        <td><strong>단위</strong></td>
                                        <td>수량 단위</td>
                                        <td>드롭다운 (T, EA, SET 등)</td>
                                    </tr>
                                    <tr>
                                        <td><strong>단가(위엔)</strong></td>
                                        <td>중국 위안화 기준 단가</td>
                                        <td>숫자 입력</td>
                                    </tr>
                                    <tr>
                                        <td><strong>판매가(원화)</strong></td>
                                        <td>한국 원화 기준 판매가격</td>
                                        <td>숫자 입력</td>
                                    </tr>
                                    <tr>
                                        <td><strong>비고</strong></td>
                                        <td>추가 정보나 메모</td>
                                        <td>텍스트 입력</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h6 class="text-primary mt-4"><i class="bi bi-lightbulb me-2"></i>사용 팁</h6>
                        <div class="alert alert-info">
                            <ul class="mb-0">
                                <li><strong>셀편집모드:</strong> 엑셀에서 복사한 데이터를 Ctrl+V로 한 번에 입력 가능</li>
                                <li><strong>카테고리 자동 매핑:</strong> 중국구매처 선택 시 해당 업체의 카테고리가 자동으로 매핑됨</li>
                                <li><strong>실시간 저장:</strong> 셀 편집 후 Enter 키를 누르면 자동으로 서버에 저장됨</li>
                                <li><strong>일괄 작업:</strong> 여러 행을 선택하여 한 번에 삭제하거나 저장 가능</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>닫기
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid mt-3 mb-3">
    <?php include '../footer_sub.php'; ?>
</div>

<!-- Tabulator JS -->
<script src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>

<script>
// 구매(중국) 원단가 조회
const chinaVendors = <?php echo json_encode($china_vendors, JSON_UNESCAPED_UNICODE); ?>;

// 카테고리 매핑 데이터
const vendorCategories = <?php echo json_encode($vendorCategories, JSON_UNESCAPED_UNICODE); ?>;

// 구매처에 맞는 카테고리 목록 가져오기
function getCategoriesForVendor(vendorName) {
    // 해당 업체의 상세 정보 찾기
    const vendor = chinaVendors.find(v => v.vendor_name === vendorName);
    const searchText = vendor?.item || vendorName;

    console.log('getCategoriesForVendor - vendorName:', vendorName, 'searchText:', searchText);

    // 1. 정확한 키 매칭 먼저 시도 (vendor.item)
    if (vendor?.item && vendorCategories[vendor.item]) {
        console.log('정확한 item 매칭:', vendor.item, vendorCategories[vendor.item].categories);
        return vendorCategories[vendor.item].categories;
    }

    // 2. 정확한 키 매칭 시도 (vendor_name)
    if (vendorCategories[vendorName]) {
        console.log('정확한 vendorName 매칭:', vendorName, vendorCategories[vendorName].categories);
        return vendorCategories[vendorName].categories;
    }

    // 3. 부분 매칭으로 검색 (item 기준)
    if (vendor?.item) {
        for (const key in vendorCategories) {
            if (key !== 'default' && vendor.item.includes(key)) {
                console.log('부분 item 매칭:', key, vendorCategories[key].categories);
                return vendorCategories[key].categories;
            }
        }
    }

    // 4. 부분 매칭으로 검색 (vendor_name 기준)
    for (const key in vendorCategories) {
        if (key !== 'default' && vendorName.includes(key)) {
            console.log('부분 vendorName 매칭:', key, vendorCategories[key].categories);
            return vendorCategories[key].categories;
        }
    }

    // 5. 기본 카테고리 반환
    console.log('기본 카테고리 사용');
    return vendorCategories.default ? vendorCategories.default.categories : ["모터", "연동제어기", "원단", "부속자재", "운송비"];
}

// 디버깅: chinaVendors 데이터 상세 분석
function debugChinaVendors() {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ===== CHINA VENDORS 디버깅 시작 =====`);
    console.log(`[${timestamp}] chinaVendors 타입:`, typeof chinaVendors);
    console.log(`[${timestamp}] Array 여부:`, Array.isArray(chinaVendors));
    console.log(`[${timestamp}] 길이:`, chinaVendors ? chinaVendors.length : 'NULL');
    console.log(`[${timestamp}] 전체 데이터:`, chinaVendors);

    if (Array.isArray(chinaVendors)) {
        chinaVendors.forEach((vendor, index) => {
            console.log(`[${timestamp}] 업체[${index}]:`, vendor);
            console.log(`[${timestamp}] - 타입:`, typeof vendor);
            console.log(`[${timestamp}] - vendor_name:`, vendor?.vendor_name, '(타입:', typeof vendor?.vendor_name, ')');
            console.log(`[${timestamp}] - image_base64:`, vendor?.image_base64 ? 'EXISTS' : 'EMPTY');
            console.log(`[${timestamp}] - num:`, vendor?.num);
        });
    }
    console.log(`[${timestamp}] ===== CHINA VENDORS 디버깅 완료 =====`);
}

// 즉시 실행
debugChinaVendors();

// 화면상 디버깅 정보 업데이트 함수들
function updateDebugStatus() {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] 화면 디버깅 정보 업데이트 시작`);

    // PHP 데이터 로딩 상태
    const phpStatus = document.getElementById('php-status');
    if (phpStatus) {
        phpStatus.textContent = '<?php echo count($china_vendors) > 0 ? "성공 (" . count($china_vendors) . "개)" : "실패 (데이터 없음)"; ?>';
        phpStatus.className = '<?php echo count($china_vendors) > 0 ? "text-success" : "text-danger"; ?>';
    }

    // JavaScript 데이터 상태
    const jsStatus = document.getElementById('js-status');
    if (jsStatus) {
        if (chinaVendors && Array.isArray(chinaVendors)) {
            jsStatus.textContent = `성공 (${chinaVendors.length}개)`;
            jsStatus.className = 'text-success';
        } else {
            jsStatus.textContent = '실패 (배열 아님)';
            jsStatus.className = 'text-danger';
        }
    }

    // 업체 개수
    const vendorCount = document.getElementById('vendor-count');
    if (vendorCount) {
        vendorCount.textContent = chinaVendors ? chinaVendors.length : 0;
    }

    console.log(`[${timestamp}] 화면 디버깅 정보 업데이트 완료`);
}

function refreshDebugInfo() {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] 디버그 새로고침 버튼 클릭됨`);

    // 디버깅 정보 다시 실행
    debugChinaVendors();
    updateDebugStatus();

    // Tabulator 상태 확인
    const tabulatorStatus = document.getElementById('tabulator-status');
    if (tabulatorStatus) {
        if (typeof table !== 'undefined' && table) {
            tabulatorStatus.textContent = '초기화 완료';
            tabulatorStatus.className = 'text-success';
        } else {
            tabulatorStatus.textContent = '초기화 실패';
            tabulatorStatus.className = 'text-danger';
        }
    }
}

// 초기 디버깅 정보 표시 
updateDebugStatus();

// 이미지+텍스트 커스텀 에디터 (중국구매처)
function vendorImageSelectEditor(cell, onRendered, success, cancel, editorParams) {
    const container = document.createElement('div');
    container.style.minWidth = '220px';

    const dropdown = document.createElement('div');
    dropdown.style.position = 'fixed';
    dropdown.style.zIndex = '20000';
    dropdown.style.background = '#fff';
    dropdown.style.border = '1px solid #ddd';
    dropdown.style.maxHeight = '240px';
    dropdown.style.overflowY = 'auto';
    dropdown.style.boxShadow = '0 2px 6px rgba(0,0,0,0.1)';

    function buildImageSrc(vendor) {
        if (!vendor || !vendor.image_base64) return '';
        return vendor.image_base64.indexOf('data:') === 0
            ? vendor.image_base64
            : 'data:image/png;base64,' + vendor.image_base64;
    }

    function positionDropdown() {
        const rect = cell.getElement().getBoundingClientRect();
        dropdown.style.left = rect.left + 'px';
        dropdown.style.top = rect.bottom + 'px';
        dropdown.style.minWidth = rect.width + 'px';
    }

    const outsideClickHandler = function(e) {
        if (!dropdown.contains(e.target)) {
            cleanup();
            cancel();
        }
    };
    const repositionHandler = function() {
        positionDropdown();
    };
    const cleanup = () => {
        document.removeEventListener('mousedown', outsideClickHandler);
        window.removeEventListener('resize', repositionHandler);
        window.removeEventListener('scroll', repositionHandler, true);
        if (dropdown.parentNode) dropdown.parentNode.removeChild(dropdown);
    };

    let openedAt = 0;

    function renderList() {
        dropdown.innerHTML = '';
        const vendors = Array.isArray(chinaVendors) ? chinaVendors : [];
        if (vendors.length === 0) {
            const emptyEl = document.createElement('div');
            emptyEl.style.padding = '6px 8px';
            emptyEl.className = 'text-muted';
            emptyEl.textContent = '업체가 없습니다';
            dropdown.appendChild(emptyEl);
            return;
        }

        vendors.forEach(vendor => {
            const item = document.createElement('div');
            item.style.padding = '6px 8px';
            item.style.cursor = 'pointer';
            item.addEventListener('mouseenter', () => { item.style.background = '#f8f9fa'; });
            item.addEventListener('mouseleave', () => { item.style.background = '#ffffff'; });

            const imageSrc = buildImageSrc(vendor);
            item.innerHTML = `<div class="d-flex align-items-center">
                <div class="vendor-image me-2">
                    ${imageSrc ?
                        `<img src="${imageSrc}" style="width: 20px; height: 20px; object-fit: contain; border: 1px solid #ddd; border-radius: 3px;" alt="${vendor.vendor_name}" />` :
                        `<div style=\"width: 20px; height: 20px; border: 1px solid #ddd; border-radius: 3px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;\"><i class=\"bi bi-building text-muted\" style=\"font-size: 10px;\"></i></div>`
                    }
                </div>
                <span style="font-size: 12px;">${vendor.item || vendor.vendor_name}</span>
            </div>`;

            item.addEventListener('mousedown', function(e) {
                e.preventDefault();
                if (typeof performance !== 'undefined' && performance.now() - openedAt < 150) {
                    return; // 오픈 직후 발생한 초기 클릭 무시
                }
                const value = vendor.vendor_name;
                cleanup();
                success(value);
            });
            dropdown.appendChild(item);
        });
    }

    container.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            e.stopPropagation();
            cleanup();
            cancel();
        }
    });

    setTimeout(() => document.addEventListener('mousedown', outsideClickHandler), 0);

    onRendered(function() {
        container.tabIndex = -1;
        container.focus();
        setTimeout(() => {
            document.body.appendChild(dropdown);
            positionDropdown();
            window.addEventListener('resize', repositionHandler);
            window.addEventListener('scroll', repositionHandler, true);
            renderList();
            openedAt = typeof performance !== 'undefined' ? performance.now() : Date.now();
        }, 0);
    });

    return container;
}

// 셀편집모드 상태
var cellEditMode = false;

// Tabulator 초기화
var table = new Tabulator("#unitprice-table", {
    pagination: "local",
    paginationSize: 50,
    paginationSizeSelector: [25, 50, 100, 200],
    movableColumns: true,
    resizableRows: false,
    selectable: true,
    selectableRangeMode: "click",
    headerFilterPlaceholder: "검색...",
    layout: "fitColumns",
    responsiveLayout: "collapse",
    height: "600px",
    placeholder: "데이터가 없습니다.",

    // 클립보드 기능 활성화
    clipboard: true,
    clipboardCopySelector: "table",
    clipboardPasteParser: "table",
    clipboardPasteAction: "insert",

    columns: [
        {
            formatter: "rowSelection",
            titleFormatter: "rowSelection",
            hozAlign: "center",
            headerSort: false,
            width: 40
        },
        {
            title: "NO.",
            field: "num",
            width: 60,
            hozAlign: "center",
            headerSort: false,
            formatter: function(cell, formatterParams, onRendered) {
                return cell.getRow().getPosition();
            }
        },
        {
            title: "중국구매처",
            field: "vendor_name",
            width: 150,
            editor: vendorImageSelectEditor,
            formatter: function(cell, formatterParams, onRendered) {
                const vendorName = cell.getValue();
                if (!vendorName) return '';

                // chinaVendors에서 해당 업체 찾기
                const vendor = chinaVendors.find(v => v.vendor_name === vendorName);
                if (!vendor) return vendorName;

                let imageSrc = '';
                if (vendor.image_base64) {
                    imageSrc = vendor.image_base64.startsWith('data:')
                        ? vendor.image_base64
                        : 'data:image/png;base64,' + vendor.image_base64;
                }

                return `<div class="d-flex align-items-center">
                    <div class="vendor-image me-2">
                        ${imageSrc ?
                            `<img src="${imageSrc}" style="width: 20px; height: 20px; object-fit: contain; border: 1px solid #ddd; border-radius: 3px;" alt="${vendorName}" />` :
                            `<div style="width: 20px; height: 20px; border: 1px solid #ddd; border-radius: 3px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;"><i class="bi bi-building text-muted" style="font-size: 10px;"></i></div>`
                        }
                    </div>
                    <span style="font-size: 12px;">${vendor.item || vendorName}</span>
                </div>`;
            },
        },
        {
            title: "카테고리",
            field: "category",
            width: 120,
            editor: "list",
            editorParams: function(cell) {
                // 같은 행의 vendor_name 값 가져오기
                const vendorName = cell.getRow().getData().vendor_name;
                if (vendorName) {
                    const categories = getCategoriesForVendor(vendorName);
                    const values = {};
                    categories.forEach(cat => {
                        values[cat] = cat;
                    });
                    return { values: values };
                }
                // 기본 카테고리
                return { values: {"모터": "모터", "연동제어기": "연동제어기", "원단": "원단", "부속자재": "부속자재", "운송비": "운송비"} };
            },
        },
        {
            title: "품목코드",
            field: "item_code",
            width: 200,
            editor: "input",
            headerFilter: "input",
            validator: "required",
            editorParams: {
                search: false,
                elementAttributes: {
                    style: "text-transform: uppercase;"
                }
            },
            mutator: function(value, data, type, params, component) {
                // 입력값에서 공백 제거 및 대문자 변환
                if (value && typeof value === 'string') {
                    const processed = value.replace(/\s/g, '').toUpperCase();
                    console.log('품목코드 mutator:', value, '->', processed);
                    return processed;
                }
                return value;
            }
        }, 
        {
            title: "품목명",
            field: "item_name",
            width: 300,
            editor: "input",
            headerFilter: "input",
            validator: "required"
        }, 
        {
            title: "규격",
            field: "specifications",
            width: 200,
            editor: "input",
            headerFilter: "input"
        },
        {
            title: "단위",
            field: "unit",
            width: 100,
            editor: "list",
            editorParams: {
                values: ["T", "EA", "SET", "KG", "M", "BOX", "개"]
            },
            hozAlign: "center"
        },
        {
            title: "단가(위엔)",
            field: "unit_price_cny",
            width: 150,
            editor: "number",
            editorParams: {
                min: 0,
                step: 0.01
            },
            formatter: "money",
            formatterParams: {
                decimal: ".",
                thousand: ",",
                symbol: "",
                precision: 2
            },
            hozAlign: "right",
            headerFilter: "number"
        },
        {
            title: "판매가(원화)",
            field: "sell_price_krw",
            width: 150,
            editor: "number",
            editorParams: {
                min: 0,
                step: 1
            },
            formatter: "money",
            formatterParams: {
                decimal: ".",
                thousand: ",",
                symbol: "₩",
                precision: 0
            },
            hozAlign: "right",
            headerFilter: "number"
        },
        {
            title: "비고",
            field: "memo",
            width: 300,
            editor: "input"
        }
    ],

    cellEdited: function(cell) {
        // 중국구매처가 변경된 경우 카테고리 자동 설정
        if (cell.getField() === "vendor_name") {
            const vendorName = cell.getValue();
            if (vendorName) {
                const categories = getCategoriesForVendor(vendorName);
                const currentCategory = cell.getRow().getData().category;

                // 현재 카테고리가 새로운 카테고리 목록에 없으면 첫 번째 카테고리로 변경
                if (!categories.includes(currentCategory)) {
                    cell.getRow().update({category: categories[0]});
                }
            }
        }

        // 수정된 행 저장
        saveRow(cell.getRow());
    },

    rowAdded: function(row) {
        // 새 행 추가
        row.update({
            category: "연동제어기",
            unit: "EA",
            min_quantity: 1,
            effective_date: new Date().toISOString().split('T')[0],
            created_by: "<?=$_SESSION['name']?>",
            is_active: 1,
            sell_price_krw: null
        });
    }
});

// 추가 버튼 클릭
document.getElementById("add-row").addEventListener("click", function() {
    table.addRow({});
});

// 삭제 버튼 클릭
document.getElementById("delete-selected").addEventListener("click", function() {
    const selectedRows = table.getSelectedRows();
    if (selectedRows.length === 0) {
        Swal.fire({
            title: '선택된 행이 없습니다',
            text: '삭제할 행을 선택해주세요.',
            icon: 'warning',
            confirmButtonText: '확인'
        });
        return;
    }

    Swal.fire({
        title: '삭제 확인',
        text: `선택된 ${selectedRows.length}개의 행을 삭제하시겠습니까?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '삭제',
        cancelButtonText: '취소'
    }).then((result) => {
        if (result.isConfirmed) {
            // 삭제할 번호들 수집과 새 행들 구분
            const deleteNums = [];
            const newRowsCount = [];

            selectedRows.forEach(row => {
                const data = row.getData();
                console.log('삭제 대상 행 데이터:', data);

                if (data.num && data.num > 0) {
                    deleteNums.push(data.num);
                } else {
                    newRowsCount.push(row);
                }
                row.delete(); // 테이블에서 시각적으로 제거
            });

            console.log('데이터베이스에서 삭제할 번호들:', deleteNums);
            console.log('새 행 개수:', newRowsCount.length);

            // 일괄 삭제 처리
            if (deleteNums.length > 0) {
                deleteRows(deleteNums);
            } else {
                Swal.fire({
                    title: '삭제 완료',
                    text: `${selectedRows.length}개 행이 삭제되었습니다.`,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    });
});

// 저장 버튼 클릭
document.getElementById("save-all").addEventListener("click", function() {
    const data = table.getData();
    saveAllData(data);
});

// 필터 이벤트 리스너 제거됨

// 수정된 행 저장
function saveRow(row) {
    const data = row.getData();

    fetch('./unitprice_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: data.num ? 'update' : 'create',
            data: data
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            if (!data.num && result.num) {
                row.update({num: result.num});
            }
            Swal.fire({
                title: '저장 완료',
                text: '서버에 저장되었습니다.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                title: '저장 실패',
                text: '단가 저장 실패: ' + result.message,
                icon: 'error',
                confirmButtonText: '확인'
            });
        }
    })
    .catch(error => {
        console.error('처리 오류:', error);
        showStatus('처리 오류', 'error');
    });
}

// 행 삭제 (단일)
function deleteRow(num) {
    // FormData 방식으로 변경 (WAF 우회용)
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('num', num);

    fetch('./unitprice_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            Swal.fire({
                title: '삭제 완료',
                text: '서버에서 삭제되었습니다.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
            // 삭제 후 데이터 새로고침
            loadData();
        } else {
            Swal.fire({
                title: '삭제 실패',
                text: '단가 삭제 실패: ' + result.message,
                icon: 'error',
                confirmButtonText: '확인'
            });
        }
    })
    .catch(error => {
        console.error('처리 오류:', error);
        showStatus('처리 오류: ' + error.message, 'error');
    });
}

// 행 일괄 삭제
function deleteRows(nums) {
    console.log('deleteRows 호출됨, nums:', nums);

    if (!Array.isArray(nums) || nums.length === 0) {
        showStatus('삭제할 데이터가 없습니다', 'error');
        return;
    }

    // 유효하지 않은 번호 제거
    const validNums = nums.filter(num => num && Number.isInteger(Number(num)) && Number(num) > 0);
    console.log('유효한 번호들:', validNums);

    if (validNums.length === 0) {
        showStatus('삭제할 유효한 데이터가 없습니다', 'error');
        return;
    }

    // FormData 방식으로 변경 (WAF 우회용)
    const formData = new FormData();
    formData.append('action', 'delete_multiple');
    formData.append('nums', JSON.stringify(validNums));

    fetch('./unitprice_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Delete Response Status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Delete API Error Response:', text);
                let errorDetail = '';
                try {
                    const errorJson = JSON.parse(text);
                    errorDetail = errorJson.message || text;
                    console.error('Delete Error Details:', errorJson);
                } catch (e) {
                    errorDetail = text;
                }
                throw new Error(`HTTP ${response.status}: ${errorDetail}`);
            });
        }
        return response.json();
    })
    .then(result => {
        console.log('Delete API Result:', result);
        if (result.success) {
            Swal.fire({
                title: '일괄 삭제 완료',
                text: `${validNums.length}개 행이 서버에서 삭제되었습니다.`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
            // 삭제 후 데이터 새로고침
            loadData();
        } else {
            Swal.fire({
                title: '삭제 실패',
                text: '일괄 삭제 실패: ' + result.message,
                icon: 'error',
                confirmButtonText: '확인'
            });
            console.error('Delete failure details:', result);
            // 실패해도 데이터 새로고침
            loadData();
        }
    })
    .catch(error => {
        console.error('일괄 삭제 오류:', error);
        showStatus('일괄 삭제 오류: ' + error.message, 'error');
        // 오류 발생해도 데이터 새로고침
        loadData();
    });
}

// 저장 버튼 클릭
function saveAllData(data) {
    console.log('저장할 데이터:', data);
    const jsonData = JSON.stringify(data);
    console.log('데이터 크기:', jsonData.length, 'bytes');

    // 데이터가 너무 크면 첫 번째 항목만 테스트
    if (jsonData.length > 8000) {
        console.log('데이터가 너무 큼, 첫 번째 항목만 테스트');
        data = data.slice(0, 1);
    }

    // FormData를 사용하여 POST 요청
    const formData = new FormData();
    formData.append('action', 'save_all');
    formData.append('data', JSON.stringify(data));

    fetch('./unitprice_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);

        // 응답이 JSON인지 먼저 확인
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);

        if (!response.ok) {
            return response.text().then(text => {
                console.log('Error response body:', text);
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }

        return response.json();
    })
    .then(result => {
        console.log('API 응답:', result);
        if (result.success) {
            Swal.fire({
                title: '일괄 저장 완료',
                text: '서버에 저장되었습니다.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
            loadData(); // 데이터 다시 로드
        } else {
            Swal.fire({
                title: '저장 실패',
                text: '단가 저장 실패: ' + result.message,
                icon: 'error',
                confirmButtonText: '확인'
            });
        }
    })
    .catch(error => {
        console.error('단가 저장 오류:', error);
        showStatus('단가 저장 오류: ' + error.message, 'error');
    });
} 

// 상태 표시
function showStatus(message, type) {
    const statusEl = document.getElementById('save-status');
    statusEl.textContent = message;
    statusEl.className = type === 'success' ? 'save-indicator' : 'error-indicator';

    setTimeout(() => {
        statusEl.textContent = '';
        statusEl.className = '';
    }, 3000);
}

// 데이터 로드 함수
function loadData() {
    fetch('./unitprice_api.php?action=list')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            table.setData(data);
            showStatus('데이터 로드 완료', 'success');
        })
        .catch(error => {
            console.error('데이터 로드 오류:', error);
            showStatus('데이터 로드 실패', 'error');
            // 오류 발생 시 빈 배열로 초기화
            table.setData([]);
        });
}

// 셀편집모드 토글
document.getElementById("cell-edit-mode").addEventListener("click", function() {
    cellEditMode = !cellEditMode;
    const button = this;

    if (cellEditMode) {
        button.classList.remove('btn-info');
        button.classList.add('btn-warning');
        button.innerHTML = '<i class="bi bi-grid-3x3-gap-fill"></i> 편집모드 ON';
        showStatus('셀편집모드 활성화 - Ctrl+V로 엑셀 데이터를 붙여넣으세요', 'success');

        // 도움말 표시
        document.getElementById("cell-edit-help").classList.remove('d-none');

        // 테이블에 포커스
        document.getElementById("unitprice-table").focus();

    } else {
        button.classList.remove('btn-warning');
        button.classList.add('btn-info');
        button.innerHTML = '<i class="bi bi-grid-3x3"></i> 셀편집모드';
        showStatus('셀편집모드 비활성화', 'success');

        // 도움말 숨김
        document.getElementById("cell-edit-help").classList.add('d-none');
    }
});

// 클립보드 이벤트 핸들러
document.addEventListener('keydown', function(e) {
    if (cellEditMode && e.ctrlKey && e.key === 'v') {
        e.preventDefault();

        navigator.clipboard.readText().then(function(clipboardData) {
            if (clipboardData) {
                parseAndInsertExcelData(clipboardData);
            }
        }).catch(function(err) {
            showStatus('클립보드 읽기 실패: ' + err.message, 'error');
        });
    }
});

// 엑셀 데이터 파싱 및 삽입
function parseAndInsertExcelData(data) {
    try {
        // 탭과 개행으로 분리된 데이터 파싱
        const rows = data.trim().split('\n');
        const parsedData = [];

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].split('\t');
            if (cells.length >= 1 && cells[0].trim() !== '') {
                const rowData = {
                    vendor_name: cells[0] || '',
                    category: cells[1] || '모터',
                    item_code: cells[2] || '',
                    item_name: cells[3] || '',
                    specifications: cells[4] || '',
                    unit: cells[5] || 'EA',
                    unit_price_cny: cells[6] ? parseFloat(cells[6]) : null,
                    sell_price_krw: cells[7] ? parseInt(cells[7]) : null,
                    memo: cells[8] || '',
                    effective_date: new Date().toISOString().split('T')[0],
                    created_by: "<?=$_SESSION['name']?>",
                    is_active: 1
                };
                parsedData.push(rowData);
            }
        }

        if (parsedData.length > 0) {
            // 데이터 추가
            parsedData.forEach(rowData => {
                table.addRow(rowData);
            });

            showStatus(`${parsedData.length}개 행이 추가되었습니다`, 'success');
        } else {
            showStatus('유효한 데이터가 없습니다', 'error');
        }

    } catch (error) {
        showStatus('데이터 파싱 오류: ' + error.message, 'error');
    }
}

// 초기화
$(document).ready(function() {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ===== DOCUMENT READY 시작 =====`);

    try {
        saveLogData('구매(중국) 원단가');

        var loader = document.getElementById('loadingOverlay');
        if(loader) loader.style.display = 'none';

        // Tabulator 초기화 상태 업데이트
        setTimeout(() => {
            const tabulatorStatus = document.getElementById('tabulator-status');
            if (tabulatorStatus) {
                if (typeof table !== 'undefined' && table) {
                    tabulatorStatus.textContent = '초기화 완료';
                    tabulatorStatus.className = 'text-success';
                    console.log(`[${timestamp}] Tabulator 초기화 성공`);
                } else {
                    tabulatorStatus.textContent = '초기화 실패';
                    tabulatorStatus.className = 'text-danger';
                    console.log(`[${timestamp}] Tabulator 초기화 실패`);
                }

                // 최종 디버깅 정보 업데이트
                updateDebugStatus();
            }
        }, 1000);

        // 데이터 로드
        loadData();

        console.log(`[${timestamp}] Document ready 처리 완료`);

    } catch (error) {
        console.error(`[${timestamp}] Document ready 오류:`, error);
        console.error(`[${timestamp}] 스택 트레이스:`, error.stack);

        // 오류 정보 화면에 표시
        const debugContent = document.getElementById('debug-content');
        if (debugContent) {
            debugContent.innerHTML += `<div class="text-danger">초기화 오류: ${error.message}</div>`;
        }
    }

    console.log(`[${timestamp}] ===== DOCUMENT READY 완료 =====`);
});
</script>
</body>
</html>