<?php
try {
    session_start();
    session_regenerate_id();

    require_once('../class/db/Safety.php');
    require_once('../class/db/Base.php');
    require_once('../class/Users.php');

    //ログイン状態のチェック
    if (empty($_SESSION['user'])) {
        header('Location:../login/index.php');
        exit;
    }
    //データベースへ接続する
    $pdo = Base::getInstance();
    $token = Safety::generateToken();
    $id = $_SESSION['user']['id'];
    //ユーザー情報を取得するメソッド
    $login_user=new Users($pdo);
    $user=$login_user->getUserInfo($id);
    
} catch (Exception $e) 
{
    header('Location:../error.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザーデータ編集</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 mb-2 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>ユーザーデータ編集</u></h1>
        <div class="row my-3">
            <div class="text col-3 font-weight-bold text-center">ユーザー名</div>
            <div class="text col-6 text-center"><?= $user['staff_name'] ?></div>
        </div>
        <form method="post" action="./edit_action.php">
            <input type="hidden" name="token" value="<?= $token ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="mailadress" value="1">
            <div class="row mb-3 text-center">
                <label for="text mail" class="col-lg-3 font-weight-bold col-form-label">メールアドレス(変更前)</label>
                <div class="text col-lg-6">
                    <?= $user['mail'] ?>
                </div>
            </div>
            <div class="row mb-3 text-center">
                <label for="text mail" class="col-lg-3 font-weight-bold col-form-label">メールアドレス(変更後)</label>
                <div class="col-lg-6">
                    <input type="mail" name="mail" class="form-control" id="mail" placeholder="メールアドレスを入力してください">
                </div>
            </div>
            <div class="row">
                <div class="text col-9 text-right">
                    <span class="text font-weight-bold text-danger"><?php if (isset($_SESSION['err']['msg']['mail'])) {
                echo $_SESSION['err']['msg']['mail'];
            }
            ?></span></div>
                    <button type="submit" class="btn btn-lg btn-primary" style="display:inline-block;">メールアドレスの変更</button>
            </div>
        </form>
        <form method="post" action="./edit_action.php">
            <input type="hidden" name="token" value="<?= $token ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="password" value="1">
            <div class="row my-3 text-center">
                <label for="text pass" class="col-lg-3 font-weight-bold col-form-label">パスワード(変更後)</label>
                <div class="col-lg-6">
                    <input type="password" name="pass" class="form-control" id="pass" placeholder="新しいパスワードを入力してください">
                </div>
            </div>
            <div class="row mb-3 text-center">
                <label for="text pass2" class="col-lg-3 font-weight-bold col-form-label">パスワード(確認)</label>
                <div class="col-lg-6">
                    <input type="password" name="pass2" class="form-control" id="pass2" placeholder="確認の為もう一度入力してください">
                </div>
            </div>
            <div class="row">
                <div class="col-9 text-right">
                <span class="text font-weight-bold text-danger"><?php if (isset($_SESSION['err']['msg']['pass'])) {
                echo $_SESSION['err']['msg']['pass'];
            }
            ?></span>
                </div>
                    <button type="submit" class="btn btn-primary btn-lg" style="display:inline-block;">パスワードの変更</button>
            </div>
            <div class="my-5 ml-2 text-right">
                <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
                <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
            </div>
    </div>
</body>

</html>