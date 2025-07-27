<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/session.php'; // 세션 파일 포함

$title_message = '카테고리 설정';      
$categoryFile = $_SERVER['DOCUMENT_ROOT'] . '/askitem_ER/category.json';

// 기본 카테고리 구조
$defaultCategories = [
    '식비',
    '운반비', 
    '자재비',
    '차량유지비',
    '기타'
];

// JSON 파일 읽기
function loadCategories($file) {
    global $defaultCategories;
    
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $categories = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($categories)) {
            // 배열을 연관배열로 변환 (키는 인덱스)
            $result = [];
            foreach ($categories as $index => $category) {
                $result['category_' . $index] = $category;
            }
            return $result;
        }
    }
    
    // 파일이 없거나 오류가 있으면 기본값 반환
    $result = [];
    foreach ($defaultCategories as $index => $category) {
        $result['category_' . $index] = $category;
    }
    return $result;
}

// JSON 파일 저장
function saveCategories($file, $categories) {
    $jsonContent = json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file, $jsonContent);
}

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save') {
        // 오류 출력 방지
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $categoriesJson = $_POST['categories'] ?? '{}';
        $categories = json_decode($categoriesJson, true) ?? [];
        
        // 카테고리 데이터 정리
        $cleanedCategories = [];
        foreach ($categories as $key => $category) {
            if (!empty($category['name'])) {
                $cleanedCategories[] = trim($category['name']);
            }
        }
        
        if (saveCategories($categoryFile, $cleanedCategories)) {
            $response = ['success' => true, 'message' => '카테고리가 저장되었습니다.'];
        } else {
            $response = ['success' => false, 'message' => '저장 중 오류가 발생했습니다.'];
        }
        
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($response);
        exit;
    }
}

// 카테고리 데이터 로드
$categories = loadCategories($categoryFile);
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?> 
<title> <?=$title_message?> </title>
</head>

<style>
.container-fluid {
    max-width: 500px;
    margin: 0 auto;
}

.category-item {
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 10px;
    padding: 10px;
    background-color: #f8f9fa;
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.sub-item {
    border: 1px solid #e9ecef;
    border-radius: 3px;
    margin: 5px 0;
    padding: 8px;
    background-color: white;
}

.btn-remove {
    color: #dc3545;
    cursor: pointer;
}

.btn-remove:hover {
    color: #c82333;
}

.btn-edit {
    color: #007bff;
    cursor: pointer;
}

.btn-edit:hover {
    color: #0056b3;
}

.add-btn {
    margin: 5px 0;
}

.form-control {
    max-width: 300px;
}
</style>

<body>
<div class="container-fluid p-3">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">지출결의서 카테고리 설정</h5>
        </div>
        <div class="card-body">
            <form id="categoryForm">
                <div id="categoriesContainer">
                    <?php foreach ($categories as $key => $category): ?>
                    <div class="category-item" data-category-key="<?= htmlspecialchars($key) ?>">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <input type="text" class="form-control form-control-sm category-input" 
                                       name="categories[<?= htmlspecialchars($key) ?>][name]" 
                                       value="<?= htmlspecialchars($category) ?>" 
                                       placeholder="카테고리명" readonly>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit me-1" 
                                        onclick="editCategory(this)">수정</button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove" 
                                        onclick="removeCategory(this)">삭제</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" class="btn btn-outline-success btn-sm" onclick="addCategory()">
                    <i class="bi bi-plus-lg"></i> 항목 추가
                </button>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">저장</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.close()">닫기</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';	        
});

let categoryCounter = <?= count($categories) ?>;

function addCategory() {
    const container = document.getElementById('categoriesContainer');
    const categoryKey = 'category_' + categoryCounter++;
    
    const categoryHtml = `
        <div class="category-item" data-category-key="${categoryKey}">
            <div class="row align-items-center">
                <div class="col-8">
                    <input type="text" class="form-control form-control-sm category-input" 
                           name="categories[${categoryKey}][name]" 
                           placeholder="카테고리명">
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-sm btn-outline-success btn-edit me-1" 
                            onclick="editCategory(this)">저장</button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove" 
                            onclick="removeCategory(this)">삭제</button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', categoryHtml);
    
    // 새로 추가된 입력창에 포커스
    const newInput = container.lastElementChild.querySelector('.category-input');
    newInput.focus();
}

function editCategory(button) {
    const categoryItem = button.closest('.category-item');
    const input = categoryItem.querySelector('.category-input');
    const editBtn = button;
    
    if (input.readOnly) {
        // 수정 모드로 변경
        input.readOnly = false;
        input.focus();
        editBtn.textContent = '저장';
        editBtn.classList.remove('btn-outline-primary');
        editBtn.classList.add('btn-outline-success');
    } else {
        // 저장 모드로 변경
        input.readOnly = true;
        editBtn.textContent = '수정';
        editBtn.classList.remove('btn-outline-success');
        editBtn.classList.add('btn-outline-primary');
    }
}

function removeCategory(button) {
    if (confirm('이 항목을 삭제하시겠습니까?')) {
        button.closest('.category-item').remove();
    }
}

// 폼 제출 처리
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // 폼 데이터 수집
    const formData = new FormData();
    formData.append('action', 'save');
    
    // 카테고리 데이터 수집
    const categories = {};
    document.querySelectorAll('.category-input').forEach((input, index) => {
        if (input.value.trim() !== '') {
            categories['category_' + index] = {
                name: input.value.trim()
            };
        }
    });
    
    formData.append('categories', JSON.stringify(categories));
    
    fetch('category_setting.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert(data.message);
                // 부모 창 새로고침
                if (window.opener && !window.opener.closed) {
                    window.opener.location.reload();
                }
            } else {
                alert('오류: ' + data.message);
            }
        } catch (e) {
            console.error('JSON Parse Error:', text);
            alert('서버 응답을 처리할 수 없습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다.');
    });
});
</script>

</body>
</html> 