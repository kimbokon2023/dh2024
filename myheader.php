<form id="eworks_board_form" name="eworks_board_form" method="post" enctype="multipart/form-data" >	    
	<input type="hidden" id="eworksPage" name="eworksPage" value="<?= isset($eworksPage) ? $eworksPage : '' ?>" > 
	<input type="hidden" id="e_viewexcept_id" name="e_viewexcept_id" value="<?= isset($e_viewexcept_id) ? $e_viewexcept_id : '' ?>" >   <!-- 전자결재 보기 제한 -->    
	<input type="hidden" id="e_num" name="e_num" value="<?= isset($e_num) ? $e_num : '' ?>" > 
	<input type="hidden" id="ripple_num" name="ripple_num" value="<?= isset($ripple_num) ? $ripple_num : '' ?>" > 
	<input type="hidden" id="SelectWork" name="SelectWork" value="<?= isset($SelectWork) ? $SelectWork : '' ?>" > 
	<input type="hidden" id="eworksel" name="eworksel" value="<?= isset($eworksel) ? $eworksel : '' ?>" >    <!-- 전자결재 진행상태  draft send -->    
	<input type="hidden" id="choice" name="choice" value="<?= isset($choice) ? $choice : '' ?>" >    <!-- 전자결재 진행상태  draft send -->        
	<input type="hidden" id="approval_right" name="approval_right" value="<?= isset($approval_right) ? $approval_right : '' ?>" >   
	<input type="hidden" id="done" name="done" value="<?= isset($done) ? $done : '' ?>" >    <!-- 전자결재 진행상태  done -->        
	<input type="hidden" id="author_id" name="author_id" value="<?= isset($author_id) ? $author_id : '' ?>" > 
	
	<!-- 전자결재 관련 배열 -->	
	<input id="numid_arr" name="numid_arr[]" type="hidden" >
	<input id="registdate_arr" name="registdate_arr[]" type="hidden" >
	<input id="eworks_item_arr" name="eworks_item_arr[]" type="hidden" >
	<input id="author_arr" name="author_arr[]" type="hidden" >
	<input id="author_id_arr" name="author_id_arr[]" type="hidden" >
	<input id="e_title_arr" name="e_title_arr[]" type="hidden" >
	<input id="e_line_id_arr" name="e_line_id_arr[]" type="hidden" >
	<input id="e_line_arr" name="e_line_arr[]" type="hidden" >
	<input id="r_line_arr" name="r_line_arr[]" type="hidden" >		   
	<input id="r_line_id_arr" name="r_line_id_arr[]" type="hidden" >		   
	<input id="e_confirm" name="e_confirm" type="hidden" >		   
	<input id="e_confirm_arr" name="e_confirm_arr[]" type="hidden" >		   
	<input id="e_confirm_id" name="e_confirm_id" type="hidden" >		   
	<input id="e_confirm_id_arr" name="e_confirm_id_arr[]" type="hidden" >		   	
 <?php if($chkMobile==false) { ?>
	<div class="container">     
 <?php } else { ?>
 	<div class="container-fluid">     
	<?php } ?>	  
<div class="row d-flex">
    <div class="col-sm-2 justify-content-center">
		<a href="<?=$WebSite?>index.php">
			 <img src="<?=$WebSite?>img/dhlogo.png" style="width:75%;" >
		 </a>	
	</div>
<div class="col-sm-10 justify-content-center">     
	<nav class="navbar navbar-expand navbar-custom">
	<div class="navbar-nav ">   
            <div class="nav-item me-2" id="home-menu">
				<!-- 드롭다운 메뉴-->
				<a class="nav-link" href="<?$root_dir?>/index.php?home=1" title="DeaHan Homepage"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-house-check-fill" viewBox="0 0 16 16">
				  <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5Z"/>
				  <path d="m8 3.293 4.712 4.712A4.5 4.5 0 0 0 8.758 15H3.5A1.5 1.5 0 0 1 2 13.5V9.293l6-6Z"/>
				  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.707l.547.547 1.17-1.951a.5.5 0 1 1 .858.514Z"/>
				</svg> </a>
            </div>		
            <div class="nav-item dropdown flex-fill me-2">			 			 
                <!-- 드롭다운 메뉴-->
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" >
                    <i class="bi bi-sliders2"></i> 업무일지
                </a>
			<div class="dropdown-menu">
				<a class="dropdown-item" href="<?=$root_dir?>/todo_task/task_list.php">
					<i class="bi bi-check2-square"></i> 오늘의 할일
				</a>
				<!-- <a class="dropdown-item" href="<?=$root_dir?>/employee_tasks/task_list.php">
					<i class="bi bi-check2-square"></i> 오늘의 할일
				</a> -->
				<!-- <a class="dropdown-item" href="<?=$root_dir?>/work/schedule.php">
					<i class="bi bi-pencil-square"></i> 작성
				</a> -->
				<a class="dropdown-item" href="<?=$root_dir?>/work/search.php">
					<i class="bi bi-search"></i> 과거일지 검색
				</a>															
				<!-- <a class="dropdown-item" href="<?=$root_dir?>/work/schedule_all.php">
					<i class="bi bi-journal-bookmark-fill"></i> 전체일지
				</a>														 -->
				<a class="dropdown-item" href="<?=$root_dir?>/workprocess/list.php">
					<i class="bi bi-app-indicator"></i> 업무요청사항
				</a>
				<!-- Expenditure resolution 지출결의서 -->
				<a class="dropdown-item" href="<?=$root_dir?>/askitem_ER/list.php">
					<i class="bi bi-list-check"></i> 지출결의서
				</a>			
				<a class="dropdown-item" href="<?=$root_dir?>/annualleave/index.php">
						<i class="bi bi-person-bounding-box"></i> 연차
				</a>					
				<a class="dropdown-item" href="<?=$root_dir?>/suggestion/list.php">
						<i class="bi bi-chat-right-text"></i> 건의사항
				</a>					
				<a class="dropdown-item" href="<?=$root_dir?>/meeting/list.php">
						<i class="bi bi-journal-text"></i> 회의록
				</a>					
			</div>
            </div>
            <div class="nav-item dropdown flex-fill me-2">			 			 
                <!-- 드롭다운 메뉴-->
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" >
                    수주/출고
                </a>
			<div class="dropdown-menu">
				<a class="dropdown-item" href="<?=$root_dir?>/motor/list.php">
					<i class="bi bi-card-checklist"></i> 수주 List
				</a>
				<a class="dropdown-item" href="<?=$root_dir?>/motor/month_schedule.php">
					<i class="bi bi-calendar-week"></i> 월간 일정표
				</a>
				<a class="dropdown-item" href="<?=$root_dir?>/motor/list_returndue.php">
					<i class="bi bi-arrow-repeat"></i> 회수 예정
				</a>
				<a class="dropdown-item" href="<?=$root_dir?>/phonebook/list.php?header=header">
					<i class="bi bi-book"></i> 발주처 주소록
				</a>
				<a class="dropdown-item" href="<?=$root_dir?>/phonebook_buy/list.php?header=header">
							<i class="bi bi-book"></i> 매입처 주소록
						</a>				
				<a class="dropdown-item" href="<?=$root_dir?>/tax/invalid_registered.php">
							<i class="bi bi-building-check"></i> 사업자 진위여부 확인
						</a>				
				<a class="dropdown-item" href="<?=$root_dir?>/workbook/list.php?header=header">
					<i class="bi bi-person-lines-fill"></i> 받는분 주소록
				</a>
				<a class="dropdown-item" href="<?=$root_dir?>/branchbook/list.php?header=header">
					<i class="bi bi-truck-front"></i> 화물지점 주소록
				</a>
				<a class="dropdown-item" href="<?=$root_dir?>/motor/outputstat.php?header=header">
					<i class="bi bi-bar-chart"></i> 출고 통계
				</a>		
				<a class="dropdown-item" href="<?=$root_dir?>/motor/statistics.php?header=header">
							<i class="bi bi-bar-chart-fill"></i> 매출 통계
					</a>													   					
			</div>

            </div>				
            <div class="nav-item dropdown flex-fill me-2">			 			 
                <!-- 드롭다운 메뉴-->
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" >
                    결선/AS
                </a>
				<div class="dropdown-menu">
					<a class="dropdown-item" href="<?=$root_dir?>/as/list.php">
						<i class="bi bi-tools"></i> 결선/AS 진행중
					</a>
					<a class="dropdown-item" href="<?=$root_dir?>/as/nodemand.php">
						<i class="bi bi-receipt-cutoff"></i> 결선/AS 미청구
					</a>
					<a class="dropdown-item" href="<?=$root_dir?>/as/receivable.php">
						<i class="bi bi-cash-coin"></i> 결선/AS 미입금
					</a>
					<a class="dropdown-item" href="<?=$root_dir?>/as/asdone.php">
						<i class="bi bi-check-circle-fill"></i> 결선/AS 완료
					</a>
					<a class="dropdown-item" href="<?=$root_dir?>/as/asreport.php">
						<i class="bi bi-file-earmark-text"></i> AS 보고서 
					</a>
				</div>
            </div>	
			<?php if($level =='1' or $level =='2') { ?>			
			<div class="nav-item dropdown flex-fill me-2">			 
                <!-- 드롭다운 메뉴-->				
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    구매 관리 
                </a>								
				<div class="dropdown-menu">
					<a class="dropdown-item" href="<?=$root_dir?>/material/list.php?header=header">
						<i class="bi bi-cart-check"></i> 품목코드 조회
					</a>    																				

					<a class="dropdown-item" href="<?=$root_dir?>/material_lot/list.php?header=header">
						<i class="bi bi-upc"></i> 로트번호
					</a>					
					<a class="dropdown-item" href="<?=$root_dir?>/m_order/unitprice_list.php?header=header">
						<i class="bi bi-currency-exchange"></i> 구매(중국) 원단가
					</a>			 		
					<a class="dropdown-item" href="<?=$root_dir?>/m_order/list.php?header=header">
						<i class="bi bi-list-ul"></i> 구매(중국)발주서
					</a>					
					<a class="dropdown-item" href="<?=$root_dir?>/m_order/list_input.php?header=header">
						<i class="bi bi-list-ul"></i> 구매(중국) 발주서 입고
					</a>			
					<a class="dropdown-item" href="<?=$root_dir?>/m_order/list_account.php?header=header">
							<i class="bi bi-list-ul"></i> 구매(중국) 송금
					</a>	
					<a class="dropdown-item" href="<?=$root_dir?>/material_reg/list.php?header=header">
						<i class="bi bi-box-arrow-in-down"></i> 자재입고
					</a>					

					<a class="dropdown-item" href="<?=$root_dir?>/motor/list_mat.php?header=header">
						<i class="bi bi-clipboard-data"></i> 재고현황
					</a>				
				</div>

            </div>		
			<div class="nav-item dropdown flex-fill me-2">			 
				<!-- 드롭다운 메뉴-->				
				<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
					자재 관리
				</a>								
				<div class="dropdown-menu">
					<a class="dropdown-item" href="<?=$root_dir?>/fee/list.php?header=header">
						<i class="bi bi-boxes"></i> 주자재 원단가
					</a>
					<a class="dropdown-item" href="<?=$root_dir?>/fee_controller/list.php?header=header">
						<i class="bi bi-cpu-fill"></i> 연동제어기 원단가
					</a>
					<a class="dropdown-item" href="<?=$root_dir?>/fee_fabric/list.php?header=header">
						<i class="bi bi-grid-1x2"></i> 원단 원단가
					</a>
					<a class="dropdown-item" href="<?=$root_dir?>/fee_sub/list.php?header=header">
						<i class="bi bi-tools"></i> 부자재 원단가
					</a>
					<a class="dropdown-item" href="<?=$root_dir?>/price/list.php?header=header">
						<i class="bi bi-currency-dollar"></i> 중국발주 원단가
					</a>					
				</div>
			</div>			
			<?php } ?>					
			<?php if($level =='1') { ?>
				<div class="nav-item dropdown flex-fill me-2">			 
					<!-- 드롭다운 메뉴-->				
					<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
						회계
					</a>								
					<div class="dropdown-menu">
						<a class="dropdown-item" href="<?=$root_dir?>/account/schedule.php">
							<i class="bi bi-calendar-check"></i> 회계 달력
						</a>

						<a class="dropdown-item" href="#" onclick="event.preventDefault(); customPopup('../account/settings.php', '계정 관리', 600, 850);">
							<i class="bi bi-gear-fill"></i> 수입/지출 계정
						</a>

						<a class="dropdown-item" href="<?=$root_dir?>/account/cardlist.php?header=header">
							<i class="bi bi-credit-card"></i> 법인카드 관리
						</a>
						<a class="dropdown-item" href="<?=$root_dir?>/account/accountlist.php">
							<i class="bi bi-cash-coin"></i>  법인계좌 관리
						</a>
						<a class="dropdown-item" href="<?=$root_dir?>/account/list.php">
							<i class="bi bi-journal-text"></i> 금전출납부
						</a>

						<a class="dropdown-item" href="<?=$root_dir?>/account/list_daily.php">
							<i class="bi bi-journal-bookmark-fill"></i> 일일 일보
						</a>

						<a class="dropdown-item" href="<?=$root_dir?>/motor/receivable.php">
							<i class="bi bi-exclamation-circle-fill"></i> 미수금현황
						</a>

						<a class="dropdown-item" href="<?=$root_dir?>/motor/customer.php?header=header">
							<i class="bi bi-person-vcard-fill"></i> 거래처 원장
						</a>

						<a class="dropdown-item" href="<?=$root_dir?>/getmoney/list.php?header=header">
							<i class="bi bi-coin"></i> 수금현황
						</a>

						<a class="dropdown-item" href="<?=$root_dir?>/motor/month_sales.php?header=header">
							<i class="bi bi-bar-chart-steps"></i> 당월판매회계반영
						</a>

						<a class="dropdown-item" href="<?=$root_dir?>/account_plan/list.php">
							<i class="bi bi-clipboard-data"></i> 월별 수입/지출 예상내역서
						</a>

						<a class="dropdown-item" href="<?=$root_dir?>/accountLoan/list.php">
							<i class="bi bi-bank2"></i> 대출금 상환 현황
						</a>
						<a class="dropdown-item" href="<?=$root_dir?>/price_motor/list.php?header=header">
							<i class="bi bi-gear-wide-connected"></i> DH모터 원가산출
						</a>						
						<a class="dropdown-item" href="<?=$root_dir?>/motor/delivery_statistics.php">
							<i class="bi bi-truck"></i> 운송비(택배/화물) 통계
						</a>						
					</div>
				</div>					
			<?php } ?>	
            <div class="nav-item dropdown flex-fill me-2">			 			 
                <!-- 드롭다운 메뉴-->
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" >
                    차량 및 장비
                </a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="<?=$root_dir?>/car/list.php">
							<i class="bi bi-truck-front"></i> 차량
						</a>                    
						<a class="dropdown-item" href="<?=$root_dir?>/lift/list.php">
							<i class="bi bi-box-seam"></i> 지게차/이동식에어컨
						</a>                    
					</div>
            </div>
            <div class="nav-item dropdown flex-fill me-2">			 
                <!-- 드롭다운 메뉴-->
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    연구소
                </a> 
				<div class="dropdown-menu">				
					<a class="dropdown-item" href="<?=$root_dir?>/ask_rndplan/list.php">
						<i class="bi bi-journal-medical"></i> 연구개발계획서
					</a>			
					<a class="dropdown-item" href="<?=$root_dir?>/ask_rndnote/list.php">
						<i class="bi bi-journal-medical"></i> 연구노트
					</a>			
					<a class="dropdown-item" href="<?=$root_dir?>/ask_rndreport/list.php">
						<i class="bi bi-journal-medical"></i> 연구개발보고서
					</a>
					<a class="dropdown-item" href="https://www.rnd.or.kr/user/main.do" target="_blank">
						<i class="bi bi-building"></i> 연구개발전담부서
					</a>
					<a class="dropdown-item" href="https://research.rnd.or.kr/krc/" target="_blank">
						<i class="bi bi-pencil-square"></i> 연구개발활동 입력확인
					</a>
					<a class="dropdown-item" href="https://www.koita.or.kr/" target="_blank">
						<i class="bi bi-globe"></i> 한국산업기술진흥협회
					</a>
				</div>
            </div>				
            <div class="nav-item dropdown flex-fill me-2">			 
                <!-- 드롭다운 메뉴-->
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    게시
                </a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="<?=$root_dir?>/notice/list.php">
							<i class="bi bi-megaphone-fill"></i> 공지사항
						</a>                    
						<a class="dropdown-item" href="<?=$root_dir?>/qna/list.php">
							<i class="bi bi-folder-symlink-fill"></i> 자료실
						</a>                    			                    
						<a class="dropdown-item" href="<?=$root_dir?>/CertProd/index.php">
							<i class="bi bi-patch-check-fill"></i> 인정업체별 사용제품
						</a>                    			                    
						<a class="dropdown-item" href="<?=$root_dir?>/motor_rnd/list.php">
							<i class="bi bi-journal-code"></i> 모터 개발일지
						</a>
						<a class="dropdown-item" href="<?=$root_dir?>/rnd/list.php">
							<i class="bi bi-journal-code"></i> 전산 개발일지
						</a>
						<a class="dropdown-item" href="<?=$root_dir?>/title/list.php">
							<i class="bi bi-bullseye"></i> 연간계획 문구 설정
						</a>                    			                    
					</div>
            </div>                      
            <div class="nav-item dropdown flex-fill me-2">			 
                <!-- 드롭다운 메뉴-->
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    공유
                </a>
				<div class="dropdown-menu">
					<a class="dropdown-item" href="<?=$root_dir?>/youtube.php">
						<i class="bi bi-youtube"></i> (주)대한
					</a>						
					<a class="dropdown-item" href="<?=$root_dir?>/roadview.php">
						<i class="bi bi-person-lines-fill"></i> 직원 주소록
					</a>                                                                         
					<a class="dropdown-item" href="<?=$root_dir?>/qrcode/index.php">
						<i class="bi bi-qr-code"></i> QR코드 생성
					</a>        
					<a class="dropdown-item" href="<?=$root_dir?>/holiday/list.php?header=header">
							<i class="bi bi-calendar-event"></i> 일정표 휴일설정
						</a>					                                                                 
				</div>

            </div>			
			<div class="nav-item dropdown flex-fill me-2">			 
				<!-- 드롭다운 메뉴-->
				<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
					<?=$user_name?>님(Lv<?=$level?>)
				</a>                

				<div class="dropdown-menu">                   
					<a class="dropdown-item" href="<?=$root_dir?>/login/logout.php">
						<i class="bi bi-box-arrow-right"></i> 로그아웃
					</a>                    
					<a class="dropdown-item" href="<?=$root_dir?>/member/updateForm.php?id=<?=$_SESSION["userid"]?>">
						<i class="bi bi-person-gear"></i> 정보수정
					</a>                    

					<?php if (intval($level) === 1) { ?>
						<a class="dropdown-item" href="<?=$root_dir?>/member/list.php">
							<i class="bi bi-person-lines-fill"></i> 직원관리
						</a>
					<?php } ?>

					<?php if (intval($level) === 1) { ?>
						<a class="dropdown-item" href="<?=$root_dir?>/logdata.php">
							<i class="bi bi-clock-history"></i> 로그인기록
						</a>
					<?php } ?>

					<?php
					if ($user_name == '개발자') {
						echo '<a class="dropdown-item" href="' . $WebSite . 'logdata_menu.php">
								<i class="bi bi-menu-button-wide"></i> 메뉴접속기록
							  </a>';
					}
					?>						
				</div>
			</div>
		
			<div class="nav-item flex-fill me-6">			 
				<!-- 전자결재 관련 알람 -->
				<a class="nav-link dropdown-toggle" href="#" onclick="seltab(3);">
					<span id="alert_eworks_bell" style="display:none; font-size:15px;">🔔결재</span>
					<i class="bi bi-file-earmark-text"></i>
					<span id="alert_eworks"></span>
					전자결재
				</a>
				<div class="dropdown-menu">
					<a class="dropdown-item" href="<?=$root_dir?>/annualleave/index.php">
						<i class="bi bi-person-bounding-box"></i> 연차
					</a>
					<hr style="margin:7px!important;">
					<!-- Expenditure resolution 지출결의서 -->
					<a class="dropdown-item" href="<?=$root_dir?>/askitem_ER/list.php">
						<i class="bi bi-list-check"></i> 지출결의서
					</a>
				</div>
			</div>	
		</div>		
	</nav>      
  </div>  			
</div>  	  
<?php 
	// 전자결재 관련 모달
	require_once($_SERVER['DOCUMENT_ROOT'] . '/eworks/list_form.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/eworks/write_form.php'); 
?>
<div class="sideEworksBanner" style="display:none;">
    <span class="text-center text-dark">
		<img src="<?=$WebSite?>img/eworks_reach.png" > 
	</span>     
</div>
</div>
</form>

