<?php
// Настройки магазина

define('SHOP_NAME',   'SamatShop');
define('SHOP_LETTER', 'S');
define('SHOP_SLOGAN', 'Всё что нужно — здесь');

// База данных

define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    'root');
define('DB_NAME',    'samatshop');
define('DB_CHARSET', 'utf8mb4');

// URL сайта

define('BASE_URL', 'http://shop');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}