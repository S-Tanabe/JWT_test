<?php

echo $_POST['id'].'<br>';
echo $_POST['pass'].'<br>';
if (! isset($_POST['id']) || ! isset($_POST['pass'])) {
    return view();
}

// client.php

$json = http_request_post(
    'http://localhost/html/jwt/auth.php', [
    'body' => ['id' => $_POST['id'], 'pass' => $_POST['pass']]
    // 'body' => ['id' => 'foo', 'pass' => 'bar']
]);

$jwt = json_decode($json, true)['jwt'];


$ret = http_request_get(
    'http://localhost/html/jwt',
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

function view() {
    echo '
<html>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
  <header></header>
  <body style="margin: 24px;">
    <form action="client.php" method="post">
      ID: <input name="id"><br><br>
      PASS: <input name="pass"><br><br>
      <button type="submit" value="送信" class="btn btn-primary">送信</button> 
    </form>
  </body>
</html>'
;
}
