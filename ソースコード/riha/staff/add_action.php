<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Staffs.php');
require_once('../class/Common.php');

if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:../error.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}

try {
    unset($_SESSION['post']);

    $post = Safety::sanitaize($_POST);

    $staff_name = $post['staff_family_name'] . $post['staff_first_name'];
    $staff_family_name = $post['staff_family_name'];
    $staff_first_name = $post['staff_first_name'];
    $job = $_POST['job'];
    $staff_gender = $_POST['staff_gender'];
    $job_started_date = $post['job_started_date'];
    $emergency_skill = $_POST['emergency_skill'];
    $mail = $post['mail'];
    $pass = $post['pass'];

    //文字数が多すぎる場合はリダイレクトする
    if (strlen($_POST['staff_family_name']) > 25  || strlen($_POST['staff_first_name'])  > 25) {
        $_SESSION['err']['msg'] = "文字数は25文字以下にしてください。";
        $_SESSION['staff']['staff_family_name'] = $staff_family_name;
        $_SESSION['staff']['staff_first_name'] = $staff_first_name;
        $_SESSION['staff']['job'] = $job;
        $_SESSION['staff']['staff_gender'] = $staff_gender;
        $_SESSION['staff']['job_started_date'] = $job_started_date;
        $_SESSION['staff']['emergency_skill'] = $emergency_skill;
        $_SESSION['staff']['mail'] = $mail;
        header("Location:./add.php");
        exit;
    }
    //空欄があった場合はリダイレクトする
    if (empty($_POST['staff_family_name']) || empty($_POST['staff_first_name']) || empty($_POST['pass']) || empty($_POST['pass2']) || empty($_POST['job_started_date'])) {
        $_SESSION['err']['msg'] = "すべての項目に入力してください";
        $_SESSION['staff']['staff_family_name'] = $staff_family_name;
        $_SESSION['staff']['staff_first_name'] = $staff_first_name;
        $_SESSION['staff']['job'] = $job;
        $_SESSION['staff']['staff_gender'] = $staff_gender;
        $_SESSION['staff']['job_started_date'] = $job_started_date;
        $_SESSION['staff']['emergency_skill'] = $emergency_skill;
        $_SESSION['staff']['mail'] = $mail;
        header('Location:./add.php');
        return;
    }
    //パスワードが不一致の場合、リダイレクトする
    if ($_POST['pass'] != $_POST['pass2']) {
        $_SESSION['err']['msg'] = "パスワードが一致しません";
        $_SESSION['staff']['staff_family_name'] = $staff_family_name;
        $_SESSION['staff']['staff_first_name'] = $staff_first_name;
        $_SESSION['staff']['job'] = $job;
        $_SESSION['staff']['staff_gender'] = $staff_gender;
        $_SESSION['staff']['job_started_date'] = $job_started_date;
        $_SESSION['staff']['emergency_skill'] = $emergency_skill;
        $_SESSION['staff']['mail'] = $mail;
        header('Location:./add.php');
        return;
    }
    //日付の値が正しくない場合、リダイレクトする
    if (!strtotime($_POST['job_started_date'])) {
        $_SESSION['err']['msg'] = "期限日の日付が正しくありません";
        $_SESSION['staff']['staff_family_name'] = $staff_family_name;
        $_SESSION['staff']['staff_first_name'] = $staff_first_name;
        $_SESSION['staff']['job'] = $job;
        $_SESSION['staff']['staff_gender'] = $staff_gender;
        $_SESSION['staff']['job_started_date'] = $job_started_date;
        $_SESSION['staff']['emergency_skill'] = $emergency_skill;
        $_SESSION['staff']['mail'] = $mail;
        header("Location: ./add.php");
        exit;
    }
    //メールアドレスの値が正しくない場合、リダイレクトする
    if (!preg_match("/^[a-zA-Z0-9_.+-]+[@][a-zA-Z0-9.-]+$/", $mail)) {
        $_SESSION['err']['msg'] = "メールアドレスを正しく入力してください";
        $_SESSION['staff']['staff_family_name'] = $staff_family_name;
        $_SESSION['staff']['staff_first_name'] = $staff_first_name;
        $_SESSION['staff']['job'] = $job;
        $_SESSION['staff']['staff_gender'] = $staff_gender;
        $_SESSION['staff']['job_started_date'] = $job_started_date;
        $_SESSION['staff']['emergency_skill'] = $emergency_skill;
        $_SESSION['staff']['mail'] = $mail;
        header('Location:./add.php');
        return;
    }

    //３年目以上か算出し、経験年数と救急対応の可否がマッチしているか確認する
    $day = Date::getDate();
    $day2 = $job_started_date;
    if ((((strtotime($day) - strtotime($day2)) / 86400) < 1096) && $emergency_skill == 1) {
        $_SESSION['err']['msg'] = "経験年数が３年目未満のスタッフは緊急対応不可に設定してください";
        $_SESSION['staff']['staff_family_name'] = $staff_family_name;
        $_SESSION['staff']['staff_first_name'] = $staff_first_name;
        $_SESSION['staff']['job'] = $job;
        $_SESSION['staff']['staff_gender'] = $staff_gender;
        $_SESSION['staff']['job_started_date'] = $job_started_date;
        $_SESSION['staff']['emergency_skill'] = $emergency_skill;
        $_SESSION['staff']['mail'] = $mail;
        header('Location:./add.php');
        return;
    }

    $pdo = Base::getInstance();

    //メールアドレス一致するスタッフがいるか検索するメソッド
    $check_staff_mail = new Staffs($pdo);
    $used_mail = $check_staff_mail->getStaffByMail($mail);

    //一致するスタッフがいればリダイレクトする
    if (!empty($used_mail)) {
        $_SESSION['err']['msg'] = "既に登録されているメーるアドレスです";
        header('Location:./add.php');
        return;
    } else {
        //パスワードをハッシュ化する
        $pass = password_hash($pass, PASSWORD_DEFAULT);
        //新規スタッフを追加するメソッド
        $add_staff = new Staffs($pdo);
        $result = $add_staff->addStaff(
            $staff_name,
            $staff_family_name,
            $staff_first_name,
            $job,
            $staff_gender,
            $job_started_date,
            $emergency_skill,
            $mail,
            $pass
        );
        if ($result) {
            //正常終了した場合はメッセージを削除
            unset($_SESSION['err']['msg']);
            unset($_SESSION['staff']);

            header('Location:./index.php');
        }
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
