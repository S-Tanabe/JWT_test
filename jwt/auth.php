<?php

// auth.php
require './vendor/autoload.php';

use \Firebase\JWT\JWT;

$users = [
    ['id' => 'foo', 'hash' => password_hash('bar', PASSWORD_DEFAULT)],
];
$jwt = '';

if (!isset($_POST['id']) || !isset($_POST['pass'])) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'msg' => 'ユーザー id もしくはパスワードが送信されていません。',
        'jwt' => $jwt
    ]);
    exit();
}

$status = 400;
$msg = 'ユーザー id もしくはパスワードが一致しません。';

foreach ($users as $user) {
    if ($user['id'] === $_POST['id'] && password_verify($_POST['pass'], $user['hash'])) {
        $status = 200;
        $msg = 'ok';
        break;
    }
}

$token = [
  'iss' => 'example.com',
  'name' => $_POST['id'],
  'exp' => time()+3600
];

$key = 'secret_key';
$jwt = JWT::encode($token, $key);

header('Content-Type: application/json');
http_response_code($status);
echo json_encode([
    'msg' => $msg,
    'jwt' => $jwt,
    $_POST
]);

