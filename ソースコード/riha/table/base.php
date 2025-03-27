<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/Table.php');

//ログイン状態のチェック
if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
try {
    $pdo = Base::getInstance();
    $token = Safety::generateToken();
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
    <title>担当者一覧</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>担当者一覧</u></h1>
        <form method="post" action="./base.php">
            <div class="row my-3">
                <label class="text control-label col-3 font-weight-bold" for="reservation_date">日付を選択してください</label>
                <input name="reservation_date" id="reservation_date" class="form-control input-md col-5" type="date">
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
            //日付が選択されていなければリダイレクトする
            if (isset($_POST['job']) && empty($_POST['reservation_date'])) {
                $_SESSION['err']['msg'] = "日付を選択してください";
                header('Location:./base.php');
                exit;
            } elseif (isset($_POST['job']) && !empty($_POST['reservation_date'])) {
                $post = Safety::sanitaize($_POST);

                unset($_SESSION['err']['msg']);
                $job = $post['job'];
                $reservation_date = $post['reservation_date'];
                //全てのスタッフの担当患者と基本単位を取得するメソッド
                $all_staff_table = new Table($pdo);
                $staffs = $all_staff_table->getBaseNumbersByStaff(
                    $job,
                    $reservation_date
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
                if (isset($_POST['reservation_date'])) {
                    Date::showDate($reservation_date);
                }
                ?>
            </span>
            <?php if (isset($_POST['job']) && !empty($_POST['reservation_date'])) {
                Info::showJob($job);
                echo "担当者一覧";
            }
            ?>
        </h4>
        <div class="text-danger font-weight-bold"><?php
                                                    if (isset($_SESSION['err']['msg'])) {
                                                        echo $_SESSION['err']['msg'];
                                                    } ?>
        </div>
        <?php if (isset($staffs)) : ?>
            <div class="scope-row">
                <div class="table-responsive mr-0 pr-0" style="display: inline-block;">
                    <table class="table table-bordered">
                        <form>
                            <tbody>
                                <tr class="table-primary">
                                    <th class="col-auto bg-warning">スタッフ氏名</th>
                                    <td>
                                        患者氏名
                                    </td>
                                    <td>
                                        合計単位
                                    </td>
                                </tr>
                                <?php foreach ($staffs as $staff) : ?>
                                    <tr>
                                        <td class="col-auto"><?= $staff['staff_name'] ?></td>
                                        <td class="col-auto">
                                            <?php
                                            echo $staff['patient_name1'] . " " . $staff['base_num1'] . " ";
                                            echo $staff['patient_name2'] . " " . $staff['base_num2'] . " ";
                                            echo $staff['patient_name3'] . " " . $staff['base_num3'] . " ";
                                            echo $staff['patient_name4'] . " " . $staff['base_num4'] . " ";
                                            echo $staff['patient_name5'] . " " . $staff['base_num5'] . " ";
                                            echo $staff['patient_name6'] . " " . $staff['base_num6'] . " ";
                                            echo $staff['patient_name7'] . " " . $staff['base_num7'] . " ";
                                            echo $staff['patient_name8'] . " " . $staff['base_num8'] . " ";
                                            echo $staff['patient_name9'] . " " . $staff['base_num9'] . " ";
                                            echo $staff['patient_name10'] . "" . $staff['base_num10'] . " ";
                                            ?>
                                        </td>
                                        <td class="col-auto">
                                            <?= $staff['SUM(tmp.pt_base_num)'] ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>

                            </tbody>
                        </form>
                    </table>
                </div>
            <?php endif ?>
            <a class="btn btn-primary text-white btn-lg" style="text-decoration:none;" href="./today.php">スタッフ介入表へ</a>
            <div class="mt-2 mb-5 ml-2 text-right">
                <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
                <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
            </div>
            </div>
</body>

</html>