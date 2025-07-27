
<?php

$id=$_REQUEST["id"];
$pass=$_REQUEST["pass"];    
$name=$_REQUEST["name"];    
$regist_day=date("Y-m-d H:i:s");

require_once("../lib/mydb.php");
$pdo = db_connect();
try {
    $pdo->beginTransaction();
    $sql="update dbchandj.member set pass=? where id=?";
    $stmh=$pdo->prepare($sql);
    $stmh->bindvalue(1,$pass,PDO::PARAM_STR);    
    $stmh->bindvalue(2,$id,PDO::PARAM_STR);
    
    $stmh->execute();
    $pdo->commit();
    
    header("Location:https://dh2024.co.kr/index.php");
    
} catch (PDOException $Exception) {
   $pdo->rollBack();
   print "오류: ".$Exception->getMessage();
}
?>
