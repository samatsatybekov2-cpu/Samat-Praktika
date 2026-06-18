<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (!$email || !$pass) { echo json_encode(['success'=>false,'message'=>'Заполните все поля']); exit; }
        $u = getUserByEmail($email);
        if (!$u || !password_verify($pass, $u['password_hash'])) { echo json_encode(['success'=>false,'message'=>'Неверный email или пароль']); exit; }
        $_SESSION['uid']  = $u['id'];
        $_SESSION['name'] = $u['name'];
        $_SESSION['role'] = $u['role'];
        echo json_encode(['success'=>true,'user'=>['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']]]);
        break;

    case 'register':
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $conf  = $_POST['confirm']  ?? '';
        if (!$name || !$email || !$pass) { echo json_encode(['success'=>false,'message'=>'Заполните все поля']); exit; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false,'message'=>'Некорректный email']); exit; }
        if (strlen($pass) < 6) { echo json_encode(['success'=>false,'message'=>'Пароль минимум 6 символов']); exit; }
        if ($pass !== $conf) { echo json_encode(['success'=>false,'message'=>'Пароли не совпадают']); exit; }
        if (getUserByEmail($email)) { echo json_encode(['success'=>false,'message'=>'Email уже зарегистрирован']); exit; }
        $id = createUser($name, $email, $pass);
        $_SESSION['uid']  = $id;
        $_SESSION['name'] = $name;
        $_SESSION['role'] = 'user';
        echo json_encode(['success'=>true,'user'=>['id'=>$id,'name'=>$name,'role'=>'user']]);
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success'=>true]);
        break;

    case 'check':
        if (isLoggedIn()) {
            $u = currentUser();
            echo json_encode(['logged'=>true,'user'=>['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']]]);
        } else {
            echo json_encode(['logged'=>false]);
        }
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Unknown action']);
}