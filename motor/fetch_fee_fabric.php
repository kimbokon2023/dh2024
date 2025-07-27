 <?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문 받는측에서 필요한 정보임 ajax로 보내는 쪽에서 type : json

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 부자재 단가표 읽어오기        
$sql = "SELECT * FROM " . $DB . ".fee_fabric where is_deleted is NULL ORDER BY basicdate DESC LIMIT 1";

try {
    $stmh = $pdo->query($sql);
    $row = $stmh->fetch(PDO::FETCH_ASSOC);
    
	// Decode JSON strings to PHP arrays (or parse however these are structured)	
	$prices = json_decode($row['price']);
	$itemcodes = json_decode($row['itemcode']);
	
	// Use array_filter to remove empty entries from the array
	$non_empty_items = array_filter($itemcodes, function($value) {
		return !empty($value) && $value !== null && $value !== '';
	});	
		
	$sub_priceData = [];  // Initialize an empty array to hold the price data

	for ($i = 0; $i < count($non_empty_items); $i++) {
		$itemcodes[$i] =  strtoupper(str_replace(' ', '', $itemcodes[$i])); 		

		if (!isset($sub_priceData[$itemcodes[$i]])) {
			$sub_priceData[$itemcodes[$i]] = [];
		}
		
		$sub_priceData[$itemcodes[$i]] = $prices[$i];
	}
} catch (PDOException $Exception) {
    print "오류: " . $Exception->getMessage();
}

$data = [   
 'num' => $num,
 'fee_fabric' => $sub_priceData 
 ]; 
 
 echo json_encode($data, JSON_UNESCAPED_UNICODE);