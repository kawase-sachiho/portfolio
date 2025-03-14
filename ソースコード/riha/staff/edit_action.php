<?php
session_start();
session_regenerate_id();

require_once('../class/db/Safety.php');
require_once('../class/db/Base.php');
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
    //文字数が多すぎる場合リダイレクトする
    if ((strlen($_POST['staff_family_name']) > 25)  || (strlen($_POST['staff_family_name']) > 25)) {
        $_SESSION['err']['msg'] = "姓・名は25文字以下にしてください";
        $_SESSION['staff']['id'] = $_POST['id'];
        header("Location:./edit.php");
        exit;
    }
    //日付の値が正しくない場合リダイレクトする
    if (!strtotime($_POST['job_started_date'])) {
        $_SESSION['err']['msg'] = "期限日の日付が正しくありません";
        $_SESSION['staff']['id'] = $_POST['id'];
        header("Location: ./edit.php");
        exit;
    }
    //空欄があった場合リダイレクトする
    if (empty($_POST['staff_family_name']) || empty($_POST['staff_first_name']) || empty($_POST['job_started_date'])) {
        $_SESSION['err']['msg'] = "すべての項目に入力してください";
        $_SESSION['staff']['id'] = $_POST['id'];
        header("Location:./edit.php");
        exit;
    }

    $day = Date::getDate();
    $day2 = $_POST['job_started_date'];
    //経験年数が3年目未満で救急対応可能になっている場合はリダイレクトする
    if ((((strtotime($day) - strtotime($day2)) / 86400) < 1096) && $_POST['emergency_skill'] == 1) {
        $_SESSION['err']['msg'] = "経験年数が３年目未満のスタッフは緊急対応不可に設定してください";
        $_SESSION['staff']['id'] = $_POST['id'];
        header('Location:./edit.php');
        return;
    }

    //サニタイズして値を代入する
    $post = Safety::sanitaize($_POST);
    $id = $post['id'];
    $staff_name = $post['staff_family_name'] . $post['staff_first_name'];
    $staff_family_name = $post['staff_family_name'];
    $staff_first_name = $post['staff_first_name'];
    $job = $_POST['job'];
    $staff_gender = $_POST['staff_gender'];
    $job_started_date = $post['job_started_date'];
    $emergency_skill = $_POST['emergency_skill'];

    $pdo = Base::getInstance();

    //データを更新するメソッド
    $edit_staff = new Staffs($pdo);
    $result = $edit_staff->editStaff(
        $id,
        $staff_name,
        $staff_family_name,
        $staff_first_name,
        $job,
        $staff_gender,
        $job_started_date,
        $emergency_skill
    );

    //正常に作動してからセッションに保存する
    if ($result) {
        //正常終了した場合はメッセージを削除
        unset($_SESSION['err']['msg']);
        header('Location:./index.php');
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
