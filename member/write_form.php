<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || intval($_SESSION["level"]) > 1) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '회원관리(등록/수정)';
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/mymodal.php'; ?>   

<?php
isset($_REQUEST["num"]) ? $num = $_REQUEST["num"] : $num = ''; 
isset($_REQUEST["mode"]) ? $mode = $_REQUEST["mode"] : $mode = ''; 

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

if ($mode == 'modify') {
    try {
        $sql = "SELECT * FROM " . $DB . ".member WHERE num = ?";
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $num, PDO::PARAM_INT); 
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

        include '_row.php';
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    } 
} else {
    include '_row.php';
    $userlevel = '8';    
    $mode = 'insert';
	$company ='대한';
}
?>  

<title> <?=$title_message?> </title> 

<style>
    .table-hover tbody tr:hover {
        cursor: pointer;
    }
</style> 

</head>
<body>

<div class="container">
    <div class="d-flex justify-content-center align-items-center">
        <div class="col-12 text-center">
            <div class="card align-middle">
                <div class="card" style="padding:10px;margin:10px;">
                    <h4 class="card-title text-center" style="color:#113366;"> 회원등록/수정 </h4>
                </div>
                <div class="card-body text-center">
                    <form id="board_form" name="board_form" class="form-signin" method="post">
                        <input type="hidden" id="mode" name="mode" value="<?=$mode?>">
                        <input type="hidden" id="num" name="num" value="<?=$num?>">

                        <table class="table table-bordered">
                            <tr>
                                <td>* 성명</td>
                                <td><input type="text" id="name" name="name" value="<?=$name?>" class="form-control text-center" required></td>
                                <td>* ID</td>
                                <td><input type="text" id="id" name="id" value="<?=$id?>" class="form-control text-center" required></td>
                            </tr>
                            <tr>
                                <td>* Password</td>
                                <td><input type="text" id="pass" name="pass" value="<?=$pass?>" class="form-control text-center" required></td>
                                <td>연락처 HP</td>
                                <td><input type="text" id="hp" name="hp" value="<?=$hp?>" class="form-control text-center"></td>
                            </tr>
                            <tr>
                                <td>* 레벨</td>
                                <td colspan="1"><input type="text" id="userlevel" name="userlevel" value="<?=$userlevel?>" class="form-control text-center" required></td>                                
                                <td colspan="2"></td>                                
                            </tr>
                            <tr>                                
                                <td>* 회사</td>
                                <td><input type="text" id="company" name="company" value="<?=$company?>" class="form-control text-center" required></td>
                                <td>* 소속</td>
                                <td><input type="text" id="part" name="part" value="<?=$part?>" class="form-control text-center" required></td>								
                            </tr>
                            <tr>
                                <td>* 직위</td>
                                <td><input type="text" id="position" name="position" value="<?=$position?>" class="form-control text-center"></td>
                                <td>번호순서 Numorder</td>
                                <td><input type="text" id="numorder" name="numorder" value="<?=$numorder?>" class="form-control text-center"></td>
                            </tr>
                            <tr>
                                <td>eworks_level</td>
                                <td><input type="text" id="eworks_level" name="eworks_level" value="<?=$eworks_level?>" class="form-control text-center"></td>
                                <td>ecountID</td>
                                <td><input type="text" id="ecountID" name="ecountID" value="<?=$ecountID?>" class="form-control text-center"></td>
                            </tr>
                            <tr>
                                <td>입사일 (Enter Date)</td>
                                <td><input type="date" id="enterDate" name="enterDate" value="<?=$enterDate?>" class="form-control text-center"></td>
                                <td>퇴사일 (Quit Date)</td>
                                <td><input type="date" id="quitDate" name="quitDate" value="<?=$quitDate?>" class="form-control text-center"></td>
                            </tr>
							<tr>
								<td>생년월일 (Birthday)</td>
								<td><input type="date" id="birthday" name="birthday" value="<?= $birthday ?>" class="form-control text-center"></td>
								
								<td>작업일지 작성</td>
								<td>
									<input type="checkbox" id="dailyworkcheck" name="dailyworkcheck" class="form-check-input"
									<?php if ($dailyworkcheck == '작성') echo 'checked'; ?>>
									<input type="hidden" id="hidden_dailyworkcheck" name="hidden_dailyworkcheck" value="<?= $dailyworkcheck ?>">
								</td>
							</tr>

                            <tr>
                                <td>자택주소 (Address)</td>
                                <td colspan="3"><input type="text" id="address" name="address" value="<?=$address?>" class="form-control text-start"></td>
                            </tr>
							
                        </table>
                        
                        <div class="d-flex justify-content-center mt-5 mb-2">
                            <?php if ($level == '1') {
                                print '<button id="saveBtn" class="btn btn-dark btn-sm me-2" type="button"> <i class="bi bi-floppy-fill"></i>  저장 </button>';
                                print '<button id="delBtn" class="btn btn-danger btn-sm me-2" type="button"> <i class="bi bi-trash"></i> 삭제 </button>';
                                print '<button class="btn btn-outline-secondary btn-sm me-2" type="button" onclick="self.close();"> &times; 닫기 </button>';
                            } ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<!-- 페이지로딩 -->
<script>
$(document).ready(function() {    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>
    
<script>
ajaxRequest = null;

$(document).ready(function() {
    var state = $('#state').val();  
    
    $("#closeModalBtn").click(function() { 
        $('#myModal').modal('hide');
    });

    $("#closeBtn").click(function() {
        // 저장하고 창닫기
    });

    $("#saveBtn").click(function() {
        var form = $('#board_form')[0];
        var datasource = new FormData(form);

        if ($("#name").val() == '' || $("#id").val() == '' || $("#pass").val() == '' || $("#part").val() == '') {
            Swal.fire({
                title: '필수입력 확인',
                text: "필수입력 사항을 확인바랍니다.",
                icon: 'error',
                confirmButtonText: '확인'
            });
        } else {
            if (ajaxRequest !== null) {
                ajaxRequest.abort();
            }
            ajaxRequest = $.ajax({
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
                    console.log(data);
                    Toastify({
                        text: "파일 저장완료",
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
                            window.opener.location.reload(); // 부모 창 새로고침
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

    $("#delBtn").click(function() {
        var state = $("#state").val();
        var level = '<?php echo $level; ?>';
        var admin = '<?php echo $admin; ?>';
        
        if (admin !== '1' && $level !== '1') {
            Swal.fire({
                title: '삭제불가',
                text: "관리자만 삭제가능합니다.",
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
                    
                    $.ajax({
                        url: "insert.php",
                        type: "post",
                        data: $("#board_form").serialize(),
                        dataType: "json",
                        success: function(data) {
                            console.log(data);
                            Toastify({
                                text: "파일 삭제완료",
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
                                    window.opener.restorePageNumber(); // 부모 창에서 페이지 번호 복원
                                    window.opener.location.reload(); // 부모 창 새로고침
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
    });
});

// 두날짜 사이 일자 구하기 
const getDateDiff = (d1, d2) => {
    const date1 = new Date(d1);
    const date2 = new Date(d2);
    
    const diffDate = date1.getTime() - date2.getTime();
    
    return Math.abs(diffDate / (1000 * 60 * 60 * 24)); // 밀리세컨 * 초 * 분 * 시 = 일

}

    $(document).ready(function() {
        // 체크박스 상태에 따라 hidden 필드에 값을 설정
        $('#dailyworkcheck').change(function() {
            if ($(this).is(':checked')) {
                $('#hidden_dailyworkcheck').val('작성');
            } else {
                $('#hidden_dailyworkcheck').val('');
            }
        });

        // 페이지 로드 시 체크박스 상태에 따라 hidden 필드 값 설정
        if ($('#dailyworkcheck').is(':checked')) {
            $('#hidden_dailyworkcheck').val('작성');
        } else {
            $('#hidden_dailyworkcheck').val('');
        }
    });

</script>
