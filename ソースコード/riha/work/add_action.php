<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Work.php');

if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:../error.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}

try {
    unset($_SESSION['post']);
    //POSTの値が空の場合
    if (!isset($_POST['id'])) {
        $_SESSION['err']['msg'] = "出勤者が一人もいません";
        header('Location:./add.php');
        return;
    }

    unset($rec);

    $working_date = $_POST['working_date'];
    $job = $_POST['job'];
    $staff_ids = $_POST['id'];

    $pdo = Base::getInstance();

    //日付・職種が同じのレコードがあるか検索するメソッド
    $check_working = new Work($pdo);
    $workers_recs = $check_working->checkWorkingData(
        $working_date,
        $job
    );

    //出勤状態で登録されているスタッフの配列
    $workers = array();
    foreach ($workers_recs as $worker_rec) {
        array_push($workers, $worker_rec['staff_id']);
    }

    //出勤→欠勤へ変更した(削除済)の人だけ取得
    $deleted_staff = new Work($pdo);
    $deleted_staffs = $deleted_staff->getDeleteWorkers(
        $working_date,
        $job
    );

    //削除済のスタッフの配列
    $deleted_workers = array();
    foreach ($deleted_staffs as $deleted_staff) {
        array_push($re_workers, $deleted_staff['staff_id']);
    }

    //新たに登録した人の配列
    $insert_workers = array_diff($staff_ids, $workers, $deleted_workers);
    //チェック外れた人の配列
    $delete_workers = array_diff($workers, $staff_ids);
    //削除済かつ新たに登録した人の配列
    $re_insert_workers = array_intersect($staff_ids, $deleted_workers);

    // foreach ($staff_ids as $staff_id) {
    //新たに登録した人のデータを追加する
    foreach ($insert_workers as $insert_worker) {
        $add_worker = new Work($pdo);
        $result = $add_worker->addWorkers(
            $working_date,
            $job,
            $insert_worker
        );
        continue;
    }

    //チェックが外れたデータを削除する
    foreach ($delete_workers as $delete_worker) {
        $clear_worker = new Work($pdo);
        $result = $clear_worker->deleteWorkers($delete_worker);
        continue;
    }

    //再度出勤登録された人のデータを書き換えるメソッド
    foreach ($re_insert_workers as $re_insert_worker) {
        $re_add_worker = new Work($pdo);
        $result = $re_add_worker->reAddWorkers($re_insert_worker);
        continue;
    }

    //正常に終了した場合はメッセージを削除する
    if ($result) {
        unset($_SESSION['err']['msg']);
        header('Location:./add.php');
    } else {
        $_SESSION['err']['msg'] = "出勤登録に変化がありません";
        header('Location:./add.php');
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
