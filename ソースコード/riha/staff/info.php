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
    $id = $_GET['id'];
    //スタッフ情報を表示するメソッド
    $staff_info = new Staffs($pdo);
    $staff = $staff_info->getStaffInfoById($id);
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
    <title>スタッフデータ確認</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>スタッフデータ確認</u></h1>
        <div class="row">

        </div>
        <div class="text row">
            <div class="text col-2 font-weight-bold">スタッフ氏名</div>
            <div class="text col-4 font-weight-bold"><?= $staff['staff_name'] ?></div>
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
        <div class="row my-5">
            <div class="col-10"></div>
            <a class="text col-auto text-primary" href="./index.php"><u>戻る</u></a>
        </div>

        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>

</html>