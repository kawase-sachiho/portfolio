<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/SelectStaff.php');
require_once('../class/CountUnit.php');
require_once('../class/Table.php');

//ログイン状態のチェック
if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
$pdo = Base::getInstance();
$token = Safety::generateToken();
$day = Date::getDate();

//日付の値が未選択かつセッションにも値がない場合はリダイレクトする
if (!isset($_POST['reservation_date']) && !isset($_SESSION['select']['reservation_date'])) {
    $_SESSION['err']['msg'] = "日付を選択して下さい";
    header('Location:./index.php');
    return;
}
//セッションまたはPOSTから値を代入する
if (isset($_SESSION['select'])) {
    $reservation_date = $_SESSION['select']['reservation_date'];
    $job = $_SESSION['select']['job'];
} else {
    $reservation_date = $_POST['reservation_date'];
    $job = $_POST['job'];
}
//セッションに患者のidがあれば破棄する
if (isset($_SESSION['select']['patient_id'])) {
    unset($_SESSION['select']['patient_id']);
}
//セッションにスタッフの名前のデータ(select_completeから取得)があれば代入
if (isset($_SESSION['select']['staff_name'])) {
    $staff_names = $_SESSION['select']['staff_name'];
}
//セッションに患者の名前のデータ(select_completeから取得)があれば代入
if (isset($_SESSION['select']['patient_name'])) {
    $patient_names = $_SESSION['select']['patient_name'];
}
//勤務テーブルから職種、出勤人数を獲得
$workers_num = new SelectStaff($pdo);
$working_staffs = $workers_num->countStaffByRihaDate(
    $reservation_date,
    $job
);
//出勤者が0の場合、リダイレクトする
if ($working_staffs['count'] == 0) {
    $_SESSION['err']['msg'] = "出勤スタッフが登録されていません";
    header('Location:./index.php');
    return;
}
//出勤者がいる場合は、患者の情報を呼び出す
$patients = new CountUnit($pdo);
$patient_lists = $patients->getPatientsByRihaDate($reservation_date);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>患者の選択</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">

</head>

<body>

    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>患者の選択</u></h1>
        <div class="text row my-3">
            <div class="col-auto font-weight-bold text-danger"><?php echo Date::showDate($reservation_date); ?></div>
            <div class="col-auto font-weight-bold text-danger"><?php Info::showJob($job); ?></div>
            <div class="col-auto font-weight-bold">の担当者調整</div>
        </div>
        <div class="text text-primary font-weight-bold mb-3">
            <?php if (!empty($_SESSION['select']['staff_name'])) {
                foreach ($staff_names as $staff_name) {
                    echo $staff_name . "さん ";
                }
                echo "の担当患者が0になっています。";
            } ?></div>
        <div class="text text-primary font-weight-bold">
            <?php if (!empty($_SESSION['select']['patient_name'])) {
                foreach ($patient_names as $patient_name) {
                    echo $patient_name . "さん ";
                }
                echo "の担当者が未登録です。";
            } ?></div>
        <form method="post" action="./select_staff.php">
            <input type="hidden" name="token" value="<?= $token ?>">
            <input type="hidden" name="reservation_date" value="<?= $reservation_date ?>">
            <input type="hidden" name="job" value="<?= $job ?>">
            <div class="row my-3">
                <div class="text col-auto font-weight-bold"><label class="control-label" for="id">患者氏名を選択してください</label></div>
                <div class="col-auto"><select name="patient_id" id="id" class="text form-control input-md" type="select">
                        <?php foreach ($patient_lists as $patient_list) {
                            echo '<option value="' . $patient_list['patient_id'] . '">' . $patient_list['patient_name'] . '</option>';
                            continue;
                        } ?>
                    </select></div>
                <div class="col-auto"><button type="submit" class="btn btn-danger btn-lg text-white ml-4">選択</button></div>
            </div>
        </form>

        <?php
        $today_staff_numbers = new Table($pdo);
        $staffs = $today_staff_numbers->getTodayNumbersByStaff($job, $reservation_date);

        if (isset($staffs)) : ?>
            <div class="scope-row">
                <div class="table-responsive mr-0 pr-0" style="display: inline-block;">
                    <table class="table table-bordered">
                        <form method="post" action="./select_delete.php">
                            <input type="hidden" name="token" value="<?= $token ?>">
                            <input type="hidden" name="reservation_date" value="<?= $reservation_date ?>">
                            <input type="hidden" name="job" value="<?= $job ?>">
                            <tbody>
                                <tr class="table-primary">
                                    <th class="col-auto bg-warning">スタッフ氏名</th>
                                    <td>
                                        患者氏名
                                    </td>
                                    <td>
                                        合計単位
                                    </td>
                                </tr>
                                <?php foreach ($staffs as $staff) : ?>
                                    <tr>
                                        <td class="col-auto"><?= $staff['staff_name'] ?></td>
                                        <td class="col-auto">
                                            <?php if (isset($staff['patient_id1'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id1'] ?>"><?php endif ?>
                                            <?= $staff['patient_name1'] . " " . $staff['today_staff_num1'] . " "; ?>
                                            <?php if (isset($staff['patient_id2'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id2'] ?>"><?php endif ?>
                                            <?= $staff['patient_name2'] . " " . $staff['today_staff_num2'] . " "; ?>
                                            <?php if (isset($staff['patient_id3'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id3'] ?>"><?php endif ?>
                                            <?= $staff['patient_name3'] . " " . $staff['today_staff_num3'] . " "; ?>
                                            <?php if (isset($staff['patient_id4'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id4'] ?>"><?php endif ?>
                                            <?= $staff['patient_name4'] . " " . $staff['today_staff_num4'] . " "; ?>
                                            <?php if (isset($staff['patient_id5'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id5'] ?>"><?php endif ?>
                                            <?= $staff['patient_name5'] . " " . $staff['today_staff_num5'] . " "; ?>
                                            <?php if (isset($staff['patient_id6'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id6'] ?>"><?php endif ?>
                                            <?= $staff['patient_name6'] . " " . $staff['today_staff_num6'] . " "; ?>
                                            <?php if (isset($staff['patient_id7'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id7'] ?>"><?php endif ?>
                                            <?= $staff['patient_name7'] . " " . $staff['today_staff_num7'] . " "; ?>
                                            <?php if (isset($staff['patient_id8'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id8'] ?>"><?php endif ?>
                                            <?= $staff['patient_name8'] . " " . $staff['today_staff_num8'] . " "; ?>
                                            <?php if (isset($staff['patient_id9'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id9'] ?>"><?php endif ?>
                                            <?= $staff['patient_name9'] . " " . $staff['today_staff_num9'] . " "; ?>
                                            <?php if (isset($staff['patient_id10'])): ?><input type="checkbox" name="id[]" value="<?= $staff['patient_id10'] ?>"><?php endif ?>
                                            <?= $staff['patient_name10'] . "" . $staff['today_staff_num10'] . " "; ?>

                                        </td>
                                        <td class="col-auto">
                                            <?= $staff['SUM(tmp.today_staff_num)'] ?>
                                        </td>
                                        <td class="col-1" style="border:none;">
                                            <button type="submit" class="btn btn-danger">取消</button>

                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </form>
                    </table>
                </div>
            <?php endif ?>

            <div class="scope-row mt-3" style="display:flex;">
                <form method="post" action="./select_complete.php">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="reservation_date" value="<?= $reservation_date ?>">
                    <input type="hidden" name="job" value="<?= $job ?>">
                    <div class="col-auto text-right" style="display:inline-block;">
                        <button type="submit" class="btn btn-primary btn-lg text-left" style="text-decoration:none;">調整を終了する</button>
                    </div>
                </form>
                <div class="font-weight-bold text-primary"><br>
                    <?php if (isset($_SESSION['select']['msg'])) {
                        echo $_SESSION['select']['msg'];
                    } ?></div>

            </div>
            <div class="mt-2 mb-5 ml-2 text-right">
                <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
                <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
            </div>
            </div>
</body>

</html>