<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

// 첫 화면 표시 문구
$title_message = 'DH 모터 수주'; 

?>

<?php 
 if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
		 sleep(1);
		  header("Location:" . $WebSite . "login/login_form.php"); 
         exit;
   }    
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
 ?>
<title> <?=$title_message?> </title>

<!-- 부트스트랩 CSS -->
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<!-- 부트스트랩 JS, Popper.js 포함 -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body>	

<!-- 모달 트리거 버튼 -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
  Open Modal with Tabs
</button>

<!-- 모달 -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal with Tabs</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- 탭 네비게이션 -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Profile</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="work-tab" data-toggle="tab" href="#work" role="tab" aria-controls="profile" aria-selected="false">work</a>
          </li>
        </ul>
        <!-- 탭 콘텐츠 -->
        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
            <p>
			
			<table class="table table-hover" id="myTable">
			  <thead class="table-primary">
				<tr>
				  <th class="text-center " style="width:50px;" >번호</th>      
				  <th class="text-center " style="width:90px;" >접수</th>
				  <th class="text-center " style="width:50px;">납기</th>
				  <th class="text-center " style="width:50px;">출고</th>
				  <th class="text-center " style="width:50px;">청구</th>      	  
				  <th id="showstatus" class="text-center" style="width:90px;"> 진행 </th>		  
				  <th class="text-center text-danger" style="width:70px;"> 출하 <ion-icon name="image-outline"></ion-icon> </th>		  
				  <th class="text-center " style="width:120px;">발주처</th>      
				  <th class="text-center " style="width:200px;">현장명</th>
				  <th class="text-center " style="width:300px;">내역</th> 
				  <th class="text-center " style="width:150px;">상차지</th>
				  <th class="text-center " style="width:100px;">배송</th>    
				  <th class="text-center " style="width:200px;">배송지</th>
				  <th class="text-center " style="width:120px;">비고</th>    
				  <th class="text-center " style="width:120px;">전달사항</th>	  
				</tr>        
			  </thead>	  
			  <tbody>
				  <td class="text-center " style="widtd:50px;" >번호</td>      
				  <td class="text-center " style="widtd:90px;" >접수</td>
				  <td class="text-center " style="widtd:50px;">납기</td>
				  <td class="text-center " style="widtd:50px;">출고</td>
				  <td class="text-center " style="widtd:50px;">청구</td>      	  
				  <td id="showstatus" class="text-center" style="widtd:90px;"> 진행 </td>		  
				  <td class="text-center text-danger" style="widtd:70px;"> 출하 <ion-icon name="image-outline"></ion-icon> </td>		  
				  <td class="text-center " style="widtd:120px;">발주처</td>      
				  <td class="text-center " style="widtd:200px;">현장명</td>
				  <td class="text-center " style="widtd:300px;">내역</td> 
				  <td class="text-center " style="widtd:150px;">상차지</td>
				  <td class="text-center " style="widtd:100px;">배송</td>    
				  <td class="text-center " style="widtd:200px;">배송지</td>
				  <td class="text-center " style="widtd:120px;">비고</td>    
				  <td class="text-center " style="widtd:120px;">전달사항</td>	  
			  </table>
			
			</p>
          </div>
          <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <p>Profile tab content goes here...</p>
          </div>
          <div class="tab-pane fade" id="work" role="tabpanel" aria-labelledby="work-tab">
            <p>Profile tab content goes here...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>

<script> 
var dataTable; // DataTables 인스턴스 전역 변수
var motorpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {			
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 50,
        "lengthMenu": [25, 50, 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[0, 'desc']]
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('motorpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var motorpageNumber = dataTable.page.info().page + 1;
        setCookie('motorpageNumber', motorpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('motorpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('motorpageNumber');
    // if (savedPageNumber) {
        // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
	location.reload(true);
}

function redirectToView(num, tablename) {	
    var url = "write_form.php?mode=view&num=" + num + "&tablename=" + tablename;          
	customPopup(url, '수주내역', 1850, 900); 		    
}

$(document).ready(function(){	
	$("#writeBtn").click(function(){ 		
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '수주내역', 1850, 900); 	
	 });			 
	 
	// 자재현황 클릭시
	$("#rawmaterialBtn").click(function(){ 			
		 popupCenter('/motor/list_part_table.php?menu=no'  , '부자재현황보기', 1050, 950);	
	});		 
			
	// 가공스케줄 클릭시
	$("#plan_cutandbending").click(function(){ 
		 popupCenter('/motor/plan_cutandbending.php?menu=no'  , '가공 스케줄', 1900, 950);	
	});		 
		
});	


$(document).ready(function() {	

    // 쿠키에서 dateRange 값을 읽어와 셀렉트 박스에 반영
    var savedDateRange = getCookie('dateRange');
    if (savedDateRange) {
        $('#dateRange').val(savedDateRange);
    }

    // dateRange 셀렉트 박스 변경 이벤트 처리
    $('#dateRange').on('change', function() {
        var selectedRange = $(this).val();
        var currentDate = new Date(); // 현재 날짜
        var fromDate, toDate;

        switch(selectedRange) {
            case '최근3개월':
                fromDate = new Date(currentDate.setMonth(currentDate.getMonth() - 3));
                break;
            case '최근6개월':
                fromDate = new Date(currentDate.setMonth(currentDate.getMonth() - 6));
                break;
            case '최근1년':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 1));
                break;
            case '최근2년':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 2));
                break;
            case '직접설정':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 1));
                break;   
            case '전체':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 20));
                break;            
            default:
                // 기본 값 또는 예외 처리
                break;
        }

        // 날짜 형식을 YYYY-MM-DD로 변환
        toDate = formatDate(new Date()); // 오늘 날짜
        fromDate = formatDate(fromDate); // 계산된 시작 날짜

        // input 필드 값 설정
        $('#fromdate').val(fromDate);
        $('#todate').val(toDate);		
		
		var selectedDateRange = $(this).val();
       // 쿠키에 저장된 값과 현재 선택된 값이 다른 경우에만 페이지 새로고침
        if (savedDateRange !== selectedDateRange) {
            setCookie('dateRange', selectedDateRange, 30); // 쿠키에 dateRange 저장
			document.getElementById('board_form').submit();      
        }		
		
    });
});

function SearchEnter(){

    if(event.keyCode == 13){		
		saveSearch();
    }
}

function saveSearch() {
    let searchInput = document.getElementById('search');
    let searchValue = searchInput.value;

    console.log('searchValue ' + searchValue);

    if (searchValue === "") {        
        document.getElementById('board_form').submit();
    } else {
        let now = new Date();
        let timestamp = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();

        let searches = getSearches();
        // 기존에 동일한 검색어가 있는 경우 제거
        searches = searches.filter(search => search.keyword !== searchValue);
        // 새로운 검색 정보 추가
        searches.unshift({ keyword: searchValue, time: timestamp });
        searches = searches.slice(0, 15);

        document.cookie = "searches=" + JSON.stringify(searches) + "; max-age=31536000";
		
		var motorpageNumber = 1;
		setCookie('motorpageNumber', motorpageNumber, 10); // 쿠키에 페이지 번호 저장		
		// Set dateRange to '전체' and trigger the change event
        $('#dateRange').val('전체').change();
        document.getElementById('board_form').submit();
    }
}

// 검색창에 쿠키를 이용해서 저장하고 화면에 보여주는 코드 묶음
	document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const autocompleteList = document.getElementById('autocomplete-list');  

    searchInput.addEventListener('input', function() {
	const val = this.value;
	let searches = getSearches();
	let matches = searches.filter(s => {
		if (typeof s.keyword === 'string') {
			return s.keyword.toLowerCase().includes(val.toLowerCase());
		}
		return false;
	});			
	   renderAutocomplete(matches);               
    });
	 
    
    searchInput.addEventListener('focus', function() {
        let searches = getSearches();
        renderAutocomplete(searches);   

        console.log(searches);				
    });
			
});

    var isMouseOverSearch = false;
    var isMouseOverAutocomplete = false;

    document.getElementById('search').addEventListener('focus', function() {
        isMouseOverSearch = true;
        showAutocomplete();
    });

	document.getElementById('search').addEventListener('blur', function() {        
		setTimeout(function() {
			if (!isMouseOverAutocomplete) {
				hideAutocomplete();
			}
		}, 100); // Delay of 100 milliseconds
	});


    function hideAutocomplete() {
        document.getElementById('autocomplete-list').style.display = 'none';
    }

    function showAutocomplete() {
        document.getElementById('autocomplete-list').style.display = 'block';
    }


function renderAutocomplete(matches) {
    const autocompleteList = document.getElementById('autocomplete-list');    

    // Remove all .autocomplete-item elements
    const items = autocompleteList.getElementsByClassName('autocomplete-item');
    while(items.length > 0){
        items[0].parentNode.removeChild(items[0]);
    }

    matches.forEach(function(match) {
        let div = document.createElement('div');
        div.className = 'autocomplete-item';
        div.innerHTML =  '<span class="text-primary">' + match.keyword + ' </span>';
        div.addEventListener('click', function() {
            document.getElementById('search').value = match.keyword;
            autocompleteList.innerHTML = '';            
			console.log(match.keyword);
            document.getElementById('board_form').submit();    
        });
        autocompleteList.appendChild(div);
    });
}


function getSearches() {
    let cookies = document.cookie.split('; ');
    for (let cookie of cookies) {
        if (cookie.startsWith('searches=')) {
            try {
                let searches = JSON.parse(cookie.substring(9));
                // 배열이 50개 이상의 요소를 포함하는 경우 처음 50개만 반환
                if (searches.length > 15) {
                    return searches.slice(0, 15);
                }
                return searches;
            } catch (e) {
                console.error('Error parsing JSON from cookies', e);
                return []; // 오류가 발생하면 빈 배열 반환
            }
        }
    }
    return []; // 'searches' 쿠키가 없는 경우 빈 배열 반환
}


$(document).ready(function(){	

		$("#denkriModel").hover(function(){
			$("#customTooltip").show();
		}, function(){
			$("#customTooltip").hide();
		});

		$("#searchBtn").click(function(){ 	
			  saveSearch(); 
		 });		



});

$(document).ready(function() {
    $('.search-condition').change(function() {
        // 모든 체크박스의 선택을 해제합니다.
        $('.search-condition').not(this).prop('checked', false);

        // 선택된 체크박스의 값으로 `check` 필드를 업데이트합니다.
        var condition = $(this).is(":checked") ? $(this).val() : '';
        $("#check").val(condition);

        // 검색 입력란을 비우고 폼을 제출합니다.
        // $("#search").val('');                                                      
        $('#board_form').submit();  
    });
});


$(document).ready(function(){	
	
    // showstatus 요소와 showstatusframe 요소가 페이지에 존재하는지 확인
    var showstatus = document.getElementById('showstatus');
    var showstatusframe = document.getElementById('showstatusframe');
    
    // 요소가 존재하지 않으면 나머지 코드는 실행하지 않음
    if (!showstatus || !showstatusframe) {
        return;
    }

    var hideTimeoutstatus; // 프레임을 숨기기 위한 타이머 변수 

    // 요소가 존재한다면 이벤트 리스너를 추가
    showstatus.addEventListener('mouseenter', function(event) {
        clearTimeout(hideTimeoutstatus);  // 이미 설정된 타이머가 있다면 취소
        showstatusframe.style.top = (showstatus.offsetTop + showstatus.offsetHeight) + 'px';
        showstatusframe.style.left = showstatus.offsetLeft + 'px';
        showstatusframe.style.display = 'block';
    });

    showstatus.addEventListener('mouseleave', startstatusHideTimer);

    showstatusframe.addEventListener('mouseenter', function() {
        clearTimeout(hideTimeoutstatus);  // 이미 설정된 타이머가 있다면 취소
    });

    showstatusframe.addEventListener('mouseleave', startstatusHideTimer);

    // 타이머를 시작하는 함수
    function startstatusHideTimer() {
        hideTimeoutstatus = setTimeout(function() {
            showstatusframe.style.display = 'none';
        }, 50);  // 300ms 후에 프레임을 숨깁니다.
    }
	
});

</script>
  