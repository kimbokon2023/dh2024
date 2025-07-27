 
function transData(sendData) {
     var ua = window.navigator.userAgent;
     var postData; 
	 
     if (ua.indexOf('MSIE') > 0 || ua.indexOf('Trident') > 0) {
                postData = encodeURI(sendData);
            } else {
                postData = sendData;
            }		 
	 return postData;
}	 


function inputNumberFormat(obj) { 
    obj.value = comma(uncomma(obj.value)); 
} 
function comma(str) { 
    str = String(str); 
    return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'); 
} 
function uncomma(str) { 
    str = String(str); 
    return str.replace(/[^\d]+/g, ''); 
}


	
function previewImage(targetObj, previewId) {
    var preview = document.getElementById(previewId); //div id   
    var ua = window.navigator.userAgent;
    if (ua.indexOf("MSIE") > -1) {//ie일때
        targetObj.select();
        try {
            var src = document.selection.createRange().text; // get file full path 
            var ie_preview_error = document
                    .getElementById("ie_preview_error_" + previewId);
            if (ie_preview_error) {
                preview.removeChild(ie_preview_error); //error가 있으면 delete
            }

            var img = document.getElementById(previewId); //이미지가 뿌려질 곳 

            img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"
                    + src + "', sizingMethod='scale')"; //이미지 로딩, sizingMethod는 div에 맞춰서 사이즈를 자동조절 하는 역할
        } catch (e) {
            if (!document.getElementById("ie_preview_error_" + previewId)) {
                var info = document.createElement("<p>");
                info.id = "ie_preview_error_" + previewId;
               info.innerHTML = "a";
                preview.insertBefore(info, null);
            }
       }
    } else { //ie가 아닐때
        var files = targetObj.files;
        for ( var i = 0; i < files.length; i++) {
            var file = files[i];
            var imageType = /image.*/; //이미지 파일일경우만.. 뿌려준다.
            if (!file.type.match(imageType))
                continue;
            var prevImg = document.getElementById("prev_" + previewId); //이전에 미리보기가 있다면 삭제
            if (prevImg) {
                preview.removeChild(prevImg);
            }
            var img = document.createElement("img"); //크롬은 div에 이미지가 뿌려지지 않는다. 그래서 자식Element를 만든다.

            img.id = "prev_" + previewId;

            img.classList.add("obj");

            img.file = file;

            img.style.width = '50px'; //기본설정된 div의 안에 뿌려지는 효과를 주기 위해서 div크기와 같은 크기를 지정해준다.

            img.style.height = '50px';         

            preview.appendChild(img);
            if (window.FileReader) { // FireFox, Chrome, Opera 확인.
                var reader = new FileReader();
                reader.onloadend = (function(aImg) {
                    return function(e) {
                        aImg.src = e.target.result;
                    };
                })(img);
                reader.readAsDataURL(file);
            } else { // safari is not supported FileReader
                //alert('not supported FileReader');
                if (!document.getElementById("sfr_preview_error_"
                        + previewId)) {
                    var info = document.createElement("p");
                    info.id = "sfr_preview_error_" + previewId;
                    info.innerHTML = "not supported FileReader";
                    preview.insertBefore(info, null);
                }

            }

        }

    }

} 

function drawbracket() {	

var brX = Number($("#brX").val());
var brY = Number($("#brY").val());
var spaceX = 200;   // 초기 x좌표 좌측 띄움
var spaceY = 100;  // 초기 y좌표 좌측 띄움
var boxspaceX = 100;
var boxspaceY = 150;
var axis = brY/2 ; // Arc radius

  var radius = brY/2/2 ; // Arc radius
  var startAngle = 0; // Starting point on circle
  var endAngle = Math.PI + (Math.PI * 2) / 2; // End point on circle
  var anticlockwise = true; // clockwise or anticlockwise
  // var anticlockwise = i % 2 == 0 ? false : true; // clockwise or anticlockwise


var boxwidth = brX+boxspaceX;  // sutter box width
var boxheight = brY+boxspaceY;  // sutter box height

// bracket 형상 그림
    ctx.beginPath();
    ctx.strokeStyle = "blue";
	ctx.moveTo(spaceX,spaceY);
	ctx.lineTo(spaceX+brX,spaceY);
	ctx.lineTo(spaceX+brX,spaceY+brY);
	ctx.lineTo(spaceX,spaceY+brY);
	ctx.lineTo(spaceX,spaceY);
	ctx.stroke();		
	
// bracket 텍스트 넣기
ctx.font = 'italic 22px Calibri';
ctx.fillText(brX + 'X'+brY +" Bracket",spaceX+brX*3/5,spaceY+brY/2);

// 샤프트 그리기
    ctx.beginPath();
    ctx.strokeStyle = "red";
    ctx.arc(spaceX+axis,spaceY+axis, radius, startAngle, endAngle, anticlockwise);
	ctx.stroke();		

//셔터박스 그리기
    ctx.beginPath();
    ctx.strokeStyle =  "black";
	ctx.moveTo(spaceX-boxspaceX*0.35,spaceY);
	ctx.lineTo(spaceX+boxwidth,spaceY);
	ctx.lineTo(spaceX+boxwidth,spaceY+boxheight);
	ctx.lineTo(spaceX-boxspaceX*0.35,spaceY+boxheight);
	ctx.lineTo(spaceX-boxspaceX*0.35,spaceY);
	ctx.stroke();	

}
//enter키로 form submit 막기
	$('input[type="text"]').keydown(function() {
    if (event.keyCode === 13) {
        event.preventDefault();
    }
});

function changeUri(tmpdata)
{
	  var ua = window.navigator.userAgent;
      var postData; 	 
	
	     if (ua.indexOf('MSIE') > 0 || ua.indexOf('Trident') > 0) {
                postData = encodeURI(tmpdata);
            } else {
                postData = tmpdata;
            }

      return postData;
}

function box_content(types,num) {
	  document.getElementById("lin3").value = types;
	  $("#ceillingEnd").hide();	
	  

            var ua = window.navigator.userAgent;
            var postData; 
            var sendData = "./load_bendingData.php?bendnum=" + num ; 

            // 윈도우라면 ? 
            if (ua.indexOf('MSIE') > 0 || ua.indexOf('Trident') > 0) {
                postData = encodeURI(sendData);
            } else {
                postData = sendData;
            }
				
	   $("#displayresult").load(postData);  

}

function rotateimage(idName,degree)
{
	console.log(idName);
	// alert(idName);
	// $("#bofore_work0").rotate();	
	
	$("#" + idName).css('transform','rotate(' + degree + 'deg)');
	
}
