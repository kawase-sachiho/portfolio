<?php
try {
    session_start();
    session_regenerate_id();

    require_once('../class/db/Safety.php');
    require_once('../class/db/Base.php');
    require_once('../class/Common.php');
    require_once('../class/Staffs.php');

    //ログイン状態のチェック
    if (empty($_SESSION['user'])) {
        header('Location:../login/index.php');
        exit;
    }
    //データベースへ接続する
    $pdo = Base::getInstance();
    $token = Safety::generateToken();

    //ユーザー名をidから取得する
    //idの値が送られていれば代入
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    } elseif (isset($_SESSION['staff']['patient'])) {
        //担当患者の名前がセッションにあれば代入 
        $id = $_SESSION['staff']['id'];
        $patient_names = $_SESSION['staff']['patient'];
    } elseif (isset($_SESSION['staff']['reservation_days'])) {
        //リハビリ予約があればセッションに代入
        unset($_SESSION['staff']['patient']);
        unset($_SESSION['staff']['working_days']);
        $id = $_SESSION['staff']['id'];
        $reservation_days = $_SESSION['staff']['reservation_days'];
    } elseif (isset($_SESSION['staff']['working_days'])) {
        //出勤の予約があればセッションに代入
        unset($_SESSION['staff']['patient']);
        unset($_SESSION['staff']['reservation_days']);
        $id = $_SESSION['staff']['id'];
        $working_days = $_SESSION['staff']['working_days'];
    }
    //スタッフ情報の表示
    $delete_staff_info = new Staffs($pdo);
    $staff = $delete_staff_info->getStaffInfoById($id);
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
    <title>スタッフデータの削除
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>スタッフデータの削除</u></h1>
        <div class="text row  my-3 text-primary font-weight-bold">
            <?php if (isset($_SESSION['staff']['patient'])) {
                echo "削除する前に、";
                foreach ($patient_names as $patient_name) {

                    echo $patient_name . "さん ";
                }
                echo "の担当者を変更してください！";
            } ?>
            <?php if (isset($_SESSION['staff']['reservation_days'])) {
                foreach ($reservation_days as $reservation_day) {
                    echo Date::showDate($reservation_day) . " ";
                }
                echo "の予約を取り消してください。";
            } ?> <?php if (isset($_SESSION['staff']['working_days'])) {
                        foreach ($working_days as $working_day) {
                            echo Date::showDate($working_day) . " ";
                        }
                        echo "の出勤を取り消してください。";
                    } ?>
        </div>
        <div class="text row">
            <div class="col-2 font-weight-bold">スタッフ氏名</div>
            <div class="col-4 font-weight-bold"><?= $staff['staff_name'] ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">性別</div>
            <div class="col-4"><?php Info::getGender($staff); ?></div>
            <div class="col-2">職種</div>
            <div class="col-4"><?php Info::showJob($staff['job']); ?></div>
        </div>
        <div class="text row my-3">
            <div class="col-2">勤務開始日</div>
            <div class="col-4"><?php Date::showDate($staff['job_started_date']); ?></div>
            <div class="col-2">急変時の対応</div>
            <div class="col-4"><?php Info::getEmegency_skill($staff); ?></div>
        </div>
        <form method="post" id="delete_form" onSubmit="return checkDelete()" action="./delete_action.php">
            <div class="row my-5">
                <div class="text col-6 text-danger font-weight-bold"><u>上記のスタッフのデータを削除します。</u></div>
                <a class="text col-4 text-primary" href="./index.php"><u>戻る</u></a>
                <input type="hidden" name="token" value="<?= $token ?>">
                <input type="hidden" name="id" id="delete_id" value="<?= $staff['id'] ?>">
                <button type="submit" class="btn btn-danger btn-lg text-white col-auto">削除</button>
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
            var result = window.confirm('スタッフのデータを削除します。本当によろしいですか？');
            if (result) {} else {
                alert('削除を中止しました。');
                return false;
            }
        };
    </script>
</body>

</html>