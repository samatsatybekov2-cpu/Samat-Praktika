<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['success'=>false,'auth'=>true]); exit; }
$uid    = (int)$_SESSION['uid'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'get';
switch ($action) {
    case 'get':
        echo json_encode(['success'=>true,'balance'=>getBalance($uid)]);
        break;
    case 'topup':
        $amount = floatval($_POST['amount']??0);
        if ($amount <= 0 || $amount > 9999999) { echo json_encode(['success'=>false,'message'=>'Некорректная сумма']); exit; }
        topupBalance($uid, $amount);
        echo json_encode(['success'=>true,'balance'=>getBalance($uid),'message'=>'Баланс пополнен!']);
        break;
    case 'log':
        echo json_encode(['success'=>true,'log'=>getBalanceLog($uid)]);
        break;
    default:
        echo json_encode(['success'=>false]);
}