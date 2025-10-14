<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
// 첫 화면 표시 문구
$title_message = 'DH모터 출고증'; 
$tablename = 'motor'; 
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?>

<title> <?=$title_message?> </title>

<style>
.smallfont {
    border: 0.5px solid #ccc !important; /* 가늘고 옅은 회색 테두리 */
    font-size: 11px !important;
    padding: 1px;
}

table, th {
    border: 0.5px solid #ccc !important; /* 가늘고 옅은 회색 테두리 */
    font-size: 15px !important;
    padding : 0px;
}

@media print {
    body {
        width: 210mm; /* Approx width of A4 paper */
        height: 297mm; /* Height of A4 paper */
        margin: 5mm; /* Provide a margin */
        font-size: 10pt; /* Reduce font size for printing */
    }   
    .table th, .table td {
        padding: 1px; /* Reduce padding */               
    }
    .text-center {
        text-align: center; /* Maintain center alignment */
    }
}

td {
    padding-top: 1px; /* 상단 여백을 2px로 설정 */
    padding-bottom: 1px; /* 하단 여백을 2px로 설정 */
    /* 기존 스타일 유지 */
    border: 0.5px solid #ccc !important; /* 가늘고 옅은 회색 테두리 */
    font-size: 14px !important;
    padding-left: 1px; /* 좌측 여백 */
    padding-right: 1px; /* 우측 여백 */
}
</style>
</head>
<body>  
<html lang="ko">
<?php
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';  

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
try {
    $sql = "select * from " . $DB . ".motor where num = ? ";
    $stmh = $pdo->prepare($sql); 
    $stmh->bindValue(1, $num, PDO::PARAM_STR); 
    $stmh->execute();
    $count = $stmh->rowCount();              
    if ($count < 1) {  
        print "검색결과가 없습니다.<br>";
    } else {
        $row = $stmh->fetch(PDO::FETCH_ASSOC);  
        include "_row.php";
        
        if ($deliverymethod == '화물' ) {
            // 상차지에 화물지점표기함
            $address = $delbranch . (!empty($delbranchaddress) ? ' (' . $delbranchaddress . ')' : '');
            $loadplace = '(주)대한 본사';
        }       
        if ($deliverymethod == '직배송' ) {
            // 상차지에 화물지점표기함         
            $loadplace = '(주)대한 본사';
        }
    }
} catch (PDOException $Exception) {
    print "오류: " . $Exception->getMessage();
}

// 인정업체가 있으면 발주 담당자 위치에 문구가 변경됨, 인정업체가 없으면 발주 담당자 위치에 문구가 변경됨
if(empty($certified_company)) {    
    $display_text1 = '발주 담당자';
    $display_text2 = $secondordman;
}
else {
    $display_text1 = '인정업체';
    $display_text2 = $certified_company;
}

?>

<div class="container mt-2">
    <div class="d-flex align-items-center justify-content-end mt-1 m-2">
        <input type="checkbox" id="lotNumberCheckbox" checked>
        <label for="lotNumberCheckbox" class="me-3">로트번호 보이기</label>
        <button class="btn btn-dark btn-sm me-1" onclick="generatePDF()"> PDF 저장 </button>
        <button class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;    
    </div>
</div>

<div id="content-to-print">    
<br>
<div class="container mt-3">
<div class="d-flex align-items-center justify-content-center mt-2 mb-2 ">
    <h3 > DH 모터 출고증 </h3>
</div>
<div class="d-flex align-items-center justify-content-end mb-1 ">
    <h6 > 출고 담당자 : 노영재 차장 </h6>
</div>
<div class="d-flex align-items-center justify-content-center mb-1">
    <table class="table ">
        <tbody>
            <tr>
                <td class="text-center smallfont" style="width:130px;"> 발주 접수일</td>
                <td class="text-center smallfont" style="width:130px;"> <?=$orderdate?></td>
                
                <td class="text-center  smallfont" style="width:130px;"> 출고예정일</td>
                <td class="text-center smallfont"> <?=$deadline?></td>
                <td class="text-center  smallfont">출고일</td>
                <td class="text-center smallfont" style="width:110px;" ><?= $outputdate == '0000-00-00' ? '' : $outputdate ?></td>
            </tr>
            <tr>
                <td class="text-center smallfont  ">발주처</td>
                <td class="text-center smallfont" > <?=$secondord?></td>
                <td class="text-center smallfont  ">현장명</td>
                <td class="text-center smallfont" colspan="3"> <?=$workplacename?></td>
                
            </tr>
            <tr>
                <td class="text-center smallfont"> <?=$display_text1?> </td>
                <td class="text-center smallfont"> <?=$display_text2?></td>
                <td class="text-center smallfont"> 연락처</td>
                <td class="text-center smallfont"  colspan="3"><?=$secondordmantel?></td>
            </tr>
            <tr>
                <td class="text-center smallfont  "> 운송 방법</td>
                <td class="text-center smallfont"> <?=$deliverymethod?></td>
                <td class="text-center smallfont  "> 배송지 주소</td>
                <td class="text-center smallfont"  colspan="3"><?=$address?></td>
                
            </tr>
            <tr>                
                <td class="text-center smallfont  ">받는 분</td>
                <td class="text-center smallfont"><?=$chargedman?></td>
                <td class="text-center smallfont  ">받는 분 tel</td>
                <td class="text-center smallfont" colspan="3"><?=$chargedmantel?></td>
            </tr>
            <tr>
                <td class=" fw-bold fs-4 text-center " colspan="6">
					<?=$memo?> <?=$comment?>
				</td>
            </tr>
        </tbody>
    </table>
</div>
            
<?php
// 모터 수량이 있나 체크한다.
$motor = json_decode($orderlist, true);        
$firstitem = true;
$motorsu = 0;

foreach ($motor as $accessory) {

    if ($accessory['col5']=='SET' || $accessory['col5']=='모터단품' ) {    
        $motorsu += $accessory['col8'] ;                
    }
}
if ($motorsu > 0 ) {
?>                  
            
<div class="d-flex align-items-center justify-content-start mt-1 ">
<h6> DH 모터 </h6>
</div>
<div class="d-flex align-items-center justify-content-center mt-1">    
    
    <table class="table " style="padding:0px; margin:0px;" >
        <thead class="table-primary" style="padding:0 !important; margin:0 !important;">
            <tr>                
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >유무선</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >구분</th>
				<th class="text-center"  style="padding:4 !important; margin:0 !important;" >전원</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >용량</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >수량</th>
                <th class="text-center lotview"  style="padding:4 !important; margin:0 !important;" >로트번호</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >전달사항</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >준비완료체크</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >비고</th>
            </tr>
        </thead>
        <tbody>
        
        <?php
        // 부자재 합계 문자열 생성
        $motor = json_decode($orderlist, true);        
        $firstitem = true;
        $motorsu = 0;

        foreach ($motor as $accessory) {
            if ($accessory['col5']=='SET' || $accessory['col5']=='모터단품' ) {
                print '<tr>';                
                    if (trim($accessory['col2']) !== '유선') {
						print '<td class="text-center" style="padding:4 !important; margin:0 !important;">'
							  . htmlspecialchars($accessory['col2'], ENT_QUOTES, 'UTF-8')
							  . '</td>';
					}
					else
					{
						print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" >  </td>';            
					}
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col3'] . ' </td>';            
				print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col1'] . ' </td>';            				
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col4'] . ' </td>';            
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col8'] . ' </td>';            
                print ' <td  class="text-center lotview" data-original-content="' . $accessory['col13'] . '" style="padding:4 !important; margin:0 !important;" > ' . $accessory['col13'] . ' </td>';            
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col15'] . ' </td>';                        
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';            
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';            
                print '</tr>';        
                $motorsu += $accessory['col8'] ;                
            }
        }
        if ($motorsu > 0 ) {
            print '<tr>';
            print ' <td  class="text-center" colspan="4"  style="padding:4 !important; margin:0 !important;" > 모터수량 합 </td>';    
            print ' <td  class="text-center"  colspan="1"  style="padding:4 !important; margin:0 !important;" > ' . $motorsu . ' </td>';    
            print '</tr>';    
        }
        ?>        
        </tbody>
    </table>
</div>

 <?php } ?>
     
        <?php
        // 부자재 합계 문자열 생성
        $motor = json_decode($orderlist, true);        
        $firstitem = true;
        $bracketsu = 0;

        foreach ($motor as $accessory) {
            if ($accessory['col5']=='브라켓트' || $accessory['col5']=='SET' ) {
                $bracketsu += $accessory['col8'] ;                
            }
        }
        if ($bracketsu > 0 ) {        
        ?>         
<div class="d-flex align-items-center justify-content-start mt-1">
<h6> 브라켓, 후렌지 </h6>    
</div>
<div class="d-flex align-items-center justify-content-center mt-1">

    <table class="table" >
        <thead class="table-info">
            <tr>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;"  >브라켓트 크기</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;"  >후렌지</th>                
                <th class="text-center"  style="padding:4 !important; margin:0 !important;"  >수량</th>
                <th class="text-center lotview"  style="padding:4 !important; margin:0 !important;"  >로트번호</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;"  >전달사항</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;"  >준비완료체크</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;"  >비고</th>
            </tr>
        </thead>
        <tbody>
        
        <?php
        // 부자재 합계 문자열 생성
        $motor = json_decode($orderlist, true);        
        $firstitem = true;
        $bracketsu = 0;

        foreach ($motor as $accessory) {
            if ($accessory['col5']=='브라켓트' || $accessory['col5']=='SET' ) {
                print '<tr>';
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col6'] . ' </td>';            
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col7'] . ' </td>';            
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col8'] . ' </td>';            
                print ' <td  class="text-center lotview" data-original-content="' . $accessory['col14'] . '" style="padding:4 !important; margin:0 !important;" > ' . $accessory['col14'] . ' </td>';            
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col15'] . ' </td>';                        
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';            
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';            
                print '</tr>';        
                $bracketsu += $accessory['col8'] ;                
            }
        }
        if ($bracketsu > 0 ) {
            print '<tr>';
            print ' <td  class="text-center"    style="padding:4 !important; margin:0 !important;"  colspan="2"> 수량 합 </td>';    
            print ' <td  class="text-center"    style="padding:4 !important; margin:0 !important;"  colspan="1"> ' . $bracketsu . ' </td>';    
            print '</tr>';    
        }
        ?>        
        </tbody>
    </table>
</div>

 <?php } ?>

 <?php 
    $controller = json_decode($controllerlist, true);
    
    if (is_array($controller)) {
        $firstitem = true;
        $controllersu = 0;            

        foreach ($controller as $accessory) {                        
            $controllersu += $accessory['col3'] ;              
        }
    }   
    if ($controllersu > 0 ) { 
?>        
<div class="d-flex align-items-center justify-content-start mt-1" >
<h6> 연동제어기 </h6>    
</div>
<div class="d-flex align-items-center justify-content-center mt-1">
    <table class="table " >
        <thead class="table-success">
            <tr>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >유무선</th>                
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >구분</th>                
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >수량</th>
                <th class="text-center lotview"  style="padding:4 !important; margin:0 !important;" >로트번호</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >전달사항</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >준비완료체크</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >비고</th>
            </tr>
        </thead>
        <tbody>
        
        <?php
        // Decode the JSON string into an associative array
        $controller = json_decode($controllerlist, true);

        // Check if $controller is an array
        if (is_array($controller)) {
            $firstitem = true;
            $controllersu = 0;            			
            foreach ($controller as $accessory) {                
                print '<tr>';					
					$val = trim($accessory['col2']);
					// '무선'이면 '무선', '유선'이면 ''
					$out = (strpos($val, '무선') !== false) ? '무선' : '';
					print '<td class="text-center" style="padding:4 !important; margin:0 !important;">'
						  . htmlspecialchars($out, ENT_QUOTES, 'UTF-8')
						  . '</td>';
					// '-무선', '-유선' 문자열 제거
					$cleaned = str_replace(['-무선', '-유선'], '', $val);
					print '<td class="text-center" style="padding:4 !important; margin:0 !important;">'
						  . htmlspecialchars(trim($cleaned), ENT_QUOTES, 'UTF-8')
						  . '</td>';
					print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col3'] . ' </td>';           
					print ' <td  class="text-center lotview" data-original-content="' . $accessory['col8'] . '" style="padding:4 !important; margin:0 !important;" > ' . $accessory['col8'] . ' </td>';           
					print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col9'] . ' </td>';                        
					print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';          
					print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';          
					print '</tr>';      
					$controllersu += $accessory['col3'] ;
            }
            if ($controllersu > 0 ) {
                print '<tr>';
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;"  colspan="2"> 연동제어기 수량 합 </td>';                    
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;"  colspan="1"> ' . $controllersu . ' </td>';    
                print '</tr>';    
            }
        }
        ?>        
        </tbody>
    </table>
  </div>
 <?php } ?>

 <?php 
    $fabric = json_decode($fabriclist, true);
    
    if (is_array($fabric)) {
        $firstitem = true;
        $fabricsu = 0;            

        foreach ($fabric as $accessory) {                                    
			$col5 = str_replace(',', '', $accessory['col5'] );  // 콤마 제거
			$fabricsu += $col5  ;                
        }
    }   
    if ($fabricsu > 0 ) { 
?>        
<div class="d-flex align-items-center justify-content-start mt-1" >
<h6> 원단 </h6>    
</div>
<div class="d-flex align-items-center justify-content-center mt-1">
    <table class="table " >
        <thead class="table-success">
            <tr>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >구분</th>                
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >수량</th>
                <th class="text-center lotview"  style="padding:4 !important; margin:0 !important;" >로트번호</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >전달사항</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >준비완료체크</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >비고</th>
            </tr>
        </thead>
        <tbody>
        
        <?php
        // Decode the JSON string into an associative array
        $fabric = json_decode($fabriclist, true);

        // Check if $fabric is an array
        if (is_array($fabric)) {
            $firstitem = true;
            $fabricsu = 0;            

            foreach ($fabric as $accessory) {                
                print '<tr>';
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col1'] . ' </td>';                            
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col4'] . ' </td>';           
                print ' <td  class="text-center lotview" data-original-content="' . $accessory['col10'] . '" style="padding:4 !important; margin:0 !important;" > ' . $accessory['col10'] . ' </td>';           
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . $accessory['col11'] . ' </td>';                        
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';          
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';          
                print '</tr>';      
				
				$col4 = str_replace(',', '', $accessory['col4'] );  // 콤마 제거
                $fabricsu += $col4  ;              
            }
            if ($fabricsu > 0 ) {
                print '<tr>';
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;"  colspan="1"> 수량 합 </td>';    
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;"  colspan="1"> ' . $fabricsu . ' </td>';    
                print '</tr>';    
            }
        }
        ?>        
        </tbody>
    </table>
  </div>
 <?php } ?>

 <?php 
    $accessories = json_decode($accessorieslist, true);
    
    if (is_array($accessories)) {
        $firstitem = true;
        $etcsu = 0;            

        foreach ($accessories as $accessory) {                        
            $etcsu += $accessory['col2'] ;              
        }
    }   
    if ($etcsu > 0 ) { 
?>        
<div class="d-flex align-items-center justify-content-start mt-1">
    <h6> 기타 부속 </h6>    
</div>
<div class="d-flex align-items-center justify-content-center mt-1">
    <table class="table " >
        <thead class="table-secondary">
            <tr>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >구분</th>                
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >수량</th>                                
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >전달사항</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >준비완료체크</th>
                <th class="text-center"  style="padding:4 !important; margin:0 !important;" >비고</th>
            </tr>
        </thead>
        <tbody>
        
        <?php
        // Decode the JSON string into an associative array
        $accessories = json_decode($accessorieslist, true);

        // Check if $accessories is an array
        if (is_array($accessories)) {
            $firstitem = true;
            $etcsu = 0;            

            foreach ($accessories as $accessory) {                
                print '<tr>';
                print ' <td  class="text-center" style="padding:4 !important; margin:0 !important;" > ' . $accessory['col1'] . ' </td>';                            
                print ' <td  class="text-center" style="padding:4 !important; margin:0 !important;" > ' . $accessory['col2'] . ' </td>';           
                print ' <td  class="text-center" data-original-content="' . $accessory['col5'] . '" style="padding:4 !important; margin:0 !important;" > ' . $accessory['col5'] . ' </td>';                                        
                print ' <td  class="text-center" style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';          
                print ' <td  class="text-center" style="padding:4 !important; margin:0 !important;" > ' . '' . ' </td>';          
                print '</tr>';      
                $etcsu += $accessory['col2'] ;              
            }
            if ($etcsu > 0 ) {
                print '<tr>';
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;"  colspan="1"> 수량 합 </td>';    
                print ' <td  class="text-center"  style="padding:4 !important; margin:0 !important;"  colspan="1"> ' . $etcsu . ' </td>';    
                print '</tr>';    
            }
        }
        ?>        
        </tbody>
    </table>	  	
  </div>
  
 <?php } ?>

  <div class="d-flex align-items-center justify-content-center mb-2">	
    <span class="fw-bold">  * 출고 준비 중 문의사항이 있으면 발주처 담당자와 통화 후 출고 부탁드립니다.		</span>
</div>  

</div>
</div>    <!-- end of content-to-print --> 

</body>

</html>

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});

document.getElementById('lotNumberCheckbox').addEventListener('change', function() {
    var lotviewThs = document.querySelectorAll('.lotview');
    var lotviewTds = document.querySelectorAll('td.lotview');

    if (this.checked) {
        // lotview 클래스의 요소를 다시 표시
        lotviewThs.forEach(function(th) {
            th.style.display = '';
        });
        lotviewTds.forEach(function(td) {
            td.style.display = '';
            td.textContent = td.dataset.originalContent;
        });
    } else {
        // lotview 클래스의 요소를 숨김
        lotviewThs.forEach(function(th) {
            th.style.display = 'none';
        });
        lotviewTds.forEach(function(td) {
            td.style.display = 'none';
        });
    }
});

function generatePDF() {
    var workplace = '<?php echo $workplacename; ?>';
    var d = new Date();
    var currentDate = ( d.getMonth() + 1 ) + "-" + d.getDate()  + "_" ;
    var currentTime = d.getHours()  + "_" + d.getMinutes() +"_" + d.getSeconds() ;
    var result = 'DH모터출고증(' + workplace +')' + currentDate + currentTime + '.pdf';    
    
    var element = document.getElementById('content-to-print');
    var opt = {
        margin:       0,
        filename:     result,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    html2pdf().from(element).set(opt).save();
}
</script>
