<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Patients.php');

//トークンのチェック
if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:../error.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}
try {
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

    //基本単位の合計が0でPOSTされてきた場合、リダイレクトする
    if ($pt_base_num + $ot_base_num + $st_base_num == 0) {
        $_SESSION['patient']['patient_name'] = $patient_name;
        $_SESSION['patient']['patient_family_name'] = $patient_family_name;
        $_SESSION['patient']['patient_first_name'] = $patient_first_name;
        $_SESSION['patient']['hope_gender'] = $hope_gender;
        $_SESSION['patient']['staff_experience'] = $staff_experience;
        $_SESSION['patient']['emergency_risk'] = $emergency_risk;
        $_SESSION['patient']['started_date'] = $started_date;
        $_SESSION['err']['msg'] = "合計単位が0になっています。";
        header('Location:./add_detail.php');
        return;
    }
    //基本単位の合計が10以上でPOSTされてきた場合、リダイレクトする
    if ($pt_base_num + $ot_base_num + $st_base_num > 9) {
        $_SESSION['patient']['patient_name'] = $patient_name;
        $_SESSION['patient']['patient_family_name'] = $patient_family_name;
        $_SESSION['patient']['patient_first_name'] = $patient_first_name;
        $_SESSION['patient']['hope_gender'] = $hope_gender;
        $_SESSION['patient']['staff_experience'] = $staff_experience;
        $_SESSION['patient']['emergency_risk'] = $emergency_risk;
        $_SESSION['patient']['started_date'] = $started_date;
        $_SESSION['err']['msg'] = "合計単位が9単位を超えています。";
        header('Location:./add_detail.php');
        return;
    }

    //データベースへ接続する
    $pdo = Base::getInstance();

    //患者を新たに追加するメソッド
    $add_patient = new Patients($pdo);
    $result = $add_patient->addPatients(
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

    if ($result) {
        //正常終了した場合はメッセージを削除
        unset($_SESSION['err']['msg']);

        header('Location:./index.php');
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
