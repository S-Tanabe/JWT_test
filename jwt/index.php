<?php

// index.php

require 'vendor/autoload.php';

use \Firebase\JWT\JWT;

$key = 'secret_key';

$jwt = preg_split('/\s+/', getallheaders()['Authorization'])[1];

// $jwt = preg_split('/\s+/', $_SERVER['HTTP_AUTHORIZATION'])[1];

try {
    $decoded = JWT::decode($jwt, $key, ['HS256']);
    $status = 200;
    $msg = 'ok';
} catch (Exception $e) {
    $status = 401;
    $msg = $e->getMessage();
}

header('Content-Type: application/json');
http_response_code($status);
echo json_encode(['msg' => $msg]);
