function alldatesearch() { // 전체 년도 설정
    // 현재 날짜를 가져옵니다.
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; // January is 0!
    var yyyy = today.getFullYear();

    // 일(day)과 월(month)이 10보다 작으면 앞에 0을 붙입니다.
    if (dd < 10) {
        dd = '0' + dd;
    }
    if (mm < 10) {
        mm = '0' + mm;
    }

    // 현재 날짜를 mm/dd/yyyy 형식으로 변환합니다.
    today = mm + '/' + dd + '/' + yyyy;

    // 시작 날짜를 '2010-01-01'로 고정합니다.
    var fromAllDate = '2010-01-01';
    
    // 종료 날짜를 현재 날짜(yyyy-mm-dd)로 설정합니다.
    var toAllDate = yyyy + '-' + mm + '-' + dd;

    // HTML 문서에서 날짜 입력 필드의 값을 설정합니다.
    document.getElementById("fromdate").value = fromAllDate;
    document.getElementById("todate").value = toAllDate;

    // 폼을 제출하여 검색 효과를 발생시킵니다.
    document.getElementById('board_form').submit();
}


function up_pre_year(){   // 윗쪽 전년도 추출
 
	// document.getElementById('search').value=null; 
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth()+1; //January is 0!
	var yyyy = today.getFullYear();

	if(dd<10) {
		dd='0'+dd;
	} 

	if(mm<10) {
		mm='0'+mm;
	} 

	today = mm+'/'+dd+'/'+yyyy;
	yyyy=yyyy-1;
	frompreyear = yyyy+'-01-01';
	topreyear = yyyy+'-12-31';	

	document.getElementById("fromdate").value = frompreyear;
	document.getElementById("todate").value = topreyear;
	document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 	
	
}  
 
function pre_year(){   // 전년도 추출
	// document.getElementById('search').value=null; 
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth()+1; //January is 0!
	var yyyy = today.getFullYear();

	if(dd<10) {
		dd='0'+dd;
	} 

	if(mm<10) {
		mm='0'+mm;
	} 

	today = mm+'/'+dd+'/'+yyyy;
	yyyy=yyyy-1;
	frompreyear = yyyy+'-01-01';
	topreyear = yyyy+'-12-31';	

	document.getElementById("fromdate").value = frompreyear;
	document.getElementById("todate").value = topreyear;
	document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 	
}  

function pre_month(){    // 전월
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; // January is 0!
    var yyyy = today.getFullYear();
    if(dd < 10) {
        dd = '0' + dd;
    }

    mm = mm - 1;
    if(mm < 1) {
        mm = '12';
        yyyy = yyyy - 1; // 연도 조정
    } 
    if(mm < 10) {
        mm = '0' + mm;
    }

    var frompreyear = yyyy + '-' + mm + '-01';
    var tmp = 0;

    switch (Number(mm)) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10:
        case 12:
            tmp = 31;
            break;
        case 2:
            // 윤년 계산
            if ((yyyy % 4 === 0 && yyyy % 100 !== 0) || (yyyy % 400 === 0)) {
                tmp = 29; // 윤년
            } else {
                tmp = 28; // 평년
            }
            break;
        case 4:
        case 6:
        case 9:
        case 11:
            tmp = 30;
            break;
    }

    var topreyear = yyyy + '-' + mm + '-' + tmp;

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
    document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
}

function up_this_year(){   // 윗쪽 당해년도
// document.getElementById('search').value=null; 
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!
var yyyy = today.getFullYear();

if(dd<10) {
    dd='0'+dd;
} 

if(mm<10) {
    mm='0'+mm;
} 

today = mm+'/'+dd+'/'+yyyy;
frompreyear = yyyy+'-01-01';
topreyear = yyyy+'-12-31';	

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
fromdate1=frompreyear;
todate1=topreyear;
document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
} 

function prepre_month(){  // 전전월
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; 
    var yyyy = today.getFullYear();

    mm = mm - 2;

    if(mm < 1) {
        yyyy = yyyy - 1;
        mm = mm + 12;
    }

    if(mm < 10) {
        mm = '0' + mm;
    }

    var frompreyear = yyyy + '-' + mm + '-01';

    var tmp = 0;

    if(mm == '01' || mm == '03' || mm == '05' || mm == '07' || mm == '08' || mm == '10' || mm == '12') {
        tmp = 31;
    } else if(mm == '02') {
        if((yyyy % 4 == 0 && yyyy % 100 != 0) || (yyyy % 400 == 0)) {
            tmp = 29; // 윤년
        } else {
            tmp = 28; // 평년
        }
    } else {
        tmp = 30;
    }

    var topreyear = yyyy + '-' + mm + '-' + tmp;

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
    document.getElementById('board_form').submit(); 
} 

function three_month_ago(){    // 석달전
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; // January is 0!
    var yyyy = today.getFullYear();
    if(dd < 10) {
        dd = '0' + dd;
    }

    mm = mm - 3; // 세 달 전

    // 월과 연도 조정
    if(mm <= 0) {
        mm = 12 + mm;  // 월 계산
        yyyy = yyyy - 1; // 연도 감소
    }

    if(mm < 10) {
        mm = '0' + mm;
    }

    frompreyear = yyyy + '-' + mm + '-01';

    var tmp = 0;
    
    // 월의 마지막 날짜 계산
    switch (Number(mm)) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10:
        case 12:
            tmp = 31;
            break;
        case 2:
            // 윤년 계산 추가
            if ((yyyy % 4 === 0 && yyyy % 100 !== 0) || (yyyy % 400 === 0)) {
                tmp = 29; // 윤년
            } else {
                tmp = 28; // 평년
            }
            break;
        case 4:
        case 6:
        case 9:
        case 11:
            tmp = 30;
            break;
    }

    topreyear = yyyy + '-' + mm + '-' + tmp;

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
    document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
}

function this_year(){   // 아래쪽 당해년도
// document.getElementById('search').value=null; 
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!
var yyyy = today.getFullYear();

if(dd<10) {
    dd='0'+dd;
} 

if(mm<10) {
    mm='0'+mm;
} 

today = mm+'/'+dd+'/'+yyyy;
frompreyear = yyyy+'-01-01';
topreyear = yyyy+'-12-31';	

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
fromdate1=frompreyear;
todate1=topreyear;
document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
} 

function up_this_month(){   // 윗쪽 당해월
// document.getElementById('search').value=null; 
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!
var yyyy = today.getFullYear();

if(dd<10) {
    dd='0'+dd;
} 

if(mm<10) {
    mm='0'+mm;
} 

frompreyear = yyyy+'-'+mm+'-01';
			switch (Number(mm)) {
				
				case 1 :
				case 3 :
				case 5 :
				case 7 :
				case 8 :
				case 10 :
				case 12 :
				  tmp=31 ;
				  break;
				case 2 :   
				   tmp=28;
				   break;
				case 4 :
				case 6 :
				case 9 :
				case 11:
				   tmp=30;
				   break;
			}  	  

			topreyear = yyyy + '-' + mm + '-' + tmp ;


    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
} 

function this_month() {   // 당해월
    var today = new Date();
    var yyyy = today.getFullYear();
    var mm = today.getMonth() + 1; // January is 0!
    
    // 월을 두 자리로 포맷팅
    var mmFormatted = mm.toString().padStart(2, '0');
    
    // 당해월의 첫 날
    var fromDate = `${yyyy}-${mmFormatted}-01`;
    
    // 당해월의 마지막 날을 구하기
    // 다음 달의 첫 날에서 하루를 빼면 당해월의 마지막 날이 됩니다.
    var lastDay = new Date(yyyy, mm, 0).getDate(); // 월은 0부터 시작하므로 mm을 그대로 사용
    
    // 마지막 날을 두 자리로 포맷팅
    var lastDayFormatted = lastDay.toString().padStart(2, '0');
    
    var toDate = `${yyyy}-${mmFormatted}-${lastDayFormatted}`;
    
    // 폼 요소에 값 설정
    document.getElementById("fromdate").value = fromDate;
    document.getElementById("todate").value = toDate;
    
    // 폼 제출
    document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
}


function From_tomorrow(){   // 익일 이후
var today = new Date();
var dd = today.getDate()+1;  // 하루를 더해준다. 익일
var mm = today.getMonth()+1; //January is 0! 항상 1을 더해야 해당월을 구한다
var yyyy = today.getFullYear();

if(dd<10) {
    dd='0'+dd;
} 

if(mm<10) {
    mm='0'+mm;
} 
frompreyear = yyyy+'-'+mm+'-'+dd;
topreyear = yyyy+'-12-31';	
    document.getElementById("fromdate").value = frompreyear;   
    document.getElementById("todate").value = topreyear;       
document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
} 

function Fromthis_today(){   // 금일이후
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0! 항상 1을 더해야 해당월을 구한다
var yyyy = today.getFullYear();

if(dd<10) {
    dd='0'+dd;
} 

if(mm<10) {
    mm='0'+mm;
} 

frompreyear = yyyy+'-'+mm+'-'+dd;
topreyear = yyyy+'-12-31';	

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
	
document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
} 

function up_this_today(){   // 윗쪽 날짜 입력란 금일
// document.getElementById('search').value=null; 
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0! 항상 1을 더해야 해당월을 구한다
var yyyy = today.getFullYear();

if(dd<10) {
    dd='0'+dd;
} 

if(mm<10) {
    mm='0'+mm;
} 

frompreyear = yyyy+'-'+mm+'-'+dd;
topreyear = yyyy+'-'+mm+'-'+dd;

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
	document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
} 

function this_today(){   // 금일
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0! 항상 1을 더해야 해당월을 구한다
var yyyy = today.getFullYear();

if(dd<10) {
    dd='0'+dd;
} 

if(mm<10) {
    mm='0'+mm;
} 

frompreyear = yyyy+'-'+mm+'-'+dd;
topreyear = yyyy+'-'+mm+'-'+dd;

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
	
document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과 
} 

function yesterday() {   // 전일
    // 어제 날짜를 구함
    var today = new Date();
    today.setDate(today.getDate() - 1); // 날짜를 하루 줄임

    var dd = today.getDate();
    var mm = today.getMonth() + 1; // January is 0! 항상 1을 더해야 해당월을 구함
    var yyyy = today.getFullYear();

    if(dd < 10) {
        dd = '0' + dd;
    } 

    if(mm < 10) {
        mm = '0' + mm;
    } 

    var frompreyear = yyyy + '-' + mm + '-' + dd;
    var topreyear = yyyy + '-' + mm + '-' + dd;

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
	
    document.getElementById('board_form').submit(); // form의 검색버튼 누른 효과 
}


function dayBeforeYesterday() {   
    // 이틀 전 날짜를 구함
    var today = new Date();
    today.setDate(today.getDate() - 2); // 날짜를 이틀 줄임

    var dd = today.getDate();
    var mm = today.getMonth() + 1; // January is 0! 항상 1을 더해야 해당월을 구함
    var yyyy = today.getFullYear();

    if(dd < 10) {
        dd = '0' + dd;
    } 

    if(mm < 10) {
        mm = '0' + mm;
    } 

    var frompreyear = yyyy + '-' + mm + '-' + dd;
    var topreyear = yyyy + '-' + mm + '-' + dd;

    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = topreyear;
	
    document.getElementById('board_form').submit(); // form의 검색버튼 누른 효과 
}


function this_tomorrow(){   // 익일
	// document.getElementById('search').value=null; 
	var today = new Date();
	var dd = today.getDate()+1;
	var mm = today.getMonth()+1; //January is 0! 항상 1을 더해야 해당월을 구한다
	var yyyy = today.getFullYear();

	if(dd<10) {
		dd='0'+dd;
	} 

	if(mm<10) {
		mm='0'+mm;
	} 

	frompreyear = yyyy+'-'+mm+'-'+dd;
	topreyear = yyyy+'-'+mm+'-'+dd;

	document.getElementById("fromdate").value = frompreyear;
	document.getElementById("todate").value = topreyear;	
	document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과  

} 

function formatDate(date) {
    var dd = date.getDate();
    var mm = date.getMonth() + 1; // January is 0!
    var yyyy = date.getFullYear();

    if (dd < 10) {
        dd = '0' + dd;
    } 
    if (mm < 10) {
        mm = '0' + mm;
    } 
    return yyyy + '-' + mm + '-' + dd;
}

function this_week() {
    var today = new Date();
    var end = new Date(today);
    end.setDate(today.getDate() + 6); // 7일 후의 날짜로 설정

    document.getElementById("fromdate").value = formatDate(today);
    document.getElementById("todate").value = formatDate(end);
    document.getElementById("displaytext").value = '이번주';
    document.getElementById('board_form').submit();
}

function next_week() {
    var today = new Date();
    var start = new Date(today);
    var end = new Date(today);
    start.setDate(today.getDate() + 7);
    end.setDate(today.getDate() + 13); // 14일 후의 날짜로 설정

    document.getElementById("fromdate").value = formatDate(start);
    document.getElementById("todate").value = formatDate(end);
	document.getElementById("displaytext").value = '2주간';
    document.getElementById('board_form').submit();
}

function nextnext_week() {
    var today = new Date();
    var start = new Date(today);
    var end = new Date(today);
    start.setDate(today.getDate() + 14);
    end.setDate(today.getDate() + 20); // 21일 후의 날짜로 설정

    document.getElementById("fromdate").value = formatDate(start);
    document.getElementById("todate").value = formatDate(end);
	document.getElementById("displaytext").value = '3주간';
    document.getElementById('board_form').submit();
}

function before_threeyear() {
    var today = new Date();
    var threeYearsAgo = new Date(today);
    threeYearsAgo.setFullYear(today.getFullYear() - 3); // 3년 전의 연도로 설정

    document.getElementById("fromdate").value = formatDate(threeYearsAgo);
    document.getElementById("todate").value = formatDate(today);
    document.getElementById("displaytext").value = '3년 전부터 현재까지';
    document.getElementById('board_form').submit();
}


function process_list(){   // 접수일 출고일 라디오버튼 클릭시

document.getElementById('board_form').submit();  // form의 검색버튼 누른 효과  

} 

function comma(str) { 
    str = String(str); 
    return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'); 
} 
function uncomma(str) { 
    str = String(str); 
	tmp = Number(str.replace(/[^\d]+/g, ''));
    return tmp; 
}


function threeDaysAgo() { // 전전전일
    var today = new Date();
    today.setDate(today.getDate() - 3);
    
    var frompreyear = formatDate(today);
    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = frompreyear;

    document.getElementById('board_form').submit(); 
}

function dayBeforeYesterday() { // 전전일
    var today = new Date();
    today.setDate(today.getDate() - 2);
    
    var frompreyear = formatDate(today);
    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = frompreyear;

    document.getElementById('board_form').submit(); 
}

function yesterday() { // 전일
    var today = new Date();
    today.setDate(today.getDate() - 1);
    
    var frompreyear = formatDate(today);
    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = frompreyear;

    document.getElementById('board_form').submit(); 
}

function this_today() { // 오늘
    var today = new Date();
    
    var frompreyear = formatDate(today);
    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = frompreyear;

    document.getElementById('board_form').submit(); 
}

// 월별 기간 선택 함수들
function twoMonthsAgo() { // 전전달
    var today = new Date();
    var twoMonthsAgo = new Date(today.getFullYear(), today.getMonth() - 2, 1);
    var lastDayOfTwoMonthsAgo = new Date(today.getFullYear(), today.getMonth() - 1, 0);
    
    var fromDate = formatDate(twoMonthsAgo);
    var toDate = formatDate(lastDayOfTwoMonthsAgo);
    
    document.getElementById("fromdate").value = fromDate;
    document.getElementById("todate").value = toDate;
    
    document.getElementById('board_form').submit();
}

function lastMonth() { // 전달
    var today = new Date();
    var lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    var lastDayOfLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
    
    var fromDate = formatDate(lastMonth);
    var toDate = formatDate(lastDayOfLastMonth);
    
    document.getElementById("fromdate").value = fromDate;
    document.getElementById("todate").value = toDate;
    
    document.getElementById('board_form').submit();
}

function thisMonth() { // 이번달
    var today = new Date();
    var thisMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    var todayDate = new Date();
    
    var fromDate = formatDate(thisMonth);
    var toDate = formatDate(todayDate);
    
    document.getElementById("fromdate").value = fromDate;
    document.getElementById("todate").value = toDate;
    
    document.getElementById('board_form').submit();
}

function twoDaysLater() { // 다다음날
    var today = new Date();
    today.setDate(today.getDate() + 2);
    
    var frompreyear = formatDate(today);
    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = frompreyear;

    document.getElementById('board_form').submit(); 
}

function threeDaysLater() { // 다다다음날
    var today = new Date();
    today.setDate(today.getDate() + 3);
    
    var frompreyear = formatDate(today);
    document.getElementById("fromdate").value = frompreyear;
    document.getElementById("todate").value = frompreyear;

    document.getElementById('board_form').submit(); 
}
