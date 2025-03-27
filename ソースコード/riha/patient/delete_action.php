<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/Patients.php');

//ワンタイムトークンのチェック
if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:../error.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}
try {

    unset($_SESSION['patient']);

    $day = Date::getDate();
    $id = $_POST['id'];

    //データベースへ接続する
    $pdo = Base::getInstance();

    //予約済のレコードがあるか確認する
    $check_reservation = new Patients($pdo);
    $reservation_dates = $check_reservation->checkReservationByPatient(
        $id,
        $day
    );
    $reservation_days = array();
    foreach ($reservation_dates as $reservation_date) {
        array_push($reservation_days, $reservation_date['reservation_date']);
    }
    //リハビリ予約がある場合はリダイレクトする
    if (!empty($reservation_days)) {
        $_SESSION['patient']['id'] = $id;
        $_SESSION['patient']['reservation_days'] = $reservation_days;
        header('Location:./delete.php');
        exit;
    }
    //リハビリ予約がない場合は削除の処理にうつる
    if (empty($reservation_days)) {
        //患者情報を削除するメソッド
        $delete_patient = new Patients($pdo);
        $result = $delete_patient->deletePatients($id);
        //正常に終了した場合はリダイレクトする
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
