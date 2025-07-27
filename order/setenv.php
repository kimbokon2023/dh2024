<?php
  
  require_once("../lib/mydb.php");
  $pdo = db_connect();	
  $sql = "select * from chandj.settings where num = ? ";
      $stmh = $pdo->prepare($sql); 
    $num=1;
    $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);
      $motor1 = $row["motor1"];
      $motor2 = $row["motor2"];
      $motor3 = $row["motor3"];
      $motor4 = $row["motor4"];
      $motor5 = $row["motor5"];
      $motor6 = $row["motor6"];
      $motor7 = $row["motor7"];
      $motor8 = $row["motor8"];
      $motor9 = $row["motor9"];
      $motor10= $row["motor10"];
	 }
 
?>
 <!DOCTYPE HTML>
 <html>
 <head>
 <meta charset="UTF-8">
 <link rel="stylesheet" type="text/css" href="../css/setenv.css">
 <title> 환경 설정 </title> 
 </head>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

</script>


   <body>
	<form method="post" > 
	<br>
  <div id="aa"><div class="aa1">  전동개폐기 권상능력 상수설정  </div> <div class="aa2"> </div>   </div>
   <div id="cc">
  <div id="a1" class="c1">   스크린방화                        </div> <div id="b1"  class="c2"> <input name="motor1"  class="c3" type="text" size="1" value="<?=$motor1?>" > </div>
  <div id="a2" class="c1">   제연커튼                          </div> <div id="b2"  class="c2"> <input name="motor2"  class="c3" type="text" size="1" value="<?=$motor2?>" > </div>
  <div id="a3" class="c1">   철재방화EGI1.6T                   </div> <div id="b3"  class="c2"> <input name="motor3"  class="c3"  type="text" size="1" value="<?=$motor3?>" > </div>
  <div id="a4" class="c1">   철재방범EGI1.6T                   </div> <div id="b4"  class="c2"> <input name="motor4"  class="c3"  type="text" size="1" value="<?=$motor4?>" > </div>
  <div id="a5" class="c1">   철재방범EGI1.2T                  </div> <div id="b5"  class="c2"> <input name="motor5"  class="c3"  type="text" size="1" value="<?=$motor5?>" > </div>
  <div id="a6" class="c1">   파이프방범16파이싱글              </div> <div id="b6"  class="c2"> <input name="motor6"  class="c3"  type="text" size="1" value="<?=$motor6?>" > </div>
  <div id="a7" class="c1">   파이프방범19파이싱글              </div> <div id="b7"  class="c2"> <input name="motor7" class="c3"  type="text" size="1" value="<?=$motor7?>" > </div>
  <div id="a8" class="c1">   파이프방범19파이더블             </div> <div id="b8"  class="c2"> <input name="motor8"  class="c3"  type="text" size="1" value="<?=$motor8?>" > </div>
  <div id="a9" class="c1">   AL이중단열                        </div> <div id="b9"  class="c2"> <input name="motor9"  class="c3"  type="text" size="1" value="<?=$motor9?>" > </div>
  <div id="a10" class="c1">  내풍압                            </div> <div id="b10"  class="c2"> <input name="motor10"  class="c3"  type="text" size="1" value="<?=$motor10?>" > </div>
  </div>
</form>

<?php
//input 문장에서 form으로 전송하려고 하면 반드시 id가 아닌 name으로 설정해야 한다.
//input 문장에서 form으로 전송하려고 하면 반드시 id가 아닌 name으로 설정해야 한다.
//input 문장에서 form으로 전송하려고 하면 반드시 id가 아닌 name으로 설정해야 한다.
//input 문장에서 form으로 전송하려고 하면 반드시 id가 아닌 name으로 설정해야 한다.
//input 문장에서 form으로 전송하려고 하면 반드시 id가 아닌 name으로 설정해야 한다.
//input 문장에서 form으로 전송하려고 하면 반드시 id가 아닌 name으로 설정해야 한다.



 ?>
	</body>
 </html>
