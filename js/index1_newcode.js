/* ===================================================================
 * 1/6: 전역 변수 및 공통 헬퍼 함수
 * =================================================================== */

// 전역 변수
var Eworks_dataTable; // DataTables 인스턴스
var activeTab; // 현재 활성 탭 ID
var isModalOpen = false; // 모달 창 상태
var ajaxRequests = {}; // AJAX 요청 관리 객체

/**
 * AJAX 요청을 관리하고 중복 호출을 방지하는 함수
 * @param {string} key - 요청을 식별하는 고유 키
 * @param {object} ajaxOptions - jQuery AJAX 옵션 객체
 */
function manageAjax(key, ajaxOptions) {
    if (ajaxRequests[key]) {
        ajaxRequests[key].abort(); // 동일한 키의 이전 요청이 있으면 중단
    }
    ajaxRequests[key] = $.ajax(ajaxOptions); // 새 요청을 저장하고 실행
}

/**
 * 문자열 포함 여부 확인
 * @param {string} str - 원본 문자열
 * @param {string} subStr - 찾을 문자열
 * @returns {boolean} 포함 여부
 */
function checkString(str, subStr) {
    if (str && subStr) {
        return str.includes(subStr);
    }
    return false;
}

/**
 * 객체가 null 또는 빈 값인지 확인
 * @param {*} obj - 확인할 객체
 * @returns {boolean} null 또는 빈 값 여부
 */
function isNull(obj) {
    return obj === null || obj === '' || typeof obj === 'undefined';
}

/**
 * 팝업을 화면 중앙에 띄우는 함수
 */
function popupCenter(url, title, w, h) {
    var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top;
    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;
    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
    if (window.focus) {
        newWindow.focus();
    }
}

/**
 * 쿠키 설정 함수
 */
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

/**
 * 쿠키 가져오기 함수
 */
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

/**
 * 로딩 인디케이터 표시/숨기기
 */
function showLoadingIndicator() {
    $("#loadingIndicator").css('display', 'flex');
}

function hideLoadingIndicator() {
    $("#loadingIndicator").css('display', 'none');
}

/**
 * 오버레이 표시/숨기기
 */
function pagestartOverlay() {
	$("#overlay").show();
	$("button").prop("disabled", true);
    Toastify({ text: "화면을 불러오는 중입니다...", duration: 5000 }).showToast();
}
function hideOverlay() {
	$("#overlay").hide();
	$("button").prop("disabled", false);
}

/**
 * 현재 날짜 및 시간 YYYY-MM-DD HH:MM:SS 형식으로 반환
 */
function getCurrentDateTime() {
    var now = new Date();
    var year = now.getFullYear();
    var month = ('0' + (now.getMonth() + 1)).slice(-2);
    var day = ('0' + now.getDate()).slice(-2);
    var hours = ('0' + now.getHours()).slice(-2);
    var minutes = ('0' + now.getMinutes()).slice(-2);
    var seconds = ('0' + now.getSeconds()).slice(-2);
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

/**
 * 검색 필드에서 Enter 키 입력 시 폼 제출
 */
function SearchEnter(){
 if(event.keyCode == 13){
	   $("#page").val('1');
	   document.getElementById('board_form').submit();
  }
}

/* ===================================================================
 * 2/6: 문서 로딩 및 공통 UI 이벤트 초기화
 * =================================================================== */

$(document).ready(function() {

    // DataTables 초기화
    if ($('#myEworks_Table').length > 0) {
        Eworks_dataTable = $('#myEworks_Table').DataTable({
            "paging": true,
            "ordering": true,
            "searching": false,
            "pageLength": 50,
            "lengthMenu": [25, 50, 100, 200, 500, 1000],
            "language": { "lengthMenu": "Show _MENU_ entries", "search": "Live Search:" },
            "columns": [
                { "data": "checkbox", "orderable": false }, { "data": "type" },
                { "data": "date" }, { "data": "author" }, { "data": "state" },
                { "data": "progress" }, { "data": "refer" }, { "data": "title" },
                { "data": "optionalColumn", "visible": false }
            ]
        });

        var savedPageNumber = getCookie('Eworks_pageNumber');
        if (savedPageNumber) {
            Eworks_dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }

        Eworks_dataTable.on('page.dt', function() {
            var pageInfo = Eworks_dataTable.page.info();
            setCookie('Eworks_pageNumber', pageInfo.page + 1, 10);
        });

        $('#myEworks_Table_length select').on('change', function() {
            Eworks_dataTable.page.len($(this).val()).draw();
        });
    }

    // 스피너 숨기기
    $("#spinner").hide();

    // 사이드 배너 스크롤 이벤트
    function handleSideBannerScroll(bannerClass) {
        var $banner = $(bannerClass);
        if ($banner.length > 0) {
            var floatPosition = parseInt($banner.css('top'));
            $(window).on('scroll', function() {
                var currentTop = $(window).scrollTop();
                $banner.stop().animate({ "top": currentTop + floatPosition + "px" }, 500);
            }).scroll();
        }
    }
    handleSideBannerScroll(".sideBanner");
    handleSideBannerScroll(".sideEworksBanner");

   // ==========================================================
    // ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼ 이 부분 추가 ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
    // ==========================================================
    // 전자결재 알림 배너(사이드바) 클릭 이벤트
    $(document).on('click', '.sideEworksBanner', function() {
        // '미결함' 탭(3번)으로 이동하는 함수 호출
        seltab(3);
        // 클릭 후 배너는 숨김
        $(this).hide();
    });
    // ==========================================================
    // ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲ 코드 추가 끝 ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲
    // ==========================================================	

    // 마우스 호버 시 프레임 표시/숨기기 로직
    function setupHoverFrame(triggerSelector, frameSelector) {
        var hideTimeout;
        $(document).on('mouseenter', triggerSelector, function() {
            clearTimeout(hideTimeout);
            var $trigger = $(this);
            $(frameSelector).css({
                top: $trigger.offset().top + $trigger.outerHeight() + 'px',
                left: $trigger.offset().left + 'px'
            }).show();
        }).on('mouseleave', triggerSelector, function() {
            hideTimeout = setTimeout(() => $(frameSelector).hide(), 100);
        });

        $(document).on('mouseenter', frameSelector, () => clearTimeout(hideTimeout))
                   .on('mouseleave', frameSelector, () => hideTimeout = setTimeout(() => $(frameSelector).hide(), 100));
    }
    setupHoverFrame('#showdate', '#showframe');
    setupHoverFrame('#showalign', '#showalignframe');
    setupHoverFrame('#showextract', '#showextractframe');
    setupHoverFrame('#showsearchtool', '#showsearchtoolframe');

    // 입력 필드 클리어 버튼(X)
    $(document).on('click', '.btnClear', function(e) {
        e.preventDefault();
        $(this).closest('.input-group').find('input').val("").focus();
    });

    // 전체 선택 체크박스
    $(document).on('change', '#checkAll', function() {
        $('.checkItem').prop('checked', this.checked);
    });

    // 날짜 범위 설정 쿠키 저장
    $('.change_dateRange').on('click', function() {
      setCookie('dateRange', '직접설정', 30);
     });
});

/* ===================================================================
 * 3/6: 전자결재 목록 및 네비게이션
 * =================================================================== */

/**
 * 탭 선택 시 호출되는 메인 함수
 * @param {number} e_num - 탭 번호 (1~9)
 */
function seltab(e_num) {
	console.log('seltab : ', e_num);
    showLoadingIndicator();
    activeTab = e_num;
    var tabMap = {
        1: 'draft', 2: 'send', 3: 'noend', 4: 'ing',
        5: 'end', 6: 'reject', 7: 'wait', 8: 'refer', 9: 'trash'
    };
    $('#choice').val(e_num);
    $('#eworksel').val(tabMap[e_num]);
    $("#search").val('');
    setTimeout(refresheworks, 500); // 약간의 딜레이 후 실행
}

/**
 * 전자결재 목록 전체를 새로고침하는 함수
 */
function refresheworks() {
    update_eworks_nav(); // 네비게이션 UI 업데이트
    eworksList();       // 목록 데이터 로드 및 DataTables 갱신
    var currentPage = $('#eworksPage').val() || 1;
    eworks_movetoPage(currentPage); // 현재 페이지 목록 HTML 로드
    hideLoadingIndicator();
}

/**
 * DataTables에 데이터를 로드하고 테이블을 다시 그리는 함수
 */
function eworksList() {
	console.log('eworksList');
    manageAjax('load_datatable', {
        url: "/eworks/load_list.php", // 서버는 DataTables가 요구하는 JSON 형식으로 응답해야 함
        type: "post",
        data: $("#eworks_board_form").serialize(),
        dataType: "json",
        success: function(data) {
            if (Eworks_dataTable) {
                Eworks_dataTable.clear();
                if (data && data.data) { // 서버 응답에 'data' 배열이 있는지 확인
                    Eworks_dataTable.rows.add(data.data);
                }
                Eworks_dataTable.draw(false); // 페이지네이션 유지하며 다시 그리기
            }
        },
        error: (jqxhr, st, err) => { if (st !== 'abort') console.error("DataTables 로드 오류:", err); }
    });
}

/**
 * 서버로부터 페이지 목록(HTML)을 받아와 특정 영역에 표시
 * @param {number} eworksPage - 이동할 페이지 번호
 */
function eworks_movetoPage(eworksPage) {
    $("#eworksPage").val(eworksPage);
    manageAjax('load_page_html', {
        url: '/eworks/list.php',
        type: 'POST',
        data: $("#eworks_board_form").serialize(),
        success: function(response) {
            $('#eworks_list').html(response);
            $(".nav-link").removeClass("active");
            $(`.nav-link[onclick="seltab('${activeTab}')"]`).addClass("active");
        }
    });
}

/**
 * 각 탭의 문서 개수를 서버에서 받아와 뱃지에 업데이트
 */
function load_eworkslist() {
    manageAjax('load_counts', {
        url: '/eworks/load_eworks.php',
        type: 'POST',
        data: $("#eworks_board_form").serialize(),
        dataType: "json",
        success: function(data) {
            if (!data) return;
            for (let i = 0; i < 7; i++) {
                $(`#badge${i+1}`).text(data[`val${i}`] > 0 ? data[`val${i}`] : '');
            }
            alert_eworkslist(); // 알림 상태 확인
        }
    });
}

/**
 * 주기적으로 미결 문서 개수를 확인하여 알림(종, 배너)을 표시
 */
function alert_eworkslist() {
    manageAjax('check_alerts', {
        url: '/eworks/load_eworks.php',
        type: 'POST',
        data: $("#eworks_board_form").serialize(),
        dataType: "json",
        success: function(data) {
            var noendCount = data ? data["val2"] : 0; // val2가 미결함
            var $alertBell = $('#alert_eworks_bell');
            var $sideBanner = $('.sideEworksBanner');

            if (noendCount > 0) {
                $alertBell.add('#bellIcon').addClass('blink').css('display', 'inline');
                if (!isModalOpen) {
                    $sideBanner.show();
                }
            } else {
                $alertBell.add('#bellIcon').removeClass('blink').hide();
                $sideBanner.hide();
            }
        }
    });
}

/**
 * 네비게이션 바(탭 메뉴) UI를 서버에서 받아와 업데이트
 */
function update_eworks_nav() {
    $.ajax({
        url: "/eworks/eworks_nav.php?selnum=" + activeTab,
        type: "GET",
        success: function(response) {
            $("#eworksNavContainer").html(response);
        },
        error: (xhr, st, err) => console.error("네비게이션 로드 오류: " + err)
    });
}

/* ===================================================================
 * 4/6: 전자결재 상세 조회 및 렌더링
 * =================================================================== */

/**
 * 전자결재 상세 보기 메인 함수
 * @param {string|number} e_num - 문서 번호
 * @param {number} eworksPage - 현재 페이지 번호
 */
viewEworks_detail = function(e_num, eworksPage) {
    $("#eworksPage").val(eworksPage);
    initializeContents();

    $('#status').val(e_num ? '' : 'draft');
    $('#e_num').val(e_num || '');

    manageAjax('load_one', {
        url: "/eworks/load_listone.php",
        type: "post",
        data: $("#eworks_board_form").serialize(),
        dataType: "json",
        success: function(data) {
            populateEworksForm(data, e_num);
            createApprovalTable(data.e_line, data.e_confirm);

            var isReadOnly = data.status && data.status !== 'draft';
            setModalElementsReadonly(isReadOnly);

            manageAjax('load_buttons', {
                url: "/eworks/eworksBtn.php",
                type: 'POST',
                data: $("#eworks_board_form").serialize(),
                success: function(response) {
                    $('#eworksBtn').html(response);
                    $("#eworks_viewmodal").modal({ backdrop: 'static', keyboard: false }).modal("show");
                }
            });
        }
    });
};

/**
 * 상세 보기 폼에 서버에서 받은 데이터 채우기
 * @param {object} data - 문서 데이터
 * @param {string|number} e_num - 문서 번호
 */
function populateEworksForm(data, e_num) {
    // 폼 초기화
    $('#eworks_board_form').find('input[type="text"], input[type="hidden"], textarea').not('#user_id, #user_name, #eworksPage, #choice, #eworksel').val('');

    if (!e_num) { // 신규 작성 시
        $('#registdate').val(getCurrentDateTime());
        $('#author_id').val($('#user_id').val());
        $('#author').val($('#user_name').val());
        $('#status').val('draft');
    } else { // 기존 문서 로드
        Object.keys(data).forEach(function(key) {
            var $element = $(`#${key}`);
            if ($element.length) {
                $element.val(data[key] ? String(data[key]).trim() : '');
            }
        });
        renderDynamicContent(data.eworks_item, data.contents);
    }
    $('#numdisplay').text(String(e_num || '신규').trim());
}

/**
 * `eworks_item` 종류에 따라 동적 HTML 컨텐츠 렌더링
 * @param {string} itemType - 문서 종류 (연차, 지출결의서 등)
 * @param {string} contents - JSON 형태의 컨텐츠
 */
function renderDynamicContent(itemType, contents) {
    // ... (이전 답변의 renderDynamicContent 함수 로직과 동일)
    // 이 함수는 매우 길고 특정 비즈니스 로직에 종속적이므로, 간결성을 위해 생략합니다.
    // 기존 코드의 해당 함수 부분을 여기에 붙여넣으시면 됩니다.
    // 핵심: JSON.parse(contents) 후, key-value를 기반으로 table HTML을 생성하여 #htmlContainer에 삽입
}

/**
 * 결재라인 테이블 동적 생성
 * @param {string} e_line - 결재자 이름 목록 (구분자: !)
 * @param {string} e_confirm - 결재일시 목록 (구분자: !)
 */
function createApprovalTable(e_line, e_confirm) {
    $('#approvalTable').empty();
    if (isNull(e_line)) return;

    var approvalNames = e_line.split('!');
    var approvalDates = (e_confirm || '').split('!');
    var table = $('<table>').addClass('table table-bordered table-sm');
    var thead = $('<thead>').html('<tr><th colspan="' + approvalNames.length + '" class="text-center p-1">결재</th></tr>');
    var tbody = $('<tbody>');
    var nameRow = $('<tr>');
    var dateRow = $('<tr>');

    $.each(approvalNames, function(index, name) {
        if (name.trim() !== "") {
            nameRow.append($('<td>').addClass('text-center p-2').css('height', '60px').text(name));
            dateRow.append($('<td>').addClass('text-center p-1').html( (approvalDates[index] || '&nbsp;') ));
        }
    });

    tbody.append(nameRow).append(dateRow);
    table.append(thead).append(tbody);
    $('#approvalTable').append(table);
}

/**
 * 컨텐츠 영역(`textarea` 또는 동적 테이블)을 초기 상태로 복원
 */
function initializeContents() {
    var defaultContent = `<textarea id="contents" class="form-control" name="contents" rows="10"></textarea>`;
    $('#htmlContainer').html(defaultContent);
}

/**
 * 모달 내의 입력 요소들의 readonly 속성을 설정
 * @param {boolean} readonly - readonly 적용 여부
 */
function setModalElementsReadonly(readonly) {
    var $modal = $('#eworks_viewmodal');
    $modal.find('input, textarea').prop('readonly', readonly);
    $modal.find('select').prop('disabled', readonly);
    // 특정 버튼들은 제외하고 disabled 처리
    $modal.find('button').not('#closeModaldetailBtn, #closesecondModalBtn, .close_eworksview, #eworks_saveBtn, #eworks_approvalBtn, #eworks_delBtn, #eworks_sendBtn, #eworks_recallBtn, #eworks_rejectBtn, #eworks_waitBtn').prop('disabled', readonly);
}

/**
 * `eworks_item` selectbox 변경 시 호출
 */
function eworksItemChanged(selectElement) {
    var value = selectElement.value;
    var status = $("#status").val();
    if (value === "연차" && isNull(status) || status === 'draft') {
        popupCenter('/annualleave/write_form_ask.php', '연차 신청', 420, 720);
    }
}


/* ===================================================================
 * 5/6: 전자결재 액션 및 모달 이벤트 (CRUD)
 * =================================================================== */

$(document).ready(function() {
    var $document = $(document);

    // 모달 닫기 버튼
    $document.on("click", "#closeModalxBtn, #closeEworksBtn, #closeModaldetailBtn, #closesecondModalBtn, .close_eworksview", function() {
        $(this).closest('.modal').modal('hide');
    });

    // 상세 모달이 닫힐 때, 알림 배너 상태 갱신
    $('#eworks_viewmodal').on('hidden.bs.modal', function () {
        isModalOpen = false;
        alert_eworkslist();
    });
    // 상세 모달이 열릴 때
    $('#eworks_viewmodal').on('shown.bs.modal', function () {
        isModalOpen = true;
        $('.sideEworksBanner').hide();
    });

    // 신규 작성창 열기
    $document.on("click", "#eworks_WindowBtn", () => viewEworks_detail('', 1));

    // 저장(임시저장) 버튼
    $document.on("click", "#eworks_saveBtn", function(e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.prop('disabled') || isNull($("#e_line").val()) || isNull($("#e_title").val())) {
            if (isNull($("#e_line").val()) || isNull($("#e_title").val())) {
                Toastify({ text: "결재라인과 제목은 필수입니다.", duration: 3000 }).showToast();
            }
            return;
        }
        $btn.prop('disabled', true);
        $("#SelectWork").val(Number($("#e_num").val()) !== 0 ? 'update' : 'insert');
        var orderdata = new FormData($('#eworks_board_form')[0]);

        manageAjax('process_save', {
            url: "/eworks/process.php", type: "post", data: orderdata, dataType: 'json',
            enctype: 'multipart/form-data', processData: false, contentType: false,
            success: function(data) {
                $("#e_num").val(data.e_num);
                Toastify({ text: "파일 저장 완료!", duration: 2000 }).showToast();
            },
            complete: () => $btn.prop('disabled', false)
        });
    });

    // 결재요청, 회수, 승인, 반려, 보류, 삭제 등 공통 액션 처리
    $document.on("click", "#eworks_sendBtn, #eworks_recallBtn, #eworks_approvalBtn, #eworks_rejectBtn, #eworks_waitBtn, #eworks_delBtn", function(e) {
        e.preventDefault();
        var action = $(this).attr('id').replace('eworks_', '').replace('Btn', '');
        var actionMap = {
            send: { title: '결재 요청', text: '결재를 올리시겠습니까?', icon: 'info' },
            recall: { title: '결재 회수', text: '결재를 회수하시겠습니까?', icon: 'info' },
            approval: { title: '결재 승인', text: '결재를 승인하시겠습니까?', icon: 'info' },
            reject: { title: '결재 반려', text: '결재를 반려하시겠습니까?', icon: 'warning' },
            wait: { title: '결재 보류', text: '결재를 보류하시겠습니까?', icon: 'warning' },
            del: { title: '문서 삭제', text: '문서를 삭제하시겠습니까?', icon: 'warning' }
        };

        // 삭제 전 상태 확인
        if (action === 'del' && ['noend', 'end', 'ing'].includes($("#status").val())) {
            Toastify({ text: "결재중이거나 완료된 문서는 삭제할 수 없습니다.", duration: 3000 }).showToast();
            return;
        }

        Swal.fire({
            title: actionMap[action].title, text: actionMap[action].text,
            icon: actionMap[action].icon, showCancelButton: true,
            confirmButtonText: actionMap[action].title, cancelButtonText: '취소'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#SelectWork").val(action === 'del' ? 'deldata' : action);
                manageAjax('process_action', {
                    url: "/eworks/process.php", type: "post",
                    data: $("#eworks_board_form").serialize(), dataType: "json",
                    success: function(data) {
                        Toastify({ text: `${actionMap[action].title} 완료!`, duration: 2000 }).showToast();
                        $("#eworks_viewmodal").modal("hide");
                        setTimeout(refresheworks, 500);
                    }
                });
            }
        });
    });

    // 선택 항목 일괄 삭제
    $document.on("click", "#deleteSelectedBtn", deleteSelectedEworks);
    // 선택 항목 일괄 결재
    $document.on("click", "#approvalSelectedBtn", approvalSelectedEworks);
});

// 선택된 항목 일괄 삭제
function deleteSelectedEworks() {
    var selectedEworks = $('.checkItem:checked').map(function() { return $(this).data('id'); }).get();
    if (selectedEworks.length === 0) {
        alert('삭제할 항목을 선택해주세요.'); return;
    }
    Swal.fire({ title: '선택 삭제', text: "선택된 항목을 삭제하시겠습니까?", icon: 'warning', showCancelButton: true, confirmButtonText: '삭제' })
        .then((result) => {
            if (result.isConfirmed) {
                manageAjax('delete_selected', {
                    url: "/eworks/deleteSelected.php", type: "post", data: { selectedIds: selectedEworks }, dataType: "json",
                    success: function(data) {
                        Toastify({ text: "선택된 항목 삭제 완료!", duration: 3000 }).showToast();
                        setTimeout(refresheworks, 500);
                    }
                });
            }
        });
}

// 선택된 항목 일괄 결재
function approvalSelectedEworks() {
    var selectedEworks = $('.checkItem:checked').map(function() { return $(this).data('id'); }).get();
    if (selectedEworks.length === 0) {
        alert('결재할 항목을 선택해주세요.'); return;
    }
    Swal.fire({ title: '일괄 결재', text: "선택된 항목을 결재하시겠습니까?", icon: 'info', showCancelButton: true, confirmButtonText: '승인' })
        .then((result) => {
            if (result.isConfirmed) {
                manageAjax('approve_selected', {
                    url: "/eworks/approvalSelected.php", type: "post", data: { selectedIds: selectedEworks }, dataType: "json",
                    success: function(data) {
                        Toastify({ text: "선택 결재 완료!", duration: 3000 }).showToast();
                        setTimeout(refresheworks, 500);
                    }
                });
            }
        });
}

/* ===================================================================
 * 6/6: 기타 유틸리티 및 보조 기능
 * =================================================================== */

/**
 * 결재라인 지정 팝업 열기
 */
function setLine() {
    var val = $("#e_line_id").val();
    popupCenter("/eworks/setline.php?e_line_id=" + val, '결재라인 지정', 600, 500);
}

/**
 * 참조인 지정 팝업 열기
 */
function setRef() {
    var val = $("#r_line_id").val();
    popupCenter("/eworks/setRef.php?r_line_id=" + val, '참조인 지정', 600, 900);
}

/**
 * 의견(댓글) 추가
 * @param {string|number} e_num - 부모 문서 번호
 */
eworks_insert_ripple = function (e_num) {
    var $btn = $(this); // 이벤트 핸들러에서 호출될 경우의 버튼
    if ($btn.prop && $btn.prop('disabled')) return;

    $("#SelectWork").val('insert_ripple');
    // e_num 파라미터를 사용하기 위해 폼 데이터에 추가할 수 있음
    // $("#e_num_for_ripple").val(e_num);

    manageAjax('insert_ripple', {
        url: "/eworks/process.php", type: "post",
        data: $("#eworks_board_form").serialize(), dataType: "json",
        success: function(data) {
            Toastify({ text: "의견 추가 완료!", duration: 2000 }).showToast();
            // 성공 시 UI에 댓글 추가하는 로직
            var newCommentHtml = `<div class="card ripple-item" id="ripple-${data.num}"> ... </div>`;
            $('#comments-container').append(newCommentHtml);
            $('#ripple_content_input').val(''); // 댓글 입력창 초기화
        }
    });
}

/**
 * 의견(댓글) 삭제
 * @param {string|number} ripple_num - 삭제할 댓글 번호
 */
eworks_delete_ripple = function (ripple_num) {
    $("#ripple_num").val(ripple_num);
    Swal.fire({ title: '의견 삭제', text: "정말 삭제 하시겠습니까?", icon: 'warning', showCancelButton: true, confirmButtonText: '삭제'})
        .then((result) => {
            if (result.isConfirmed) {
                $("#SelectWork").val('delete_ripple');
                manageAjax('delete_ripple', {
                    url: "/eworks/process.php", type: "post",
                    data: $("#eworks_board_form").serialize(), dataType: "json",
                    success: function(data) {
                        Toastify({ text: "의견 삭제 완료!", duration: 2000 }).showToast();
                        $("#ripple-" + ripple_num).remove();
                    }
                });
            }
    });
}

/**
 * 휴지통에서 문서 복구
 * @param {string|number} e_num - 복구할 문서 번호
 */
restore = function (e_num) {
    $("#e_num").val(e_num);
    Swal.fire({ title: '문서 복구', text: "정말 복구 하시겠습니까?", icon: 'info', showCancelButton: true, confirmButtonText: '복구'})
        .then((result) => {
            if (result.isConfirmed) {
                $("#SelectWork").val('restore');
                manageAjax('restore', {
                    url: "/eworks/process.php", type: "post",
                    data: $("#eworks_board_form").serialize(), dataType: "json",
                    success: function(data) {
                        Toastify({ text: "파일 복구 완료!", duration: 2000 }).showToast();
                        setTimeout(refresheworks, 500);
                    }
                });
            }
    });
}

/**
 * 목록에서 영구 제외 (휴지통 비우기 개념)
 * @param {string|number} e_num - 제외할 문서 번호
 */
viewExcept = function (e_num) {
    $("#e_num").val(e_num);
    Swal.fire({ title: '완전 삭제', text: "복구할 수 없습니다. 정말 삭제하시겠습니까?", icon: 'error', showCancelButton: true, confirmButtonText: '완전 삭제'})
        .then((result) => {
            if (result.isConfirmed) {
                $("#SelectWork").val('except'); // 'except'는 영구삭제를 의미
                manageAjax('except', {
                    url: "/eworks/process.php", type: "post",
                    data: $("#eworks_board_form").serialize(), dataType: "json",
                    success: function(data) {
                        Toastify({ text: "파일 완전 삭제 완료!", duration: 2000 }).showToast();
                        setTimeout(refresheworks, 500);
                    }
                });
            }
    });
}

/**
 * 결재 완료 문서를 보관함으로 이동 (목록에서 제외)
 * @param {string|number} e_num - 처리할 문서 번호
 */
approvalviewExcept = function (e_num) {
    Swal.fire({ title: '보관함으로 이동', text: "결재 완료된 문서를 보관 처리하시겠습니까?", icon: 'info', showCancelButton: true, confirmButtonText: '확인'})
        .then((result) => {
            if (result.isConfirmed) {
                manageAjax('archive', { // 'archive'는 보관을 의미 (가상)
                    url: "/eworks/approvalSelected.php", type: "post", data: { selectedIds: [e_num] }, dataType: "json",
                    success: function(data) {
                        Toastify({ text: "보관 처리 완료!", duration: 2000 }).showToast();
                        setTimeout(refresheworks, 500);
                    }
                });
            }
    });
}