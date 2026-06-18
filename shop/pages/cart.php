<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success'=>false,'auth'=>true,'message'=>'Требуется авторизация']); exit; }
$uid    = (int)$_SESSION['uid'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        $items = getCart($uid);
        echo json_encode(['success'=>true,'items'=>$items,'total'=>cartTotal($uid),'count'=>cartCount($uid)]);
        break;
    case 'add':
        $pid = (int)($_POST['product_id']??0);
        $qty = max(1,(int)($_POST['qty']??1));
        $p   = getProductById($pid);
        if (!$p) { echo json_encode(['success'=>false,'message'=>'Товар не найден']); exit; }
        cartAdd($uid, $pid, $qty);
        echo json_encode(['success'=>true,'message'=>'Добавлено в корзину','count'=>cartCount($uid)]);
        break;
    case 'update':
        cartUpdate($uid,(int)$_POST['product_id'],(int)$_POST['qty']);
        echo json_encode(['success'=>true,'total'=>cartTotal($uid),'count'=>cartCount($uid)]);
        break;
    case 'remove':
        cartRemove($uid,(int)$_POST['product_id']);
        echo json_encode(['success'=>true,'total'=>cartTotal($uid),'count'=>cartCount($uid)]);
        break;
    case 'checkout':
        $items = getCart($uid);
        if (!$items) { echo json_encode(['success'=>false,'message'=>'Корзина пуста']); exit; }
        $total   = cartTotal($uid);
        $balance = getBalance($uid);
        if ($balance < $total) { echo json_encode(['success'=>false,'message'=>'Недостаточно средств. Пополните баланс.','need'=>round($total-$balance,2)]); exit; }
        $address = trim($_POST['address'] ?? 'Самовывоз');
        $oi = array_map(function($i){ return ['product_id'=>$i['product_id'],'quantity'=>$i['quantity'],'price'=>$i['price']]; }, $items);
        $oid = createOrder($uid, $address, $oi, $total);
        chargeBalance($uid, $total, 'Заказ #'.$oid);
        cartClear($uid);
        echo json_encode(['success'=>true,'message'=>'Заказ #'.$oid.' оформлен!','order_id'=>$oid]);
        break;
    default:
        echo json_encode(['success'=>false,'message'=>'Unknown']);
}