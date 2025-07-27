<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");  // Use JSON content type

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';  

function conv_num($num) {
    return (int)str_replace(',', '', $num);
}

function pipetocomma($str) {
    return str_replace('|', ',', $str);
}

$cols = [];
for ($i = 1; $i <= 13; $i++) {
    $col_var = "col$i";
    isset($_REQUEST[$col_var]) ? $cols[$i] = explode(",", pipetocomma($_REQUEST[$col_var][0])) : $cols[$i] = [];
}

$orderday = date("Y-m-d"); // Current date

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

for ($i = 0; $i < count($cols[1]); $i++) {
    if ($cols[1][$i] != '') {
        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO " . $DB . "." . $tablename . " (item_code, item_name, spec, spec_info, unit, item_type, set_flag, stock, prod_proc, in_price, in_price_vat, out_price, out_price_vat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmh = $pdo->prepare($sql);

            for ($j = 1; $j <= 13; $j++) {
                $stmh->bindValue($j, $cols[$j][$i], PDO::PARAM_STR);
            }

            $stmh->execute();
            $pdo->commit();
        } catch (PDOException $Exception) {
            $pdo->rollBack();
            print "오류: " . $Exception->getMessage();
        }
    }
}

// Store each column data into an array
$data = array(
    "colarr1" => $cols[1]
);

// Output JSON
echo(json_encode($data, JSON_UNESCAPED_UNICODE));

?>
