<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Users.php');

try {
    $pdo = Base::getInstance();
    //ワンタイムトークンのチェック
    if (!Safety::isValidToken($_POST['token'])) {
        $_SESSION['err']['msg'] = "不正な処理が行われました";
        header('Location:../error.php');
        return;
    } else {
        unset($_SESSION['err']['msg']);
    }
    //空欄のままPOSTされた場合、リダイレクトする
    if (empty($_POST['mail']) || empty($_POST['pass'])) {
        $_SESSION['err']['msg'] = "記入漏れがあります";
        header('Location:index.php');
    }
    $post = Safety::sanitaize($_POST);
    $mail = $post['mail'];
    $pass = $post['pass'];

    //メールアドレスからユーザーを検索
    $search_user = new Users($pdo);
    $user = $search_user->searchUserByMail($mail);
    if (!$user) {
        //同じアドレスのユーザーがいなければ、リダイレクトする
        $_SESSION['err']['msg'] = "該当のメールアドレスのユーザーが見つかりません";
        unset($user['pass']);
        header('Location:index.php');
    } elseif (!password_verify(
        $pass,
        $user['pass']
    )) {
        //パスワードが一致しなければ、リダイレクトする
        $_SESSION['err']['msg'] = "パスワードが一致しません";
        unset($user['pass']);
        header('Location:index.php');
    } else {
        //メールアドレス・パスワードが一致したら、セッションにデータを保存しログイン状態にする
        $_SESSION['user'] = $user;

        unset($_SESSION['err']['msg']);
        unset($_SESSION['post']);

        header('Location: ../index.php');
        exit;
    }
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
