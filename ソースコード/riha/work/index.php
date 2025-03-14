<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/Work.php');

if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
try {
    $pdo = Base::getInstance();
    $token = Safety::generateToken();

    //本日の日付を取得し$dayに入れる
    $day = Date::getDate();
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
    <title>出勤スタッフ一覧</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>出勤スタッフ一覧</u></h1>
        <form method="post" action="./index.php">
            <div class="row my-3">
                <label class="text control-label col-3 font-weight-bold" for="working_date">日付を選択してください</label>
                <input name="working_date" id="working_date" class="form-control input-md col-5" type="date">
            </div>
            <div class="row my-3">
                <label class="text control-label col-3 font-weight-bold" for="job">職種を選択してください</label>

                <label class="text radio-inline" for="job[0]">
                    <input name="job" id="job[0]" value="0" checked="checked" type="radio">
                    <span style="margin-right:4em;">PT</span>
                </label>
                <label class="text radio-inline" for="job[1]">
                    <input name="job" id="job[1]" value="1" type="radio">
                    <span style="margin-right:4em;">OT</span>
                </label>
                <label class="text radio-inline" for="job[2]">
                    <input name="job" id="job[2]" value="2" type="radio">
                    ST
                </label>
            </div>
            <div class="row my-3">
                <div class="col-3"></div>
                <div class="col-5"></div>
                <button type="submit" class="btn btn-lg btn-danger text-white">表示</button>
            </div>
        </form>

        <?php
        try {
            if (isset($_POST['job']) && empty($_POST['working_date'])) {
                $_SESSION['err']['msg'] = "日付を選択してください";
                header('Location:./index.php');
                exit;
            } elseif (isset($_POST['job']) && !empty($_POST['working_date'])) {
                $post = Safety::sanitaize($_POST);

                unset($_SESSION['err']['msg']);
                $job = $post['job'];
                $working_date = $post['working_date'];
                //出勤しているスタッフの情報を獲得するメソッド
                $working_staff = new Work($pdo);
                $staffs = $working_staff->getWorkingStaff(
                    $job,
                    $working_date
                );
            } else {
            }
        } catch (Exception $e) {
            header('Location:../error.php');
            exit;
        }
        ?>
        <h4 class="text-center my-5">
            <span class="text-danger mr-4">
                <?php
                if (isset($_POST['working_date'])) {
                    Date::showDate($working_date);
                }
                ?>
            </span>
            <?php if (isset($_POST['job']) && !empty($_POST['working_date'])) {
                Info::showJob($job);
            }
            ?>
            出勤者一覧
        </h4>
        <div class="text text-danger font-weight-bold">
            <?php
            if (isset($_SESSION['err']['msg'])) {
                echo $_SESSION['err']['msg'];
            } ?>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <tbody>
                    <tr class="table-primary">
                        <th class="col-4">
                            氏名
                        </th>
                        <th class="col-2">
                            性別
                        </th>
                        <th class="col-3">
                            経験年数
                        </th>
                        <th class="col-3">
                            急変時の対応
                        </th>
                    </tr>
                    <?php if (isset($staffs)): ?>
                        <?php foreach ($staffs as $staff) : ?>
                            <tr>
                                <td class="col-4">
                                    <?= $staff['staff_name'] ?>
                                </td>
                                <td class="col-2">
                                    <?php
                                    Info::getGender($staff);
                                    ?>
                                </td>
                                <td class="col-3">
                                    <?php
                                    if (Date::getExperience($day, $staff)) {
                                        echo "3年目以上";
                                    } else {
                                        echo "3年目未満";
                                    }
                                    ?>
                                </td>
                                <td class="col-3">
                                    <?php
                                    Info::getEmegency_skill($staff);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
        <h6 class="my-3 font-weight-bold">
            <?php if (isset($staffs)) {
                echo "出勤：" . count($staffs) . "人";
            } ?></h6>
        <a class="btn btn-danger text-white btn-lg" style="text-decoration:none;" href="./add.php">出勤登録</a>
        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>

</html>