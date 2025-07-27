<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");

// POST 데이터 수신
$savedName = $_POST['savedName'] ?? '';
$filePath = './approvalLine/approvalLine_' . $_SESSION['userid'] . '.json';

if(file_exists($filePath)) {
    $data = json_decode(file_get_contents($filePath), true);
    
    foreach ($data as $approvalLine) {
        if ($approvalLine['savedName'] === $savedName) {
			echo json_encode($approvalLine, JSON_UNESCAPED_UNICODE);            
			// echo json_encode(["status" => "success", "num" => "num"], JSON_UNESCAPED_UNICODE);          
			// echo json_encode(array('status' => 'success', 'message' => 'Approval line saved successfully.'));
            exit;
        }
    }	
	
}


?>
