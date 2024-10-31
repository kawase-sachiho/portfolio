<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/Patients.php');

if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
unset($_SESSION['patient']);
try{
$pdo = Base::getInstance();
$token = Safety::generateToken();

//本日の日付を取得し$dayに入れる
$day = Date::getDate();

//患者情報を全件取得するメソッド
$all_patients=new Patients($pdo);
$patients = $all_patients->getAllPatients();
}
catch (Exception $e) 
{
    header('Location:../error.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>患者一覧</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">

</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>患者一覧</u></h1>
        <div class="table-responsive">
            <table class="table table-bordered">
                <form>
                    <tbody>
                        <tr class="table-primary">
                            <th class="col-auto">患者氏名</th>
                            <th class="col-auto">担当PT</th>
                            <th class="col-auto">単位</th>
                            <th class="col-auto">担当OT</th>
                            <th class="col-auto">単位</th>
                            <th class="col-auto">担当ST</th>
                            <th class="col-auto">単位</th>
                            <th class="col-auto">合計</th>
                            <td class="col-2 bg-white" style="border:none;"></td>
                        </tr>
                        <?php foreach ($patients as $patient) : ?>
                            <tr>
                                <td class="font-weight-bold"><?= $patient['patient_name'] ?></td>
                                <td><?= $patient['pt_name'] ?></td>
                                <td><?= $patient['pt_base_num'] ?></td>
                                <td><?= $patient['ot_name'] ?></td>
                                <td><?= $patient['ot_base_num'] ?></td>
                                <td><?= $patient['st_name'] ?></td>
                                <td><?= $patient['st_base_num'] ?></td>
                                <td><?= array_sum($patient)-($patient['id']+$patient['pt_base_id']+$patient['ot_base_id']+$patient['st_base_id'])?></td>
                            
                        <td class="col-2 bg-white text-center" style="border:none;">
                            <a class="btn btn-danger btn-sm" style="text-decoration:none;" href="./edit_base.php?id=<?= $patient['id']?>">編集</a>
                            <a class="btn btn-danger btn-sm" style="text-decoration:none;" href="./delete.php?id=<?= $patient['id'] ?>">削除</a>
                            <a class="btn btn-danger btn-sm" style="text-decoration:none;" href="./info.php?id=<?= $patient['id'] ?>">詳細</a>
                        </td>
                        </tr>
                        <?php endforeach ?>
                        <tr>
                            <td style="border:none;">
                                <a class="btn btn-danger text-white btn-lg" href="./add_base.php">患者登録</a>
                            </td>
                        </tr>
                    </tbody>
                </form>
            </table>
        </div>
        <div class="mt-2 mb-5 ml-2 text-right">
        <span class="font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
        <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>

</html>