<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['success'=>false,'auth'=>true,'message'=>'Требуется авторизация']); exit; }
$uid    = (int)$_SESSION['uid'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'get';
switch ($action) {
    case 'get':
        echo json_encode(['success'=>true,'items'=>getWishlist($uid),'count'=>wishCount($uid),'ids'=>getWishlistProductIds($uid)]);
        break;
    case 'toggle':
        $pid = (int)($_POST['product_id'] ?? 0);
        if (!$pid) { echo json_encode(['success'=>false,'message'=>'Неверный товар']); exit; }
        $added = wishToggle($uid, $pid);
        echo json_encode(['success'=>true,'added'=>$added,'count'=>wishCount($uid),'product_id'=>$pid]);
        break;
    case 'ids':
        echo json_encode(['success'=>true,'ids'=>getWishlistProductIds($uid),'cart_ids'=>getCartProductIds($uid)]);
        break;
    default:
        echo json_encode(['success'=>false,'message'=>'Unknown']);
}