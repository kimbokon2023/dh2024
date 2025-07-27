<?php
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$loanStartDate = isset($_REQUEST['loanStartDate']) ? $_REQUEST['loanStartDate'] : '';
$bank = isset($_REQUEST['bank']) ? $_REQUEST['bank'] : '';
$loanAmount = isset($_REQUEST['loanAmount']) ? $_REQUEST['loanAmount'] : '';
$content = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';
$interestRate = isset($_REQUEST['interestRate']) ? $_REQUEST['interestRate'] : '';
$interestPaymentDate = isset($_REQUEST['interestPaymentDate']) ? $_REQUEST['interestPaymentDate'] : '';
$memo = isset($_REQUEST['memo']) ? $_REQUEST['memo'] : '';
$crew = isset($_REQUEST['crew']) ? $_REQUEST['crew'] : '';
$crewphone = isset($_REQUEST['crewphone']) ? $_REQUEST['crewphone'] : '';
$loanAccount = isset($_REQUEST['loanAccount']) ? $_REQUEST['loanAccount'] : '';
$interestAccount = isset($_REQUEST['interestAccount']) ? $_REQUEST['interestAccount'] : '';
$maturityDate = isset($_REQUEST['maturityDate']) ? $_REQUEST['maturityDate'] : '';
$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : null;
?>
