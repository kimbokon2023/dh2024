
<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">

	<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
	<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
	<input type="hidden" id="user_name" name="user_name" value="<?= isset($user_name) ? $user_name : '' ?>">
    <input type="hidden" id="level" name="level" value="<?= isset($level) ? $level : '' ?>">
	<input type="hidden" id="tablename" name="tablename" value="employee_tasks">
	<input type="hidden" id="tasksCount" name="tasksCount" value="0">
	<input type="hidden" id="plan_month" name="plan_month" value="<?= date('Y-m') ?>">

<!-- todo모달 컨테이너 -->
<div class="container-fluid">
	<!-- Modal -->
	<div id="taskModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="taskModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<span class="modal-title">오늘의 할일 #<span id="task_num"></span></span>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" style="max-height: 700px; overflow-y: auto;">
					<div class="custom-card"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- 구글 스타일 검색창 UI 및 검색 기능 추가 -->
	<div class="row mt-3 mb-3 justify-content-center">
		<div class="col-3 col-md-3">
			<form id="todo-search-form" > 
				<div class="d-flex align-items-center shadow rounded-pill px-3 py-2" style="background: #fff; border: 1px solid #e3e3e3;">
					<i class="bi bi-search fs-4 text-secondary me-2"></i>
					<input type="text" id="todo-search-input" class="form-control border-0 shadow-none bg-transparent" placeholder="할일 검색 (이름, 내용 등 입력 후 엔터)" style="font-size: 1.1rem; outline: none; box-shadow: none;" autocomplete="off">
					<button type="button" id="todoSearchBtn" class="btn btn-link text-secondary ms-2 p-0" style="text-decoration: none;">
						<i class="bi bi-arrow-right-circle fs-4"></i>
					</button>
				</div>
			</form>
		</div>
	</div>
	<div id="todo-search-result" class="mb-3"></div>

</div>

<!-- todo Calendar -->
<?php if($chkMobile==false) { ?>
    <div class="container-fluid">     
<?php } else { ?>
    <div class="container-fluid">      
<?php } ?>  
	<div class="card mt-1">
        <div class="card-body">
   		 <div class="row mt-3">
        <!-- Calendar Controls -->         
        <div class="col-sm-3">
		  <div class="d-flex justify-content-start align-items-center mt-3 ">            
			<h5> <오늘의 할일> </h5> 
		  </div>
        </div>
        <div class="col-sm-6">
            <div class="d-flex justify-content-center align-items-center mb-2">
                <button type="button" id="task-prev-month_work" class="btn btn-info btn-sm me-2"> <i class="bi bi-arrow-left"></i>  </button>
                 <span id="task-current-period" class="text-dark fs-6 me-2"></span>
                <button  type="button" id="task-next-month_work" class="btn btn-info btn-sm me-2"> <i class="bi bi-arrow-right"></i> </button>
                <button  type="button" id="task-current-month_work" class="btn btn-outline-info fw-bold btn-sm me-5"> <?php echo date("m",time()); ?> 월</button>                
				<button type="button" class="btn btn-dark btn-sm me-1" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      
            </div>        
        </div>       
        <div class="col-sm-3"> </div>
        </div>        
        <script>
        // 검색 함수: 기간 무관하게 오늘의 할일 전체에서 검색
        function searchTodoTasks(keyword) {
            if (!keyword || keyword.trim() === "") {
                $('#todo-search-result').html('');
                return;
            }
			console.log(keyword);
            // AJAX로 서버에서 전체 할일 데이터 가져오기 (기간 무관)
            $.ajax({
                url: '/todo_task/search_all_tasks.php',
                type: 'POST',
                data: { keyword: keyword },
                dataType: 'json',
                success: function(res) {
					console.log(res);
					console.log(res.length);
                    if (res && res.length > 0) {
                        let html = '<div class="d-flex justify-content-center"><div class="card shadow-sm w-50"><div class="card-body">';
                        html += `<h6 class="mb-3"><span class="text-primary fw-bold">"${keyword}"</span> 검색 결과 (${res.length}건)</h6>`;
                        html += '<ul class="list-group list-group-flush">';
                        res.forEach(function(item) {
                            // item: {task_date, employee_name, department, tasks(json), num}
                            let tasks = [];
                            try {
                                tasks = JSON.parse(item.tasks);
                            } catch(e) {}
                            // task_content, is_completed, title, content 등 다양한 필드명 대응
                            let matchedTasks = tasks.filter(t => {
                                // content, title, task_content 모두 검사
                                let content = t.content || t.task_content || '';
                                let title = t.title || '';
                                return (
                                    (content && content.toLowerCase().includes(keyword.toLowerCase())) ||
                                    (title && title.toLowerCase().includes(keyword.toLowerCase()))
                                );
                            });
                            // 매칭되는 할일이 없더라도, 전체 자료를 보여주기 위해 아래 조건문을 주석처리 또는 제거
                            // if (matchedTasks.length === 0) return;
                            html += `<li class="list-group-item py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">${item.employee_name ? item.employee_name : ''}</span>
                                    <span class="badge bg-light text-dark border">${item.task_date ? item.task_date : ''}</span>
                                </div>
                                <div class="text-muted mb-1" style="font-size:0.97em;">
                                    ${item.department ? '<i class="bi bi-people"></i> ' + item.department : ''}
                                </div>
                                <ul class="mt-2 mb-0 ps-3" style="font-size:0.98em;">`;
                            // 매칭된 할일이 있으면 강조해서 보여주고, 없으면 전체 tasks를 보여줌
                            let showTasks = matchedTasks.length > 0 ? matchedTasks : tasks;
                            showTasks.forEach(function(t) {
                                let content = t.content || t.task_content || '';
                                let title = t.title || '';
                                let isCompleted = (t.is_completed === true || t.is_completed === 1) ? 1 : 0;
                                html += `<li>
                                    <span class="${isCompleted ? 'text-success text-decoration-line-through' : ''}">
                                        ${title ? '<b>' + title + '</b>: ' : ''}${content}
                                    </span>
                                    ${isCompleted ? '<span class="badge bg-success ms-2">완료</span>' : ''}
                                </li>`;
                            });
                            html += `</ul>
                                <div class="text-end mt-1">
                                  <!--  <button class="btn btn-outline-primary btn-sm" data-id="${item.num}" data-date="${item.task_date}" onclick="openTaskModal(${item.num});return false;">상세보기</button> -->                                
                                </div>
                            </li>`;
                        });
                        html += '</ul></div></div></div>';
                        $('#todo-search-result').html(html);
                    } else {
                        $('#todo-search-result').html('<div class="alert alert-warning mb-0">검색 결과가 없습니다.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = '<b>검색 중 오류가 발생했습니다.</b><br>';
                    errorMsg += '<ul class="mb-0" style="font-size:0.97em;">';
                    errorMsg += `<li><b>HTTP 상태:</b> ${xhr.status} (${xhr.statusText})</li>`;
                    errorMsg += `<li><b>AJAX status:</b> ${status}</li>`;
                    errorMsg += `<li><b>에러 메시지:</b> ${error ? error : '없음'}</li>`;
                    if (xhr && xhr.responseText) {
                        let detail = xhr.responseText;
                        // JSON이면 파싱해서 예쁘게 보여주기
                        try {
                            const json = JSON.parse(detail);
                            if (json.message) {
                                detail = json.message;
                            } else {
                                detail = JSON.stringify(json, null, 2);
                            }
                        } catch(e) {}
                        errorMsg += `<li><b>서버 응답 상세:</b> <pre class="text-danger mb-0" style="white-space:pre-wrap;">${detail}</pre></li>`;
                    }
                    errorMsg += '</ul>';
                    $('#todo-search-result').html('<div class="alert alert-danger mb-0">' + errorMsg + '</div>');
                    console.error('검색 에러 상세:', xhr, status, error, xhr ? xhr.responseText : '');
                }
            });
        }

        // 엔터키 또는 버튼 클릭 시 검색
        $('#todoSearchBtn').on('click', function() {
            const keyword = $('#todo-search-input').val();
            searchTodoTasks(keyword);
        });
        $('#todo-search-input').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const keyword = $('#todo-search-input').val();
                searchTodoTasks(keyword);
            }
        });

        // 상세보기 모달 열기 함수 (이미 있는 taskModal 활용)
        function openTaskModal(taskNum) {
           // 화면에 해당되는 사람의 모달창 나오는 코드 필요함
		   console.log(taskNum);
		    let num = $(this).data('id');
			let date = $(this).data('date');
			let plan_month = '';
		    if (date) {
				let d = new Date(date);
				let year = d.getFullYear();
				let month = ('0' + (d.getMonth() + 1)).slice(-2);
				plan_month = year + '-' + month;
		    }
			console.log(num, date, plan_month);
			// loadTaskForm(num, date, plan_month);
        }
        </script>
        <style>
        /* 구글 스타일 검색창 */
        #todo-search-form {
            transition: box-shadow 0.2s;
        }
        #todo-search-form:focus-within {
            box-shadow: 0 2px 8px rgba(60, 120, 240, 0.10);
            border-color: #b3d1ff;
        }
        #todo-search-input::placeholder {
            color: #b0b0b0;
            font-style: italic;
        }
        #todo-search-input:focus {
            background: #f8faff;
        }
        </style>
        
        <div id="task-calender-task" class="d-flex p-1 justify-content-center"></div>
        <!-- 일자별 할일 목록 -->
        <div id="task-list" class="d-flex p-1 justify-content-center"></div>
    </div>
</div>
</div>


</form>

<!-- 스크립트를 body 끝에 배치하여 로딩 순서 보장 -->
<script>   
// 페이지 로딩
$(document).ready(function() {
	var loader = document.getElementById('loadingOverlay');
	if (loader) {
		loader.style.display = 'none';
	}
});

$(document).ready(function(){
	saveLogData('오늘의 할일 달력'); 

	// 페이지 로드 시 '전체' 함수 자동 실행
	setTimeout(function() {
		if (typeof load_member_work === 'function') {
			load_member_work('전체');
		} else {
			console.error('load_member_work 함수가 정의되지 않았습니다.');
		}
	}, 100);
	

});

// 라디오버튼 클릭시 함수 실행
$(document).on('change', '.member-radio', function() {
	var memberName = $(this).data('member');
	if (typeof load_member_work === 'function') {
		load_member_work(memberName);
	}
});

// 전역 변수로 선언
let todo_currentMonth = new Date().getMonth();
let todo_currentYear = new Date().getFullYear();

// 동적으로 선택한 사용자의 작업일정을 불러오는 함수 (전역 함수로 선언)
function load_member_work(memberName) {
    todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear, memberName);    
}

// 캘린더 데이터를 가져오는 함수 (전역 함수로 선언)
function todo_fetchCalendarData_work(month, year, search_member = null) {
    if(search_member && search_member !== '전체') {
        search_member = search_member.replace(/\s/g, '');
        url = "/todo_task/fetch_todo.php?member=" + search_member;
    } else {
        url = "/todo_task/fetch_todo.php";
    }

    console.log(url);

	$.ajax({
		url: url,  // 새로운 할 일 데이터를 가져오는 파일
		type: "post",
		data: { month: month + 1, year: year },  // 서버에서 처리할 수 있도록 월+1
		dataType: "json",
		success: function(response) {
			// 캘린더 데이터를 받아서 화면에 렌더링
            console.log(response);
			let calendarHtml = todo_generateCalendarHtml_work(response.todo_data, response.leave_data, response.holiday_data, response.level, response.plan_month);
			if ($('#task-calender-task').length) {
				$('#task-calender-task').html(calendarHtml);
				$('#task-calender-task').css("display", "block"); // 항상 보이게 설정
			}								
		},
		error: function(xhr, status, error) {
			console.error('데이터 가져오기 실패');
			console.error('상태:', status);
			console.error('오류:', error);
			console.error('응답:', xhr.responseText);
			if (xhr.status) {
				console.error('HTTP 상태 코드:', xhr.status);
			}
			if (xhr.statusText) {
				console.error('상태 텍스트:', xhr.statusText); 
			}
		}
	});
}

// 텍스트 자르기 함수 (전역 함수로 선언)
function truncateText(text, maxLength = 14) {
	if (text.length <= maxLength) {
		return text;
	}
	return text.substring(0, maxLength) + '...';
}

// 캘린더 HTML을 생성하는 함수 (전역 함수로 선언)
function todo_generateCalendarHtml_work(todoData, leaveData, holidayData, level, plan_month) {
    
    const daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];
    let date = new Date(todo_currentYear, todo_currentMonth, 1);
    let firstDay = date.getDay();
    let lastDate = new Date(todo_currentYear, todo_currentMonth + 1, 0).getDate();
    let today = new Date();
    
    let todayYear = today.getFullYear();
    let todayMonth = today.getMonth();
    let todayDate = today.getDate();

    let calendarHtml = '<table id="task-list_work" class="table table-condensed">';
    calendarHtml += '<thead class="table-info text-start"><tr>';
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
                let dayData = todoData.filter(item => new Date(item.task_date).getDate() === day);
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

				calendarHtml += `<td class="${dayClass}"><div class="fw-bold fs-6"> <span class="fs-6 day-number" data-date="${todo_currentYear}-${('0' + (todo_currentMonth + 1)).slice(-2)}-${('0' + day).slice(-2)}">${day}</span> 
					<button type="button" class="event btn btn-outline-dark btn-sm" style="border:0px;" 
					data-date="${todo_currentYear}-${('0' + (todo_currentMonth + 1)).slice(-2)}-${('0' + day).slice(-2)}" 
					data-plan_month="${todo_currentYear}-${('0' + (todo_currentMonth + 1)).slice(-2)}"
					date-num=""
					> 
						<i class="bi bi-plus"></i> 
					</button></div>`;

				// 휴일 holiday 표시
				holidayForDay.forEach(item => {
					calendarHtml += `<div><span class='badge bg-danger'>${item.comment}</span></div>`;
				});
		

                if (j !== 0 && j !== 6) { // Exclude weekends
                    leaveDataForDay.forEach(item => {
                        calendarHtml += '<div class="leave-info"><span class="badge bg-success">' + item.author + ' (' + item.al_item  + ')</span></div>';
                    });
                }				

                 // 오늘의 할일 데이터 표시
                 if (dayData.length > 0) {
                     // 해당 날짜의 요약 데이터 계산
                     let totalTasks = 0;
                     let completedTasks = 0;
                     
                     dayData.forEach(item => {
                         let tasks = [];
                         try {
                             tasks = JSON.parse(item.tasks);
                         } catch (e) {
                             console.error('JSON 파싱 오류:', e);
                             tasks = [];
                         }
                         totalTasks += tasks.length;
                         completedTasks += tasks.filter(task => task.is_completed == 1).length;
                     });
                     
                     let completionRate = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
                     
                     // 요약 badge 추가 (할일이 있는 경우만 표시)
                     if (totalTasks > 0) {
                         calendarHtml += '<div class="d-flex gap-1 mb-2">';
                         calendarHtml += '&nbsp; <span class="badge bg-primary" style="font-size: 10px;">할일: ' + totalTasks + '</span> <span class="badge bg-success" style="font-size: 10px;">완료: ' + completedTasks + '</span> <span class="badge bg-' + (completionRate >= 100 ? 'success' : completionRate >= 50 ? 'warning' : 'secondary') + '" style="font-size: 10px;"> 완료율:' + completionRate + '%</span>';
                         calendarHtml += '</div>';
                     }
                     
                     // 3명씩 표시하기 위해 데이터를 청크로 나눔
                     for(let i = 0; i < dayData.length; i += 3) {
                         calendarHtml += '<div class="d-flex justify-content-between mb-1">';
                         
                         // 3명씩 처리
                         for(let j = i; j < Math.min(i + 3, dayData.length); j++) {
                             let item = dayData[j];
                             let tasks = [];
                             try {
                                 tasks = JSON.parse(item.tasks);
                             } catch (e) {
                                 console.error('JSON 파싱 오류:', e);
                                 tasks = [];
                             }
                             
                             let completedTasks = tasks.filter(task => task.is_completed == 1).length;
                             let totalTasks = tasks.length;
                             let completionRate = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
                             
                             calendarHtml += '<div class="event p-1 border rounded flex-fill mx-1" data-id="' + item.num + '" data-date="' + item.task_date + '" data-plan_month="' + plan_month + '" style="background-color: #f8f9fa; cursor: pointer; font-size: 11px;">';
                             calendarHtml += '<div class="d-flex justify-content-between align-items-center">';
                             calendarHtml += '<span class="fw-bold text-primary">' + item.employee_name + '</span>';
                             calendarHtml += '<span style="font-size: 10px;" class="text-' + (completionRate >= 100 ? 'success' : completionRate >= 50 ? 'warning' : 'secondary') + ' ">' + completionRate + '%</span>';
                             calendarHtml += '</div>';            
                             calendarHtml += '</div>';
                         }
                         
                         calendarHtml += '</div>';
                     }
                 }


                calendarHtml += '</td>';
                day++;
            }
        }
        calendarHtml += '</tr>';
    }

    calendarHtml += '</tbody></table>';

    $('#task-current-period').text(todo_currentYear + '/' + ('0' + (todo_currentMonth + 1)).slice(-2));

    return calendarHtml;
}


document.addEventListener('DOMContentLoaded', function() {
	
	// 두 이벤트 핸들러를 하나로 통합 ('.event'와 '.task-row-clickable' 모두 처리)
	$(document).on('click', '#task-calender-task .event, #task-list .task-row-clickable', function() {
		// .event와 .task-row-clickable의 data 속성명이 다르므로 우선순위로 처리
		let num = $(this).data('id') !== undefined ? $(this).data('id') : $(this).data('task-num');
		let date = $(this).data('date');
		let plan_month = $(this).data('plan_month');
		loadTaskForm(num, date, plan_month);
	});

    // 일자별 할일 목록 클릭 시 할일 목록 표시
    $('#task-calender-task').on('click', '.day-number', function() {
        let date = $(this).data('date');
        // 할일 목록을 초기화하기 전에 기존 내용 저장
        let prevContent = $('#task-list').html();
        $('#task-list').html('로딩중...');
        console.log(date);
        // fetch_task_list.php 로 해당요일의 정보를 가져온다.
		$.ajax({
			type: "POST",
			url: "/todo_task/fetch_task_list.php",
			data: { sendDate : date },
			dataType: "html",
			success: function(response) {                                              
                $('#task-list').html(response);                
            },
            error: function(xhr, status, error) {
                console.error('데이터 가져오기 실패');
                console.error('상태:', status);
                console.error('오류:', error);
                console.error('응답:', xhr.responseText);
            }
        });

    });

	function loadTaskForm(num, date, plan_month) {
		let mode = num == 'undefined' || num == null ? 'insert' : 'modify';
		
		//Debugging logs
		console.log("num:", num);
		console.log("date:", date);
		console.log("plan_month:", plan_month);
		console.log("mode:", mode);

		// num이 undefined나 null일 때, 해당 날짜에 현재 사용자의 데이터가 있는지 확인
		if (num == 'undefined' || num == null) {
			// 먼저 해당 날짜에 기존 데이터가 있는지 확인
			$.ajax({
				type: "POST",
				url: "/todo_task/fetch_modal.php",
				data: { 
					mode: 'check_existing', 
					seldate: date, 
					plan_month: plan_month 
				},
				dataType: "json",
				success: function(checkResponse) {
					console.log("기존 데이터 확인 응답:", checkResponse);
					
					if (checkResponse.exists && checkResponse.num) {
						// 기존 데이터가 있으면 수정 모드로 변경
						num = checkResponse.num;
						mode = 'modify';
						console.log("기존 데이터 발견, 수정 모드로 변경. num:", num);
					}
					
					// 실제 폼 로드 실행
					proceedWithFormLoad(mode, num, date, plan_month);
				},
				error: function(jqxhr, status, error) {
					console.log("기존 데이터 확인 중 오류:", status, error);
					// 오류 발생 시 원래 모드로 진행
					proceedWithFormLoad(mode, num, date, plan_month);
				}
			});
		} else {
			// num이 있으면 바로 폼 로드 실행
			proceedWithFormLoad(mode, num, date, plan_month);
		}
	}

	function proceedWithFormLoad(mode, num, date, plan_month) {
		// Set form values
		$("#mode").val(mode);
		$("#num").val(num);    
		// 모달창 상단에 고유번호 보여주기
		$("#task_num").text(num);

		// $로 된 php코드를 가져오기    
		$("#plan_month").val(plan_month);

		$.ajax({
			type: "POST",
			url: "/todo_task/fetch_modal.php",
			data: { mode: mode, num: num, seldate: date, plan_month: plan_month },
			dataType: "html",
			success: function(response) {                
				document.querySelector(".modal-body .custom-card").innerHTML = response;

				// Bootstrap 5 modal show method
				const modal = new bootstrap.Modal(document.getElementById('taskModal'));
				modal.show();


				// 전역 변수
				let taskRowCount = '<?php echo isset($tasksCount) ? $tasksCount : 0; ?>';
				taskRowCount = typeof tasksCount !== 'undefined' ? Number(tasksCount) : 0;    
				
				// 통계 업데이트 함수
				window.updateStatistics = function() {
					let totalTasks = 0;
					let completedTasks = 0;
					
					$('.task-row').each(function() {
						const taskContent = $(this).find('.task-content-input').val();
						const isCompleted = $(this).find('.task-checkbox').is(':checked');
						
						if (taskContent.trim()) {
							totalTasks++;
							if (isCompleted) {
								completedTasks++;
							}
						}
					});
					
					const pendingTasks = totalTasks - completedTasks;
					const completionRate = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
					
					$('#totalTasks').text(totalTasks);
					$('#completedTasks').text(completedTasks);
					$('#pendingTasks').text(pendingTasks);
					$('#completionRate').text(completionRate + '%');
				};
				
				// 초기 통계 업데이트
				updateStatistics();
				
				// 할일 내용 입력 시 통계 업데이트
				$(document).on('input', '.task-content-input', function() {
					updateStatistics();
				});
				
				// 체크박스 변경 시 통계 업데이트
				$(document).on('change', '.task-checkbox', function() {
					updateStatistics();
				});
				
				// 전역 함수로 정의
				window.updateCompletionDate = function(checkbox) {
					const row = $(checkbox).closest('.task-row');
					const completionDateInput = row.find('.completion-date-input');
					
					if (checkbox.checked) {
						// 체크된 경우 오늘 날짜로 설정
						const today = new Date().toISOString().split('T')[0];
						completionDateInput.val(today);
						completionDateInput.prop('readonly', false);
					} else {
						// 체크 해제된 경우 날짜 초기화
						completionDateInput.val('');
						completionDateInput.prop('readonly', true);
					}
				};

				window.addRowAfter = function(rowIndex) {
					const newRowIndex = taskRowCount;
					const newRow = `
						<tr class="task-row" data-row="${newRowIndex}">
							<td class="text-center align-middle">
								<div class="d-flex align-items-center justify-content-center">
									<div class="btn-group btn-group-sm" role="group" style="gap: 1px;">
										<button type="button" class="btn btn-outline-primary btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="addRowAfter(${newRowIndex})" title="아래에 행 추가">
											<i class="bi bi-plus"></i>
										</button>
										<button type="button" class="btn btn-outline-success btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="copyRow(${newRowIndex})" title="행 복사">
											<i class="bi bi-files"></i>
										</button>
										<button type="button" class="btn btn-outline-danger btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="deleteRow(${newRowIndex})" title="행 삭제">
											<i class="bi bi-dash"></i>
										</button>
									</div>
								</div>
							</td>
							<td class="text-center align-middle">
								<input type="text" name="tasks[${newRowIndex}][task_content]" class="form-control form-control-sm task-content-input" placeholder="할일을 입력하세요">
							</td>
							<td class="text-center align-middle">
								<div class="form-check d-flex justify-content-center">
									<input class="form-check-input task-checkbox" type="checkbox" name="tasks[${newRowIndex}][is_completed]" value="1" onchange="updateCompletionDate(this)">
								</div>
							</td>
							<td class="text-center align-middle">
								<input type="date" name="tasks[${newRowIndex}][completion_date]" class="form-control form-control-sm completion-date-input" readonly>
							</td>
							<td class="text-center align-middle">
								<span class="badge bg-secondary elapsed-days-display" data-original-date="${$('#task_date').val() || new Date().toISOString().split('T')[0]}">-</span>
							</td>
						</tr>
					`;
					
					// 지정된 행 뒤에 새 행 삽입
					const targetRow = $(`.task-row[data-row="${rowIndex}"]`);
					targetRow.after(newRow);
					
					taskRowCount++;
					updateRowNumbers();
					updateStatistics();
				};

				window.copyRow = function(rowIndex) {
					const sourceRow = $(`.task-row[data-row="${rowIndex}"]`);
					const newRowIndex = taskRowCount;
					
					// 소스 행의 데이터 복사
					const taskContent = sourceRow.find('.task-content-input').val();
					const isCompleted = sourceRow.find('.task-checkbox').is(':checked');
					const completionDate = sourceRow.find('.completion-date-input').val();
					
					const newRow = `
						<tr class="task-row" data-row="${newRowIndex}">
							<td class="text-center align-middle">
								<div class="d-flex align-items-center justify-content-center">
									<div class="btn-group btn-group-sm" role="group" style="gap: 1px;">
										<button type="button" class="btn btn-outline-primary btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="addRowAfter(${newRowIndex})" title="아래에 행 추가">
											<i class="bi bi-plus"></i>
										</button>
										<button type="button" class="btn btn-outline-success btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="copyRow(${newRowIndex})" title="행 복사">
											<i class="bi bi-files"></i>
										</button>
										<button type="button" class="btn btn-outline-danger btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="deleteRow(${newRowIndex})" title="행 삭제">
											<i class="bi bi-dash"></i>
										</button>
									</div>
								</div>
							</td>
							<td class="text-center align-middle">
								<input type="text" name="tasks[${newRowIndex}][task_content]" class="form-control form-control-sm task-content-input" placeholder="할일을 입력하세요" value="${taskContent}">
							</td>
							<td class="text-center align-middle">
								<div class="form-check d-flex justify-content-center">
									<input class="form-check-input task-checkbox" type="checkbox" name="tasks[${newRowIndex}][is_completed]" value="1" ${isCompleted ? 'checked' : ''} onchange="updateCompletionDate(this)">
								</div>
							</td>
							<td class="text-center align-middle">
								<input type="date" name="tasks[${newRowIndex}][completion_date]" class="form-control form-control-sm completion-date-input" value="${completionDate}" ${isCompleted ? '' : 'readonly'}>
							</td>
							<td class="text-center align-middle">
								<span class="badge bg-secondary elapsed-days-display" data-original-date="${$('#task_date').val() || new Date().toISOString().split('T')[0]}">-</span>
							</td>
						</tr>
					`;
					
					// 소스 행 뒤에 새 행 삽입
					sourceRow.after(newRow);
					
					taskRowCount++;
					updateRowNumbers();
					updateStatistics();
				};

				window.deleteRow = function(rowIndex) {
					const row = $(`.task-row[data-row="${rowIndex}"]`);
					if ($('.task-row').length > 1) {
						row.remove();
						updateRowNumbers();
						updateStatistics();
					} else {
						alert('최소 1개의 행은 유지해야 합니다.');
					}
				};

				window.updateRowNumbers = function() {
					$('.task-row').each(function(index) {
						$(this).attr('data-row', index);
						
						// 버튼의 onclick 속성 업데이트
						const buttons = $(this).find('.btn-group button');
						buttons.eq(0).attr('onclick', `addRowAfter(${index})`);
						buttons.eq(1).attr('onclick', `copyRow(${index})`);
						buttons.eq(2).attr('onclick', `deleteRow(${index})`);
					});
				};

				// 모달이 완전히 닫힌 후 실행될 코드 (전역적으로 한 번만 등록)
				if (!window.modalEventHandlersInitialized) {
					$("#taskModal").on('hidden.bs.modal', function() {
						$(this).find('form').trigger('reset'); // 폼 초기화
						$(this).find('.modal-body .custom-card').empty(); // 모달 내용 비우기
					});
					
					window.modalEventHandlersInitialized = true;
				}

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

				// 개별일정 저장 버튼
				$("#saveBtn").off("click").on("click", function() {	
					try {
						// JSON 데이터 생성
						const tasks = [];
						$('.task-row').each(function() {
							const taskContent = $(this).find('.task-content-input').val();
							const isCompleted = $(this).find('.task-checkbox').is(':checked');
							const completionDate = $(this).find('.completion-date-input').val();
							const originalDate = $(this).find('.elapsed-days-display').data('original-date') || '';
							const uniqueId = $(this).find('input[name*="[unique_id]"]').val() || '';
							const isPending = $(this).find('input[name*="[is_pending]"]').val() === '1';
							const dateKey = $(this).find('input[name*="[date_key]"]').val() || '';
							const elapsedDaysText = $(this).find('.elapsed-days-display').text();
							
							// elapsed_days를 숫자로 변환
							let elapsedDays = 0;
							if (elapsedDaysText === '예정' || elapsedDaysText === '-') {
								elapsedDays = 0;
							} else {
								// "X일" 형태에서 숫자만 추출
								const match = elapsedDaysText.match(/(\d+)일/);
								if (match) {
									elapsedDays = parseInt(match[1]);
								} else {
									elapsedDays = 0;
								}
							}

							if (taskContent.trim()) { // 빈 내용이 아닌 경우만 추가
								const taskData = {
									task_content: taskContent,
									is_completed: isCompleted,
									completion_date: completionDate,
									original_date: originalDate,
									elapsed_days: elapsedDays
								};
								
								// 추적 관련 데이터 추가
								if (uniqueId) {
									taskData.unique_id = uniqueId;
								}
								if (isPending) {
									taskData.is_pending = true;
								}
								// date_key 전달
								if (dateKey) {
									taskData.date_key = dateKey;
								}
								
								tasks.push(taskData);
							}
						});
						
						// 추적 시스템 관련 데이터 계산
						const pendingTasks = tasks.filter(task => task.is_pending).length;
						const completedPendingTasks = tasks.filter(task => task.is_pending && task.is_completed).length;
						const hasTracking = pendingTasks > 0;
						
						// 폼 데이터 수집
						const formData = new FormData();
						const mode = $('#mode').val();
						const num = $('#num').val();
						const tablename = $('#tablename').val();
						const task_date = $('#task_date').val();
						const employee_name = $('#employee_name').val();
						const department = $('#department').val();
						const memo = $('#memo').val();

						formData.append('mode', mode);
						formData.append('num', num);
						formData.append('tablename', tablename);
						formData.append('task_date', task_date);
						formData.append('employee_name', employee_name);
						formData.append('department', department);
						formData.append('memo', memo);
						formData.append('tasks_json', JSON.stringify(tasks));
						
						// 추적 시스템 관련 필드 추가
						formData.append('tracking_enabled', hasTracking ? 'Y' : 'N');
						formData.append('last_tracking_date', hasTracking ? task_date : '');
						formData.append('tracking_count', pendingTasks);
					
						$.ajax({
							url: "/todo_task/task_process.php",
							type: "post",
							data: formData,
							processData: false,
							contentType: false,
							dataType: "json",
							timeout: 30000,
							success: function(response) {
								console.log('Response:', response);
								if (response.result === 'success') {
									Toastify({
										text: response.message,
										duration: 3000,
										close: true,
										gravity: "top",
										position: "center",
										backgroundColor: "#4fbe87",
									}).showToast();
									// Bootstrap 5 modal hide method
									const modal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
									if (modal) {
										modal.hide();
									}
									todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear); // 변경된 데이터만 다시 로드
								} else {
									Toastify({
										text: "저장 실패: " + response.message,
										duration: 5000,
										close: true,
										gravity: "top", 
										position: "center",
										backgroundColor: "#ff0000",
									}).showToast();
								}
							},
							error: function(jqxhr, status, error) {
								console.log('Error:', jqxhr, status, error);
								Toastify({
									text: "저장 중 오류가 발생했습니다.",
									duration: 5000,
									close: true,
									gravity: "top", 
									position: "center",
									backgroundColor: "#ff0000",
								}).showToast();
							}
						});
					} catch (error) {
						console.error('JavaScript 오류가 발생했습니다:');
						console.error('오류 메시지:', error.message);
						console.error('오류 이름:', error.name);
						console.error('오류 스택:', error.stack);
						
						Toastify({
							text: "오류가 발생했습니다: " + error.message,
							duration: 5000,
							close: true,
							gravity: "top", 
							position: "center",
							backgroundColor: "#ff0000",
						}).showToast();
					}
				});

				// 삭제 버튼
				$("#deleteBtn").on("click", function() {                    
					var user_name = $("#user_name").val();
					var employee_name = $("#employee_name").val();
					var level = $("#level").val();

					// 관리자이거나 작성자인 경우만 삭제 가능
					if (level !== '1' && user_name !== employee_name) {
						Swal.fire({
							title: '삭제불가',
							text: "관리자 또는 작성자만 삭제 가능합니다.",
							icon: 'error',
							confirmButtonText: '확인'
						});
						return;
					}

					Swal.fire({
						title: '할일 삭제',
						text: "정말로 이 할일을 삭제하시겠습니까?",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#d33',
						cancelButtonColor: '#3085d6',
						confirmButtonText: '삭제',
						cancelButtonText: '취소'
					}).then((result) => {
						if (result.isConfirmed) {
							const formData = new FormData();
							formData.append('mode', 'delete');
							formData.append('num', $('#num').val());
							formData.append('tablename', $('#tablename').val());

							$.ajax({
								url: "/todo_task/task_process.php",
								type: "post",
								data: formData,
								processData: false,
								contentType: false,
								dataType: "json",
								timeout: 30000,
								success: function(response) {
									console.log('Delete Response:', response);
									if (response.result === 'success') {
										Toastify({
											text: response.message,
											duration: 2000,
											close: true,
											gravity: "top",
											position: "center",
											backgroundColor: "#4fbe87",
										}).showToast();

										// Bootstrap 5 modal hide method
										const modal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
										if (modal) {
											modal.hide();
										}
										todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear); // 변경된 데이터만 다시 로드
									} else {
										Toastify({
											text: "삭제 실패: " + response.message,
											duration: 5000,
											close: true,
											gravity: "top", 
											position: "center",
											backgroundColor: "#ff0000",
										}).showToast();
									}
								},
								error: function(jqxhr, status, error) {
									console.log('Delete Error:', jqxhr, status, error);
									Toastify({
										text: "삭제 중 오류가 발생했습니다.",
										duration: 5000,
										close: true,
										gravity: "top", 
										position: "center",
										backgroundColor: "#ff0000",
									}).showToast();
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
				
			},
			error: function(jqxhr, status, error) {
				console.log("AJAX Error: ", status, error);
			}
		});
	}

	$('#task-prev-month_work').off("click").on("click", function() {
		todo_currentMonth--;
		if (todo_currentMonth < 0) {
			todo_currentMonth = 11;
			todo_currentYear--;
		}
		todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear);
	});

	$('#task-next-month_work').off("click").on("click", function() {
		todo_currentMonth++;
		if (todo_currentMonth > 11) {
			todo_currentMonth = 0;
			todo_currentYear++;
		}
		todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear);
	});

	$('#task-current-month_work').off("click").on("click", function() {
		todo_currentMonth = new Date().getMonth();
		todo_currentYear = new Date().getFullYear();
		todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear);
	});

	todo_fetchCalendarData_work(todo_currentMonth, todo_currentYear);

	// 1초후에 호출이 있다면
	setTimeout(function() {
		var Get_task_num = "<?= isset($Get_task_num) && $Get_task_num !== '' ? $Get_task_num : '' ?>";
		if (Get_task_num) {
			loadTaskForm(Get_task_num);		
		}
	}, 1000);

});

</script>