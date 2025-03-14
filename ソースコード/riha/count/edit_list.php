<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/CountUnit.php');

//ログインチェック
if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
try {
    $pdo = Base::getInstance();
    $token = Safety::generateToken();

    //本日の日付を取得し$dayに入れる
    $day = Date::getDate();

    //前のページで計算した値を入れると共に、editのページで保存した値を代入したい
    $working_date = $_POST['working_date'];
    $pt_adjustment = $_POST['pt_adjustment'];
    $ot_adjustment = $_POST['ot_adjustment'];
    $st_adjustment = $_POST['st_adjustment'];

    //スタッフ患者テーブルのレコードにある患者の名前を取ってくる
    $patient_names = new CountUnit($pdo);
    $patient_lists = $patient_names->getPatientsUnitUpdated($working_date);

    //スタッフ‐患者テーブルから患者の単位数を取ってくる(PT)
    $job = 0;
    $unit_pt = new CountUnit($pdo);
    $pt_numbers = $unit_pt->getUnitUpdated(
        $job,
        $working_date
    );
    //スタッフ‐患者テーブルから患者の単位数を取ってくる(OT)
    $job = 1;
    $unit_ot = new CountUnit($pdo);
    $ot_numbers = $unit_ot->getUnitUpdated(
        $job,
        $working_date
    );
    //スタッフ‐患者テーブルから患者の単位数を取ってくる(ST)
    $job = 2;
    $unit_st = new CountUnit($pdo);
    $st_numbers = $unit_st->getUnitUpdated(
        $job,
        $working_date
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
    <title>単位調整一覧</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="text-center text-warning"><u>単位調整一覧</u></h1>
        <div class="text row my-3">
            <div class="col-auto font-weight-bold">日付</div>
            <div class="col-auto font-weight-bold text-danger"><?php echo Date::showDate($working_date); ?>
            </div>
        </div>
        <div class="text my-2 font-weight-bold">以下の患者の単位を調整しました。</div>
        <div class="scope-row" style="display: flex;">
            <div class="table-responsive" style="display: inline-block;">
                <table class="table table-bordered">
                    <form>
                        <tbody>
                            <tr class="table-primary">
                                <th class="col-auto bg-warning">患者氏名</th>
                            </tr>
                            <?php foreach ($patient_lists as $patient_list) : ?>
                                <tr>
                                    <td class="col-auto"><?= $patient_list['patient_name'] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </form>
                </table>
            </div>
            <div class="table-responsive" style="display: inline-block;">
                <table class="table table-bordered">
                    <form>
                        <tbody>
                            <tr class="table-primary">
                                <th class="col-auto">PT</th>
                            </tr>
                            <?php foreach ($pt_numbers as $pt_number) : ?>
                                <tr>
                                    <td class="col-auto"><?= $pt_number['today_staff_num'] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </form>
                </table>
            </div>
            <div class="table-responsive" style="display: inline-block;">
                <table class="table table-bordered">
                    <form>
                        <tbody>
                            <tr class="table-primary">
                                <th class="col-auto">OT</th>
                            </tr>
                            <?php foreach ($ot_numbers as $ot_number) : ?>
                                <tr>
                                    <td class="col-auto"><?= $ot_number['today_staff_num'] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </form>
                </table>
            </div>
            <div class="table-responsive" style="display: inline-block;">
                <table class="table table-bordered">
                    <form>
                        <tbody>
                            <tr class="table-primary">
                                <th class="col-auto">ST</th>
                            </tr>
                            <?php foreach ($st_numbers as $st_number) : ?>
                                <tr>
                                    <td class="col-auto"><?= $st_number['today_staff_num'] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </form>
                </table>
            </div>
        </div>
        <div class="mt-3 scope-row" style="display: flex;">
            <form method="post" action="./select.php">
                <input type="hidden" name="token" value="<?= $token ?>">
                <input type="hidden" name="working_date" value="<?= $working_date ?>">
                <input type="hidden" name="pt_adjustment" value="<?= $pt_adjustment ?>">
                <input type="hidden" name="ot_adjustment" value="<?= $ot_adjustment ?>">
                <input type="hidden" name="st_adjustment" value="<?= $st_adjustment ?>">
                <input type="hidden" name="pt_base_num" value="<?= $pt_base_num ?>">
                <input type="hidden" name="ot_base_num" value="<?= $ot_base_num ?>">
                <input type="hidden" name="st_base_num" value="<?= $st_base_num ?>">
                <div class="col-auto" style="display: inline-block;">
                    <button type="submit" class="btn-danger btn-lg" style="text-decoration:none;">単位調整を続ける</button>
                </div>
            </form>
            <div class="col-auto">
                <a href="../person/index.php" class="btn-primary btn-lg" style="text-decoration:none; display: inline-block;">担当スタッフの調整に進む</a>
            </div>
        </div>

        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>

</html>