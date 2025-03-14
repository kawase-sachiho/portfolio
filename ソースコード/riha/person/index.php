<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');

//ログイン状態のチェック
if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
$pdo = Base::getInstance();
$token = Safety::generateToken();
//本日の日付を取得し$dayに入れる
$day = Date::getDate();

unset($_SESSION['select']);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>担当者の調整</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">

</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>担当スタッフの調整</u></h1>
        <div class="font-weight-bold text-danger mb-5">
            <u><?php if (isset($_SESSION['err']['msg'])) {
                    echo "！" . $_SESSION['err']['msg'] . "！";
                } ?>
            </u>
        </div>
        <form method="post" action="./select_patient.php">
            <input type="hidden" name="token" value="<?= $token ?>">
            <div class="row my-3">
                <label class="text control-label col-3 font-weight-bold" for="reservation_date">日付を選択してください</label>
                <input name="reservation_date" id="reservation_date" class="form-control input-md col-5" type="date">
            </div>
            <div class="row my-3">
                <label class="text control-label col-3 font-weight-bold" for="job">職種を選択してください</label>

                <label class="text radio-inline" for="job[0]">
                    <input name="job" id="job[0]" value="0" checked="checked" type="radio">
                    <span style="margin-right:4em;">PT</span>
                </label>
                <label class="text radio-inline" for="job[1]">
                    <input name="job" id="job[1]" value="1" type="radio">
                    <span style="margin-right:4em;">OT</span>
                </label>
                <label class="text radio-inline" for="job[2]">
                    <input name="job" id="job[2]" value="2" type="radio">
                    ST
                </label>
            </div>
            <div class="row my-3">
                <div class="col-3"></div>
                <div class="col-5"></div>
                <button type="submit" class="btn btn-danger btn-lg text-white">決定</button>
            </div>
        </form>
        <div class="mt-2 mb-5 ml-2 text-right">
        <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
        <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>

</html>