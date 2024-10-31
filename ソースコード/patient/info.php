<?php
session_start();
session_regenerate_id();

require_once('../class/db/Safety.php');
require_once('../class/db/Base.php');
require_once('../class/Common.php');
require_once('../class/Patients.php');
require_once('../class/selectStaff.php');

if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}

//データベースへ接続する
$pdo = Base::getInstance();
$token = Safety::generateToken();

//ユーザー名をidから取得する
$id = $_GET['id'];

try {
    //患者情報を取得して表示するメソッド
    $patient_info = new Patients($pdo);
    $patient = $patient_info->showPatientInfo($id);

    //担当PTの獲得
    $job = 0;
    $base_id = $patient['pt_base_id'];
    $pt_info = new SelectStaff($pdo);
    $base_pt = $pt_info->getBaseStaffInfo(
        $base_id,
        $job
    );

    //担当OTの獲得
    $job = 1;
    $base_id = $patient['ot_base_id'];
    $ot_info = new SelectStaff($pdo);
    $base_ot = $ot_info->getBaseStaffInfo(
        $base_id,
        $job
    );

    //担当STの獲得
    $job = 2;
    $base_id = $patient['st_base_id'];
    $st_info = new SelectStaff($pdo);
    $base_st = $st_info->getBaseStaffInfo(
        $base_id,
        $job
    );
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>患者データ確認</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>
<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>患者データ確認</u></h1>
        <div class="row">

        </div>
        <div class="text row">
            <div class="col-2 font-weight-bold">患者氏名</div>
            <div class="col-4 font-weight-bold"><?= $patient['patient_name'] ?>様</div>
        </div>
        <div class="text row my-3">
            <div class="col-2">担当者の性別</div>
            <div class="col-4"><?= Info::showHopeGender($patient) ?></div>
            <div class="col-3">担当スタッフの経験年数</div>
            <div class="col-3"><?= Info::needExperience($patient) ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">急変リスク</div>
            <div class="col-4"><?= Info::showEmergencyRisk($patient) ?></div>
            <div class="col-3">リハビリ開始日</div>
            <div class="col-3"><?= Date::showDate($patient['started_date']) ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">担当PT</div>
            <div class="col-4"><?= $base_pt['staff_name'] ?></div>
            <div class="col-2">単位数</div>
            <div class="col-4"><?= $patient['pt_base_num']; ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">担当OT</div>
            <div class="col-4"><?= $base_ot['staff_name'] ?></div>
            <div class="col-2">単位数</div>
            <div class="col-4"><?= $patient['ot_base_num']; ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">担当ST</div>
            <div class="col-4"><?= $base_st['staff_name'] ?></div>
            <div class="col-2">単位数</div>
            <div class="col-4"><?= $patient['st_base_num']; ?></div>
        </div>
        <div class="text row my-5">
            <div class="col-10"></div>
            <a class="col-auto text-primary" href="./index.php"><u>戻る</u></a>
        </div>

        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>
</html>