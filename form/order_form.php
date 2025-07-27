
<div class="container">
<!--Extra Full Modal -->
<div class="modal fade" id="deliveryModal" role="dialog" >
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-full" role="document" >
		<div class="modal-content" >
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel"> 운송방법(배송) 선택  <span id="orderwindow_msg"> </span>  <button id="saveExceptListBtn" class="btn btn-danger btn-sm ms-5 me-5">제외 리스트 저장</button> </h4>
				
				<button type="button" class="btn btn-outline-secondary closemodal" aria-label="x">
					<i class="bi bi-x-circle"></i>
				</button>				
			</div>
				<div class="modal-body">
					<ul class="nav nav-tabs" id="deliveryTabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="favorites-tab" data-bs-toggle="tab" href="#favorites" role="tab">즐겨찾기</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="all-tab" data-bs-toggle="tab" href="#all" role="tab">전체</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="except-tab" data-bs-toggle="tab" href="#except" role="tab">제외 목록</a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane fade show active" id="favorites" role="tabpanel">
							<div id="favoritesDeliveryInfo"></div>
						</div>
						<div class="tab-pane fade" id="all" role="tabpanel">
							<div id="allDeliveryInfo"></div>
						</div>
						<div class="tab-pane fade" id="except" role="tabpanel">
							<div id="exceptDeliveryInfo"></div>
						</div>
					</div>
				</div>

			<div class="modal-footer justify-content-end">
				<button type="button" class="btn btn-outline-secondary closeModal">
						<i class="bi bi-x-circle"></i> 닫기 
				</button>
			</div>
		</div>
	</div>
</div>
</div>
