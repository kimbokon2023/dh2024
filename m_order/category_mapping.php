<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 1) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
?>

<title>카테고리 연결 관리</title>
<link href="css/style.css" rel="stylesheet">
<style>
.category-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.5rem;
    margin: 0.25rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.category-item .category-text {
    flex-grow: 1;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    transition: background-color 0.2s;
}

.category-item .category-text:hover {
    background-color: #e9ecef;
}

.category-item .category-edit-input {
    flex-grow: 1;
    margin-right: 0.5rem;
}

.category-item .category-buttons {
    display: flex;
    gap: 0.25rem;
}

.category-item .btn-edit,
.category-item .btn-remove {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.vendor-section {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
    background: white;
}

.vendor-header {
    background: #e9ecef;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
    font-weight: bold;
}

.add-category-section {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-top: 1rem;
}
</style>
</head>

<body>
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-center align-items-center mt-3 mb-4">
                <span class="fs-4 me-5">카테고리 연결 관리</span>
                <button type="button" class="btn btn-success btn-sm me-2" onclick="saveAllMappings()">
                    <i class="bi bi-save"></i> 저장
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="self.close();">
                    &times; 닫기
                </button>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h5>구매처별 카테고리 설정</h5>
                    <div id="vendorMappings">
                        <!-- 동적으로 생성됨 -->
                    </div>

                    <!-- 새로운 구매처 추가 섹션 -->
                    <div class="add-category-section">
                        <h6>새로운 구매처 추가</h6>
                        <div class="d-flex mb-2">
                            <input type="text" id="newVendorName" class="form-control me-2 w-50" placeholder="구매처명 (예: 셔터모터)">
                            <button type="button" class="btn btn-primary btn-sm" onclick="addNewVendor()">
                                <i class="bi bi-plus"></i> 추가
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5>중국 업체 현황</h5>
                    <div id="chineseVendors">
                        <!-- PHP에서 동적으로 생성됨 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var loader = document.getElementById('loadingOverlay');
    if(loader) loader.style.display = 'none';
});
let vendorCategories = {};

// 페이지 로드 시 데이터 불러오기
$(document).ready(function() {
    loadVendorCategories();
    loadChineseVendors();
});

// JSON 파일에서 카테고리 데이터 불러오기
function loadVendorCategories() {
    $.ajax({
        url: 'category_api.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            vendorCategories = data;
            renderVendorMappings();
        },
        error: function(xhr, status, error) {
            console.error('카테고리 로드 실패:', error);
            alert('카테고리 데이터를 불러오는데 실패했습니다.');
        }
    });
}

// 중국 업체 목록 불러오기
function loadChineseVendors() {
    $.ajax({
        url: 'get_chinese_vendors.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            renderChineseVendors(data);
        },
        error: function(xhr, status, error) {
            console.error('중국 업체 로드 실패:', error);
        }
    });
}

// 구매처별 카테고리 매핑 렌더링
function renderVendorMappings() {
    const container = $('#vendorMappings');
    container.empty();

    Object.keys(vendorCategories).forEach(vendorKey => {
        const vendor = vendorCategories[vendorKey];
        const vendorSection = $(`
            <div class="vendor-section" data-vendor-key="${vendorKey}">
                <div class="vendor-header d-flex justify-content-between align-items-center">
                    <span>${vendor.name}</span>
                    ${vendorKey !== 'default' ? `<button type="button" class="btn btn-danger btn-sm" onclick="removeVendor('${vendorKey}')"><i class="bi bi-trash"></i></button>` : ''}
                </div>
                <div class="categories-list">
                    ${vendor.categories.map((cat, index) => `
                        <div class="category-item" data-vendor-key="${vendorKey}" data-category-index="${index}">
                            <span class="category-text" onclick="editCategory('${vendorKey}', ${index})">${cat}</span>
                            <input type="text" class="form-control category-edit-input" value="${cat}" style="display: none;" onblur="saveCategory('${vendorKey}', ${index})" onkeypress="handleCategoryKeyPress(event, '${vendorKey}', ${index})">
                            <div class="category-buttons">
                                <button type="button" class="btn btn-primary btn-sm btn-edit" onclick="editCategory('${vendorKey}', ${index})" title="수정">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-remove" onclick="removeCategory('${vendorKey}', ${index})" title="삭제">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="add-category-section mt-2">
                    <div class="d-flex">
                        <input type="text" class="form-control me-2 new-category-input w-50" placeholder="새 카테고리명">
                        <button type="button" class="btn btn-success btn-sm" onclick="addCategory('${vendorKey}')">
                            <i class="bi bi-plus"></i> 추가
                        </button>
                    </div>
                </div>
            </div>
        `);
        container.append(vendorSection);
    });
}

// 중국 업체 목록 렌더링
function renderChineseVendors(vendors) {
    const container = $('#chineseVendors');
    container.empty();

    if (vendors.length === 0) {
        container.append('<p class="text-muted">등록된 중국 업체가 없습니다.</p>');
        return;
    }

    vendors.forEach(vendor => {
        const vendorDiv = $(`
            <div class="vendor-section">
                <div class="d-flex align-items-center">
                    ${vendor.image_base64 ? `
                        <img src="${vendor.image_base64.startsWith('data:') ? vendor.image_base64 : 'data:image/png;base64,' + vendor.image_base64}"
                             style="width: 30px; height: 30px; object-fit: contain; border: 1px solid #ddd; border-radius: 5px;"
                             alt="${vendor.vendor_name}" class="me-2" />
                    ` : `
                        <div style="width: 30px; height: 30px; border: 1px solid #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;" class="me-2">
                            <i class="bi bi-building text-muted" style="font-size: 14px;"></i>
                        </div>
                    `}
                    <div>
                        <div class="fw-bold">${vendor.vendor_name}</div>
                        <small class="text-muted">${vendor.item || vendor.vendor_name}</small>
                    </div>
                </div>
            </div>
        `);
        container.append(vendorDiv);
    });
}

// 새로운 구매처 추가
function addNewVendor() {
    const vendorName = $('#newVendorName').val().trim();
    if (!vendorName) {
        alert('구매처명을 입력해주세요.');
        return;
    }

    if (vendorCategories[vendorName]) {
        alert('이미 존재하는 구매처입니다.');
        return;
    }

    vendorCategories[vendorName] = {
        name: vendorName,
        categories: ["운송비"]  // 기본 카테고리
    };

    $('#newVendorName').val('');
    renderVendorMappings();
}

// 구매처 삭제
function removeVendor(vendorKey) {
    if (vendorKey === 'default') {
        alert('기본 설정은 삭제할 수 없습니다.');
        return;
    }

    if (confirm(`"${vendorCategories[vendorKey].name}" 구매처를 삭제하시겠습니까?`)) {
        delete vendorCategories[vendorKey];
        renderVendorMappings();
    }
}

// 카테고리 추가
function addCategory(vendorKey) {
    const input = $(`.vendor-section[data-vendor-key="${vendorKey}"] .new-category-input`);
    const categoryName = input.val().trim();

    if (!categoryName) {
        alert('카테고리명을 입력해주세요.');
        return;
    }

    if (vendorCategories[vendorKey].categories.includes(categoryName)) {
        alert('이미 존재하는 카테고리입니다.');
        return;
    }

    vendorCategories[vendorKey].categories.push(categoryName);
    input.val('');
    renderVendorMappings();
}

// 카테고리 수정 모드 진입
function editCategory(vendorKey, categoryIndex) {
    const categoryItem = $(`.category-item[data-vendor-key="${vendorKey}"][data-category-index="${categoryIndex}"]`);
    const textSpan = categoryItem.find('.category-text');
    const editInput = categoryItem.find('.category-edit-input');
    const buttons = categoryItem.find('.category-buttons');

    // 다른 수정 중인 항목들을 저장하고 종료
    $('.category-edit-input:visible').each(function() {
        $(this).blur();
    });

    // 수정 모드로 전환
    textSpan.hide();
    buttons.hide();
    editInput.show().focus().select();
}

// 카테고리 수정 저장
function saveCategory(vendorKey, categoryIndex) {
    const categoryItem = $(`.category-item[data-vendor-key="${vendorKey}"][data-category-index="${categoryIndex}"]`);
    const textSpan = categoryItem.find('.category-text');
    const editInput = categoryItem.find('.category-edit-input');
    const buttons = categoryItem.find('.category-buttons');

    const newValue = editInput.val().trim();

    if (!newValue) {
        alert('카테고리명을 입력해주세요.');
        editInput.focus();
        return;
    }

    // 중복 체크 (현재 편집 중인 항목 제외)
    const currentCategories = vendorCategories[vendorKey].categories;
    const isDuplicate = currentCategories.some((cat, index) =>
        index !== categoryIndex && cat === newValue
    );

    if (isDuplicate) {
        alert('이미 존재하는 카테고리입니다.');
        editInput.focus();
        return;
    }

    // 카테고리 업데이트
    vendorCategories[vendorKey].categories[categoryIndex] = newValue;

    // UI 업데이트
    textSpan.text(newValue).show();
    editInput.hide();
    buttons.show();
}

// 키 이벤트 처리 (Enter, Escape)
function handleCategoryKeyPress(event, vendorKey, categoryIndex) {
    if (event.key === 'Enter') {
        event.preventDefault();
        saveCategory(vendorKey, categoryIndex);
    } else if (event.key === 'Escape') {
        event.preventDefault();
        cancelCategoryEdit(vendorKey, categoryIndex);
    }
}

// 카테고리 수정 취소
function cancelCategoryEdit(vendorKey, categoryIndex) {
    const categoryItem = $(`.category-item[data-vendor-key="${vendorKey}"][data-category-index="${categoryIndex}"]`);
    const textSpan = categoryItem.find('.category-text');
    const editInput = categoryItem.find('.category-edit-input');
    const buttons = categoryItem.find('.category-buttons');

    // 원래 값으로 복원
    const originalValue = vendorCategories[vendorKey].categories[categoryIndex];
    editInput.val(originalValue);

    // 표시 모드로 복원
    textSpan.show();
    editInput.hide();
    buttons.show();
}

// 카테고리 삭제
function removeCategory(vendorKey, categoryIndex) {
    if (vendorCategories[vendorKey].categories.length <= 1) {
        alert('최소 1개의 카테고리는 유지해야 합니다.');
        return;
    }

    const categoryName = vendorCategories[vendorKey].categories[categoryIndex];
    if (confirm(`"${categoryName}" 카테고리를 삭제하시겠습니까?`)) {
        vendorCategories[vendorKey].categories.splice(categoryIndex, 1);
        renderVendorMappings();
    }
}

// 모든 매핑 저장
function saveAllMappings() {
    $.ajax({
        url: 'category_api.php',
        method: 'POST',
        data: {
            action: 'save',
            data: JSON.stringify(vendorCategories)
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('카테고리 매핑이 저장되었습니다.');
            } else {
                alert('저장 실패: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('저장 실패:', error);
            alert('저장 중 오류가 발생했습니다.');
        }
    });
}
</script>
</body>
</html>