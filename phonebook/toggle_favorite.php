<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// PHP 오류를 로그 파일에 기록하도록 설정
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/php_errors.log');

$data = json_decode(file_get_contents('php://input'), true);
$secondordnum = isset($data['secondordnum']) ? $data['secondordnum'] : null;
$deliverymethod = isset($data['deliverymethod']) ? $data['deliverymethod'] : null;
$address = isset($data['address']) ? $data['address'] : null;
$receiver = isset($data['receiver']) ? $data['receiver'] : null;
$tel = isset($data['tel']) ? $data['tel'] : null;
$carinfo = isset($data['carinfo']) ? $data['carinfo'] : null;
$delcompany = isset($data['delcompany']) ? $data['delcompany'] : null;
$chargedman = isset($data['chargedman']) ? $data['chargedman'] : null;
$chargedmantel = isset($data['chargedmantel']) ? $data['chargedmantel'] : null;
$delcaritem = isset($data['delcaritem']) ? $data['delcaritem'] : null;
$delbranch = isset($data['delbranch']) ? $data['delbranch'] : null;

header('Content-Type: application/json'); // JSON 형식으로 응답

if ($secondordnum) {
    try {
        $sql = "SELECT * FROM delivery_favorites WHERE secondordnum = :secondordnum";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':secondordnum', $secondordnum, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $fav_item = [
            'secondordnum' => $secondordnum,
            'deliverymethod' => $deliverymethod,
            'address' => $address,
            'receiver' => $receiver,
            'tel' => $tel,
            'carinfo' => $carinfo,
            'delcompany' => $delcompany,
            'chargedman' => $chargedman,
            'chargedmantel' => $chargedmantel,
            'delcaritem' => $delcaritem,
            'delbranch' => $delbranch
        ];

        if ($result) {
            $favorites = json_decode($result['favorites_list'], true) ?? [];
            $existingKey = false;

            // 고유한 항목을 찾기 위해 모든 정보를 비교합니다.
            foreach ($favorites as $key => $fav) {
                if (
                    $fav['secondordnum'] == $fav_item['secondordnum'] &&
                    $fav['deliverymethod'] == $fav_item['deliverymethod'] &&
                    $fav['address'] == $fav_item['address'] &&
                    $fav['receiver'] == $fav_item['receiver'] &&
                    $fav['tel'] == $fav_item['tel'] &&
                    $fav['carinfo'] == $fav_item['carinfo'] &&
                    $fav['delcompany'] == $fav_item['delcompany'] &&
                    $fav['chargedman'] == $fav_item['chargedman'] &&
                    $fav['chargedmantel'] == $fav_item['chargedmantel'] &&
                    $fav['delcaritem'] == $fav_item['delcaritem'] &&
                    $fav['delbranch'] == $fav_item['delbranch']
                ) {
                    $existingKey = $key;
                    break;
                }
            }
            
            if ($existingKey !== false) {
                unset($favorites[$existingKey]);
            } else {
                $favorites[] = $fav_item;
            }

            $favorites_list = json_encode(array_values($favorites));
            $sql = "UPDATE delivery_favorites SET favorites_list = :favorites_list WHERE secondordnum = :secondordnum";
        } else {
            $favorites = [$fav_item];
            $favorites_list = json_encode($favorites);
            $sql = "INSERT INTO delivery_favorites (secondordnum, favorites_list) VALUES (:secondordnum, :favorites_list)";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':favorites_list', $favorites_list);
        $stmt->bindParam(':secondordnum', $secondordnum, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(array("success" => true));
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(array("error" => "An error occurred while processing your request."));
    }
} else {
    echo json_encode(array("error" => "No secondordnum provided"));
}
?>
