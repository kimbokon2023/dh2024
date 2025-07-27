<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
$title_message = 'QR 코드 생성'; 

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}       
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>
<title> <?=$title_message?> </title>
<link href="css/style.css" rel="stylesheet" >   

<style>
#viewTable th, td{
	border : 1px #aaaaaa solid ;
}

</style>
</head>
<body>

	 
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<div class="container">
    <div class="row mt-5">
        <div class="col-md-8 offset-md-3">
            <div class="mb-3">
                <label for="text" class="form-label fs-4">생성하실 주소 입력</label>
                <input id="text" type="text" class="form-control fs-4 " placeholder="주소를 입력하세요, http:// ~ " />
            </div>
            <button id="generate-btn" class="btn btn-primary">생성</button>
            <button id="save-btn" class="btn btn-success">저장</button>
            <div id="qrcode" style="width:100px; height:100px; margin-top:15px;"></div>
        </div>
    </div>
</div>

<div class="container-fluid mt-3 mb-3">
    <? include '../footer_sub.php'; ?>
</div>

<script type="text/javascript">
var qrcode = new QRCode(document.getElementById("qrcode"), {
    width : 100,
    height : 100
});

function makeCode() {        
    var elText = document.getElementById("text");
    
    if (!elText.value) {
        alert("Input a text");
        elText.focus();
        return;
    }
    
    qrcode.makeCode(elText.value);
}

document.getElementById("generate-btn").addEventListener("click", function() {
    makeCode();
});

document.getElementById("text").addEventListener("keydown", function(e) {
    if (e.keyCode === 13) {
        makeCode();
    }
});

document.getElementById("save-btn").addEventListener("click", function() {
    var canvas = document.querySelector("#qrcode canvas");
    var img = canvas.toDataURL("image/png");
    var link = document.createElement("a");
    link.href = img;
    link.download = "qrcode.png";
    link.click();
});
</script>

<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>

</body>
