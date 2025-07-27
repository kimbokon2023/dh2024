	
  <!-- Modal --> 
  <div  id="myModal"  class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg modal-center" >
    
      <!-- Modal content-->
      <div class="modal-content modal-lg">
        <div class="modal-header">          
          <h2 class="modal-title">알림</h2>
        </div>
        <div class="modal-body">
		<div class="d-flex justify-content-center text-dark fs-3 mb-2"> 
		    <span id="alertmsg"> </span>
			<!-- <img id=popupwindow src="./img/popupmall.jpg"  style="width:60%; height:60%;"> 	-->
			<!-- <img id=popupwindow src="./img/steelname2.jpg"  style="width:100%; height:100%;"> 	-->
			
		</div>
		</div>
			
        <div class="modal-footer">		
          <button type="button" class="btn btn-default" id="closemodalBtn" data-dismiss="modal">닫기</button>
        </div>
		</div>
      </div>
	</div>


<div class="container-fluid">
  <!-- Modal -->
  <div id="updatepriceModal" class="modal">
    <div class="modal-content" style="width:800px;">
      <div class="modal-header">          
       <h2 class="modal-title">정보 수정</h2>
		<button type="button" class="closemodalBtn" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
		</button>
      </div>
      <div class="modal-body">
        <table class="table table-hover">
          <thead class="table-primary">
            <tr>
              <th class="text-center">번호</th>
              <th class="text-center" style="width:300px;">품목</th>
              <th class="text-center" style="width:100px;">할인여부</th>
              <th class="text-center" style="width:100px;">원가</th>
              <th class="text-center" style="width:100px;">단가</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-center">1</td>
              <td><input type="text" class="form-control" id="modalItem"></td>
              <td><input type="text" class="form-control" id="modalIsDc"></td>
              <td><input type="text" class="form-control" id="modalOriginalCost" oninput="formatNumber(this)"></td>
              <td><input type="text" class="form-control" id="modalPrice" oninput="formatNumber(this)"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">		
        <button type="button" class="btn btn-dark" id="saveChangesBtn">수정</button>
        <button type="button" class="btn btn-outline-dark closemodalBtn" data-dismiss="modal"><i class="bi bi-x-lg"></i> 닫기</button>
      </div>
    </div>
  </div>
</div>



 <!-- Modal HTML -->
    <div id="timeModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">서버 이관작업 안내</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h2> 금일 작업한 도면을 Nas2dual 회사 서버에 올려주세요. </h2>
					<br>
					<br>
					<h2> 오늘도 수고 많으셨습니다.</h2>
                </div>
                <div class="modal-footer">
                    <button id="timeModalcloseBtn" type="button" class="btn btn-secondary fs-3"  onclick="stopInterval()" data-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>
	
<!-- Modal --> 
<!-- Vertically centered modal -->    
<div class="modal fade" id="Approval Modal" role="dialog">
	<div class="modal-dialog modal-dialog-centered">

		<!-- Modal content-->
		<div class="modal-content modal-lg">
			<div class="modal-header">          
			<h4 class="modal-title">결재 알림</h4>
			</div>
				<div class="modal-body">
				<div class="d-flex justify-content-center mb-2 fs-5"> 
				결재 내용이 있습니다. 확인바랍니다.
				</div>
				</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" id="closemodalApprovalBtn" data-dismiss="modal">닫기</button>
			</div>
		</div>
    </div>
</div>

<!-- 모터, 브라켓 Modal -->
<div class="container-fluid justify-content-center align-items-center">  
<div id="lotModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document" >
        <div class="modal-content ">
            <div class="modal-header">          
                <h2 class="modal-title">품목코드/로트번호 매칭</h2>
                <button type="button" class="btn btn-outline-dark lotModalclose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body">
				
            </div>
		  <div class="modal-footer">		
             모터, 브라켓트 수량합(참고) :  <input type="number" id="request_qty" name="request_qty" class="form-control text-center me-2" style="width:100px;" />   		  
			<button type="button" class="btn btn-dark btn-sm adaptBtn me-2" > 주문적용</button>
			<button type="button" class="btn btn-outline-dark btn-sm lotModalclose" data-dismiss="modal"><i class="bi bi-x-lg"></i> 닫기</button>
		  </div>			
        </div>
    </div>
</div>
</div>

<!-- 연동제어기 모달 Modal -->
<div class="container-fluid justify-content-center align-items-center">  
<div id="controllerlotModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document" >
        <div class="modal-content ">
            <div class="modal-header">          
                <h2 class="modal-title">품목코드/로트번호 매칭</h2>
                <button type="button" class="btn btn-outline-dark controllerlotModalclose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body">
				
            </div>
		  <div class="modal-footer">		
             주문수량(참고) :  <input type="number" id="controllerrequest_qty" name="controllerrequest_qty" class="form-control text-center me-2" style="width:100px;" />   		  
			<button type="button" class="btn btn-dark btn-sm controlleradaptBtn me-2" > 주문적용</button>
			<button type="button" class="btn btn-outline-dark btn-sm controllerlotModalclose" data-dismiss="modal"><i class="bi bi-x-lg"></i> 닫기</button>
		  </div>			
        </div>
    </div>
</div>
</div>

<!-- 부속자재 모달 Modal -->
<div class="container-fluid justify-content-center align-items-center">  
<div id="sublotModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document" >
        <div class="modal-content ">
            <div class="modal-header">          
                <h2 class="modal-title">품목코드/로트번호 매칭</h2>
                <button type="button" class="btn btn-outline-dark sublotModalclose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>                            
                            <th class="text-center" style="width:35%;">품목코드</th>                            
                            <th class="text-center" style="width:35%;">로트번호</th>
                            <th class="text-center" style="width:15%;">재고</th>
                            <th class="text-center" style="width:15%;">주문수량</th>
                        </tr>
                    </thead>
                    <tbody id="sublotModalBody">
                    </tbody>
                </table>
            </div>
		  <div class="modal-footer">		
             주문수량(참고) :  <input type="number" id="subrequest_qty" name="subrequest_qty" class="form-control text-center me-2" style="width:100px;" />   		  
			<button type="button" class="btn btn-dark btn-sm subadaptBtn me-2" > 주문적용</button>
			<button type="button" class="btn btn-outline-dark btn-sm sublotModalclose" data-dismiss="modal"><i class="bi bi-x-lg"></i> 닫기</button>
		  </div>			
        </div>
    </div>
</div>
</div>

<!-- 원단 로트번호 Modal -->
<div class="container-fluid justify-content-center align-items-center">  
<div id="fabriclotModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document" >
        <div class="modal-content ">
            <div class="modal-header">          
                <h2 class="modal-title">품목코드/로트번호 매칭</h2>
                <button type="button" class="btn btn-outline-dark fabriclotModalclose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>                            
                            <th class="text-center" style="width:35%;">품목코드</th>                            
                            <th class="text-center" style="width:35%;">로트번호</th>
                            <th class="text-center" style="width:15%;">재고</th>
                            <th class="text-center" style="width:15%;">주문수량</th>
                        </tr>
                    </thead>
                    <tbody id="fabriclotModalBody">
                    </tbody>
                </table>
            </div>
		  <div class="modal-footer">		
             주문수량(참고) :  <input type="number" id="fabricrequest_qty" name="fabricrequest_qty" class="form-control text-center me-2" style="width:100px;" />   		  
			<button type="button" class="btn btn-dark btn-sm fabricadaptBtn me-2" > 주문적용</button>
			<button type="button" class="btn btn-outline-dark btn-sm fabriclotModalclose" data-dismiss="modal"><i class="bi bi-x-lg"></i> 닫기</button>
		  </div>			
        </div>
    </div>
</div>
</div>

