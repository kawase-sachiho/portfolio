<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/Staffs.php');

//ワンタイムトークンのチェック
if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:../error.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}

try {
    $day = Date::getDate();
    $id = $_POST['id'];

    //データベースへ接続する
    $pdo = Base::getInstance();

    //担当患者の有無を確認するメソッド
    $check_handle_patient = new Staffs($pdo);
    $patients = $check_handle_patient->checkPatientByStaff($id);

    //担当患者の名前を配列に入れる
    $patient_names = array();
    foreach ($patients as $patient) {
        array_push($patient_names, $patient['patient_name']);
    }

    //担当患者が1人でもいればリダイレクトする
    if (!empty($patient_names)) {
        $_SESSION['staff']['id'] = $id;
        $_SESSION['staff']['patient'] = $patient_names;
        header('Location:./delete.php');
        exit;
    }

    //予約済のレコードがあるか確認するメソッド
    $check_staff_reservation = new Staffs($pdo);
    $reservation_dates = $check_staff_reservation->checkReservationByStaff(
        $id,
        $day
    );

    //予約されている日の日程を配列に入れる
    $reservation_days = array();
    foreach ($reservation_dates as $reservation_date) {
        array_push($reservation_days, $reservation_date['reservation_date']);
    }
    //予約されている日が一日でもあればリダイレクトする
    if (!empty($reservation_days)) {
        $_SESSION['staff']['id'] = $id;
        $_SESSION['staff']['reservation_days'] = $reservation_days;
        header('Location:./delete.php');
        exit;
    }
    //出勤予約の有無を確認するメソッド
    $check_working_reservation = new Staffs($pdo);
    $working_dates = $check_working_reservation->checkWorking(
        $id,
        $day
    );

    //出勤日(予約)を配列に入れる
    $working_days = array();
    foreach ($working_dates as $working_date) {
        array_push($working_days, $working_date['working_date']);
    }

    //出勤の予定があればリダイレクトする
    if (!empty($working_days)) {
        $_SESSION['staff']['id'] = $id;
        $_SESSION['staff']['working_days'] = $working_days;
        header('Location:./delete.php');
        exit;
    }

    //担当患者もおらず、リハビリ予約もなく、出勤の予定もない場合
    if (empty($patient_names) && empty($reservation_days) && empty($working_days)) {
        //スタッフのデータを削除
        $delete_staff = new Staffs($pdo);
        $result = $delete_staff->deleteStaff($id);
        //正常終了した場合はメッセージを削除する
        if ($result) {
            unset($_SESSION['err']['msg']);
            unset($_SESSION['staff']);
            header('Location:./index.php');
            exit;
        }
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
