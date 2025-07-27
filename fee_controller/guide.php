<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>새로 추가할 경우 간단 설명서</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .guide-step {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .example-box {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }
        .close-btn:hover {
            color: #343a40;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <!-- 닫기 버튼 -->
        <div class="close-btn" onclick="window.close()">
            <i class="bi bi-x-circle"></i>
        </div>
        
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mb-4">
                    <i class="bi bi-info-circle text-primary"></i>
                    새로 추가할 경우 간단 설명서
                </h3>
                
                <div class="guide-step">
                    <h5><i class="bi bi-1-circle text-primary"></i> 기본 단계</h5>
                    <p class="mb-0">
                        <strong>기준일이 최신 데이터를 클릭</strong> → <strong>'복사' 버튼을 누르고</strong> → <strong>새로 작성</strong>
                    </p>
                </div>

                <div class="example-box">
                    <h6><i class="bi bi-lightbulb text-info"></i> 예시: 연동제어기 품목 추가</h6>
                    <ol>
                        <li>기존 데이터 중 <strong>가장 최근 기준일</strong>의 데이터를 클릭</li>
                        <li><strong>'복사' 버튼</strong> 클릭</li>
                        <li>가장 하단에 <strong>새로운 '품명'과 단가</strong>를 추가</li>
                        <li><strong>저장</strong> 버튼 클릭</li>
                    </ol>
                </div>

                <div class="warning-box">
                    <h6><i class="bi bi-exclamation-triangle text-warning"></i> 주의사항</h6>
                    <ul class="mb-0">
                        <li><strong>품목코드에는 공백이 없게 작성</strong>해주세요</li>
                        <li>단가연동시 공백에 따른 연동 오류가 발생할 수 있습니다</li>
                        <li>특별히 주의해서 작성해주세요!</li>
                    </ul>
                </div>

                <div class="guide-step">
                    <h5><i class="bi bi-2-circle text-success"></i> 연동 원리</h5>
                    <ul>
                        <li>기존의 단가테이블은 <strong>유지</strong>됩니다</li>
                        <li>새로운 단가가 <strong>우선시</strong>되어 적용됩니다</li>
                        <li>수주리스트에서 해당 항목을 선택하면 <strong>최신 단가가 자동으로 연동</strong>됩니다</li>
                        <li>새로운 날짜가 항상 단가조회시 <strong>우선시</strong>됩니다</li>
                    </ul>
                </div>

                <div class="text-center mt-4">
                    <button type="button" class="btn btn-primary" onclick="window.close()">
                        <i class="bi bi-check-circle"></i> 확인했습니다
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 