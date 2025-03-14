<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/SelectStaff.php');

if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:../error.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}

try {
    //予定日・職種を取得
    $reservation_date = $_POST['reservation_date'];
    $job = $_POST['job'];

    //データベースへ接続する
    $pdo = Base::getInstance();

    //１人でも患者が割り当てられているスタッフを獲得するメソッド
    $get_selected_staffs = new SelectStaff($pdo);
    $selected_staffs = $get_selected_staffs->getSelectedWorkers(
        $job,
        $reservation_date
    );

    //上記のスタッフの名前を配列に入れる
    $selected_staff_names = array();
    foreach ($selected_staffs as $selected_staff) {
        array_push($selected_staff_names, $selected_staff['staff_name']);
    }

    //出勤しているすべてのスタッフを獲得するメソッド
    $get_all_staffs = new SelectStaff($pdo);
    $all_staffs = $get_all_staffs->getWorkersNames(
        $job,
        $reservation_date
    );

    //上記のスタッフの名前を配列に入れる
    $all_staff_names = array();
    foreach ($all_staffs as $all_staff) {
        array_push($all_staff_names, $all_staff['staff_name']);
    }

    /*出勤しているすべてのスタッフから1人でも患者が割り当てられているスタッフの名前を除外
    (＝担当患者が0の状態のスタッフを獲得する) */
    $diff_staff_names = array_diff($all_staff_names, $selected_staff_names);

    //担当者が登録済の患者を取得するメソッド
    $get_selected_patients = new SelectStaff($pdo);
    $selected_patients = $get_selected_patients->getSelectedNames(
        $reservation_date,
        $job
    );

    //上記の患者の名前を配列に入れる
    $selected_patient_names = array();
    foreach ($selected_patients as $selected_patient) {
        array_push($selected_patient_names, $selected_patient['patient_name']);
    }

    //当日の単位が0に調整された患者を取得するメソッド
    $get_0unit_patient = new SelectStaff($pdo);
    $no_unit_patients = $get_0unit_patient->getNoUnitNames($reservation_date);

    //上記の患者の名前を配列に入れる
    $no_unit_patient_names = array();
    foreach ($no_unit_patients as $no_unit_patient) {
        array_push($no_unit_patient_names, $no_unit_patient['patient_name']);
    }

    //登録の有無にかかわらず、全ての患者の名前を取得するメソッド(基本単位が0の患者を除く) 
    if ($job == 0) {
        //PT
        $all_pt = new SelectStaff($pdo);
        $all_patients = $all_pt->getAllNamesByJob(
            $job,
            $reservation_date
        );
    } elseif ($job == 1) {
        //OT
        $all_ot = new SelectStaff($pdo);
        $all_patients = $all_ot->getAllNamesByJob(
            $job,
            $reservation_date
        );
    } else {
        //ST
        $all_st = new SelectStaff($pdo);
        $all_patients = $all_st->getAllNamesByJob(
            $job,
            $reservation_date
        );
    }
    //上記の患者の名前を配列に入れる
    $all_patient_names = array();
    foreach ($all_patients as $all_patient) {
        array_push($all_patient_names, $all_patient['patient_name']);
    }

    //全ての患者から、当日の単位数が0の患者・基本単位が0の患者の名前を除外する
    $diff_patient_names = array_diff($all_patient_names, $selected_patient_names, $no_unit_patient_names);

    if (!empty($diff_staff_names) || !empty($diff_patient_names)) {
        $_SESSION['select']['staff_name'] = $diff_staff_names;
        $_SESSION['select']['patient_name'] = $diff_patient_names;
        $_SESSION['select']['reservation_date'] = $_POST['reservation_date'];
        $_SESSION['select']['job'] = $_POST['job'];
        header('Location:./select_patient.php');
        return;
    } else {
        $_SESSION['select']['msg'] = "正常に完了しました。";
        $_SESSION['select']['reservation_date'] = $_POST['reservation_date'];
        $_SESSION['select']['job'] = $_POST['job'];
        header('Location:./select_patient.php');
        return;
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
