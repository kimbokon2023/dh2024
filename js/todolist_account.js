$(document).ready(function() {
    let todo_currentMonth = new Date().getMonth();
    let todo_currentYear = new Date().getFullYear();

	function todo_fetchCalendarData_account(month, year) {
		$.ajax({
			url: "/todo_account/fetch_todo.php",
			type: "post",
			data: { month: month + 1, year: year },
			dataType: "json",
			success: function(response) {
				let calendarHtml = todo_generateCalendarHtml_account(response.todo_data, response.leave_data, response.monthly_data, response.holiday_data);
				if ($('#todo-calendar-container_account').length) {
					$('#todo-calendar-container_account').html(calendarHtml);					
					$('#todo-calendar-container_account').css("display", "block"); // 항상 보이게 하기
				}
			},
			error: function() {
				console.log('Failed to fetch data');
			}
		});
	}

function todo_generateCalendarHtml_account(todoData, leaveData, monthlyData, holidayData) {
    const daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];
    let date = new Date(todo_currentYear, todo_currentMonth, 1);
    let firstDay = date.getDay();
    let lastDate = new Date(todo_currentYear, todo_currentMonth + 1, 0).getDate();
    let today = new Date();
    
    let todayYear = today.getFullYear();
    let todayMonth = today.getMonth();
    let todayDate = today.getDate();

    let calendarHtml = '<table id="todo-list_account" class="table table-condensed">';
    calendarHtml += '<thead class="table-danger text-start"><tr>';
    daysOfWeek.forEach(day => {
        calendarHtml += '<th class="fs-6 text-start" style="width:14%;">' + day + '</th>';
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
                let dayData = todoData.filter(item => new Date(item.orderdate).getDate() === day);
                let currentDate = new Date(todo_currentYear, todo_currentMonth, day);
                let leaveDataForDay = leaveData.filter(item => {
                    let leaveStart = new Date(item.al_askdatefrom);
                    let leaveEnd = new Date(item.al_askdateto);

                    leaveStart.setHours(0, 0, 0, 0);
                    leaveEnd.setHours(0, 0, 0, 0);
                    currentDate.setHours(0, 0, 0, 0);

                    return currentDate >= leaveStart && currentDate <= leaveEnd;
                });

                let dayClass = (j === 0 || j === 6) ? 'red-day' : '';
                
                if (currentDate.getFullYear() === todayYear && currentDate.getMonth() === todayMonth && currentDate.getDate() === todayDate) {
                    dayClass += ' today-bg';
                }
				
				    // 휴일처리를 위한 구문 추가 20240830
                    // 한국 시간대를 고려한 날짜 변환 함수
                    function convertToKST(dateString) {
                        const utcDate = new Date(dateString + 'T00:00:00Z'); // UTC로 변환
                        const kstDate = new Date(utcDate.getTime() + 9 * 60 * 60 * 1000); // 9시간 추가 (KST)
                        kstDate.setHours(0, 0, 0, 0);  // 시간을 0으로 설정
                        return kstDate;
                    }

                    // holidayData에 해당 날짜가 있는지 확인
                    let holidayForDay = holidayData.filter(item => {
                        let startDate = convertToKST(item.startdate);
                        let endDate = item.enddate && item.enddate !== '0000-00-00' ? convertToKST(item.enddate) : startDate;
                        return currentDate >= startDate && currentDate <= endDate;
                    });

                    // 만약 해당 날짜가 holiday에 포함되면 red-day 추가
                    if (holidayForDay.length > 0) {
                        dayClass += ' text-danger';
                    }
				//	휴일처리를 위한 구문 끝.		
				// align-top 부트스트랩 위쪽 정렬을 위한 코드
                calendarHtml += `<td class="${dayClass} align-top"><div class="fw-bold fs-6">${day} <button type="button" class="event btn btn-outline-dark btn-sm" style="border:0px;"  data-date="${todo_currentYear}-${('0' + (todo_currentMonth + 1)).slice(-2)}-${('0' + day).slice(-2)}" > <i class="bi bi-plus"></i> </button> </div>`;



				// 휴일 holiday 표시
				holidayForDay.forEach(item => {
					calendarHtml += `<div><span class='badge bg-danger'>${item.comment}</span></div>`;
				});

                // 월간일정 더하기
                monthlyData.forEach(item => {
                    if (parseInt(item.specialday) === day) {
                        calendarHtml += `<div><span class="fw-bold">${item.title}</span></div>`;
                    }
                });
				
                // 년간일정 더하기
                monthlyData.forEach(item => {
                    if (item.yearlyspecialday) {
                        // '9/3' 같은 데이터를 월과 일로 나누기
                        let [month, dayOfMonth] = item.yearlyspecialday.split('/').map(num => parseInt(num));

                        // 현재 달과 일과 비교
                        if (month === (todo_currentMonth + 1) && dayOfMonth === day) {
                            calendarHtml += `<div><span class="badge bg-primary">${item.title}</span></div>`;
                        }
                    }
                });				

                if (j !== 0 && j !== 6) { // Exclude weekends
                    leaveDataForDay.forEach(item => {
                        calendarHtml += '<div class="leave-info"><span class="badge bg-success">' + item.author + ' (' + item.al_item + ':' + item.al_content + ')</span></div>';
                    });
                }
                
                dayData.forEach(item => {
                    calendarHtml += '<div class="todo-event event" data-id="' + item.num + '"><span class="badge bg-primary">' + item.work_status  + '</span> ' + item.title + '</div>';
                });

                calendarHtml += '</td>';
                day++;
            }
        }
        calendarHtml += '</tr>';
    }

    calendarHtml += '</tbody></table>';

    $('#todo-current-period').text(todo_currentYear + '/' + ('0' + (todo_currentMonth + 1)).slice(-2));

    return calendarHtml;
}


    $('#todo-calendar-container_account').on('click', '.event', function() {
        let num = $(this).data('id');
        let date = $(this).data('date');
        loadForm(num, date);
    });

    function loadForm(num, date) {
        let mode = num == 'undefined' ||  num ==  null ? 'insert' : 'update';
        // console.log(date);
         console.log(num);
         console.log(mode);
		 $("#mode").val(mode);
		 $("#num").val(num);
        $.ajax({
            type: "POST",
            url: "/todo_account/fetch_modal.php",
            data: { mode: mode, num: num, seldate : date },
            dataType: "html",
            success: function(response) {                
                document.querySelector(".modal-body .custom-card").innerHTML = response;
                $("#todoModal").show();

                // Bootstrap's data-dismiss="modal" handles modal closing automatically
                // Removed custom click handlers to prevent conflicts

				// Log 파일보기
				$("#showlogBtn").click( function() {     	
					var num	= $("#num").val();
					// table 이름을 넣어야 함
					var workitem =  'todos' ;
					// 버튼 비활성화
					var btn = $(this);						
						popupCenter("/Showlog.php?num=" + num + "&workitem=" + workitem , '로그기록 보기', 500, 500);									 
					btn.prop('disabled', false);					 					 
				});	

				// 요청처리일을 입력하면 진행상태를 '완료'로 변경하고, 날짜를 지우면 '작성'으로 변경
				$('#deadline').change(function() {
					if ($(this).val()) {
						$('#work_status').val('완료');
					} else {
						$('#work_status').val('작성');
					}
				});

                // 개별일정 저장 버튼
                $("#saveBtn").on("click", function() {
                    var formData = $("#board_form").serialize();

                    $.ajax({
                        url: "/todo_account/process.php",
                        type: "post",
                        data: formData,
                        success: function(response) {
							console.log(response);
                            Toastify({
                                text: "저장완료",
                                duration: 3000,
                                close: true,
                                gravity: "top",
                                position: "center",
                                backgroundColor: "#4fbe87",
                            }).showToast();
                            $("#todoModal").hide();
                            todo_fetchCalendarData_account(todo_currentMonth, todo_currentYear); // 변경된 데이터만 다시 로드
                        },
                        error: function(jqxhr, status, error) {
                            console.log(jqxhr, status, error);
                        }
                    });
                });
				
                // 삭제 버튼
                $("#deleteBtn").on("click", function() {                    
                    var user_name = $("#user_name").val();
                    var first_writer = $("#first_writer").val();

                    if (user_name !== first_writer && user_name !=='개발자') {
                        Swal.fire({
                            title: '삭제불가',
                            text: "작성자만 삭제 가능합니다.",
                            icon: 'error',
                            confirmButtonText: '확인'
                        });
                        return;
                    }

                    Swal.fire({
                        title: '자료 삭제',
                        text: "삭제는 신중! 정말 삭제하시겠습니까?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '삭제',
                        cancelButtonText: '취소'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#mode").val('delete');
                            var formData = $("#board_form").serialize();

                            $.ajax({
                                url: "/todo_account/process.php",
                                type: "post",
                                data: formData,
                                success: function(response) {
                                    Toastify({
                                        text: "일정 삭제완료",
                                        duration: 2000,
                                        close: true,
                                        gravity: "top",
                                        position: "center",
                                        style: {
                                            background: "linear-gradient(to right, #00b09b, #96c93d)"
                                        },
                                    }).showToast();

                                    $("#todoModal").hide();
                                    todo_fetchCalendarData_account(todo_currentMonth, todo_currentYear); // 변경된 데이터만 다시 로드
                                },
                                error: function(jqxhr, status, error) {
                                    console.log(jqxhr, status, error);
                                }
                            });
                        }
                    });
                });
 
				// 체크박스 클릭시 처리
				  function updateApproversInput() {
						let approvers = [];
						$('.approver-checkbox:checked').each(function() {
							approvers.push($(this).data('user-name'));
						});
						$('#towhom').val(approvers.join(', '));
					}

					$('.approver-checkbox').change(function() {
						updateApproversInput();
					});

					// 기존에 선택된 사용자를 반영합니다.
					let selectedApprovers = $('#towhom').val().split(', ');
					$('.approver-checkbox').each(function() {
						if (selectedApprovers.includes($(this).data('user-name'))) {
							$(this).prop('checked', true);
						}
					});				
			
			},
            error: function(jqxhr, status, error) {
                console.log("AJAX Error: ", status, error);
            }
        });
    }

    $('#todo-prev-month_account').click(function() {
        todo_currentMonth--;
        if (todo_currentMonth < 0) {
            todo_currentMonth = 11;
            todo_currentYear--;
        }
        todo_fetchCalendarData_account(todo_currentMonth, todo_currentYear);
    });

    $('#todo-next-month_account').click(function() {
        todo_currentMonth++;
        if (todo_currentMonth > 11) {
            todo_currentMonth = 0;
            todo_currentYear++;
        }
        todo_fetchCalendarData_account(todo_currentMonth, todo_currentYear);
    });

    $('#todo-current-month_account').click(function() {
        todo_currentMonth = new Date().getMonth();
        todo_currentYear = new Date().getFullYear();
        todo_fetchCalendarData_account(todo_currentMonth, todo_currentYear);
    });

    todo_fetchCalendarData_account(todo_currentMonth, todo_currentYear);
    
});

