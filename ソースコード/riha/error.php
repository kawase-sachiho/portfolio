<?php
session_start();
session_regenerate_id();

if (empty($_SESSION['user'])) {
    header('Location:./login/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>リハビリ単位調整アプリ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="/class/css/style.css">
</head>
<body>
    <div class="container">
    <div class="mt-5 mb-2 text-right">
    <a class="btn-warning btn-lg" style="text-decoration:none;" href="./index.php">TOPへ戻る</a>
    </div>
        <h1 class="my-5 text-center text-warning"><u>エラー発生</u></h1>
        <div class="text col-9 font-weight-bold">
        申し訳ございません。エラーが発生しました。
        </div>
        <div class="mt-2 mb-5 ml-2 text-right">
        <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>
</html>