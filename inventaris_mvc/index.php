<?php
declare(strict_types=1);

/**
 * Front controller / pintu masuk aplikasi.
 * Buka:  http://localhost/inventaris_mvc/
 */
session_start();
ini_set('display_errors', '0');          // jangan tampilkan error PHP ke pengguna
error_reporting(E_ALL);

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require __DIR__ . '/config/database.php';
require __DIR__ . '/models/Barang.php';
require __DIR__ . '/controllers/BarangController.php';

try {
    $controller = new BarangController(Database::connect());
    $controller->index();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Koneksi Gagal</title>'
       . '<link rel="stylesheet" href="assets/style.css"></head><body class="error-page"><div class="error-box">'
       . '<h1>Database tidak dapat diakses</h1>'
       . '<p>Pastikan MySQL sudah berjalan, database <b>db_inventaris</b> sudah diimpor dari '
       . '<code>database/db_inventaris.sql</code>, dan user/password di <code>config/database.php</code> benar.</p>'
       . '</div></body></html>';
}
