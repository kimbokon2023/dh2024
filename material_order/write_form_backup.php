<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : "";

$first_writer = '';

if ($mode === 'copy') {
    $title_message = "(데이터복사) 구매 리스트";
} else {
    $title_message = "구매 리스트";
}

$tablename = 'material_order';
?> 

<link href="css/style.css" rel="stylesheet">    
<title> <?=$title_message?> </title>

<style>
.hidden {
    display: none;
}

.scrollable-modal-body {
    max-height: 500px;
    overflow-y: auto;
}
</style>
</head>
<body>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$todate = date("Y-m-d"); // 현재일자 변수지정

if ($mode == "modify" || $mode == "view") {
    try {
        $sql = "select * from material_order where num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_STR);
        $stmh->execute();
        $count = $stmh->rowCount();
        if ($count < 1) {
            print "검색결과가 없습니다.<br>";
        } else {
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
            include "_row.php";
        }
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode !== "modify" && $mode !== "copy" && $mode !== "split" && $mode !== "view") {
    include '_request.php';
    $first_writer = $user_name;
    $orderDate = $todate;
}

if ($mode == "copy" || $mode == 'split') {
    try {
        $sql = "select * from material_order where num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_STR);
        $stmh->execute();
        $count = $stmh->rowCount();
        if ($count < 1) {
            print "검색결과가 없습니다.<br>";
        } else {
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
        }
        include '_row.php';
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }
    $num = 0;
    $orderDate = $todate;
    $mode = "insert";
}
?>
<form id="board_form" name="board_form" method="post" enctype="multipart/form-data" onkeydown="return captureReturnKey(event)">
    <input type="hidden" id="first_writer" name="first_writer" value="<?= isset($first_writer) ? $first_writer : '' ?>">
    <input type="hidden" id="update_log" name="update_log" value="<?= isset($update_log) ? $update_log : '' ?>">
    <input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
    <input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">
    <input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
    <input type="hidden" id="motorlist" name="motorlist">
    <input type="hidden" id="wirelessClist" name="wirelessClist">
    <input type="hidden" id="wireClist" name="wireClist">
    <input type="hidden" id="wirelessLinklist" name="wirelessLinklist">
    <input type="hidden" id="wireLinklist" name="wireLinklist">
    <input type="hidden" id="bracketlist" name="bracketlist">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/material_order/modal.php'; ?>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center mt-3 mb-5">
                    <span class="fs-5 me-5"> <?=$title_message?> (<?=$mode?>) </span>
                    <?php if ($mode !== 'view') { ?>
                        <button type="button" class="btn btn-dark btn-sm me-2 saveBtn"> <ion-icon name="save-outline"></ion-icon> 저장 </button>
                    <?php } else { ?>
                        <button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php?mode=modify&num=<?=$num?>';"> <ion-icon name="color-wand-outline"></ion-icon> 수정 </button>
                        <button type="button" class="btn btn-danger btn-sm me-1 deleteBtn"> <ion-icon name="trash-outline"></ion-icon> 삭제 </button>
                        <button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php';"> <ion-icon name="create-outline"></ion-icon> 신규 </button>
                        <button type="button" class="btn btn-primary btn-sm me-1" onclick="location.href='write_form.php?mode=copy&num=<?=$num?>';"> <i class="bi bi-copy"></i> 복사</button>
                        <button type="button" class="btn btn-secondary btn-sm me-1" onclick="generateExcel();"> Excel 구매 발주서 </button>
                    <?php } ?>
                    &nbsp;&nbsp;
                    최초 : <?=$first_writer?>
                    <br>
                    <?php $update_log_extract = substr($update_log, 0, 31); ?>
                    &nbsp;&nbsp; 수정 : <?=$update_log_extract?> &nbsp;&nbsp;&nbsp;
                    <span class="text-end" style="width:10%;">
                        <button type="button" class="btn btn-outline-dark btn-sm me-2" id="showlogBtn"> H </button>
                        <button class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 닫기 </button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </span>
                </div>
                <div class="d-flex row justify-content-center">
                    <div class="d-flex row">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td style="width:80px;"> 등록일 </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-start">
                                            <input type="date" name="orderDate" id="orderDate" value="<?=$orderDate?>" class="form-control" style="width:100px;">
                                        </div>
                                    </td>
                                    <td> 메모 </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-start">
                                            <textarea name="memo" id="memo" class="form-control text-start" style="height:40px;"><?=$memo?></textarea>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
        function generateTableSection($id, $title, $badgeClass = 'bg-dark') {
            echo "
            <div class='d-flex row justify-content-center m-1 p-2 rounded' style='border: 1px solid #392f31;'>
                <div class='d-flex mb-2'>
                    <span class='badge $badgeClass fs-6 me-3'>$title</span>
                    <button type='button' class='btn btn-primary btn-sm viewNoBtn add-row' data-table='{$id}Table' style='margin-right: 5px;'>+</button>
                </div>
                <div class='d-flex row'>
                    <table class='table table-bordered' id='{$id}Table'>
                        <thead id='thead_$id'>
                            <tr>
                                <th class='text-center' style='width:200px;'>型号(모델)</th>
                                <th class='text-center' style='width:150px;'>数量(수량)</th>
                                <th class='text-center' style='width:100px;'>单价(단가)</th>
                                <th class='text-center' style='width:150px;'>配置(비고)</th>
                                <th class='text-center' style='width:150px;'>合计(합계)</th>
                                <th class='text-center' style='width:30px;'>삭제</th>
                            </tr>
                        </thead>
                        <tbody id='{$id}Group'>
                            <!-- 자동생성 -->
                        </tbody>
                    </table>
                </div>
            </div>
            ";
        }

        generateTableSection('motorlist', '电机 (모터)');
        generateTableSection('wirelessClist', '无线分控 (무선 콘트롤러)');
        generateTableSection('wireClist', '有线分控 (유선 콘트롤러)');
        generateTableSection('wirelessLinklist', '无线主控 (무선 제어기)');
        generateTableSection('wireLinklist', '有线主控 (유선 제어기)');
        generateTableSection('bracketlist', '支架板 (브라켓트)');
        ?>
    </div>
</form>

<script>
var ajaxRequest = null;
var ajaxRequest_write = null;
var motorlistOptions = [] ;
var wirelessClistOptions =  [];
var wireClistOptions = [];
var wirelessLinklistOptions = [];
var wireLinklistOptions = [];
var bracketlistOptions = [];

$(document).ready(function() {
    initializePage();
    bindEventHandlers();
    fetchItemOptions();

    $("#showlogBtn").click(function() {
        var num = '<?= $num ?>';
        var workitem = 'material_order';
        var btn = $(this);
        popupCenter("../Showlog.php?num=" + num + "&workitem=" + workitem, '로그기록 보기', 500, 500);
        btn.prop('disabled', false);
    });

    $(".saveBtn").click(function() {
        saveData();
    });

    $(".deleteBtn").click(function() {
        deleteData();
    });
});

function initializePage() {
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';

    var motorlist = <?= json_encode($motorlist ?? []) ?>;
    var wirelessClist = <?= json_encode($wirelessClist ?? []) ?>;
    var wireClist = <?= json_encode($wireClist ?? []) ?>;
    var wirelessLinklist = <?= json_encode($wirelessLinklist ?? []) ?>;
    var wireLinklist = <?= json_encode($wireLinklist ?? []) ?>;
    var bracketlist = <?= json_encode($bracketlist ?? []) ?>;

    loadTableData('#motorlistTable', motorlist, 'motorlistTable');
    loadTableData('#wirelessClistTable', wirelessClist, 'wirelessClistTable');
    loadTableData('#wireClistTable', wireClist, 'wireClistTable');
    loadTableData('#wirelessLinklistTable', wirelessLinklist, 'wirelessLinklistTable');
    loadTableData('#wireLinklistTable', wireLinklist, 'wireLinklistTable');
    loadTableData('#bracketlistTable', bracketlist, 'bracketlistTable');

    $('input[name="col1[]"]').each(function() {
        initializeAutocomplete(this, getTableOptions(this));
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    if ('<?= $mode ?>' === 'view') {
        disableInputsForViewMode();
    }

    $('input').attr('autocomplete', 'off');
}

function bindEventHandlers() {
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });

    $(document).on('input', 'input[name="col1[]"]', function() {
        initializeAutocomplete(this, getTableOptions(this));
    });

    $(document).on('click', '.specialbtnClear', function(e) {
        e.preventDefault();
        $(this).siblings('input').val('').focus();
    });

    $(document).on('click', '.motorlistTable', function() {
        var input = $(this).closest('tr').find('input[name="col1[]"]');
        showModal(motorlistOptions, '#itemModalTemplate', '#itemModalBody', input);
    });

    $(document).on('click', '.wirelessClistTable', function() {
        var input = $(this).closest('tr').find('input[name="col1[]"]');
        showModal(wirelessClistOptions, '#itemModalTemplate', '#itemModalBody', input);
    });

    $(document).on('click', '.wireClistTable', function() {
        var input = $(this).closest('tr').find('input[name="col1[]"]');
        showModal(wireClistOptions, '#itemModalTemplate', '#itemModalBody', input);
    });

    $(document).on('click', '.wirelessLinklistTable', function() {
        var input = $(this).closest('tr').find('input[name="col1[]"]');
        showModal(wirelessLinklistOptions, '#itemModalTemplate', '#itemModalBody', input);
    });

    $(document).on('click', '.wireLinklistTable', function() {
        var input = $(this).closest('tr').find('input[name="col1[]"]');
        showModal(wireLinklistOptions, '#itemModalTemplate', '#itemModalBody', input);
    });

    $(document).on('click', '.bracketlistTable', function() {
        var input = $(this).closest('tr').find('input[name="col1[]"]');
        showModal(bracketlistOptions, '#itemModalTemplate', '#itemModalBody', input);
    });

    $(document).on('click', '#itemModalBody tr', function() {
        var selectedItem = $(this).text().trim();
        if (targetInput) {
            targetInput.val(selectedItem);
            targetInput = null;
        }
        $('#itemModalTemplate').modal('hide');
    });

    $(document).on('click', '.close-modal', function() {
        $(this).closest('.modal').modal('hide');
    });

    $(document).on('click', '.add-row', function() {
        var tableId = $(this).data('table');
        var tableBody = $('#' + tableId).find('tbody');
        addRow(tableBody, {}, tableId);
    });
}

function fetchItemOptions() {
    if (ajaxRequest !== null) {
        ajaxRequest.abort();
    }
    ajaxRequest = $.ajax({
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        url: "fetch_item.php",
        type: "post",
        data: '',
        dataType: "json",
        success: function(data) {
            motorlistOptions = data.motorItems || [];
            wirelessClistOptions = data.wirelessControllerItems || [];
            wireClistOptions = data.wireControllerItems || [];
            wirelessLinklistOptions = data.wirelessLinkItems || [];
            wireLinklistOptions = data.wireLinkItems || [];
            bracketlistOptions = data.bracketItems || [];
            ajaxRequest = null;
        },
        error: function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
        }
    });
}

function addRow(tableBody, rowData, typebutton) {
    var newRow = $('<tr>');
    newRow.append('<td class="text-center">' +
        '<div class="d-flex">' +
        '<button type="button" class="btn btn-outline-primary btn-sm viewNoBtn ' + typebutton + ' me-1" data-modal="itemModalTemplate"><i class="bi bi-search"></i></button>' +
        '<div class="specialinputWrap"><input type="text" name="col1[]" style="width:100%;" class="form-control text-start" required value="' + (rowData.col1 || '') + '" data-modal="itemModalTemplate"><button class="specialbtnClear"></button></div></div></td>');
    newRow.append('<td class="text-center"><input type="text" name="col2[]" class="form-control text-center" required onkeyup="formatNumber(this);" value="' + (rowData.col2 || '') + '"></td>');
    newRow.append('<td class="text-center"><input type="text" name="col3[]" class="form-control text-center"  onkeyup="formatNumber(this);" value="' + (rowData.col3 || '') + '"></td>');
    newRow.append('<td class="text-center">' +
        '<select name="col4[]" class="form-control text-center">' +
        '<option value=""></option>' +
        '<option value="220V:60HZ"' + (rowData.col4 === '220V:60HZ' ? ' selected' : '') + '>220V:60HZ</option>' +
        '<option value="380V:60HZ"' + (rowData.col4 === '380V:60HZ' ? ' selected' : '') + '>380V:60HZ</option>' +
        '</select>' +
        '</td>');
    newRow.append('<td class="text-center"><input type="text" name="col5[]" class="form-control text-center" value="' + (rowData.col5 || '') + '"></td>');
    newRow.append('<td class="text-center"><button type="button" class="btn btn-danger btn-sm viewNoBtn remove-row">-</button></td>');

    tableBody.append(newRow);
    initializeAutocomplete(newRow.find('input[name="col1[]"]'), getTableOptions(newRow.find('input[name="col1[]"]')));

    if (tableBody.children('tr').length === 1) {
        tableBody.closest('table').find('thead').show();
    }
}

function loadTableData(tableId, dataList, typebutton) {
    var tableBody = $(tableId).find('tbody');
    var theadId;

    switch (tableId) {
        case '#motorlistTable':
            theadId = '#thead_motorlist';
            break;
        case '#wirelessClistTable':
            theadId = '#thead_wirelessClist';
            break;
        case '#wireClistTable':
            theadId = '#thead_wireClist';
            break;
        case '#wirelessLinklistTable':
            theadId = '#thead_wirelessLinklist';
            break;
        case '#wireLinklistTable':
            theadId = '#thead_wireLinklist';
            break;
        case '#bracketlistTable':
            theadId = '#thead_bracketlist';
            break;
        default:
            theadId = null;
    }

    if (typeof dataList === 'string') {
        try {
            dataList = JSON.parse(dataList);
        } catch (e) {
            console.error('Failed to parse dataList:', e);
            dataList = [];
        }
    }

    if (theadId) {
        if (dataList.length === 0) {
            $(theadId).hide();
        } else {
            $(theadId).show();
        }
    }

    if (!Array.isArray(dataList)) {
        dataList = [];
    }

    if (dataList.length === 0) {
        console.log('no record');
    } else {
        dataList.forEach(function(item) {
            addRow(tableBody, item, typebutton);
        });
    }
}

function showModal(options, modalId, modalBodyId, input) {
    targetInput = input;
    $(modalBodyId).empty();
    options.forEach(function(item) {
        $(modalBodyId).append('<tr><td class="text-center">' + item + '</td></tr>');
    });
    $(modalId).modal('show');
}

function initializeAutocomplete(input, options) {
    $(input).autocomplete({
        source: function(request, response) {
            try {
                var filteredOptions = $.grep(options, function(option) {
                    return option.toLowerCase().indexOf(request.term.toLowerCase()) !== -1;
                });
                response(filteredOptions);
            } catch (e) {
                console.error("Error in autocomplete source function: ", e);
                response([]);
            }
        },
        select: function(event, ui) {
            $(this).val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $(this).val(ui.item.value);
            return false;
        }
    });
}

function getTableOptions(input) {
    var tableId = $(input).closest('table').attr('id');
    switch (tableId) {
        case 'motorlistTable':
            return motorlistOptions;
        case 'wirelessClistTable':
            return wirelessClistOptions;
        case 'wireClistTable':
            return wireClistOptions;
        case 'wirelessLinklistTable':
            return wirelessLinklistOptions;
        case 'wireLinklistTable':
            return wireLinklistOptions;
        case 'bracketlistTable':
            return bracketlistOptions;
        default:
            return [];
    }
}

function formatNumber(input) {
    input.value = input.value.replace(/\D/g, '');
    input.value = input.value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function saveData() {
    const myform = document.getElementById('board_form');
    const inputs = myform.querySelectorAll('input[required]');
    let allValid = true;

    console.log(inputs);

    inputs.forEach(input => {
        if (!input.value) {
            allValid = false;
            Toastify({
                text: "수량 등 필수입력 부분을 확인해 주세요.",
                duration: 2000,
                close: true,
                gravity: "top",
                position: "center",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)"
                },
            }).showToast();
            return;
        }
    });

    if (!allValid) return;

    var num = $("#num").val();
    $("#overlay").show();
    $("button").prop("disabled", true);

    Toastify({
        text: "저장중...",
        duration: 2000,
        close: true,
        gravity: "top",
        position: "center",
        style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)"
        },
    }).showToast();

    if (Number(num) < 1) {
        $("#mode").val('insert');
    } else {
        $("#mode").val('modify');
    }

    let formData = [];
    $('#motorlistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    let jsonString = JSON.stringify(formData);
    $('#motorlist').val(jsonString);

    formData = [];
    $('#wirelessClistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#wirelessClist').val(jsonString);

    formData = [];
    $('#wireClistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#wireClist').val(jsonString);

    formData = [];
    $('#wirelessLinklistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#wirelessLinklist').val(jsonString);

    formData = [];
    $('#wireLinklistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#wireLinklist').val(jsonString);

    formData = [];
    $('#bracketlistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#bracketlist').val(jsonString);

    var form = $('#board_form')[0];
    var datasource = new FormData(form);

    if (ajaxRequest_write !== null) {
        ajaxRequest_write.abort();
    }

    ajaxRequest_write = $.ajax({
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        url: "insert.php",
        type: "post",
        data: datasource,
        dataType: "json",
        success: function(data) {
            setTimeout(function() {
                if (window.opener && !window.opener.closed) {
                    if (typeof window.opener.restorePageNumber === 'function') {
                        window.opener.restorePageNumber();
                    }
                }
            }, 1000);
            ajaxRequest_write = null;
            setTimeout(function() {
                hideOverlay();
                self.close();
            }, 1000);
        },
        error: function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
        }
    });
}

function deleteData() {
    var first_writer = '<?= $first_writer ?>';
    var level = '<?= $level ?>';

    if (!first_writer.includes(first_writer) && level !== '1') {
        Swal.fire({
            title: '삭제불가',
            text: "작성자와 관리자만 삭제가능합니다.",
            icon: 'error',
            confirmButtonText: '확인'
        });
    } else {
        Swal.fire({
            title: '자료 삭제',
            text: "삭제는 신중! 정말 삭제하시겠습니까?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '삭제',
            cancelButtonText: '취소'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#mode").val('delete');
                var form = $('#board_form')[0];
                var formData = new FormData(form);

                formData.set('mode', $("#mode").val());
                formData.set('num', $("#num").val());

                if (ajaxRequest_write !== null) {
                    ajaxRequest_write.abort();
                }

                ajaxRequest_write = $.ajax({
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false,
                    cache: false,
                    timeout: 1000000,
                    url: "insert.php",
                    type: "post",
                    data: formData,
                    dataType: "json",
                    success: function(data) {
                        Toastify({
                            text: "파일 삭제완료 ",
                            duration: 2000,
                            close: true,
                            gravity: "top",
                            position: "center",
                            style: {
                                background: "linear-gradient(to right, #00b09b, #96c93d)"
                            },
                        }).showToast();
                        setTimeout(function() {
                            if (window.opener && !window.opener.closed) {
                                if (typeof window.opener.restorePageNumber === 'function') {
                                    window.opener.restorePageNumber();
                                }
                                window.opener.location.reload();
                                window.close();
                            }
                        }, 1000);
                    },
                    error: function(jqxhr, status, error) {
                        console.log(jqxhr, status, error);
                    }
                });
            }
        });
    }
}

function disableInputsForViewMode() {
    $('input, textarea').prop('readonly', true);
    $('select, .restrictbtn, .sub_add, .add').prop('disabled', true);
    $('input[type=file]').prop('readonly', false);
    $('input[type=checkbox]').prop('disabled', true);
    $('.viewNoBtn').prop('disabled', true);
    $('.specialbtnClear').prop('disabled', true);
}

function captureReturnKey(e) {
    if (e.keyCode == 13 && e.srcElement.type != 'textarea') {
        return false;
    }
}

function closePopup() {
    if (popupWindow && !popupWindow.closed) {
        popupWindow.close();
        isWindowOpen = false;
    }
}

function showWarningModal() {
    Swal.fire({
        title: '등록 오류 알림',
        text: '필수입력 요소를 확인바랍니다.',
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            return;
        }
    });
}

function showlotError() {
    Swal.fire({
        title: '등록 오류 알림',
        text: '입력 항목들을 점검해주세요.',
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            return;
        }
    });
}

function inputNumber(input) {
    const cursorPosition = input.selectionStart;
    const value = input.value.replace(/,/g, '');
    const formattedValue = Number(value).toLocaleString();
    input.value = formattedValue;
    input.setSelectionRange(cursorPosition, cursorPosition);
}
</script>

<script>
function generateExcel() {
    var table = document.getElementById('myTable');
    var rows = table.getElementsByTagName('tr');
    var data = [];

    // 각 행을 반복하여 데이터 수집
    for (var i = 1; i < rows.length; i++) { // 헤더 행을 건너뜀
        var cells = rows[i].getElementsByTagName('td');
        var checkbox = cells[0].querySelector('input');

        if (checkbox && checkbox.checked) { // 체크박스가 체크된 경우에만 데이터 수집
            var rowData = {};
            rowData['checkbox'] = checkbox.checked;
            rowData['delivery'] = cells[1]?.innerText || '';
            rowData['postalCode'] = cells[2]?.innerText || '';
            rowData['office'] = cells[3]?.innerText || '';
            rowData['receiver'] = cells[4]?.innerText || '';
            rowData['phone'] = cells[5]?.innerText || '';
            rowData['otherPhone'] = cells[6]?.innerText || '';
            rowData['address'] = cells[7]?.innerText || '';
            rowData['address1'] = cells[8]?.innerText || '';
            rowData['item'] = cells[9]?.innerText || '';
            rowData['quantity'] = cells[10]?.innerText || '';
            rowData['packaging'] = cells[11]?.innerText || '';
            rowData['unitPrice'] = cells[12]?.innerText || '';
            rowData['shippingType'] = cells[13]?.innerText || '';
            rowData['freight'] = cells[14]?.innerText || '';
            rowData['freight1'] = cells[15]?.innerText || '';
            rowData['freight2'] = cells[16]?.innerText || '';

            data.push(rowData);
        }
    }

    // saveExcel.php에 데이터 전송
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "order_saveExcel.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        console.log('Excel file generated successfully.');
                        // 다운로드 스크립트로 리디렉션
                        window.location.href = 'downloadExcel.php?filename=' + encodeURIComponent(response.filename.split('/').pop());
                    } else {
                        console.log('Failed to generate Excel file: ' + response.message);
                    }
                } catch (e) {
                    console.log('Error parsing response: ' + e.message + '\nResponse text: ' + xhr.responseText);
                }
            } else {
                console.log('Failed to generate Excel file: Server returned status ' + xhr.status);
            }
        }
    };
    xhr.send(JSON.stringify(data));
}

</script>

</body>
</html>