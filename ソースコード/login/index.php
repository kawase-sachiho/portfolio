<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');

try{
$pdo = Base::getInstance();
$token = Safety::generateToken();
}
catch (Exception $e) 
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
    <title>ログイン画面</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <h1 class="my-5 text-center text-warning"><u>スタッフログイン</u></h1>
        <form action="login.php" method="post">
            <input type="hidden" name="token" value="<?= $token ?>">
            <div class="row mb-3 text-center">
                <label for="text mail" class="col-lg-3 font-weight-bold col-form-label">メールアドレス</label>
                <div class="col-lg-6">
                    <input type="mail" name="mail" class="form-control" id="mail" placeholder="メールアドレスを入力してください">
                </div>
            </div>
            <div class="row mb-3 text-center">
                <label for="text pass" class="col-lg-3 font-weight-bold col-form-label">パスワード</label>
                <div class="col-lg-6">
                    <input type="password" name="pass" class="form-control" id="pass" placeholder="パスワードを入力してください">
                </div>
            </div>
            <div class="scope-row" style="display:flex;">
                <div class="text text-danger  text-center col-9"><?php if(isset($_SESSION['err']['msg'])){echo $_SESSION['err']['msg'];}?></div>
            <button type="submit" class="btn btn-primary btn-lg">ログイン</button>
            </div>
        </form>
    </div>
</body>

</html>