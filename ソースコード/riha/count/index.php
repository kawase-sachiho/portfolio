<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/CountUnit.php');

//ログインの確認
if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
try {
    $pdo = Base::getInstance();
    $token = Safety::generateToken();
    $day = Date::getDate();
    unset($_SESSION['patient']);
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
    <title>合計単位一覧</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>
<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>本日の合計単位</u></h1>
        <form method="post" action="./index.php">
            <div class="row my-3">
                <label class="text control-label col-3 font-weight-bold" for="working_date">日付を選択してください</label>
                <input name="working_date" id="working_date" class="form-control input-md col-5" type="date">
                <button type="submit" class="btn btn-danger btn-lg text-white ml-4">表示</button>
            </div>
        </form>

        <?php
        try {
            //日付を送信していない時
            if (empty($_POST['working_date'])) {
                $_SESSION['err']['msg'] = "日付を選択してください";
            } elseif (!empty($_POST['working_date'])) {
                //日付をした後の処理
                $post = Safety::sanitaize($_POST);
                unset($_SESSION['err']['msg']);
                $working_date = $post['working_date'];
                //勤務テーブルから、職種ごとに出勤人数を計算
                $worker_num = new CountUnit($pdo);
                $working_staffs = $worker_num->countWorkingStaff($working_date);
                //出勤スタッフが1人でもいる場合(PT)
                if (!empty($working_staffs[0])) {
                    $pt_workers = $working_staffs[0]['count(staff_id)'];
                } else {
                    $pt_workers = 0;
                }
                //出勤スタッフが1人でもいる場合(OT)
                if (!empty($working_staffs[1])) {
                    $ot_workers = $working_staffs[1]['count(staff_id)'];
                } else {
                    $ot_workers = 0;
                }
                //出勤スタッフが1人でもいる場合(ST)
                if (!empty($working_staffs[2])) {
                    $st_workers = $working_staffs[2]['count(staff_id)'];
                } else {
                    $st_workers = 0;
                }

                //PT・OT・STの合計単位数を取得する
                $sum_number = new CountUnit($pdo);
                $patient_numbers = $sum_number->sumNumbers($working_date);
                //PTの過不足を変数に入れる
                $pt_adjustment = $patient_numbers['sum(pt_base_num)'] - ($pt_workers * 18);
                //OTの過不足を変数に入れる
                $ot_adjustment = $patient_numbers['sum(ot_base_num)'] - ($ot_workers * 18);
                //STの過不足を変数に入れる
                $st_adjustment = $patient_numbers['sum(st_base_num)'] - ($st_workers * 18);
            }
        } catch (Exception $e) {
            header('Location:../error.php');
            exit;
        }
        ?>
        <h4 class="text-center mt-4">
            <span class="text text-danger mr-4">
                <?php
                if (!empty($_POST['working_date'])) {
                    Date::showDate($working_date);
                }
                ?>
            </span>
            合計単位一覧
        </h4>
        <table class="table table-bordered mt-4 text-center">
            <tr class="table-primary">
                <th class="col-2">職種</th>
                <th class="col-2">出勤人数</th>
                <th class="col-2">合計単位数</th>
                <th class="col-3">必要単位数</th>
                <th class="col-3 bg-warning">過不足</th>
            </tr>
            <tr>
                <td>PT</td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $pt_workers;
                    } ?></td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $patient_numbers['sum(pt_base_num)'];
                    } ?></td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $pt_workers * 18;
                    } ?></td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $pt_adjustment;
                    } ?></td>
            </tr>
            <tr>
                <td>OT</td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $ot_workers;
                    } ?></td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $patient_numbers['sum(ot_base_num)'];
                    } ?></td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $ot_workers * 18;
                    } ?></td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $ot_adjustment;
                    } ?></td>
            </tr>
            <tr>
                <td>ST</td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $st_workers;
                    } ?></td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $patient_numbers['sum(st_base_num)'];
                    } ?></td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $st_workers * 18;
                    } ?></td>
                <td><?php if (!empty($_POST['working_date'])) {
                        echo $st_adjustment;
                    } ?></td>
            </tr>
        </table>

        <form method="post" action="./select.php">
            <?php if (!empty($_POST['working_date'])) : ?>
                <input type="hidden" name="token" value="<?= $token ?>">
                <input type="hidden" name="working_date" value="<?= $_POST['working_date'] ?>">
                <input type="hidden" name="pt_adjustment" value="<?= $pt_adjustment ?>">
                <input type="hidden" name="ot_adjustment" value="<?= $ot_adjustment ?>">
                <input type="hidden" name="st_adjustment" value="<?= $st_adjustment ?>">
                <input type="hidden" name="pt_workers" value="<?= $pt_workers ?>">
                <input type="hidden" name="ot_workers" value="<?= $ot_workers ?>">
                <input type="hidden" name="st_workers" value="<?= $st_workers ?>">
            <?php endif ?>
            <div class="text row">
                <button type="submit" class="btn btn-danger text-right btn-lg ml-3">単位数の調整に進む</button>
                <span class="font-weight-bold text-danger ml-4 my-3">
                    <?php if (isset($_SESSION['err']['msg'])) {
                        echo $_SESSION['err']['msg'];
                    } ?></span>
            </div>
        </form>
        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>

</html>