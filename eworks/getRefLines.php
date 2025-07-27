<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");

$filePath = './RefLine/RefLine_' . $_SESSION['userid'] . '.json';

if(file_exists($filePath)) {
    $data = json_decode(file_get_contents($filePath), true);
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo $jsonData;
    } else {
        echo json_encode(["error" => "JSON encoding error"]);
    }
} else {
    echo json_encode([]);
}

?>
