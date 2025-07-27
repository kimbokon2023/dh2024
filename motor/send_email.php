<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'; // 운영 서버에서의 경로

// Function to clean filename
function cleanFilename($filename) {
    // Remove or replace problematic characters
    $filename = preg_replace('/[\/\\\\:*?"<>|]/u', '_', $filename);
    $filename = preg_replace('/[^\p{L}\p{N}\s\-_\.]/u', '_', $filename); // Only allow letters, numbers, spaces, hyphens, underscores, dots
    $filename = preg_replace('/\s+/', '_', $filename); // Replace multiple spaces with single underscore
    $filename = preg_replace('/_+/', '_', $filename); // Replace multiple underscores with single underscore
    $filename = trim($filename, '._'); // Remove leading/trailing dots and underscores
    $filename = substr($filename, 0, 200); // Limit length to 200 characters
    
    // Ensure filename is not empty
    if (empty($filename)) {
        $filename = 'document_' . date('Y-m-d_H-i-s') . '.pdf';
    }
    
    // Ensure it ends with .pdf
    if (!preg_match('/\.pdf$/i', $filename)) {
        $filename .= '.pdf';
    }
    
    return $filename;
}

header('Content-Type: application/json');
$email = $_POST['email'] ?? '';
$vendorName = $_POST['vendorName'] ?? '';
$filename = $_POST['filename'] ?? '' ; // 전달받은 파일명
$item = $_POST['item'] ?? ''; // 거래명세표, 견적서, 총거래원장 등 선택
$issueDate = $_POST['formattedDate'] ?? ''; // [24.07.30]형태의 날짜
$sendCompany = '(주)대한';
$num = $_POST['num'] ?? '';

function sendInvoiceEmail($to, $vendorName, $sendCompany, $issueDate, $item, $filename = null) {
    $mail = new PHPMailer(true);
    
    $setFrom_email =  'dhm2024@naver.com';  // 보내는 회사이메일
    $email =  'dhm2024@naver.com';  // 보내는 회사이메일 표기되는 것
	$password = 'dh240227@';   // 대한 메일 패스워드
    $setFrom_company =  '(주)대한';  // 보내는 회사 
    $phone = '010-3966-2024';    	

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.naver.com';
        $mail->SMTPAuth = true;
        $mail->Username = $setFrom_email;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom($setFrom_email, $setFrom_company);
        $mail->addAddress($to);
        
        // 첨부파일 추가 (필수)
        if ($filename) {
            // Clean filename for file path
            $cleanFilename = cleanFilename($filename);
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/pdfs/' . $cleanFilename; // 파일 경로
            if (file_exists($filePath)) {
                $mail->addAttachment($filePath);
            } else {
                return false; // PDF 파일이 없으면 이메일 전송 실패
            }
        }       

        $mail->isHTML(true);
        $mail->Subject = '[' . $sendCompany . '](으)로부터 [' . $item . '](이)가 도착했습니다. ' . $issueDate;

        // HTML 내용 작성
        $mail->Body    = '
        <html>
        <body>
            <p>수신 : ' . $vendorName . '</p> <br>
            <p>' . $sendCompany . '(으)로부터 ' . $item . '(이)가 도착하였습니다.</p>
            <br>
            <table border="1" style="border-collapse: collapse; width: 100%;">
                <tr>
                    <th style="padding: 8px; text-align: left;">보낸회사</th>
                    <td style="padding: 8px;">' . $sendCompany . '</td>
                    <th style="padding: 8px; text-align: left;">발행일자</th>
                    <td style="padding: 8px;">' . $issueDate . '</td>
                </tr>
                <tr>
                    <th style="padding: 8px; text-align: left;">Email</th>
                    <td style="padding: 8px;">' . $email . '</td>
                    <th style="padding: 8px; text-align: left;">연락처</th>
                    <td style="padding: 8px;">' . $phone . '</td>
                </tr>
                <tr>
                    <th style="padding: 8px; text-align: left;">메모</th>
                    <td style="padding: 8px;" colspan="3"></td>
                </tr>
            </table>
        </body>
        </html>';

        $mail->AltBody = '수신 : ' . $vendorName . '\n' . $sendCompany . '(으)로부터 ' . $item . '(이)가 도착하였습니다.\n보낸회사: ' . $sendCompany . '\n발행일자: ' . $issueDate . '\nEmail: dhm2024@naver.com\n연락처: 010-3966-2024\n메모:';

        // 읽음 확인 설정
        $mail->addCustomHeader('Disposition-Notification-To', $setFrom_email);
        $mail->addCustomHeader('Return-Receipt-To', $setFrom_email);

        $mail->send();
        return true; // 메일 전송 성공 시 true 반환
    } catch (Exception $e) {
        return false; // 메일 전송 실패 시 false 반환
    }
}

$result = sendInvoiceEmail($email, $vendorName, $sendCompany, $issueDate, $item, $filename);

if ($result) {
    if($item == '거래명세표') {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
        $pdo = db_connect();
        $sql = "UPDATE {$DB}.motor SET statement_sent_at = NOW() WHERE num = '$num'";
        $stmh = $pdo->prepare($sql);
        $stmh->execute();
    }
    echo json_encode(['success' => '메일이 전송되었습니다.']);
} else {
    echo json_encode(['error' => '메일 전송에 실패했습니다.']);
}

?>
