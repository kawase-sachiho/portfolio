<?php
session_start();
session_regenerate_id();
require_once('./class/db/Base.php');
require_once('./class/db/Safety.php');
//ログイン状態のチェック
if (empty($_SESSION['user'])) {
    header('Location:./login/index.php');
    exit;
}
unset($_SESSION['err']['msg']);
unset($_SESSION['patient']);
unset($_SESSION['staff']);
unset($_SESSION['select']);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>リハビリ単位調整アプリ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="./class/css/style.css">
</head>

<body>
    <div class="container">
        <h1 class="mt-5 mb-3 text-center text-warning"><u>リハビリ単位調整アプリ</u></h1>
        <div class="row mt-5 px-1">
            <a class="btn menu btn-lg  btn-primary col-md-5 my-2  text-center text-white" style="text-decoration:none;" href="./work/index.php">出勤管理</a>
            <div class="box-1 col-md-2"></div>
            <a class="btn menu btn-primary btn-lg col-md-5 my-2 text-center text-white" style="text-decoration:none;" href="./table/today.php">介入表の確認</a>
            <a class="btn menu btn-primary btn-lg col-md-5 my-2  text-center text-white" style="text-decoration:none;" href="./count/index.php">単位数の調整</a>
            <div class="box-1 col-md-2"></div>
            <a class="btn menu btn-primary btn-lg col-md-5 my-2 text-center text-white" style="text-decoration:none;" href="./patient/index.php">患者登録</a>
            <a class="btn menu btn-primary btn-lg col-md-5 my-2 text-center text-white" style="text-decoration:none;" href="./person/index.php">担当者の調整</a>
            <div class="box-1 col-md-2"></div>
            <a class="btn menu btn-primary btn-lg col-md-5 my-2 text-center text-white" style="text-decoration:none;" href="./staff/index.php">スタッフ登録</a>
        </div>
        <div class="row mt-5">
            <div class="manual text-left">
                <h5 class="font-weight-bold">単位調整の手順を確認する</h5>
                <ol class="hidden">
                    <li>出勤管理画面で、PT・OT・STの出勤スタッフを登録する</li>
                    <li>単位数の調整画面に移動し、必要な単位数をPT・OT・ST間で調整する</li>
                    <li>担当者の調整画面で、患者ごとに担当するスタッフを割り当てる(※スタッフ1名につき18単位)</li>
                    <li>介入表の確認画面にて、最終確認する</li>
                </ol>
            </div>
        </div>
        <div class="text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-lg btn-danger mr-3" href="./user/edit.php">登録情報の変更</a>
            <a class="btn btn-lg btn-warning " href="./login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script>
        'use strict';
        $(document).ready(function() {
            $('.manual h5').on('click', function() {
                $(this).next().slideToggle('hidden');
            });
        });
    </script>
</body>

</html>