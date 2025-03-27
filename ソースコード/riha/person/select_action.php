<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/SelectStaff.php');

//トークンのチェック
if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:../error.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}
try {
    //予定日・職種・患者IDを取得
    $reservation_date = $_POST['reservation_date'];
    $job = $_POST['job'];
    $patient_id = $_POST['patient_id'];
    //職種から担当者と単位数の情報を取得する
    if ($job == 0) {
        $today_staff_id = $_POST['pt_today_id'];
        $today_staff_num = $_POST['pt_today_num'];
    } elseif ($job == 1) {
        $today_staff_id = $_POST['ot_today_id'];
        $today_staff_num = $_POST['ot_today_num'];
    } else {
        $today_staff_id = $_POST['st_today_id'];
        $today_staff_num = $_POST['st_today_num'];
    }
    //データベースへ接続する
    $pdo = Base::getInstance();
    //リハビリ‐患者テーブルのスタッフの合計単位を計算
    $unit_sum = new SelectStaff($pdo);
    $sum = $unit_sum->sumNumberByStaff(
        $today_staff_id,
        $reservation_date,
        $job
    );
    //選択したスタッフの合計単位が20を超えてしまう場合はリダイレクトする
    if ($sum['SUM(today_staff_num)'] + $today_staff_num > 20) {
        $_SESSION['err']['msg'] = "該当スタッフの単位数が20を超えています";
        $_SESSION['select']['reservation_date'] = $reservation_date;
        $_SESSION['select']['job'] = $job;
        $_SESSION['select']['patient_id'] = $patient_id;
        header('Location:./select_staff.php');
        return;
    }
    //リハビリ‐患者テーブルの予約(レコード)の数を計算
    $reservation_count = new SelectStaff($pdo);
    $count = $reservation_count->countPatientByStaff(
        $today_staff_id,
        $reservation_date,
        $job
    );
    //予約人数が10人以上の場合はリダイレクトする
    if ($count['COUNT(id)'] >= 10) {
        $_SESSION['err']['msg'] = "各スタッフの担当患者は10人以内で調整してください";
        $_SESSION['select']['reservation_date'] = $reservation_date;
        $_SESSION['select']['job'] = $job;
        $_SESSION['select']['patient_id'] = $patient_id;
        header('Location:./select_staff.php');
        return;
    }
    //リハビリ‐患者テーブルにデータがあるか検索
    $check_unit_updated = new SelectStaff($pdo);
    $search = $check_unit_updated->searchUnitUpdated(
        $patient_id,
        $reservation_date,
        $job
    );
    if ($search) {
        //レコードが存在する場合、アップデートして担当者を決定する
        $updated_unit = new SelectStaff($pdo);
        $result = $updated_unit->selectTodayStaff(
            $patient_id,
            $today_staff_id,
            $today_staff_num,
            $reservation_date
        );
    } else {
        //レコードが存在しない場合、新たにデータを挿入する
        $change_unit = new SelectStaff($pdo);
        $result = $change_unit->selectStaffAndNumber(
            $patient_id,
            $reservation_date,
            $today_staff_id,
            $today_staff_num
        );
    }
    //正常に作動してからセッションに保存する
    if ($result) {
        $_SESSION['select']['reservation_date'] = $_POST['reservation_date'];
        $_SESSION['select']['job'] = $_POST['job'];
        //正常終了した場合はメッセージを削除
        unset($_SESSION['err']['msg']);
        header('Location:./select_patient.php');
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
