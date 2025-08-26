$(document).ready(function() {
    let todo_currentMonth = new Date().getMonth();
    let todo_currentYear = new Date().getFullYear();

    function todo_fetchCalendarData(month, year) {
        $.ajax({
            url: "/todo/fetch_todo.php",
            type: "post",
            data: { month: month + 1, year: year },
            dataType: "json",
            success: function(response) {
				// console.log(response.as_data);
				let selectedFilter = $('input[name="filter"]:checked').attr('id');				
				 // console.log(selectedFilter);
                let calendarHtml = todo_generateCalendarHtml(response.todo_data, response.leave_data, selectedFilter, response.holiday_data, response.work_data, response.as_data, response.meeting_data);		
                $('#todo-calendar-container').html(calendarHtml);
					var showTodoView = getCookie("showTodoView");
					var todoCalendarContainer = $("#todo-list");
					if (showTodoView === "show") {
						todoCalendarContainer.css("display", "block");
					} else {
						todoCalendarContainer.css("display", "none");
					}			
            },
            error: function() {
                console.log('Failed to fetch data');
            }
        });
    }


function todo_generateCalendarHtml(todoData, leaveData, filter, holidayData, workData, asData, meetingData) {
        console.log(meetingData);
        const daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];
        let date = new Date(todo_currentYear, todo_currentMonth, 1);
        let firstDay = date.getDay();
        let lastDate = new Date(todo_currentYear, todo_currentMonth + 1, 0).getDate();
        let today = new Date();

        let todayYear = today.getFullYear();
        let todayMonth = today.getMonth();
        let todayDate = today.getDate();

	    let calendarHtml = '';
	    calendarHtml += '<div class="table-responsive">';
	    calendarHtml += '<table id="todo-list" class="table">';
        calendarHtml += '<thead class="table-info text-start"><tr>';
		daysOfWeek.forEach(day => {
			calendarHtml += '<th class="fs-6 text-start" style="width: calc(100% / ' + daysOfWeek.length + ');">' + day + '</th>';
		});
        calendarHtml += '</tr></thead><tbody class="text-start" >';

        let day = 1;
        for (let i = 0; i < 6; i++) {
            calendarHtml += '<tr>';
            for (let j = 0; j < 7; j++) {
                if (i === 0 && j < firstDay) {
                    calendarHtml += '<td></td>';
                } else if (day > lastDate) {
                    calendarHtml += '<td></td>';
                } else {					
                    let currentDate = new Date(todo_currentYear, todo_currentMonth, day);					
                    currentDate.setHours(0, 0, 0, 0);  // 시간을 0으로 설정하여 날짜만 비교

                    let dayClass = (j === 0 || j === 6) ? 'red-day' : '';

                    // 오늘 날짜인지 확인
                    if (currentDate.getFullYear() === todayYear && currentDate.getMonth() === todayMonth && currentDate.getDate() === todayDate) {
                        dayClass += ' today-bg';
                    }

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
					
                    calendarHtml += `<td  class="${dayClass}"><div class="fw-bold fs-6">${day} <button type="button" class="event btn btn-outline-dark btn-sm" style="border:0px;"  data-date="${todo_currentYear}-${('0' + (todo_currentMonth + 1)).slice(-2)}-${('0' + day).slice(-2)}" > <i class="bi bi-plus"></i> </button> </div>`;

                    // holiday 표시
                    holidayForDay.forEach(item => {
                        calendarHtml += `<div><span class='badge bg-danger'>${item.comment}</span></div>`;
                    });									

                    // 주말 제외
                    if (j !== 0 && j !== 6) {
                        let leaveDataForDay = leaveData.filter(item => {
                            let leaveStart = new Date(item.al_askdatefrom);
                            let leaveEnd = new Date(item.al_askdateto);

                            leaveStart.setHours(0, 0, 0, 0);
                            leaveEnd.setHours(0, 0, 0, 0);
                            currentDate.setHours(0, 0, 0, 0);

                            return currentDate >= leaveStart && currentDate <= leaveEnd;
                        });
						if (filter === 'filter_all' || filter === 'filter_al' )
							{
								leaveDataForDay.forEach(item => {
									calendarHtml += '<div class="leave-info"><span class="fw-bold text-dark">' + item.author + ' (' + item.al_item + ':' + item.al_content + ')</span></div>';
								});
							}
                    }

                    // 기존 todo_data 표시
                    let dayData = todoData.filter(item => {
                        let orderDate = new Date(item.orderdate);
                        orderDate.setHours(0, 0, 0, 0); // 시간을 0으로 설정
                        return orderDate.getDate() === day && orderDate.getMonth() === todo_currentMonth && orderDate.getFullYear() === todo_currentYear;
                    });
                    // 기존 todo_data 표시 해야 할일
                    if (filter === 'filter_all' || filter === 'filter_etc' )
						{
							dayData.forEach(item => {
								if(item.towhom === '') item.towhom = '';
								else item.towhom = item.towhom;

								switch (item.work_status) {
									case '작성':
										item.work_status = '';
										break;
									case '완료':
										item.work_status = '<span class="badge bg-danger">' + item.work_status + '</span>';
										break;
								}
								calendarHtml += '<div class="todo-event event" data-id="' + item.num + '"><span class="badge bg-primary">' + item.towhom + '</span> ' + item.title + item.work_status + '</div>';
							});
						}
							
                    // 기존 work_data 표시
                    let work_dayData = workData.filter(item => {
                        let orderDate = new Date(item.orderdate);
                        orderDate.setHours(0, 0, 0, 0); // 시간을 0으로 설정
                        return orderDate.getDate() === day && orderDate.getMonth() === todo_currentMonth && orderDate.getFullYear() === todo_currentYear;
                    });					

                    work_dayData.forEach(item => {                       
                    if (filter === 'filter_all' || filter === 'filter_workrecord' )
						{
							// 문자열이 10자 이상일 경우 '...' 추가하는 함수
							function truncateText(text, maxLength = 6) {
								if (text.length > maxLength) {
									return text.substring(0, maxLength) + '...';
								}
								return text;
							}
							let plan_month = item.orderdate;
							// 오전 작업
							if (item.title) {
								calendarHtml += '<div class="todo-event"  style="border: 1px dashed gray;" data-id="' + item.num + '" data-plan_month="' + plan_month + '"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + item.title + '" >';
								calendarHtml += '<span class="badge bg-secondary me-1 ">' + item.first_writer + ' </span> ';
								
								// 제목을 10자까지만 표시하고, 넘으면 '...' 추가
								calendarHtml += '<span class="badge bg-dark">전</span> ' + truncateText(item.title) + '</div>';
							}

							// 오후 작업
							if (item.title_after) {
								calendarHtml += '<div class="todo-event"  style="border: 1px dashed gray;" data-id="' + item.num + '" data-plan_month="' + plan_month + '"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + item.title_after + '" >';
								calendarHtml += '<span class="badge bg-secondary me-1 ">' + item.first_writer + ' </span> ';
								
								// 제목을 10자까지만 표시하고, 넘으면 '...' 추가
								calendarHtml += '<span class="badge bg-primary">후</span> ' + truncateText(item.title_after) + '</div>';
							}	
						}							
                    });

                    // 기존 asdata 표시
                    let as_dayData = asData.filter(item => {
                        let orderDate = new Date(item.asproday);
                        orderDate.setHours(0, 0, 0, 0); // 시간을 0으로 설정
                        return orderDate.getDate() === day && orderDate.getMonth() === todo_currentMonth && orderDate.getFullYear() === todo_currentYear;
                    });					

                    as_dayData.forEach(item => {                       
                    if (filter === 'filter_all' || filter === 'filter_as' )
						{
							// 문자열이 10자 이상일 경우 '...' 추가하는 함수
							function truncateText(text, maxLength = 8) {
								if (text.length > maxLength) {
									return text.substring(0, maxLength) + '..';
								}
								return text;
							}
							let plan_month = item.asproday;
							// 오전 작업
							if (item.address) {
								calendarHtml += '<div class="todo-event as-event"  style="border: 1px dashed orange;" data-id="' + item.num + '" data-plan_month="' + plan_month + '"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + item.address + '" >';
								calendarHtml += '<span class="badge bg-secondary">' + item.asman + '</span> ';
								
								// 제목을 10자까지만 표시하고, 넘으면 '...' 추가
								calendarHtml += '<span class="badge bg-warning">AS</span>' + truncateText(item.address) + '</div>';
							}
						}							
                    });

                    // 회의록 표시
                    let meeting_dayData = meetingData.filter(item => {
                        let orderDate = new Date(item.registration_date);
                        orderDate.setHours(0, 0, 0, 0); // 시간을 0으로 설정
                        return orderDate.getDate() === day && orderDate.getMonth() === todo_currentMonth && orderDate.getFullYear() === todo_currentYear;
                    });					

                    //console.log(meeting_dayData);
                    meeting_dayData.forEach(item => {                       
                    if (filter === 'filter_all' || filter === 'filter_meeting' )
						{							
							let plan_month = item.registration_date;
                            let message = '회의록 보기';							
								calendarHtml += '<div class="meeting-event" data-id="' + item.num + '" data-plan_month="' + plan_month + '"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + message + '" >';
								calendarHtml += '<span class="badge bg-success">' + message + '</span> </div>';								
						}							
                    });

                    calendarHtml += '</td>';
                    day++;
                }
            }
            calendarHtml += '</tr>';
        }

        calendarHtml += '</tbody></table></div>';

        let startDate = new Date(todo_currentYear, todo_currentMonth, 1);
        let endDate = new Date(todo_currentYear, todo_currentMonth, lastDate);
        $('#todo-current-period').text(todo_currentYear + '/' + ('0' + (todo_currentMonth + 1)).slice(-2));

        return calendarHtml;
    }
    
    $('#todo-calendar-container').on('click', '.event', function() {
        let num = $(this).data('id');
        let date = $(this).data('date');
        loadForm(num, date);
    });

    // AS 클릭처리
    $('#todo-calendar-container').on('click', '.as-event', function() {
        let num = $(this).data('id');        
        popupCenter('/as/write.php?tablename=as&mode=view&num=' + num, 'AS 클릭처리', 1400, 900);
    });

    // 회의록 클릭처리
    $('#todo-calendar-container').on('click', '.meeting-event', function() {
        let num = $(this).data('id');
        let date = $(this).data('date');
        popupCenter('/meeting/view.php?tablename=meeting&mode=view&num=' + num, '회의록', 1000, 800);
    });

    function loadForm(num, date) {
        let mode = num == 'undefined' ||  num ==  null ? 'insert' : 'update';
        // console.log(date);
         // console.log(num);
         // console.log(mode);
		 $("#mode").val(mode);
		 $("#num").val(num);
        $.ajax({
            type: "POST",
            url: "/todo/fetch_modal.php",
            data: { mode: mode, num: num, seldate : date },
            dataType: "html",
            success: function(response) {                
                document.querySelector(".modal-body .custom-card").innerHTML = response;
                $("#todoModal").show();

                // Bootstrap's data-dismiss="modal" handles modal closing automatically
                // Removed custom click handlers to prevent conflicts

                $("#closeBtn").off("click").on("click", function() {
                    $("#todoModal").hide();
                });
                $(".todo-close").off("click").on("click", function() {
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

                // 저장 버튼
                $("#saveBtn").on("click", function() {
                    var formData = $("#board_form").serialize();

                    $.ajax({
                        url: "/todo/process.php",
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
                            todo_fetchCalendarData(todo_currentMonth, todo_currentYear); // 변경된 데이터만 다시 로드
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

                    if (user_name !== first_writer) {
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
                                url: "/todo/process.php",
                                type: "post",
                                data: formData,
                                success: function(response) {
                                    Toastify({
                                        text: "파일 삭제완료",
                                        duration: 2000,
                                        close: true,
                                        gravity: "top",
                                        position: "center",
                                        style: {
                                            background: "linear-gradient(to right, #00b09b, #96c93d)"
                                        },
                                    }).showToast();

                                    $("#todoModal").hide();
                                    todo_fetchCalendarData(todo_currentMonth, todo_currentYear); // 변경된 데이터만 다시 로드
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
	
   // 초기 라디오 버튼 상태 설정 및 필터 변경 이벤트
    function initializeRadioButtons() {
        let selectedFilter = getCookie("todoFilter") || 'filter_all';
        $('#' + selectedFilter).prop('checked', true);
        todo_fetchCalendarData(todo_currentMonth, todo_currentYear);
    }

    $('input[name="filter"]').on('change', function() {
        let selectedFilter = $('input[name="filter"]:checked').attr('id');
        setCookie("todoFilter", selectedFilter, 10);
        todo_fetchCalendarData(todo_currentMonth, todo_currentYear);
    });	

    $('#todo-prev-month').click(function() {
        todo_currentMonth--;
        if (todo_currentMonth < 0) {
            todo_currentMonth = 11;
            todo_currentYear--;
        }
        todo_fetchCalendarData(todo_currentMonth, todo_currentYear);
    });

    $('#todo-next-month').click(function() {
        todo_currentMonth++;
        if (todo_currentMonth > 11) {
            todo_currentMonth = 0;
            todo_currentYear++;
        }
        todo_fetchCalendarData(todo_currentMonth, todo_currentYear);
    });

    $('#todo-current-month').click(function() {
        todo_currentMonth = new Date().getMonth();
        todo_currentYear = new Date().getFullYear();
        todo_fetchCalendarData(todo_currentMonth, todo_currentYear);
    });
	
/* 부트스트랩 툴팁 */

  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });  
	
	
    // 페이지 로드 시 초기화
    initializeRadioButtons();    
    
});

