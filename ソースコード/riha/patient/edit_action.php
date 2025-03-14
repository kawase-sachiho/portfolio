<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Patients.php');


if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:../error.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}

try {
    $id = $_POST['id'];
    $pt_base_id = $_POST['pt_base_id'];
    $pt_base_num = $_POST['pt_base_num'];
    $ot_base_id = $_POST['ot_base_id'];
    $ot_base_num = $_POST['ot_base_num'];
    $st_base_id = $_POST['st_base_id'];
    $st_base_num = $_POST['st_base_num'];

    $post = Safety::sanitaize($_POST);

    $patient_name = $post['patient_family_name'] . $post['patient_first_name'];
    $patient_family_name = $post['patient_family_name'];
    $patient_first_name = $post['patient_first_name'];
    $hope_gender = $_POST['hope_gender'];
    $staff_experience = $_POST['staff_experience'];
    $emergency_risk = $_POST['emergency_risk'];
    $started_date = $post['started_date'];

    //合計単位が0でPOSTされた場合リダイレクトする
    if ($pt_base_num + $ot_base_num + $st_base_num == 0) {
        $_SESSION['patient']['id'] = $_POST['id'];
        $_SESSION['patient']['pt_base_id'] = $pt_base_id;
        $_SESSION['patient']['ot_base_id'] = $ot_base_id;
        $_SESSION['patient']['st_base_id'] = $st_base_id;
        $_SESSION['patient']['pt_base_num'] = $pt_base_num;
        $_SESSION['patient']['ot_base_num'] = $ot_base_num;
        $_SESSION['patient']['st_base_num'] = $st_base_num;
        $_SESSION['patient']['patient_name'] = $patient_name;
        $_SESSION['patient']['patient_family_name'] = $patient_family_name;
        $_SESSION['patient']['patient_first_name'] = $patient_first_name;
        $_SESSION['patient']['hope_gender'] = $hope_gender;
        $_SESSION['patient']['staff_experience'] = $staff_experience;
        $_SESSION['patient']['emergency_risk'] = $emergency_risk;
        $_SESSION['patient']['started_date'] = $started_date;
        $_SESSION['err']['msg'] = "合計単位が0になっています。";
        header('Location:./edit_detail.php');
        return;
    }
    //合計単位が10以上でPOSTされた場合リダイレクトする
    if ($pt_base_num + $ot_base_num + $st_base_num > 9) {
        $_SESSION['patient']['id'] = $_POST['id'];
        $_SESSION['patient']['pt_base_id'] = $pt_base_id;
        $_SESSION['patient']['ot_base_id'] = $ot_base_id;
        $_SESSION['patient']['st_base_id'] = $st_base_id;
        $_SESSION['patient']['pt_base_num'] = $pt_base_num;
        $_SESSION['patient']['ot_base_num'] = $ot_base_num;
        $_SESSION['patient']['st_base_num'] = $st_base_num;
        $_SESSION['patient']['patient_name'] = $patient_name;
        $_SESSION['patient']['patient_family_name'] = $patient_family_name;
        $_SESSION['patient']['patient_first_name'] = $patient_first_name;
        $_SESSION['patient']['hope_gender'] = $hope_gender;
        $_SESSION['patient']['staff_experience'] = $staff_experience;
        $_SESSION['patient']['emergency_risk'] = $emergency_risk;
        $_SESSION['patient']['started_date'] = $started_date;
        $_SESSION['err']['msg'] = "合計単位が9単位を超えています。";
        header('Location:./edit_detail.php');
        return;
    }

    //データベースへ接続する
    $pdo = Base::getInstance();

    //患者情報を更新するメソッド
    $edit_patient = new Patients($pdo);
    $result = $edit_patient->editPatients(
        $id,
        $patient_name,
        $patient_family_name,
        $patient_first_name,
        $hope_gender,
        $staff_experience,
        $emergency_risk,
        $started_date,
        $pt_base_id,
        $ot_base_id,
        $st_base_id,
        $pt_base_num,
        $ot_base_num,
        $st_base_num
    );

    //正常に作動してからセッションに保存する
    if ($result) {
        $_SESSION['patient']['patient_name'] = $_POST['patient_family_name'] . $_POST['patient_first_name'];
        $_SESSION['patient']['patient_family_name'] = $_POST['patient_family_name'];
        $_SESSION['patient']['patient_first_name'] = $_POST['patient_first_name'];
        $_SESSION['patient']['hope_gender'] = $_POST['hope_gender'];
        $_SESSION['patient']['staff_experience'] = $_POST['staff_experience'];
        $_SESSION['patient']['emergency_risk'] = $_POST['emergency_risk'];
        $_SESSION['patient']['started_date'] = $_POST['started_date'];

        //正常終了した場合はメッセージを削除
        unset($_SESSION['err']['msg']);
        header('Location:./index.php');
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
