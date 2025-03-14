<?php
try {
    session_start();
    session_regenerate_id();

    require_once('../class/db/Safety.php');
    require_once('../class/db/Base.php');
    require_once('../class/Common.php');
    require_once('../class/SelectStaff.php');

    //データベースへ接続する
    $pdo = Base::getInstance();

    $reservation_date = $_POST['reservation_date'];
    $job = $_POST['job'];
    $patient_ids = $_POST['id'];

    foreach ($patient_ids as $patient_id) {
        /* リハビリの予約を取り消し、スタッフidを空に変更するメソッド */
        $delete_reservation = new SelectStaff($pdo);
        $result = $delete_reservation->deleteReservation(
            $patient_id,
            $job,
            $reservation_date
        );
        //正常に終了した場合はセッションに値を保存してリダイレクトする
        if ($result) {
            $_SESSION['select']['reservation_date'] = $_POST['reservation_date'];
            $_SESSION['select']['job'] = $_POST['job'];
            unset($_SESSION['err']['msg']);
            header('Location:./select_patient.php');
        }
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
