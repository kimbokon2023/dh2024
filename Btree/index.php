<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}
// 에러 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '계정관리(수입,지출)';
?>

<title> <?=$title_message?> </title>
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-start mb-1">		
		<h3>수입/지출 계정 관리</h3> <button type="button" class="btn btn-dark btn-sm ms-3 me-2" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      
	</div>
    <div class="d-flex justify-content-end mb-3">		
        <button class="btn btn-dark btn-sm me-1 ms-1 saveData">
            <i class="bi bi-floppy2-fill"></i> 저장
        </button>
        <button class="btn btn-secondary btn-sm" onclick="self.close();">
            <i class="bi bi-x-lg"></i> 닫기
        </button>
    </div>
    <ul id="accountList" class="list-group"></ul>
</div>

<script>
$(document).ready(function () {
    // 로딩 오버레이 제거
    var loader = $('#loadingOverlay');
    if (loader.length) {
        loader.hide();
    }
	
    const fileName = 'accountContents.json';
    let accountData = {}; // 데이터를 전역 변수로 관리

    // JSON 데이터 로드
    function loadAccounts() {
        $.getJSON(fileName, function (data) {
            accountData = data; // 데이터를 전역 변수에 저장
            renderAccounts(data);
        }).fail(function () {
            alert('JSON 파일을 로드할 수 없습니다.');
        });
    }

    // JSON 데이터 저장
    function saveAccounts() {
        const now = new Date();
        const formattedDate = now.toISOString().slice(0, 10).replace(/-/g, '');
        const formattedTime = now.toTimeString().slice(0, 8).replace(/:/g, '');
        const backupFileName = `${formattedDate}_${formattedTime}_backup.json`;

        $.post('saveAccountContents.php', {
            fileName,
            backupFileName,
            data: JSON.stringify(accountData)
        }, function (response) {
            const result = JSON.parse(response);
            Swal.fire('저장 완료', `데이터가 서버에 저장되었습니다.\n백업 파일: ${backupFileName}`, 'success');
        });
    }

    // 계정 렌더링
    function renderAccounts(data) {
        $('#accountList').empty();
        $.each(data, function (category, accounts) {
            const categoryItem = $(`<li class="list-group-item">
                <span class="editable" data-category="${category}" data-level="0">${category}</span>
                <button class="btn btn-sm btn-outline-primary ms-2 add-main-account" data-category="${category}">+</button>
                <ul class="list-group mt-2 ms-3"></ul>
            </li>`);
            const subAccountList = categoryItem.find('ul');
            $.each(accounts, function (accountName, accountDetails) {
                appendAccountItem(subAccountList, category, accountName, accountDetails, category);
            });
            $('#accountList').append(categoryItem);
        });
    }

    // 하위 계정 추가
	function appendAccountItem(parentList, category, accountName, accountDetails, parentName) {
		// 추가 버튼 여부 결정
		const addButton = accountDetails.level < 2 ? `
			<button class="btn btn-sm btn-outline-primary ms-2 add-sub-account" 
					data-category="${category}" 
					data-account="${accountName}">
				+
			</button>
		` : ''; // level 3에서는 추가 버튼이 나오지 않음

		const accountItem = $(`<li class="list-group-item">
			<span class="editable" 
				  data-category="${category}" 
				  data-account="${accountName}" 
				  data-level="${accountDetails.level}" 
				  data-description="${accountDetails.description}" 
				  data-parent="${parentName}">
				  ${accountName}
			</span>
			${addButton}
			<button class="btn btn-sm btn-outline-danger ms-2 delete-account" 
					data-category="${category}" 
					data-account="${accountName}">
				-
			</button>
			<ul class="list-group mt-2 ms-3"></ul>
		</li>`);

		const subAccountList = accountItem.find('ul');
		if (accountDetails.하위계정 && accountDetails.하위계정.length > 0) {
			accountDetails.하위계정.forEach(subAccount => {
				const subAccountName = Object.keys(subAccount)[0];
				const subAccountDetails = subAccount[subAccountName];
				appendAccountItem(subAccountList, category, subAccountName, subAccountDetails, accountName);
			});
		}

		parentList.append(accountItem);
	}


    // 계정 추가
    function addAccount(category, parentAccount, name, description, isMain) {
        if (isMain) {
            accountData[category][name] = {
                level: 1,
                description,
                parent: category,
                하위계정: []
            };
        } else {
            const parent = accountData[category][parentAccount];
            if (!parent.하위계정) parent.하위계정 = [];
            parent.하위계정.push({
                [name]: {
                    level: (parent.level || 1) + 1,
                    description,
                    parent: parentAccount,
                    하위계정: []
                }
            });
        }
    }

    // 계정 삭제
    function deleteAccount(category, account) {
        const parentAccount = accountData[category];
        if (parentAccount[account]) {
            delete parentAccount[account];
        } else {
            // Find and delete in subaccounts
            $.each(parentAccount, function (key, value) {
                if (value.하위계정) {
                    const index = value.하위계정.findIndex(sub => Object.keys(sub)[0] === account);
                    if (index !== -1) {
                        value.하위계정.splice(index, 1);
                        return false; // Exit loop
                    }
                }
            });
        }
    }


// 계정 수정

// 계정 수정
$('#accountList').on('click', '.editable', function () {
    const currentName = $(this).text().trim(); // 현재 계정 이름
    const currentDescription = $(this).data('description') || ''; // 현재 설명
    const parentCategory = $(this).closest('ul').closest('li').find('> .editable').data('category'); // 최상위 카테고리
    const level = $(this).data('level'); // 계정 레벨 정보

    // 디버깅 정보 출력
    console.log('DEBUG: currentName =', currentName);
    console.log('DEBUG: currentDescription =', currentDescription);
    console.log('DEBUG: parentCategory =', parentCategory);
    console.log('DEBUG: level =', level);

    // 최상위 레벨은 수정 불가
    if (level !== 1 && level !== 2 ) {
        Swal.fire('알림', '최상위 계정은 수정할 수 없습니다.', 'info');
        return;
    }

    Swal.fire({
        title: '계정 수정',
        html: `
            <input type="text" id="accountName" class="swal2-input" placeholder="새 계정 이름" value="${currentName}" autocomplete="off">
            <input type="text" id="accountDescription" class="swal2-input" placeholder="새 계정 설명" value="${currentDescription}" autocomplete="off">
        `,
        showCancelButton: true,
        confirmButtonText: '수정',
        cancelButtonText: '취소',
        preConfirm: () => {
            const newName = $('#accountName').val().trim();
            const newDescription = $('#accountDescription').val().trim();

            if (!newName || !newDescription) {
                Swal.showValidationMessage('모든 입력란을 채워주세요.');
            }
            return { newName, newDescription };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { newName, newDescription } = result.value;

            try {
                let found = false;

                // 최상위 카테고리 탐색
                if (accountData[parentCategory]) {
                    $.each(accountData[parentCategory], function (key, value) {
                        if (key === currentName) {
                            // 최상위 계정 수정
                            accountData[parentCategory][newName] = value;
                            delete accountData[parentCategory][currentName];
                            accountData[parentCategory][newName].description = newDescription;
                            found = true;
                            return false; // 루프 종료
                        }

                        // 하위 계정 탐색
                        if (value.하위계정) {
                            value.하위계정.forEach((subAccount, index) => {
                                const subAccountName = Object.keys(subAccount)[0];
                                if (subAccountName === currentName) {
                                    // 하위 계정 수정
                                    value.하위계정[index] = {
                                        [newName]: {
                                            ...subAccount[subAccountName],
                                            description: newDescription
                                        }
                                    };
                                    found = true;
                                    return false; // 루프 종료
                                }
                            });
                        }
                    });
                }

                if (!found) {
                    throw new Error(`계정을 찾을 수 없습니다. currentName: ${currentName}, parentCategory: ${parentCategory}`);
                }

                renderAccounts(accountData); // UI 업데이트
            } catch (error) {
                console.error('Error updating account:', error);
                Swal.fire('오류', `계정을 수정하는 중 문제가 발생했습니다.\n${error.message}`, 'error');
            }
        }
    });
});


    // 버튼 핸들러
    $('#accountList').on('click', '.add-main-account, .add-sub-account, .delete-account', function () {
        const isMain = $(this).hasClass('add-main-account');
        const isDelete = $(this).hasClass('delete-account');
        const category = $(this).data('category');
        const account = $(this).data('account');

        if (isDelete) {
            Swal.fire({
                title: '삭제 확인',
                text: '해당 계정을 삭제하시겠습니까?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '삭제',
                cancelButtonText: '취소'
            }).then(result => {
                if (result.isConfirmed) {
                    deleteAccount(category, account);
                    renderAccounts(accountData);
                }
            });
        } else {
            Swal.fire({
                title: isMain ? `${category}의 하위 계정 추가` : `${account}의 하위 계정 추가`,
                html: `
                    <input type="text" id="accountName" class="swal2-input" placeholder="계정 이름" autocomplete="off">
                    <input type="text" id="accountDescription" class="swal2-input" placeholder="계정 설명" autocomplete="off">
                `,
                focusConfirm: false,
                showCancelButton: true,
                preConfirm: () => {
                    const name = $('#accountName').val().trim();
                    const description = $('#accountDescription').val().trim();
                    if (!name || !description) {
                        Swal.showValidationMessage('모든 입력란을 채워주세요.');
                    }
                    return { name, description };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    const { name, description } = result.value;
                    addAccount(category, account, name, description, isMain);
                    renderAccounts(accountData);
                }
            });
        }
    });

    // 저장 버튼 클릭
    $('.saveData').on('click', saveAccounts);

    loadAccounts();
});
</script>


<style>
.editable {
    cursor: pointer;
}
</style>
</body>


</html>

