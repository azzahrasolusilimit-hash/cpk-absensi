
<?php

require_once __DIR__ . '/../../src/controllers/AuthController.php';

header('Content-Type: application/json; charset=utf-8');

// Pengaturan session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Mengambil aksi dari URL
$action = strtolower($_GET['action'] ?? '');

// Menjalankan aksi berdasarkan request
switch ($action) {

    case 'register':
        AuthController::register();
        break;

    case 'login':
        AuthController::login();
        break;

    case 'me':
        AuthController::me();
        break;

    case 'logout':
        AuthController::logout();
        break;

    default:
        jsonResponse([
            'success' => false,
            'message' => 'Endpoint tidak ditemukan.',
            'available_actions' => [
                'register',
                'login',
                'me',
                'logout'
            ]
        ], 404);
        break;
}
