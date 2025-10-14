
<!-- 이월자료 Modal -->
<div class="container-fluid justify-content-center align-items-center">
<div id="monthlyBalanceModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">이월자료 수정</h2>
                    <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <table class="table table-hover">
                    <thead class="table-info">
                        <tr>
                            <th class="text-center" style="width:20%;">기준일자</th>
                            <th class="text-center" style="width:30%;">거래처명</th>
                            <th class="text-center" style="width:20%;">이월잔액</th>                            
                            <th class="text-center" style="width:30%;">적요</th>                            
                            <th style="display:none;">고유번호</th>
                            <th style="display:none;">secondordnum</th>
                        </tr>
                    </thead>
                    <tbody id="monthlyBalanceModalBody">
                        <!-- 데이터를 여기에 동적으로 추가 -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark btn-sm" onclick="saveMonthlyBalanceData()"> <i class="bi bi-floppy2-fill"></i>  저장</button>
                <span class="badge bg-dark close">&times;</span>
            </div>
        </div>
    </div>
</div>
</div>


<!-- 판매일괄회계반영 Modal -->
<div class="container-fluid justify-content-center align-items-center">
<div id="monthlysaleModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"> 판매일괄회계반영(계산서발행) </h2>                
                    <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>📅 날짜 입력 안내:</strong> 기준일자는 <span class="badge bg-primary">매월 말일</span>로 입력해 주세요!
                    <br><small class="text-muted">예: 2024년 10월이면 2024-10-31로 입력</small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <table class="table table-hover">
                    <thead class="table-info">
                        <tr>
                            <th class="text-center" >기준일자</th>
                            <th class="text-center" >거래처명</th>
                            <th class="text-center" >당월 발생금액</th>
                            <th class="text-center" style="width:140px;"  >계산서 발행</th>
                            <th class="text-center" >적요</th>
                            <th class="text-center" style="width:80px;" > NUM </th>
                            <th class="text-center" >거래처 코드</th>
                        </tr>
                    </thead>
                    <tbody id="monthlysaleModalBody">
                        <!-- 데이터를 여기에 동적으로 추가 -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark btn-sm" onclick="saveMonthlyBalanceData()"> <i class="bi bi-floppy2-fill"></i> 저장</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="saveMonthlyDelete()"> <i class="bi bi-trash3-fill"></i> 삭제 </button>
                <span class="badge bg-dark close">&times;</span>
            </div>
        </div>
    </div>
</div>
</div>


	