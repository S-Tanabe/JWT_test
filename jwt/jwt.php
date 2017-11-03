<?php

// client.php

$json = http_request_post(
    'http://localhost/jwt/auth.php', [
    'body' => ['id' => 'foo', 'pass' => 'bar']
]);

$jwt = json_decode($json, true)['jwt'];


$ret = http_request_get(
    'http://localhost/jwt',
    ['headers' =>
      ['Authorization' => 'Bearer '.$jwt]
    ]
);

var_dump($ret);

function http_request_get($url, $args = null)
{
    $header = '';

    if (!empty($args['headers'])) {

        foreach ($args['headers'] as $key => $value) {
            $header .= $key .': '.$value."\r\n";
        }
    }

    $opts['http'] = [
        'method'  => 'GET',
        'header' => $header
    ];
    $context  = stream_context_create($opts);

    return file_get_contents($url, false, $context);
}

function http_request_post($url, $args = null)
{
    $opts['http']['method'] = 'POST';

    $header = '';

    if (!empty($args['headers'])) {

        foreach ($args['headers'] as $key => $value) {
            $header .= $key .': '.$value."\r\n";
        }
    }

    $opts['http']['header'] = $header;

    if (is_array($args['body'])) {
        $body = http_build_query($args['body'], '', '&', PHP_QUERY_RFC3986);
        $opts['http']['header'] .= "\r\nContent-Type: application/x-www-form-urlencoded";
        $opts['http']['content'] = $body;
    } else {
        $opts['http']['content'] = $args['body'];
    }

    $context  = stream_context_create($opts);

    return file_get_contents($url, false, $context);
}

// auth.php
require 'vendor/autoload.php';

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

// index.php

require 'vendor/autoload.php';

$key = 'secret_key';

$jwt = preg_split('/\s+/', $_SERVER['HTTP_AUTHORIZATION'])[1];

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
