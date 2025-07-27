<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}   

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';

$tablename = 'material_lot';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$lotnum = '';
$china_num = '';
$korea_date = date('md');
$registedate = '';

if ($mode === 'update' && $num) {
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        include '_row.php';
		
        $lot_type_checked = [];	
		if($manual_input_checkbox !=='checked')
		{
			// Parse lotnum to populate form fields
			$lotnum_parts = explode('-', $lotnum);
			if (count($lotnum_parts) === 3) {
				$lot_type_checked = [$lotnum_parts[1]];
				$china_num = $lotnum_parts[2];
			}
		}
    } catch (PDOException $Exception) {
        echo "오류: ".$Exception->getMessage();
        exit;
    }
} else {
    include '_request.php';
    $mode = 'insert';
    $registedate = date('Y-m-d');
	$lot_type_checked = ['M','M(방범)', 'C', 'B', 'T', 'F','초기로트'];	
}

$title_message = ($mode === 'update') ? '로트번호 수정' : '로트번호 신규 등록';

?>

<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
<input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">
<input type="hidden" id="update_log" name="update_log" value="<?= isset($update_log) ? $update_log : '' ?>">

<div class="container-fluid">                    
	<div class="d-flex align-items-center justify-content-center m-2 w-100">            
		<span class="text-center fs-5"><?=$title_message?></span>                                
	</div>            
	<div class="row justify-content-center text-center">  
		<div class="d-flex align-items-center justify-content-center m-2 w-100">
			<table class="table table-bordered ">
				<tbody>
					<tr>
						<td class="text-center fs-6 fw-bold" style="width:120px;">등록일자</td>
						<td class="text-center"> 
							<input type="date" class="form-control fs-6" id="registedate" name="registedate" style="width:120px;" value="<?=$registedate?>"> 
						</td>
					</tr>
					<tr>
						<td class="text-center fs-6 fw-bold" style="width:120px;">로트번호</td>
						<td class="text-start fs-6"> 
							<?=$lotnum?>
						</td>
					</tr>
					<tr>
						<td class="text-center fs-6 fw-bold" style="width:120px;">로트번호<br>생성규칙 </td>
						<td class="text-center ">
							<div class="d-flex align-items-center justify-content-start">
								<span class="fs-6 me-2"> DH-</span>
								<label><input type="checkbox" name="lot_type[]" class="record-checkbox fs-6 me-2" value="M" <?= in_array('M', $lot_type_checked) ? 'checked' : '' ?>> M (모터) &nbsp;&nbsp;</label>
								<label><input type="checkbox" name="lot_type[]" class="record-checkbox fs-6 me-2" value="M(방범)" <?= in_array('M(방범)', $lot_type_checked) ? 'checked' : '' ?>> M(방범) (모터) &nbsp;&nbsp;</label>
								<label><input type="checkbox" name="lot_type[]" class="record-checkbox fs-6 me-2" value="C" <?= in_array('C', $lot_type_checked) ? 'checked' : '' ?>> C (연동제어기) &nbsp;&nbsp;</label>
								<label><input type="checkbox" name="lot_type[]" class="record-checkbox fs-6 me-2" value="B" <?= in_array('B', $lot_type_checked) ? 'checked' : '' ?>> B (브라켓트) &nbsp;&nbsp;</label>
								<label><input type="checkbox" name="lot_type[]" class="record-checkbox fs-6 me-2" value="T" <?= in_array('T', $lot_type_checked) ? 'checked' : '' ?>> T (콘트롤박스) &nbsp;&nbsp;</label>
								<label><input type="checkbox" name="lot_type[]" class="record-checkbox fs-6 me-2" value="F" <?= in_array('F', $lot_type_checked) ? 'checked' : '' ?>> F (원단) &nbsp;&nbsp;</label>
								<label><input type="checkbox" name="lot_type[]" class="record-checkbox fs-6 me-2" value="초기로트" <?= in_array('초기로트', $lot_type_checked) ? 'checked' : '' ?>> 초기로트 &nbsp;&nbsp;&nbsp;&nbsp;</label>
								<span class="fs-6"> - </span>
							</div>
							<div class="d-flex align-items-center justify-content-start mt-2">
									<input type="checkbox" id="select-all" <?php echo ($mode == 'insert') ? 'checked' : ''; ?> >
									<label for="select-all" > 전체 선택 </label>
								 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span class="text-center" > <span class="badge bg-secondary fs-6 me-2" > 중국 제조번호 </span> </span>
								<span class="text-center"> 
									<input type="text" class="form-control fs-5" id="china_num" name="china_num" style="width:90px;" autocomplete="off"  value="<?=$china_num?>"> 
								</span>
							</div>
						</td>
					</tr>
					<tr>
						<td class="text-center fs-6 fw-bold" style="width:120px;">
							<label>
								<input type="checkbox" id="manual_input_checkbox" name="manual_input_checkbox" class="fs-6 me-2" value="checked" <?= isset($manual_input_checkbox) && $manual_input_checkbox ? 'checked' : '' ?>> 수동생성
							</label>
						</td>
						<td class="text-center ">
							<div class="d-flex align-items-center justify-content-start">
								<span class="fs-6 me-2"> DH-</span>                                        
								<input type="text" class="form-control fs-5" id="manual_input" name="manual_input" style="width:200px;" autocomplete="off" value="<?= isset($manual_input) ? $manual_input : '' ?>">                                             
							</div>
						</td>
					</tr>

				</tbody>
			</table>   
		</div>
	</div>

	<div class="d-flex justify-content-center">
		<button type="button" id="saveBtn" class="btn btn-dark btn-sm me-3">
			<i class="bi bi-floppy-fill"></i> 저장
		</button>  
		<?php  if($mode != 'insert') { ?>
		<button type="button" id="deleteBtn" class="btn btn-danger btn-sm me-3">
			<i class="bi bi-trash"></i>  삭제 
		</button>
		<?php  } ?>
		<button type="button" id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
			&times; 닫기
		</button>
	</div>
</div>
