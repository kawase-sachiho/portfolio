<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/CountUnit.php');

if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}

try {
    $pdo = Base::getInstance();
    $token = Safety::generateToken();
    $day = Date::getDate();

    //前のページからPOSTされた値、あるいはセッションに保存された日付・単位の過不足を代入
    if (isset($_SESSION['patient'])) {
        $working_date = $_SESSION['patient']['working_date'];
        $pt_adjustment = $_SESSION['patient']['pt_adjustment'];
        $ot_adjustment = $_SESSION['patient']['ot_adjustment'];
        $st_adjustment = $_SESSION['patient']['st_adjustment'];
    } else {
        $working_date = $_POST['working_date'];
        $patient_id = $_POST['patient_id'];
        $pt_adjustment = $_POST['pt_adjustment'];
        $ot_adjustment = $_POST['ot_adjustment'];
        $st_adjustment = $_POST['st_adjustment'];
    }
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
    <title>単位調整</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>単位調整</u></h1>
        <div class="text row my-3">
            <div class="col-auto font-weight-bold">日付</div>
            <div class="col-auto font-weight-bold text-danger"><?php echo Date::showDate($working_date); ?></div>
            <?php
            if (isset($_SESSION['patient']['patient_id'])) {
                $patient_id = $_SESSION['patient']['patient_id'];
            } else {
                $patient_id = $_POST['patient_id'];
            }
            //2回目以降の単位調整の場合 →確認要！
            $unit_changed_patient = new CountUnit($pdo);
            $patient = $unit_changed_patient->checkUpadateUnit(
                $patient_id,
                $working_date
            );
            if ($patient) {
                $patient_name = $patient['patient_name'];
                $job = 0;
                //PTの調整済の単位を取得
                $pt_changed_num = new CountUnit($pdo);
                $pt_number = $pt_changed_num->getTodayNum(
                    $job,
                    $patient_id,
                    $working_date
                );
              if (!$pt_number) {
                    //PTの基本単位を取得
                    $pt_basic_num = new CountUnit($pdo);
                    $pt_base_number = $pt_basic_num->getBaseNumByjob(
                        $job,
                        $patient_id
                    );
                    $pt_base_num = $pt_base_number['pt_base_num'];
                } else {
                    $pt_base_num = $pt_number['today_staff_num'];
                }
                //OTの調整済みの単位を取得
                $job = 1;
                $ot_changed_num = new CountUnit($pdo);
                $ot_number = $ot_changed_num->getTodayNum(
                    $job,
                    $patient_id,
                    $working_date
                );
                if (!$ot_number) {
                    //OTの基本単位を取得
                    $o_basic_num = new CountUnit($pdo);
                    $ot_base_number = $o_basic_num->getBaseNumByjob(
                        $job,
                        $patient_id
                    );
                    $ot_base_num = $ot_base_number['ot_base_num'];
                } else {
                    $ot_base_num = $ot_number['today_staff_num'];
                }
                //STの調整済の単位を取得
                $job = 2;
                $st_changed_num = new CountUnit($pdo);
                $st_number = $st_changed_num->getTodayNum(
                    $job,
                    $patient_id,
                    $working_date
                );
                if (!$st_number) {
                    //STの基本単位を取得
                    $s_basic_num = new CountUnit($pdo);
                    $st_base_number = $s_basic_num->getBaseNumByjob(
                        $job,
                        $patient_id
                    );
                    $st_base_num = $st_base_number['st_base_num'];
                } else {
                    $st_base_num = $st_number['today_staff_num'];
                }
            } else {
                //全てが初回の単位調整の場合
                $all_unit_nums = new CountUnit($pdo);
                $patient_numbers = $all_unit_nums->getBaseNumbers($patient_id);
                $pt_base_num = $patient_numbers['pt_base_num'];
                $ot_base_num = $patient_numbers['ot_base_num'];
                $st_base_num = $patient_numbers['st_base_num'];
                $patient_name = $patient_numbers['patient_name'];
            }
            ?>
            <div class="text col-auto"><span class="font-weight-bold">
                    <?php echo $patient_name; ?></span>さんの単位一覧</div>
            <div class="text font-weight-bold">
                <?php if (isset($_SESSION['err']['msg'])) {
                    echo $_SESSION['err']['msg'];
                } ?></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <form method="post" action="./edit_action.php">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="working_date" value="<?= $working_date ?>">
                    <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
                    <input type="hidden" name="pt_adjustment" value="<?= $pt_adjustment ?>">
                    <input type="hidden" name="ot_adjustment" value="<?= $ot_adjustment ?>">
                    <input type="hidden" name="st_adjustment" value="<?= $st_adjustment ?>">
                    <input type="hidden" name="pt_base_num" value="<?= $pt_base_num ?>">
                    <input type="hidden" name="ot_base_num" value="<?= $ot_base_num ?>">
                    <input type="hidden" name="st_base_num" value="<?= $st_base_num ?>">
                    <tbody>
                        <tr class="table-primary">
                            <th class="col-auto">職種</th>
                            <th class="col-auto">単位数</th>
                            <th class="col-auto bg-warning">増減</th>
                            <th class="col-auto bg-warning">調整</th>
                        </tr>
                        <!--PTの単位数-->
                        <tr>
                            <td>PT</td>
                            <td><?php
                                echo $pt_base_num;
                                ?>
                            </td>
                            <td>
                                <select name="pt_change" id="pt_change" class="form-control input-md" type="select">
                                    <?php for ($i = - ($pt_base_num); $i <= ($pt_base_num + $i <= 9); $i++) {
                                        if ($i == 0) {
                                            echo "<option selected>";
                                            echo "$i";
                                            echo "</option>";
                                        } else {
                                            echo "<option>";
                                            echo "$i";
                                            echo "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <!--リハビリ‐患者テーブルにデータが存在しない＝未となる-->
                                <?php
                                if (!$patient) {
                                    echo "未";
                                } elseif ($patient && !$pt_number) {
                                    echo "未";
                                } else {
                                    echo "済";
                                } ?>
                            </td>
                        </tr>
                        <!--OTの単位数-->
                        <tr>
                            <td>OT</td>
                            <td><?php echo $ot_base_num;
                                ?></td>
                            <td>
                                <select name="ot_change" id="ot_change" class="form-control input-md" type="select">
                                    <?php for ($i = - ($ot_base_num); $i <= ($ot_base_num + $i <= 9); $i++) {
                                        if ($i == 0) {
                                            echo "<option selected>";
                                            echo "$i";
                                            echo "</option>";
                                        } else {
                                            echo "<option>";
                                            echo "$i";
                                            echo "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <!--リハビリ‐患者テーブルにデータが存在しない＝未となる-->
                                <?php
                                if (!$patient) {
                                    echo "未";
                                } elseif ($patient && !$ot_number) {
                                    echo "未";
                                } else {
                                    echo "済";
                                } ?>
                            </td>
                        </tr>
                        <!--STの単位数-->
                        <tr>
                            <td>ST</td>
                            <td><?php echo $st_base_num; ?>
                            </td>
                            <td>
                                <select name="st_change" id="st_change" class="form-control input-md" type="select">
                                    <?php for ($i = - ($st_base_num); $i <= ($st_base_num + $i <= 9); $i++) {
                                        if ($i == 0) {
                                            echo "<option selected>";
                                            echo "$i";
                                            echo "</option>";
                                        } else {
                                            echo "<option>";
                                            echo "$i";
                                            echo "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <!--リハビリ‐患者テーブルにデータが存在しない＝未となる-->
                                <?php
                                if (!$patient) {
                                    echo "未";
                                } elseif ($patient && !$st_number) {
                                    echo "未";
                                } else {
                                    echo "済";
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-auto" style="border:none" ;></td>
                            <td class="col-auto" style="border:none" ;></td>
                            <td class="col-auto text-right" style="border:none" ;>
                            </td>
                            <td class="text-right" style="border:none" ;>
                                <button type="submit" class="btn btn-danger  text-white btn-lg" style="text-decoration:none;">保存</button>
                            </td>
                        </tr>
                    </tbody>
                </form>
            </table>
            <div class="scope-row">
                <div class="col-9"></div>
                <form method="post" action="./select.php">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="working_date" value="<?= $working_date ?>">
                    <input type="hidden" name="pt_adjustment" value="<?= $pt_adjustment ?>">
                    <input type="hidden" name="ot_adjustment" value="<?= $ot_adjustment ?>">
                    <input type="hidden" name="st_adjustment" value="<?= $st_adjustment ?>">
                    <input type="hidden" name="pt_base_num" value="<?= $pt_base_num ?>">
                    <input type="hidden" name="ot_base_num" value="<?= $ot_base_num ?>">
                    <input type="hidden" name="st_base_num" value="<?= $st_base_num ?>">
                    <div class="col-auto text-right">
                        <button type="submit" class="btn btn-warning mb-3 text-white btn-lg" style="text-decoration:none;">患者を選び直す</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <tbody>
                    <tr class="table-primary scope-row">
                        <th class="col-2">職種</th>
                        <th class="col-2 bg-warning">過不足</th>
                        <th class="col-8 bg-white" th style="border:none;"></th>
                    </tr>
                    <tr class="scope-row">
                        <td class="col-2">PT</td>
                        <td class="col-2"><?= $pt_adjustment ?></td>
                        <td class="col-8 bg-white" td style="border:none;"></td>
                    </tr>
                    <tr class="scope-row">
                        <td class="col-2">OT</td>
                        <td class="col-2"><?= $ot_adjustment ?></td>
                        <td class="col-8 bg-white" td style="border:none;"></td>
                    </tr>
                    <tr class="scope-row">
                        <td class="col-2">ST</td>
                        <td class="col-2"><?= $st_adjustment ?></td>
                        <td class="col-8 bg-white" td style="border:none;"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <form method="post" action="./edit_list.php">
                <input type="hidden" name="token" value="<?= $token ?>">
                <input type="hidden" name="working_date" value="<?= $working_date ?>">
                <input type="hidden" name="pt_adjustment" value="<?= $pt_adjustment ?>">
                <input type="hidden" name="ot_adjustment" value="<?= $ot_adjustment ?>">
                <input type="hidden" name="st_adjustment" value="<?= $st_adjustment ?>">
                <input type="hidden" name="pt_base_num" value="<?= $pt_base_num ?>">
                <input type="hidden" name="ot_base_num" value="<?= $ot_base_num ?>">
                <input type="hidden" name="st_base_num" value="<?= $st_base_num ?>">
                <button type="submit" class="btn-primary btn-lg" style="text-decoration:none;">確認画面へ進む</button>
            </form>
        </div>
        <div class="mt-2 mb-5 ml-2 text-right">
            <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
            <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
        </div>
    </div>
</body>

</html>