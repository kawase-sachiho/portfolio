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
    <title>出勤スタッフ登録</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>出勤スタッフ登録</u></h1>
        <form method="post" action="./add.php">
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
                header('Location:./add.php');
                exit;
            }

            if (isset($_POST['job']) && !empty($_POST['working_date'])) {
                unset($_SESSION['err']['msg']);
                $post = Safety::sanitaize($_POST);
                $job = $post['job'];
                $working_date = $post['working_date'];

                //職種ごとにスタッフの情報と出勤の有無を確認するメソッド
                $check_working_staff = new Work($pdo);
                $staffs = $check_working_staff->getStaffListForWork(
                    $job,
                    $working_date
                );

                //職種ごとにリハビリ予約が存在するかを確認するメソッド
                $check_riha_reservation = new Work($pdo);
                $reservation_names = $check_riha_reservation->checkReservation(
                    $working_date,
                    $job
                );

                //リハビリ予約が入っているスタッフの名前を取得して配列にいれる
                $staff_name_reserved = array();
                foreach ($reservation_names as $reservation_name) {
                    array_push($staff_name_reserved, $reservation_name['staff_name']);
                }
                $selected_names = array_unique($staff_name_reserved);
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
            出勤登録
        </h4>
        <div class="text text-danger font-weight-bold"><?php
                                                    if (isset($_SESSION['err']['msg'])) {
                                                        echo $_SESSION['err']['msg'];
                                                    }
                                                    ?>
        </div>
        <div class="text text-primary font-weight-bold">
            <?php if (isset($_POST['job']) && !empty($_POST['working_date']) && !empty($reservation_names)) {
                foreach ($selected_names as $selected_name) {
                    echo $selected_name . "さん ";
                }
                echo "のリハビリ予約が入っているため、出勤登録を変更できません。";
            } ?> </div>
        <div class="table-responsive">

            <table class="table table-bordered">
                <form method="post" action="./add_action.php">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="job" value="<?= $job ?>">
                    <input type="hidden" name="working_date" value="<?= $working_date ?>">
                    <tbody>
                        <tr class="table-primary">
                            <th class="col-3">
                                氏名
                            </th>
                            <th class="col-1 bg-warning text-white">
                                出勤
                            </th>
                            <th class="col-1">
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
                                    <td><?= $staff['staff_name'] ?></td>
                                    <td>
                                        <!--出勤登録済のスタッフはチェックボックスにチェックを入れる。-->
                                        <?php if (!is_null($staff['working_staff']) &&  $staff['is_deleted'] == 0) : ?>
                                            <!--リハビリ予約が入っている場合はチェックボックスを操作できない。-->
                                            <input name="id[]" id="woking" value="<?= $staff['id'] ?>" type="checkbox" checked="checked" <?= !empty($reservation_names) ? "disabled" : "" ?>
                                                <?php else : ?>
                                                <input name="id[]" id="woking" value="<?= $staff['id'] ?>" type="checkbox" <?= !empty($reservation_names) ? "disabled" : "" ?>
                                                <?php endif ?>
                                                </td>
                                    <td>
                                        <?php
                                        Info::getGender($staff);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (Date::getExperience($day, $staff)) {
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
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
            </table>
            <div class="text-right my-3">
                <button type="submit" class="btn btn-danger btn-lg">登録</button>
            </div>
            </form>
        </div>

        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>

</html>