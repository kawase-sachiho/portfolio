<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/CountUnit.php');

$pdo = Base::getInstance();
$day = Date::getDate();

//ワンタイムトークンのチェック
if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:./edit.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}

try {
    $working_date = $_POST['working_date'];
    $patient_id = intval($_POST['patient_id']);
    $pt_base_num = intval($_POST['pt_base_num']);
    $ot_base_num = intval($_POST['ot_base_num']);
    $st_base_num = intval($_POST['st_base_num']);
    $pt_adjustment = intval($_POST['pt_adjustment']);
    $ot_adjustment = intval($_POST['ot_adjustment']);
    $st_adjustment = intval($_POST['st_adjustment']);
    $pt_change = intval($_POST['pt_change']);
    $ot_change = intval($_POST['ot_change']);
    $st_change = intval($_POST['st_change']);

    //PTの単位を計算
    $pt_today_num = $pt_base_num + $pt_change;
    $pt_adjustment = $pt_adjustment + $pt_change;
    //OTの単位を計算
    $ot_today_num = $ot_base_num + $ot_change;
    $ot_adjustment = $ot_adjustment + $ot_change;
    //STの単位を計算
    $st_today_num = $st_base_num + $st_change;
    $st_adjustment = $st_adjustment + $st_change;
    //調整前の単位数の合計を計算
    $total_number = $pt_base_num + $st_base_num + $ot_base_num;

    //全ての単位数に変化が無い場合
    if ($pt_change == 0 && $ot_change == 0 && $st_change == 0) {
        $_SESSION['err']['msg'] = "単位数の変化がありません";
        $_SESSION['patient']['working_date'] = $working_date;
        $_SESSION['patient']['pt_adjustment'] = $pt_adjustment;
        $_SESSION['patient']['ot_adjustment'] = $ot_adjustment;
        $_SESSION['patient']['st_adjustment'] = $st_adjustment;
        $_SESSION['patient']['patient_id'] = $patient_id;
        header('Location:./edit.php');
        return;
    }
    //単位数の合計が9を超える場合
    if ($pt_today_num + $ot_today_num + $st_today_num > 9) {
        $_SESSION['err']['msg'] = "単位数の合計は9単位以内で調整してください";
        $_SESSION['patient']['working_date'] = $working_date;
        $_SESSION['patient']['pt_adjustment'] = $pt_adjustment;
        $_SESSION['patient']['ot_adjustment'] = $ot_adjustment;
        $_SESSION['patient']['st_adjustment'] = $st_adjustment;
        $_SESSION['patient']['patient_id'] = $patient_id;
        header('Location:./edit.php');
        return;
    }
    //どれかの単位がマイナスになっている場合
    if ($pt_today_num < 0 || $ot_today_num < 0 || $st_today_num < 0) {
        $_SESSION['err']['msg'] = "単位数がマイナスにならないように調整してください";
        $_SESSION['patient']['working_date'] = $working_date;
        $_SESSION['patient']['pt_adjustment'] = $pt_adjustment;
        $_SESSION['patient']['ot_adjustment'] = $ot_adjustment;
        $_SESSION['patient']['st_adjustment'] = $st_adjustment;
        $_SESSION['patient']['patient_id'] = $patient_id;
        header('Location:./edit.php');
        return;
    }
    //調整した値の合計単位が調整前の合計単位と一致しない場合
    if (($pt_today_num + $ot_today_num + $st_today_num) != $total_number) {
        $_SESSION['err']['msg'] = "単位の合計が、調整前と調整後で一致しません";
        $_SESSION['patient']['working_date'] = $working_date;
        $_SESSION['patient']['pt_adjustment'] = $pt_adjustment;
        $_SESSION['patient']['ot_adjustment'] = $ot_adjustment;
        $_SESSION['patient']['st_adjustment'] = $st_adjustment;
        $_SESSION['patient']['patient_id'] = $patient_id;
        header('Location:./edit.php');
        return;
    }

    //２回目以降の変更かチェックする(PT)
    $job = 0;
    $check_pt_changed = new CountUnit($pdo);
    $search_pt = $check_pt_changed->checkUpdateUnitByjob(
        $job,
        $patient_id,
        $working_date
    );
    if ($search_pt) {
        //単位数を再変更するメソッド
        $job = 0;
        $num = $pt_today_num;
        $update_pt_unit = new CountUnit($pdo);
        $pt_result = $update_pt_unit->reUpdateUnitByJob(
            $patient_id,
            $job,
            $num,
            $working_date
        );
    } else {
        //単位数を初めて変更するメソッド
        $job = 0;
        $num = $pt_today_num;
        $change_pt_unit = new CountUnit($pdo);
        $pt_result = $change_pt_unit->updateUnitByJob(
            $patient_id,
            $job,
            $working_date,
            $num
        );
    }
    //2回目以降の変更かチェック(OT)
    $job = 1;
    $check_ot_changed = new CountUnit($pdo);
    $search_ot = $check_ot_changed->checkUpdateUnitByjob(
        $job,
        $patient_id,
        $working_date
    );
    if ($search_ot) {
        //単位数を再変更するメソッド
        $job = 1;
        $num = $ot_today_num;
        $update_ot_unit = new CountUnit($pdo);
        $ot_result = $update_ot_unit->reUpdateUnitByJob(
            $patient_id,
            $job,
            $num,
            $working_date
        );
    } else {
        //単位数を初めて変更するメソッド
        $job = 1;
        $num = $ot_today_num;
        $change_ot_unit = new CountUnit($pdo);
        $ot_result = $change_ot_unit->updateUnitByJob(
            $patient_id,
            $job,
            $working_date,
            $num
        );
    }
    //2回目以降の変更かチェックする(ST)
    $job = 2;
    $check_st_changed = new CountUnit($pdo);
    $search_st = $check_st_changed->checkUpdateUnitByjob(
        $job,
        $patient_id,
        $working_date
    );
    if ($search_st) {
        //単位数を再変更するメソッド
        $job = 2;
        $num = $st_today_num;
        $update_st_unit = new CountUnit($pdo);
        $st_result = $update_st_unit->reUpdateUnitByJob(
            $patient_id,
            $job,
            $num,
            $working_date
        );
    } else {
        //単位数を初めて変更するメソッド
        $job = 2;
        $num = $st_today_num;
        $change_st_unit = new CountUnit($pdo);
        $st_result = $change_st_unit->updateUnitByJob(
            $patient_id,
            $job,
            $working_date,
            $num
        );
    }

    $_SESSION['patient']['working_date'] = $working_date;
    $_SESSION['patient']['pt_adjustment'] = $pt_adjustment;
    $_SESSION['patient']['ot_adjustment'] = $ot_adjustment;
    $_SESSION['patient']['st_adjustment'] = $st_adjustment;

    //PT・OT・STすべての調整が正しく実行された時
    if (isset($pt_result) && isset($ot_result) && isset($st_result)) {
        unset($_SESSION['err']['msg']);
        unset($_SESSION['patient']['patient_id']);
        header('Location:./select.php');
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
