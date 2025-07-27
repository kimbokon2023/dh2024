 <?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문 받는측에서 필요한 정보임 ajax로 보내는 쪽에서 type : json

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 부자재 단가표 읽어오기        
$sql = "SELECT * FROM " . $DB . ".fee_sub where is_deleted is NULL ORDER BY basicdate DESC LIMIT 1";

try {
    $stmh = $pdo->query($sql);
    $row = $stmh->fetch(PDO::FETCH_ASSOC);
    
	// Decode JSON strings to PHP arrays (or parse however these are structured)	
	$items = json_decode($row['item']);	
	$is_dcs = json_decode($row['is_dc']);
	$prices = json_decode($row['price']);
	
	// Use array_filter to remove empty entries from the array
	$non_empty_items = array_filter($items, function($value) {
		return !empty($value) && $value !== null && $value !== '';
	});	
		
	$sub_priceData = [];  // Initialize an empty array to hold the price data

	for ($i = 0; $i < count($non_empty_items); $i++) {
		$items[$i] =  strtoupper(str_replace(' ', '', $items[$i])); 		
		$is_dcs[$i] =  strtoupper(str_replace(' ', '', $is_dcs[$i])); 

		if (!isset($sub_priceData[$items[$i]])) {
			$sub_priceData[$items[$i]] = [];
		}
		if (!isset($sub_priceData[$items[$i]][$is_dcs[$i]])) {
			$sub_priceData[$items[$i]][$is_dcs[$i]] = [];
		}
		$sub_priceData[$items[$i]][$is_dcs[$i]] = $prices[$i];
	}
} catch (PDOException $Exception) {
    print "오류: " . $Exception->getMessage();
}

$data = [   
 'num' => $num,
 'fee_sub' => $sub_priceData
 
 ]; 
 
 echo json_encode($data, JSON_UNESCAPED_UNICODE);