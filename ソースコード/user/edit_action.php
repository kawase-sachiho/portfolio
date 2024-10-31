<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Users.php');

if (!Safety::isValidToken($_POST['token'])) {
    $_SESSION['err']['msg'] = "不正な処理が行われました";
    header('Location:../error.php');
    return;
} else {
    unset($_SESSION['err']['msg']);
}

try {
    unset($_SESSION['post']);
    //メールアドレスの修正ボタンが押された場合
    if (!empty($_POST['mailadress'])) {
        //空欄だった場合
        if (empty($_POST['mail'])) {
            $_SESSION['err']['msg']['mail'] = "メールアドレスが空欄です";
            header('Location:./edit.php');
            return;
        }
        $post = Safety::sanitaize($_POST);
        $mail = $post['mail'];
        $id = $_POST['id'];
        //表記が正しくない場合
        if (!preg_match("/^[a-zA-Z0-9_.+-]+[@][a-zA-Z0-9.-]+$/", $mail)) {
            $_SESSION['err']['msg']['mail'] = "メールアドレスを正しく入力してください";
            header('Location:./edit.php');
            return;
        }
        $pdo = Base::getInstance();

        //メールアドレスを変更するメソッド
        $change_mail = new Users($pdo);
        $changed_mail = $change_mail->changeMail(
            $id,
            $mail
        );
    }

    //パスワードの変更ボタンが押された場合
    if (!empty($_POST['password'])) {
        //空欄があった場合
        if (empty($_POST['pass']) || empty($_POST['pass2'])) {
            $_SESSION['err']['msg']['pass'] = "パスワードに空欄があります";
            header('Location:./edit.php');
            return;
        }
        //パスワードが一致しない場合
        if ($_POST['pass'] != $_POST['pass2']) {
            //パスワードが不一致の場合、リダイレクトする
            $_SESSION['err']['msg']['pass'] = "パスワードが一致しません";
            header('Location:./edit.php');
            return;
        }
        $post = Safety::sanitaize($_POST);
        $pass = $post['pass'];
        $pass = password_hash($pass, PASSWORD_DEFAULT);
        $id = $_POST['id'];

        $pdo = Base::getInstance();

        //パスワードを変更するメソッド
        $change_pass = new Users($pdo);
        $changed_pass = $change_pass->changePass(
            $id,
            $pass
        );
    }
    if ($changed_mail || $changed_pass) {
        //正常終了した場合はメッセージを削除
        unset($_SESSION['err']['msg']);
        header('Location:./edit.php');
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
