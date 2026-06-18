<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $limit  = min(40, max(1, (int)($_GET['limit']  ?? 16)));
        $offset = max(0,  (int)($_GET['offset'] ?? 0));
        $cat    = isset($_GET['cat']) && $_GET['cat'] ? (int)$_GET['cat'] : null;
        $sort   = $_GET['sort'] ?? 'newest';
        $prods  = getProducts($limit, $offset, $cat, $sort);
        $total  = countProducts($cat);
        echo json_encode(['success'=>true,'products'=>$prods,'total'=>$total]);
        break;

    case 'featured':
        echo json_encode(['success'=>true,'products'=>getFeatured((int)($_GET['limit']??8))]);
        break;

    case 'search':
        $q = trim($_GET['q'] ?? '');
        echo json_encode(['success'=>true,'products'=> $q ? searchProducts($q) : []]);
        break;

    case 'categories':
        echo json_encode(['success'=>true,'categories'=>getCategories()]);
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Unknown action']);
}