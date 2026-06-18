<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация', 'auth' => true]);
    exit;
}

$uid    = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $orders = getOrders($uid);
        foreach ($orders as &$o) {
            $o['items'] = getOrderItems($o['id']);
        }
        echo json_encode(['success' => true, 'orders' => $orders]);
        break;

    case 'detail':
        $oid   = (int)($_GET['id'] ?? 0);
        $order = getOrderById($oid, $uid);
        if (!$order) { echo json_encode(['success' => false, 'message' => 'Заказ не найден']); exit; }
        $order['items'] = getOrderItems($oid);
        echo json_encode(['success' => true, 'order' => $order]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}