<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>불량 분석 보고서</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light text-dark">

  <div class="container py-5">
    <h1 class="mb-4 fw-bold">📊 셔터 및 제어기 불량 분석 보고서</h1>

    <!-- 주요 불량 유형 통계 -->
    <section class="mb-5">
      <h4 class="text-primary">📌 주요 불량 유형 통계</h4>
      <table class="table table-bordered table-striped mt-3">
        <thead class="table-secondary">
          <tr>
            <th>불량 유형</th>
            <th>발생 건수</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>부품불량</td><td>5건</td></tr>
          <tr><td>통신오류</td><td>2건</td></tr>
          <tr><td>프로그램문제</td><td>2건</td></tr>
          <tr><td>작동불량</td><td>1건</td></tr>
          <tr><td>기타</td><td>1건</td></tr>
        </tbody>
      </table>
    </section>

    <!-- 분석 요약 카드 -->
    <section>
      <h4 class="text-primary mb-4">🔍 분석 요약</h4>

      <div class="row g-4">
        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title fw-bold">1. 부품불량 (5건)</h5>
              <p class="card-text">
                제어기 배터리, 모터 출력, 검정색 기판 등 다양한 부품에서 불량이 발생하였습니다.<br>
                ✅ 예방을 위해 <strong>주기적인 점검</strong>과 <strong>예비 부품 확보</strong>가 필요합니다.
              </p>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title fw-bold">2. 통신오류 (2건)</h5>
              <p class="card-text">
                무선 통신 및 신호불량이 다수 발생하였습니다.<br>
                ✅ <strong>전파 간섭</strong> 또는 <strong>배선 상태</strong> 점검 필요. 무선 모듈의 <strong>위치 보정</strong>도 권장됩니다.
              </p>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title fw-bold">3. 프로그램문제 (2건)</h5>
              <p class="card-text">
                버전 업데이트 및 셋팅 관련 문제가 보고되었습니다.<br>
                ✅ 설비별 펌웨어 버전 확인 및 <strong>정기 업데이트 체계</strong> 도입 필요합니다.
              </p>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title fw-bold">4. 작동불량 (1건)</h5>
              <p class="card-text">
                셔터 작동이 아예 되지 않는 사례로, 대부분 전원 또는 설정 문제와 연계됩니다.<br>
                ✅ <strong>전원 상태, 설정값 점검</strong>이 병행되어야 합니다.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 각주 -->
    <footer class="text-muted small mt-5 border-top pt-3">
      ※ 본 보고서는 최근 수집된 일부 불량 데이터를 기준으로 작성되었습니다.<br>
      월별 추세 분석, 위치별 집중 분석 보고가 필요할 경우 별도 요청 바랍니다.
    </footer>
  </div>

</body>
</html>
