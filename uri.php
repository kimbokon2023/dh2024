<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = 'The decodeURIComponent() Method'; 

?>
 
<link href="css/style.css" rel="stylesheet">   
<title> <?=$title_message?> </title>


</head>
<body>   

<h1>JavaScript Global Methods</h1>
<h2>The decodeURIComponent() Method</h2>

<p id="demo"></p>

<input id="source" name="source" type="text" style="width:1600px;" >
<h2>The decodeURIComponent() Method</h2>
<button type="button" id="doBtn" > 실행 </button>
<input id="source_out" name="source_out" type="text" style="width:1600px; height: 30px; font-size:20px;" >


<script>

// let encoded = encodeURIComponent(uri);

$(document).ready(function () {

	$("#doBtn").click(function () {
		$("#source_out").val(decodeURIComponent($("#source").val()));
	});
});
	
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});	
	
</script>

</body>
</html>
