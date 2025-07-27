$(document).ready(function() {
    let todo_currentMonth = new Date().getMonth();
    let todo_currentYear = new Date().getFullYear();

	function todo_fetchCalendarData_work(month, year) {
		$.ajax({
			url: "/todo_work/fetch_todo.php",  // 기존 할 일 데이터를 가져오는 파일
			type: "post",
			data: { month: month + 1, year: year },  // 서버에서 처리할 수 있도록 월+1
			dataType: "json",
			success: function(response) {
				// 캘린더 데이터를 받아서 화면에 렌더링
				let calendarHtml = todo_generateCalendarHtml_work(response.todo_data, response.leave_data, response.holiday_data, response.level, response.plan_month);
				if ($('#todo-calendar-container_work').length) {
					$('#todo-calendar-container_work').html(calendarHtml);
					$('#todo-calendar-container_work').css("display", "block"); // 항상 보이게 설정
				}				
				// 월간 계획도 업데이트
				updateMonthlyPlan(month, year); // 월간 계획을 업데이트하는 함수 호출
			},
			error: function() {
				console.log('Failed to fetch data');
			}
		});
	}
	
// 월간 계획을 가져와 업데이트하는 함수
function updateMonthlyPlan(month, year) {
    $.ajax({
        url: "/work/monthly_plan_fetch.php",  // 월간 계획을 가져오는 PHP 파일
        type: "POST",
        data: { month: month + 1, year: year },  // month는 0부터 시작하므로 +1
        success: function(response) {
            // 멤버별 월간 계획 데이터를 화면에 업데이트
            $('#monthly-plan-content').html(response); // 월간 계획을 보여주는 div
        },
        error: function(xhr, status, error) {
            console.error("Error fetching monthly plan: ", error);
        }
    });
}

function todo_generateCalendarHtml_work(todoData, leaveData, holidayData, level, plan_month) {
    
    const daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];
    let date = new Date(todo_currentYear, todo_currentMonth, 1);
    let firstDay = date.getDay();
    let lastDate = new Date(todo_currentYear, todo_currentMonth + 1, 0).getDate();
    let today = new Date();
    
    let todayYear = today.getFullYear();
    let todayMonth = today.getMonth();
    let todayDate = today.getDate();

    let calendarHtml = '<table id="todo-list_work" class="table table-condensed">';
    calendarHtml += '<thead class="table-primary text-start"><tr>';
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

				calendarHtml += `<td class="${dayClass}"><div class="fw-bold fs-6">${day} 
					<button type="button" class="event btn btn-outline-dark btn-sm" style="border:0px;" 
					data-date="${todo_currentYear}-${('0' + (todo_currentMonth + 1)).slice(-2)}-${('0' + day).slice(-2)}" 
					data-plan_month="${todo_currentYear}-${('0' + (todo_currentMonth + 1)).slice(-2)}"> 
					<i class="bi bi-plus"></i> </button></div>`;

				// 휴일 holiday 표시
				holidayForDay.forEach(item => {
					calendarHtml += `<div><span class='badge bg-danger'>${item.comment}</span></div>`;
				});
		

                if (j !== 0 && j !== 6) { // Exclude weekends
                    leaveDataForDay.forEach(item => {
                        calendarHtml += '<div class="leave-info"><span class="badge bg-success">' + item.author + ' (' + item.al_item  + ')</span></div>';
                    });
                }
					
				// console.log('level:' + level);				
				dayData.forEach(item => {
					// 문자열이 10자 이상일 경우 '...' 추가하는 함수
					function truncateText(text, maxLength = 14) {
						if (text.length > maxLength) {
							return text.substring(0, maxLength) + '...';
						}
						return text;
					}

					// 오전 작업
					if (item.title) {
						calendarHtml += '<div class="todo-event event" data-id="' + item.num + '" data-plan_month="' + plan_month + '">';
						// if (level == '1')							
							calendarHtml += '<span class="badge bg-secondary me-1 ">' + item.first_writer + ' </span> ';
						
						// 제목을 10자까지만 표시하고, 넘으면 '...' 추가
						calendarHtml += '<span class="badge bg-dark">전</span> ' + truncateText(item.title) + '</div>';
					}

					// 오후 작업
					if (item.title_after) {
						calendarHtml += '<div class="todo-event event" data-id="' + item.num + '" data-plan_month="' + plan_month + '">';
						// if (level == '1')							
							calendarHtml += '<span class="badge bg-secondary me-1 ">' + item.first_writer + ' </span> ';
						
						// 제목을 10자까지만 표시하고, 넘으면 '...' 추가
						calendarHtml += '<span class="badge bg-primary">후</span> ' + truncateText(item.title_after) + '</div>';
					}
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

$('#todo-calendar-container_work').on('click', '.event', function() {
    let num = $(this).data('id');
    let date = $(this).data('date');
    let plan_month = $(this).data('plan_month');  // Capture the plan_month data
    loadForm(num, date, plan_month);
});

function loadForm(num, date, plan_month) {
    let mode = num == 'undefined' || num == null ? 'insert' : 'update';
    
    // Debugging logs
    // console.log("num:", num);
    // console.log("date:", date);
    // console.log("plan_month:", plan_month);
    // console.log("mode:", mode);

    // Set form values
    $("#mode").val(mode);
    $("#num").val(num);    

    $.ajax({
        type: "POST",
        url: "/todo_work/fetch_modal.php",
        data: { mode: mode, num: num, seldate: date, plan_month: plan_month },
            dataType: "html",
            success: function(response) {                
                document.querySelector(".modal-body .custom-card").innerHTML = response;
                $("#todoModal").show();
							
			  // "상동" 체크박스가 변경될 때 실행
					$('#sameAsMorning').change(function() {
						if ($(this).is(':checked')) {
							// 오전 일정 내용을 오후 일정에 복사
							$('#title_after').val($('#title').val());
						}
					});

					// 오전일정 내용이 변경될 때 "상동"이 체크된 경우, 오후일정에 동일하게 업데이트
					$('#title').on('input', function() {
						if ($('#sameAsMorning').is(':checked')) {
							$('#title_after').val($(this).val());
						}
					});

					// 오전일정 삭제 기능
					$('#removeMorning').click(function() {
						$('#title').val(''); // textarea 내용 삭제
					});

					// 오후일정 삭제 기능
					$('#removeAfternoon').click(function() {
						$('#title_after').val(''); // textarea 내용 삭제
					});			

                $(".todo-close").on("click", function() {
                    $("#todoModal").hide();
                });
				
                $("#closeBtn").on("click", function() {
                    $("#todoModal").hide();
                });

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
				var morningSchedule = $("#title").val().trim();
				var afternoonSchedule = $("#title_after").val().trim();

				// 공백 검증
				// if (morningSchedule === "" &&  afternoonSchedule === "") {
					// Swal.fire({
						// icon: 'error',
						// title: '입력 오류',
						// text: '일정 내용을 입력해 주세요.',
						// confirmButtonText: '확인'
					// });
					// return; // 서버 요청 중지
				// }

				$.ajax({
					url: "/todo_work/process.php",
					type: "post",
					data: formData,
					success: function(response) {
						// console.log(response);
						Toastify({
							text: "저장완료",
							duration: 3000,
							close: true,
							gravity: "top",
							position: "center",
							backgroundColor: "#4fbe87",
						}).showToast();
						$("#todoModal").hide();
						todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear); // 변경된 데이터만 다시 로드
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
                                url: "/todo_work/process.php",
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
                                    todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear); // 변경된 데이터만 다시 로드
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

    $('#todo-prev-month_work').click(function() {
        todo_currentMonth--;
        if (todo_currentMonth < 0) {
            todo_currentMonth = 11;
            todo_currentYear--;
        }
        todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear);
    });

    $('#todo-next-month_work').click(function() {
        todo_currentMonth++;
        if (todo_currentMonth > 11) {
            todo_currentMonth = 0;
            todo_currentYear++;
        }
        todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear);
    });

    $('#todo-current-month_work').click(function() {
        todo_currentMonth = new Date().getMonth();
        todo_currentYear = new Date().getFullYear();
        todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear);
    });

    todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear);
    
});

