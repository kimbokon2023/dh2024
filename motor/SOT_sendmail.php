<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'; // 운영 서버에서의 경로

function sendInvoiceEmail($to, $subject, $body, $attachmentPath = null) {
    $mail = new PHPMailer(true);

    try {
        // 서버 설정
        $mail->isSMTP();
        $mail->Host = 'smtp.naver.com'; // 네이버 SMTP 서버 주소
        $mail->SMTPAuth = true;
        $mail->Username = 'coolsalespro@naver.com'; // 네이버 이메일 계정
        $mail->Password = 'rnstks100!!'; // 네이버 이메일 비밀번호
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 또는 PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 465; // 465 또는 587

        // 인코딩 설정
        $mail->CharSet = 'UTF-8';

        // 수신자 설정
        $mail->setFrom('coolsalespro@naver.com', '(주) 대한');
        $mail->addAddress($to);

        // 첨부파일 추가
        if ($attachmentPath) {
            $mail->addAttachment($attachmentPath);
        }

        // 이메일 내용 설정
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        // 읽음 확인 설정
        $mail->addCustomHeader('Disposition-Notification-To', 'coolsalespro@naver.com');
        $mail->addCustomHeader('Return-Receipt-To', 'coolsalespro@naver.com');

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

$pdfPath = $_SERVER['DOCUMENT_ROOT'] . '/pdfs/DH.pdf'; // PDF 파일 경로
sendInvoiceEmail('nobackup2015@naver.com', '메일테스트', '메일보내기 테스트입니다.', $pdfPath);
?>
