<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/Staffs.php');

//ログイン状態のチェック
if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
unset($_SESSION['staff']);
try {
    $pdo = Base::getInstance();
    $token = Safety::generateToken();
    $day = Date::getDate();
    //スタッフ全員の情報を取得するメソッド
    $all_staffs = new Staffs($pdo);
    $staffs = $all_staffs->getStaffInfo();
} catch (Exception $e) {
    header('Location:../error.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ一覧</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>スタッフ一覧</u></h1>
        <div class="table-responsive">
            <table class="table table-bordered">
                <form>
                    <tbody>
                        <tr class="table-primary">
                            <th class="col-auto">スタッフ氏名</th>
                            <th class="col-auto">職種</th>
                            <th class="col-auto">性別</th>
                            <th class="col-auto">経験年数</th>
                            <th class="col-auto">急変時の対応</th>
                            <td class="col-2 bg-white" style="border:none;"></td>
                        </tr>
                        <?php foreach ($staffs as $staff) : ?>
                            <tr>
                                <td><?= $staff['staff_name'] ?></td>
                                <td>
                                    <?php
                                    Info::showJob($staff['job']);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    Info::getGender($staff);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (Date::getExperience(
                                        $day,
                                        $staff
                                    )) {
                                        echo "3年目以上";
                                    } else {
                                        echo "3年目未満";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    Info::getEmegency_skill($staff);
                                    ?>
                                </td>
                                <td class="col-2 bg-white text-center" style="border:none;">
                                    <a class="btn btn-danger btn-sm" style="text-decoration:none;" href="./edit.php?id=<?= $staff['id'] ?>">編集</a>
                                    <a class="btn btn-danger btn-sm" style="text-decoration:none;" href="./delete.php?id=<?= $staff['id'] ?>">削除</a>
                                    <a class="btn btn-danger btn-sm" style="text-decoration:none;" href="./info.php?id=<?= $staff['id'] ?>">詳細</a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <tr>
                            <td style="border:none;">
                                <a class="btn btn-danger text-white btn-lg" style="text-decoration:none;" href="./add.php">スタッフ登録</a>
                            </td>
                        </tr>
                    </tbody>
                </form>
            </table>
        </div>
        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>
</html>