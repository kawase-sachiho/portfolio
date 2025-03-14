<?php
try {
    session_start();
    session_regenerate_id();

    require_once('../class/db/Safety.php');
    require_once('../class/db/Base.php');
    require_once('../class/Common.php');
    require_once('../class/Patients.php');
    require_once('../class/SelectStaff.php');

    if (empty($_SESSION['user'])) {
        header('Location:../login/index.php');
        exit;
    }

    //データベースへ接続する
    $pdo = Base::getInstance();
    $token = Safety::generateToken();

    //ユーザー名をidから取得する

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    } elseif (isset($_SESSION['patient']['reservation_days'])) {
        $id = $_SESSION['patient']['id'];
        $reservation_days = $_SESSION['patient']['reservation_days'];
    }
    //患者情報を表示するメソッド
    $patient_info = new Patients($pdo);
    $patient = $patient_info->showPatientInfo($id);

    //担当PTの獲得
    $job = 0;
    $base_id = $patient['pt_base_id'];
    $pt_info = new SelectStaff($pdo);
    $pt = $pt_info->getBaseStaffInfo(
        $base_id,
        $job
    );

    //担当OTの獲得
    $job = 1;
    $base_id = $patient['ot_base_id'];
    $ot_info = new SelectStaff($pdo);
    $ot = $ot_info->getBaseStaffInfo(
        $base_id,
        $job
    );

    //担当STの獲得
    $job = 2;
    $base_id = $patient['st_base_id'];
    $st_info = new SelectStaff($pdo);
    $st = $st_info->getBaseStaffInfo(
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
    <title>患者データの削除
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>
<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>患者データの削除</u></h1>
        <div class="text row  my-3 text-primary font-weight-bold">
            <?php if (isset($_SESSION['patient']['reservation_days'])) {
                foreach ($reservation_days as $reservation_day) {
                    Date::showDate($reservation_day) . " ";
                }
                echo "の予約を取り消してください。";
            } ?>
        </div>
        <div class="text row">
            <div class="col-2">患者氏名</div>
            <div class="col-4 font-weight-bold"><?= $patient['patient_name'] ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">性別の希望</div>
            <div class="col-4"><?php Info::showHopeGender($patient); ?></div>
            <div class="col-3">担当者の経験年数</div>
            <div class="col-3"><?php Info::needExperience($patient) ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">急変リスク</div>
            <div class="col-4"><?php Info::showEmergencyRisk($patient) ?></div>
            <div class="col-3">リハビリ開始日</div>
            <div class="col-3"><?php Date::showDate($patient['started_date']); ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">担当PT</div>
            <div class="col-4"><?= $pt['staff_name'] ?></div>
            <div class="col-2">PT単位数</div>
            <div class="col-4"><?= $patient['pt_base_num'] ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">担当OT</div>
            <div class="col-4"><?= $ot['staff_name'] ?></div>
            <div class="col-2">OT単位数</div>
            <div class="col-4"><?= $patient['ot_base_num'] ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">担当ST</div>
            <div class="col-4"><?= $st['staff_name'] ?></div>
            <div class="col-2">ST単位数</div>
            <div class="col-4"><?= $patient['st_base_num'] ?></div>
        </div>
        <form method="post" id="delete_form" onSubmit="return checkDelete()" action="./delete_action.php">
            <div class="row my-5">
                <div class="text col-6 text-danger font-weight-bold"><u>上記の患者のデータを削除します。</u></div>
                <a class="text col-4 text-primary" href="./index.php"><u>戻る</u></a>
                <input type="hidden" name="token" value="<?= $token ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <button type="submit" class="btn btn-lg btn-danger text-white col-auto">削除</button>
            </div>
        </form>

        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
    <script>
        'use strict';
        document.getElementById('delete_form').onsubmit = function checkDelete() {
            var result = window.confirm('患者のデータを削除します。本当によろしいですか？');
            if (result) {} else {
                alert('削除を中止しました。');
                return false;
            }
        };
    </script>
</body>

</html>