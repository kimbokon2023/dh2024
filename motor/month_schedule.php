<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$today = date("Y-m-d");
$title_message = 'DH모터 일정(출고예정일 기준)';
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>
<title> <?=$title_message?> </title>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/myheader.php'; ?>
<style>
	.red-day {
	  color: red !important;
	}

	.today {
	  background-color: red !important;
	}

	td {
		vertical-align: top;
	}

	.scrollable-modal-body {
		max-height: 500px;
		overflow-y: auto;
	}
</style>

<!-- Modal Structure -->
<div class="container-fluid">
  <!-- Modal -->
  <div id="show_listModal" class="modal fade">
    <div class="modal-dialog modal-lg" style="width:800px;">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title">출고 내역 조회</h2>          
            <span  class="close-modal" aria-hidden="true">&times;</span>          
        </div>
        <div class="modal-body scrollable-modal-body">
          <table class="table table-hover">
            <thead class="table-primary">
              <tr>
                <th class="text-center">현장명</th>
                <th class="text-center">품목명</th>
                <th class="text-center">수량 or M</th>
              </tr>
            </thead>
            <tbody id="modalTableBody" >
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark btn-sm close-modal" data-dismiss="modal"><i class="bi bi-x-lg"></i> 닫기</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">
<div class="card mt-2 mb-2">
  <div class="row mt-4">
    <div class="col-sm-3"></div>
    <div class="col-sm-5">
      <div class="d-flex justify-content-center align-items-center mb-4">
        <button id="prev-month" class="btn btn-primary btn-sm me-1"> <i class="bi bi-arrow-left"></i> 전월 </button>
        <button id="next-month" class="btn btn-primary btn-sm me-1">   다음 달  <i class="bi bi-arrow-right"></i>  </button>
        <button id="current-month" class="btn btn-outline-primary btn-sm me-5 ">이번 달</button>
			<h4 id="clickableText_motor"> <?=$title_message?> </h4>
			<button type="button" class="btn btn-dark btn-sm mx-3" onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>
			<small class="ms-5 text-muted"> 출고예정일 기준 월간 출고내역입니다.</small>  
      </div>
      <div class="d-flex justify-content-center items-center" style="vertical-align: center">
        <span id="current-period" class="text-primary fs-6 me-3"></span>
      </div>  
    </div>  
    <div class="col-sm-3">
      <div class="d-flex justify-content-center mb-2">
        <span class="badge bg-success fs-6 me-1"> 출고예정 수량(예정일 기준) </span>		
      </div>
      <div class="d-flex justify-content-center">
        <table class="table table-bordered table-striped w-75">
          <thead class="table-success">
            <tr>
              <th class="text-center text-primary">모터(M)</th>
              <th class="text-center text-primary">브라켓(B)</th>
              <th class="text-center text-success">연동제어기(C)</th>
              <th class="text-center text-info">원단(F)-단위:m</th>
            </tr>
          </thead>
          <tbody>
				<tr>
				  <td class="text-center"><span id="motor_sum" data-type="motor"></span></td>
				  <td class="text-center"><span id="bracket_sum" data-type="bracket"></span></td>
				  <td class="text-center"><span id="controller_sum"  data-type="controller"></span></td>
				  <td class="text-center"><span id="fabricSum" data-type="fabric"></span></td>
				</tr>
          </tbody>
        </table>
      </div>
    </div>
  <div class="col-sm-1"></div>
</div>  
<div id="calendar-container" class="d-flex p-2 justify-content-center"></div>  
</div>
</div>

<script>
// 페이지 로딩
$(document).ready(function() {
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';

    $(document).on('click', '.close-modal', function() {
        $('#show_listModal').modal('hide');
    });
});

let currentMonth ;
let currentYear ;
let currentDay ;
let ajaxRequest = null ;

$(document).ready(function() {
    currentMonth = new Date().getMonth();
    currentYear = new Date().getFullYear();

    function fetchCalendarData(month, year) {
        if (ajaxRequest !== null) {
            ajaxRequest.abort();
        }
        ajaxRequest = $.ajax({
            url: "/motor/fetch_deadline.php",
            type: "post",
            data: { month: month + 1, year: year },
            dataType: "json",
            success: function(response) {
                updateCalendar(month + 1, year, response.data_motor);
                ajaxRequest = null;
            },
			error: function(jqXHR, textStatus, errorThrown) {
				let errorMsg = 'AJAX 요청 실패\n';
				errorMsg += '상태: ' + textStatus + '\n';
				errorMsg += '오류: ' + errorThrown + '\n';
				errorMsg += '응답 텍스트: ' + jqXHR.responseText;
				console.log("상태:", textStatus);
				console.log("오류:", errorThrown);
				console.log("응답 텍스트:", jqXHR.responseText);
				alert(errorMsg);				
			}
	});
}

function updateCalendar(month, year, data) {
    let calendarHtml = generateCalendarHtml(data);
    $('#calendar-container').html(calendarHtml);

    if (ajaxRequest !== null) {
        ajaxRequest.abort();
    }

    // 현재 날짜를 가져옴
    const today = new Date();
    const currentMonth = today.getMonth() + 1; // 월은 0부터 시작하므로 1을 더함
    const currentYear = today.getFullYear();
    const currentDay = today.getDate();

    ajaxRequest = $.ajax({
        url: "/motor/fetch_outputdate.php", // 출고예정일 데이터로 수정 deadline은 출고예정일 20250402 수정
        type: "post",
        data: { month: month, year: year },
        dataType: "json",
        success: function(response) {
			
			// console.log(response.data_motor);
            let data_motor = response.data_motor;
            let motorSum = 0;
            let bracketSum = 0;
            let controllerSum = 0;
            let fabricSum = 0;
			
            if (Array.isArray(data_motor)) {
                data_motor.forEach(item => {
                    // 현재의 달과 년도가 선택되었을 경우 오늘 날짜까지의 데이터만 계산
                    if (year == currentYear && month == currentMonth) {
                        const outputDate = new Date(item.outputdate);
                        if (outputDate.getDate() > currentDay) {
                            return; // 오늘 날짜 이후의 데이터는 계산하지 않음
                        }
                    }

                    let orderlist = item.orderlist ? JSON.parse(item.orderlist) : [];
                    let controllerlist = item.controllerlist ? JSON.parse(item.controllerlist) : [];
                    let fabriclist = item.fabriclist ? JSON.parse(item.fabriclist) : [];
					
					// console.log(fabriclist);					

                    orderlist.forEach(function(i) {
                        if (i.col5 === 'SET' || i.col5 === '모터단품') {
                            motorSum += parseInt(i.col8, 10);
                        }
                    });
                    orderlist.forEach(function(i) {
                        if (i.col5 === 'SET' || i.col5 === '브라켓트') {
                            bracketSum += parseInt(i.col8, 10);
                        }
                    });

                    controllerlist.forEach(function(i) {
                        let value = parseInt(i.col3, 10);
                        if (!isNaN(value)) {
                            controllerSum += value;
                        }
                    });

                    /* 에서 i.col5의 값이 "1,000"이라면 **parseInt("1,000", 10)**의 결과는 **1**입니다. 즉, 쉼표(,)가 있는 숫자 문자열은 올바르게 처리되지 않습니다. */
					fabriclist.forEach(function(i) {
						let value = Number((i.col5 || "0").replace(/,/g, ""));   
						if (!isNaN(value)) {
							fabricSum += value;
						}
					});

                });

                $('#motor_sum').text(motorSum);
                $('#bracket_sum').text(bracketSum);
                $('#controller_sum').text(controllerSum);
                $('#fabricSum').text(fabricSum);
            } else {
                console.error("Unexpected response format:", response);
                alert('Unexpected response format');
            }

            ajaxRequest = null;
        },
		error: function(jqXHR, textStatus, errorThrown) {
			let errorMsg = 'AJAX 요청 실패\n';
			errorMsg += '상태: ' + textStatus + '\n';
			errorMsg += '오류: ' + errorThrown + '\n';
			errorMsg += '응답 텍스트: ' + jqXHR.responseText;
			console.log("상태:", textStatus);
			console.log("오류:", errorThrown);
			console.log("응답 텍스트:", jqXHR.responseText);
			alert(errorMsg);				
		}
    });
}


    function generateCalendarHtml(data) {
        const daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];
        let date = new Date(currentYear, currentMonth, 1);
        let firstDay = date.getDay();
        let lastDate = new Date(currentYear, currentMonth + 1, 0).getDate();

        let today = new Date();
        let todayYear = today.getFullYear();
        let todayMonth = today.getMonth();
        let todayDate = today.getDate();

        let deliveryMethodOrder = [
            "직접수령",
            "직배송",
            "배차",
            "경동공장",
            "선/경동화물",
            "착/경동화물",
            "대신화물",
            "선/택배",
            "착/택배"
        ];

        let calendarHtml = '<table class="table table-condensed table-tight">';
        calendarHtml += '<thead class="table-primary"><tr>';

        daysOfWeek.forEach(day => {
            calendarHtml += '<th class="fs-6">' + day + '</th>';
        });

        calendarHtml += '</tr></thead><tbody>';

        let day = 1;
        for (let i = 0; i < 6; i++) {
            calendarHtml += '<tr>';
            for (let j = 0; j < 7; j++) {
                if (i === 0 && j < firstDay) {
                    calendarHtml += '<td></td>';
                } else if (day > lastDate) {
                    calendarHtml += '<td></td>';
                } else {
                    let dayData = data.filter(item => new Date(item.deadline).getDate() === day);
                    let dayClass = (j === 0 || j === 6) ? 'red-day' : '';

                    let currentDate = new Date(currentYear, currentMonth, day);
                    if (currentDate.getFullYear() === todayYear && currentDate.getMonth() === todayMonth && currentDate.getDate() === todayDate) {
                        dayClass += ' today-bg';
                    }

                    let motorNum = 0;
                    let bracketNum = 0;
                    let controllerNum = 0;
                    let fabricNum = 0;

                    dayData.forEach(item => {
                        let orderlist = item.orderlist ? JSON.parse(item.orderlist) : [];
                        let controllerlist = item.controllerlist ? JSON.parse(item.controllerlist) : [];
                        let fabriclist = item.fabriclist ? JSON.parse(item.fabriclist) : [];

                        orderlist.forEach(function(i) {
                            if (i.col5 === 'SET' || i.col5 === '모터단품') {
                                motorNum += parseInt(i.col8, 10);
                            }
                        });
                        orderlist.forEach(function(i) {
                            if (i.col5 === 'SET' || i.col5 === '브라켓트') {
                                bracketNum += parseInt(i.col8, 10);
                            }
                        });	

                        controllerlist.forEach(function(i) {
                            let value = parseInt(i.col3, 10);
                            if (!isNaN(value)) {
                                controllerNum += value;
                            }
                        });
											

						fabriclist.forEach(function(i) {
							let value = Number((i.col5 || "0").replace(/,/g, ""));
							if (!isNaN(value)) {
								fabricNum += value;
							}
						});

                    });

                    calendarHtml += '<td class="' + dayClass + '"><div class="fw-bold fs-6">' + day + '</div>';

                    if (motorNum > 0) {
                        calendarHtml += '<span class="badge bg-primary fs-6 badge-clickable" data-type="motor" data-date="' + day + '"> M ' + motorNum + '</span> &nbsp;';
                    }
                    if (bracketNum > 0) {
                        calendarHtml += '<span class="badge bg-danger fs-6 badge-clickable" data-type="bracket" data-date="' + day + '"> B ' + bracketNum + '</span> &nbsp;';
                    }
                    if (controllerNum > 0) {
                        calendarHtml += '<span class="badge bg-success fs-6 badge-clickable" data-type="controller" data-date="' + day + '"> C ' + controllerNum + '</span> &nbsp;';
                    }
                    if (fabricNum > 0) {
                        calendarHtml += '<span class="badge bg-info fs-6 badge-clickable" data-type="fabric" data-date="' + day + '"> F ' + fabricNum + '</span> &nbsp;';
                    }

                    dayData.sort((a, b) => {
                        let methodA = a.deliverymethod;
                        let methodB = b.deliverymethod;
                        return deliveryMethodOrder.indexOf(methodA) - deliveryMethodOrder.indexOf(methodB);
                    });

                    dayData.forEach(item => {
                        let orderlist = item.orderlist ? JSON.parse(item.orderlist) : [];
                        let controllerlist = item.controllerlist ? JSON.parse(item.controllerlist) : [];
                        let fabriclist = item.fabriclist ? JSON.parse(item.fabriclist) : [];

                        let sum1 = 0;
                        let sum2 = 0;
                        let sum3 = 0;
                        let sum4 = 0;

                        orderlist.forEach(function(i) {
                            if (i.col5 === 'SET' || i.col5 === '모터단품') {
                                sum1 += parseInt(i.col8, 10);
                            }
                        });
                        orderlist.forEach(function(i) {
                            if (i.col5 === 'SET' || i.col5 === '브라켓트') {
                                sum2 += parseInt(i.col8, 10);
                            }
                        });
                        controllerlist.forEach(function(i) {
                            sum3 += parseInt(i.col3, 10);
                        });

						fabriclist.forEach(function(i) {
							let value = Number((i.col5 || "0").replace(/,/g, ""));   
							if (!isNaN(value)) {
								sum4 += value;
							}
						});						

                        let totalsum = sum1 + sum2 + sum3 + sum4;

                        let statusClass = item.status === '출고완료' ? 'text-dark' : 'text-primary';
                        let deliveryClass = 'bg-secondary';
                        switch (item.deliverymethod) {
                            case '직접수령':
                                deliveryClass = 'bg-danger';
                                break;
                            case '직배송':
                                deliveryClass = 'bg-info';
                                break;
                            case '배차':
                                deliveryClass = 'bg-dark';
                                break;
                            case '선/경동화물':
                            case '착/경동화물':
                                deliveryClass = 'bg-primary';
                                break;
                            case '대신화물':
                                deliveryClass = 'bg-success';
                                break;
                            case '선/택배':
                            case '착/택배':
                                deliveryClass = 'bg-warning';
                                break;
                        }

                        var statustag = '';
                        switch (item.status) {
                            case '출고완료':
                                statustag = '<span class="text-danger fw-bold">완료</span>';
                                break;
                            default:
                                statustag = item.status;
                                break;
                        }

                        calendarHtml += '<div class="event" data-id="' + item.num + '"><span class="badge ' + deliveryClass + '"> ' + item.deliverymethod + ' </span> <span class="' + statusClass + '">' + item.workplacename + '[' + item.secondord + '] ' + totalsum + ' ' + statustag + '</span></div>';
                    });

                    calendarHtml += '</td>';
                    day++;
                }
            }
            calendarHtml += '</tr>';
        }

        calendarHtml += '</tbody></table>';

        let startDate = new Date(currentYear, currentMonth, 1);
        let endDate = new Date(currentYear, currentMonth, lastDate);
        $('#current-period').text(startDate.toLocaleDateString() + ' ~ ' + endDate.toLocaleDateString());

        return calendarHtml;
    }

    $('#calendar-container').on('click', '.event', function() {
        let id = $(this).data('id');
        if (id) {
            popupCenter('../motor/write_form.php?mode=view&num=' + id, '수주내역', 1850, 900);
        }
    });

    $('#prev-month').click(function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        fetchCalendarData(currentMonth, currentYear);
    });

    $('#next-month').click(function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        fetchCalendarData(currentMonth, currentYear);
    });

    $('#current-month').click(function() {
        currentMonth = new Date().getMonth();
        currentYear = new Date().getFullYear();
        fetchCalendarData(currentMonth, currentYear);
    });

    fetchCalendarData(currentMonth, currentYear);
});

$(document).on('click', '.badge-clickable', function() {
    var type = $(this).data('type');
    var date = $(this).data('date');
    fetchAndDisplayModalData(type, date);
});

function fetchAndDisplayModalData(type, date) {
    if (ajaxRequest !== null) {
        ajaxRequest.abort();
    }
    ajaxRequest = $.ajax({
        url: "/motor/fetch_modal_data.php",
        type: "post",
        data: { type: type, date: date, month: currentMonth + 1, year: currentYear },
        dataType: "json",
        success: function(response) {
            console.log(response);
            let modalTableBody = $('#modalTableBody');
            modalTableBody.empty();

            response.data.forEach(item => {
                modalTableBody.append('<tr><td class="text-start">' + item.workplacename + '</td><td class="text-start">' + item.itemname + '</td><td class="text-center">' + item.quantity + '</td></tr>');
            });

            $('#show_listModal').modal('show');
			ajaxRequest = null;
        },
		error: function(jqXHR, textStatus, errorThrown) {
			let errorMsg = 'AJAX 요청 실패\n';
			errorMsg += '상태: ' + textStatus + '\n';
			errorMsg += '오류: ' + errorThrown + '\n';
			errorMsg += '응답 텍스트: ' + jqXHR.responseText;
			console.log("상태:", textStatus);
			console.log("오류:", errorThrown);
			console.log("응답 텍스트:", jqXHR.responseText);
			alert(errorMsg);				
		}
    });
}


$(document).ready(function(){
	saveLogData('DH모터 출고 일정'); 
});
</script>

</body>
</html>
