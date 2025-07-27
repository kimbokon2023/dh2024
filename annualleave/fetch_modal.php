<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

// 결재권자 직함과 이름을 정해준다.

$approvalName = '대표이사 신지환';

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';

$tablename = "eworks";
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$company = '대한';

// 배열로 기본정보 불러옴
require_once($_SERVER['DOCUMENT_ROOT'] . "/almember/load_DB.php");	

if ($mode === 'update' && $num) {
    try {
        $sql = "SELECT * FROM " . $DB . "." . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_STR);
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        include 'rowDBask.php';
    } catch (PDOException $Exception) {
        echo "오류: " . $Exception->getMessage();
    }
} else {
    include '_request.php';
    $mode = 'insert';

    $registdate = date("Y-m-d H:i:s");
    $al_askdatefrom = date("Y-m-d");
    $al_askdateto = date("Y-m-d");
    $al_usedday = abs(strtotime($al_askdateto) - strtotime($al_askdatefrom)) + 1;
    $al_item = '연차';
    $al_part = '대한';
    $status = 'send';
    $statusstr = '결재요청';
    $author = $_SESSION["name"];
    $author_id = $user_id;
	
    // 전 직원 배열로 계산 후 사용일수 남은일수 값 넣기
    for ($i = 0; $i < count($basic_name_arr); $i++) {
        if (trim($basic_name_arr[$i]) == trim($author)) {
            $al_part = $basic_part_arr[$i];
            break;
        }
    }
}

try {
    $totalusedday = 0;
    $totalremainday = 0;
    for ($i = 0; $i < count($totalname_arr); $i++) {
        if ($author == $totalname_arr[$i]) {
            $availableday = $availableday_arr[$i];
        }
    }
	
    for ($i = 0; $i < count($totalname_arr); $i++) {
        if ($author == $totalname_arr[$i]) {
            $totalusedday = $totalused_arr[$i];
            $totalremainday = $availableday - $totalusedday;
        }
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}

// echo '<pre>';
// print_r($employee_data);
// echo '</pre>';

?>

    <input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
    <input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">    
    <input type="hidden" id="registdate" name="registdate" value="<?= isset($registdate) ? $registdate : '' ?>">
    <input type="hidden" id="author_id" name="author_id" value="<?= isset($author_id) ? $author_id : '' ?>">    
	<input type="hidden" id="status" name="status" value="<?= isset($status) ? $status : '' ?>">
	<input type="hidden" id="company" name="company" value="<?= isset($company) ? $company : '' ?>">
    <input type="hidden" id="htmltext" name="htmltext">

    <div class="container-fluid" style="width:380px;">
        <div class="row d-flex justify-content-center align-items-center h-75">
            <div class="col-12 text-center">
                <div class="card align-middle" style="border-radius:20px;">                    
					 <h3 class="card-title text-center" style="color:#113366;"> 직원 연차 </h3>                    
                    <div class="card-body text-center">
                        <?php if ($e_confirm === '') {
                            $formattedDate = date("m/d", strtotime($registdate));
                            if ($al_part == '대한') {
                                $approvals = array(
                                    array("name" => $approvalName , "date" =>  $formattedDate),
                                );
                            }
                        } else {
                            $approver_ids = explode('!', $e_confirm_id);
                            $approver_details = explode('!', $e_confirm);
                            $approvals = array();
                            foreach ($approver_ids as $index => $id) {
                                if (isset($approver_details[$index])) {
                                    preg_match("/^(.+ \d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})$/", $approver_details[$index], $matches);
                                    if (count($matches) === 3) {
                                        $nameWithTitle = $matches[1];
                                        $time = $matches[2];
                                        $date = substr($nameWithTitle, -10);
                                        $nameWithTitle = trim(str_replace($date, '', $nameWithTitle));
                                        $formattedDate = date("m/d H:i:s", strtotime("$date $time"));
                                        $approvals[] = array("name" => $nameWithTitle, "date" => $formattedDate);
                                    }
                                }
                            }
                        }
                        if ($status === 'end') { ?>
                            <div class="row d-flex justify-content-center" style="width:300px;">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th colspan="<?php echo count($approvals); ?>" class="text-center fs-6">결재</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <?php foreach ($approvals as $approval) { ?>
                                                <td class="text-center fs-6" style="height: 70px;"><?php echo $approval["name"]; ?></td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <?php foreach ($approvals as $approval) { ?>
                                                <td class="text-center"><?php echo $approval["date"]; ?></td>
                                            <?php } ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                        <div id="savetext">
							<div class="row d-flex justify-content-center align-items-center">
								성명
								<select name="author" id="author" class="form-select form-select-sm text-center w-auto mx-1" >
									<?php
									for ($i = 0; $i < count($employee_name_arr); $i++) {
										if ($author == $employee_name_arr[$i])
											print "<option selected value='" . $employee_name_arr[$i] . "'> " . $employee_name_arr[$i] . "</option>";
										else
											print "<option value='" . $employee_name_arr[$i] . "'> " . $employee_name_arr[$i] . "</option>";
									}
									?>
								</select>
								&nbsp; &nbsp; 부서
								<input type="text" id="al_part" name="al_part" class="form-control mx-1" style="width:100px;" value="<?= $al_part ?>" readonly>
							</div>

                            <h6 class="form-signin-heading mt-2 mb-2">구분</h6>
                            <div class="d-flex justify-content-center align-items-center">
                                <?php
                                $item_arr = array('연차', '오전반차','오후반차','경조사');
                                for ($i = 0; $i < count($item_arr); $i++) {
                                    if ($al_item == $item_arr[$i])
                                        print "<input type='radio' name='al_item'  checked='checked' value='" . $item_arr[$i] . "'> " . $item_arr[$i] .   " &nbsp ";
                                    else
                                        print "<input type='radio' name='al_item'  value='" . $item_arr[$i] . "'> " . $item_arr[$i] .   " &nbsp ";
                                    if ($i % 2 == 0)
                                        print "<br>";
                                }
                                ?>
                            </div>
							 <div class="row d-flex justify-content-center align-items-center">
								<h6 class="form-signin-heading mt-2 mb-2">기간</h6>
							</div>
                            <div class="row d-flex justify-content-center align-items-center">
                                신청시작일 <input type="date" id="al_askdatefrom" name="al_askdatefrom" class="form-control" style="width:100px;" required value="<?= $al_askdatefrom ?>">
                            </div> 
                            <div class="row d-flex justify-content-center align-items-center">
                                신청종료일
                                <input type="date" id="al_askdateto" name="al_askdateto"  class="form-control" style="width:100px;"  required value="<?= $al_askdateto ?>">
                            </span>
                            <div class="row d-flex justify-content-center align-items-center">
                                <span style="color:blue">신청 기간 산출</span>
                                <input type="text" id="al_usedday" size="2" name="al_usedday" readonly class="form-control text-center " style="width:50px;" value="<?= $al_usedday ?>">
                            </div>
                            <div class="row d-flex justify-content-center align-items-center">
                                <span style="color:red">연차 잔여일수</span>
                                <input type="text" id="totalremainday" name="totalremainday" size="2"  class="form-control text-center " style="width:50px;"  readonly value="<?= $totalremainday ?>">
                            </div>
                            <br>
                            <h6 class="form-signin-heading mt-2 mb-2">신청 사유</h6>
							<div class="row d-flex justify-content-center align-items-center">
                            <select name="al_content" id="al_content" class="form-select form-select-sm text-center w-auto mx-1" >
                                <?php
                                $al_content_arr = array("개인사정", "휴가", "여행", "병원진료등", "전직원연차", "경조사", "기타");
                                for ($i = 0; $i < count($al_content_arr); $i++) {
                                    if ($al_content == $al_content_arr[$i])
                                        print "<option selected value='" . $al_content_arr[$i] . "'> " . $al_content_arr[$i] .   "</option>";
                                    else
                                        print "<option value='" . $al_content_arr[$i] . "'> " . $al_content_arr[$i] .   "</option>";
                                }
                                ?>
                            </select>
							</div>
                        </div> <!-- end of savetext -->
                        <?php
                        switch ($status) {
                            case 'send':
                                $statusstr = '결재요청';
                                break;
                            case 'ing':
                                $statusstr = '결재중';
                                break;
                            case 'end':
                                $statusstr = '결재완료';
                                break;
                            default:
                                $statusstr = '';
                                break;
                        }
                        ?>
                        <h6 class="form-signin-heading mt-2 mb-2">결재 상태</h6>  
						<div class="row d-flex justify-content-center align-items-center mb-5">						
								<input type="text" id="statusstr" name="statusstr" class="form-control text-center mb-3" style="width:100px;" readonly value="<?= $statusstr ?>">
						</div>
							<div class=" d-flex justify-content-center align-items-center">						
							<? if ((int)$num > 0 and $level =='1' ) {
								print '<button  type="button"  id="saveBtn" class="btn btn-sm btn-dark me-1 " type="button">';
								print '결재요청(수정)';
								print '</button>';
							} else if ($statusstr !== '결재완료') {
								print '<button  type="button" id="saveBtn" class="btn btn-sm btn-dark me-1 " type="button">';
								print '결재요청';
								print '</button>';
							}
							if ((int)$num > 0 and $level =='1'  ) { ?>
								<button type="button" id="deleteBtn" class="btn btn-sm btn-danger me-1" type="button"> <i class="bi bi-trash"></i>  삭제 </button>
							<? } ?>
			  
							<button  type="button"  class="btn btn-dark btn-sm me-1" id="closeBtn"> &times; 닫기 </button>
						</div>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>
