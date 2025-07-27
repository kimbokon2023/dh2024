<?php
$num=$_REQUEST["num"];
$pass=$_REQUEST["pass"];    

require_once("../lib/mydb.php");
$pdo = db_connect();
try {
    $pdo->beginTransaction();
    $sql="update dbchandj.phonebook set ppw=? where num=?";
    $stmh=$pdo->prepare($sql);
    $stmh->bindvalue(1,$pass,PDO::PARAM_STR);    
    $stmh->bindvalue(2,$num,PDO::PARAM_STR);
    
    $stmh->execute();
    $pdo->commit();
    
    header("Location:https://dh2024.co.kr/index.php");
    
} catch (PDOException $Exception) {
   $pdo->rollBack();
   print "오류: ".$Exception->getMessage();
}
?>
