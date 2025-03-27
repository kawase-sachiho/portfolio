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
unset($_SESSION['err']['msg']);
unset($_SESSION['patient']['patient_id']);

try{
$pdo = Base::getInstance();
$token = Safety::generateToken();
$day = Date::getDate();

//日付が選択されていないかつ、セッションにデータが保存されていない場合
if (!isset($_SESSION['patient']) && !isset($_POST['working_date'])) {
    $_SESSION['err']['msg'] = "！日付を選択してから単位の調整に進んでください！";
    header('Location:./index.php');
    return;
}
//セッションに値がある場合はセッションから代入、そうでなければPOSTされてきた値を代入する
if (isset($_SESSION['patient'])) {
    $working_date = $_SESSION['patient']['working_date'];
    $pt_adjustment = $_SESSION['patient']['pt_adjustment'];
    $ot_adjustment = $_SESSION['patient']['ot_adjustment'];
    $st_adjustment = $_SESSION['patient']['st_adjustment'];
} else {
    $working_date = $_POST['working_date'];
    $pt_adjustment = $_POST['pt_adjustment'];
    $ot_adjustment = $_POST['ot_adjustment'];
    $st_adjustment = $_POST['st_adjustment'];
    $pt_workers = $_POST['pt_workers'];
    $ot_workers = $_POST['ot_workers'];
    $st_workers = $_POST['st_workers'];
}
//出勤スタッフ０名の時、リダイレクトする
if (!isset($_SESSION['patient']) && $pt_workers + $ot_workers + $st_workers == 0) {
    $_SESSION['err']['msg'] = "！スタッフが一人も出勤していません！";
    header('Location:./index.php');
    return;
} else {
    //予約日とリハビリ開始日に基づいて患者一覧を取得するメソッド
    $patients = new CountUnit($pdo);
    $patient_lists = $patients->getPatientsByRihaDate($working_date);
}
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
    <title>単位調整</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>
<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>単位調整</u></h1>
        <div class="text row my-3">
            <div class="col-auto font-weight-bold">日付</div>
            <div class="col-auto font-weight-bold text-danger"><?php echo Date::showDate($working_date); ?></div>
        </div>
        <form method="post" action="./edit.php">
            <input type="hidden" name="token" value="<?= $token ?>">
            <input type="hidden" name="working_date" value="<?= $working_date ?>">
            <input type="hidden" name="pt_adjustment" value="<?= $pt_adjustment ?>">
            <input type="hidden" name="ot_adjustment" value="<?= $ot_adjustment ?>">
            <input type="hidden" name="st_adjustment" value="<?= $st_adjustment ?>">
            <div class="text row my-3">
                <div class="col-auto font-weight-bold"><label class="control-label" for="id">患者氏名を選択してください</label></div>
                <div class="col-auto"><select name="patient_id" id="id" class="form-control input-md" type="select">
                        <?php foreach ($patient_lists as $patient_list) {
                            echo '<option value="' . $patient_list['patient_id'] . '">' . $patient_list['patient_name'] . '</option>';
                            continue;
                        } ?>
                    </select></div>
                <div class="col-auto"><button type="submit" class="btn btn-danger btn-lg text-white ml-4">選択</button></div>
            </div>
        </form>
        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>

</html>