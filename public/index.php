<?php
declare(strict_types=1);

require __DIR__ . '/../config/Env.php';
require __DIR__ . '/../src/Database/Db.php';

require __DIR__ . '/../src/Http/Response.php';
require __DIR__ . '/../src/Http/Request.php';

require __DIR__ . '/../src/Repositories/BookRepository.php';
require __DIR__ . '/../src/Controllers/BookController.php';

use App\Config\Env;
use App\Database\Db;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\BookRepository;
use App\Controllers\BookController;

Env::load(__DIR__ . '/../.env');

$pdo = Db::getConnection();
$repo = new BookRepository($pdo);
$controller = new BookController($repo);

$method = Request::method();
$path = Request::path();

// CORS (ha frontend külön hoston fut)
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Headers: Content-Type');
// header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS');
// if ($method === 'OPTIONS') Response::json(['ok' => true]);

// Egyszerű health
if ($path === '/health') {
    Response::json(['ok' => true]);
}

// API routes
// GET    /api/books?search=...
if ($path === '/api/books' && $method === 'GET') {
    $controller->index(Request::query('search'));
}

// POST   /api/books
if ($path === '/api/books' && $method === 'POST') {
    $controller->store(Request::jsonBody());
}

// GET    /api/books/{id}
if (preg_match('#^/api/books/(\d+)$#', $path, $m) && $method === 'GET') {
    $controller->show((int)$m[1]);
}

// PUT    /api/books/{id}
if (preg_match('#^/api/books/(\d+)$#', $path, $m) && $method === 'PUT') {
    $controller->update((int)$m[1], Request::jsonBody());
}

// DELETE /api/books/{id}
if (preg_match('#^/api/books/(\d+)$#', $path, $m) && $method === 'DELETE') {
    $controller->destroy((int)$m[1]);
}

// Kiegészítő: borrow/return
// POST /api/books/{id}/borrow
if (preg_match('#^/api/books/(\d+)/borrow$#', $path, $m) && $method === 'POST') {
    $controller->borrow((int)$m[1]);
}

// POST /api/books/{id}/return
if (preg_match('#^/api/books/(\d+)/return$#', $path, $m) && $method === 'POST') {
    $controller->giveBack((int)$m[1]);
}

Response::error('Not found', 404);
