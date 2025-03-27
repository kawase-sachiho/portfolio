<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/CountUnit.php');
require_once('../class/Patients.php');
require_once('../class/SelectStaff.php');

//ログイン状態のチェック
if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
//トークンのチェック
if (isset($_POST['token'])) {
    if (!Safety::isValidToken($_POST['token'])) {
        $_SESSION['err']['msg'] = "不正な処理が行われました";
        header('Location:../error.php');
        return;
    } else {
        unset($_SESSION['err']['msg']);
    }
}
try {
    //POSTの値が存在する場合
    if (isset($_POST['patient_family_name']) || isset($_POST['patient_first_name']) || isset($_POST['started_date'])) {
        //文字数が多すぎる場合リダイレクトする
        if ((strlen($_POST['patient_family_name']) > 25) || (strlen($_POST['patient_first_name'])  > 25)) {
            $_SESSION['err']['msg'] = "文字数は25文字以下にしてください。";
            $_SESSION['patient']['id'] = $_POST['id'];
            header("Location:./edit_base.php");
            exit;
        }
        //空欄があった場合リダイレクトする
        if (empty($_POST['patient_family_name']) || empty($_POST['patient_first_name']) || empty($_POST['started_date'])) {
            $_SESSION['err']['msg'] = "すべての項目に入力してください";
            $_SESSION['patient']['id'] = $_POST['id'];
            header('Location:./edit_base.php');
            exit;
        }
        //日付の値が正しくない場合はリダイレクトする
        if (!strtotime($_POST['started_date'])) {
            $_SESSION['err']['msg'] = "期限日の日付が正しくありません";
            $_SESSION['patient']['id'] = $_POST['id'];
            header("Location: ./edit_base.php");
            exit;
        }
        unset($_SESSION['err']['msg']);
    }
    unset($rec);

    $post = Safety::sanitaize($_POST);
    //POSTで送られてきた値を代入
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $pt_base_id = $_POST['pt_base_id'];
        $ot_base_id = $_POST['ot_base_id'];
        $st_base_id = $_POST['st_base_id'];
        $pt_base_num = $_POST['pt_base_num'];
        $ot_base_num = $_POST['ot_base_num'];
        $st_base_num = $_POST['st_base_num'];
        $patient_name = $post['patient_family_name'] . $post['patient_first_name'];
        $patient_family_name = $post['patient_family_name'];
        $patient_first_name = $post['patient_first_name'];
        $hope_gender = $_POST['hope_gender'];
        $staff_experience = $_POST['staff_experience'];
        $emergency_risk = $_POST['emergency_risk'];
        $started_date = $post['started_date'];
    } else {
        //POSTでなければセッションから値を代入
        $id = $_SESSION['patient']['id'];
        $pt_base_id = $_SESSION['patient']['pt_base_id'];
        $ot_base_id = $_SESSION['patient']['ot_base_id'];
        $st_base_id = $_SESSION['patient']['st_base_id'];
        $pt_base_num = $_SESSION['patient']['pt_base_num'];
        $ot_base_num = $_SESSION['patient']['ot_base_num'];
        $st_base_num = $_SESSION['patient']['st_base_num'];
        $patient_name = $_SESSION['patient']['patient_name'];
        $patient_family_name = $_SESSION['patient']['patient_family_name'];
        $patient_first_name = $_SESSION['patient']['patient_first_name'];
        $hope_gender = $_SESSION['patient']['hope_gender'];
        $staff_experience = $_SESSION['patient']['staff_experience'];
        $emergency_risk = $_SESSION['patient']['emergency_risk'];
        $started_date = $_SESSION['patient']['started_date'];
    }
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
    <title>患者登録</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../">TOPへ戻る</a>
        </div>
        <h1 class="mt-5 text-center text-warning"><u>担当者の修正</u></h1>
        <div class="text text-primary font-weight-bold">
            <u>！まだ修正は完了していません！</u>
        </div>

        <div class="table-responsive">
            <table class="table table-borderless ">
                <form method="post" action="./edit_action.php">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="patient_name" value="<?= $patient_name ?>">
                    <input type="hidden" name="patient_family_name" value="<?= $patient_family_name ?>">
                    <input type="hidden" name="patient_first_name" value="<?= $patient_first_name ?>">
                    <input type="hidden" name="hope_gender" value="<?= $hope_gender ?>">
                    <input type="hidden" name="staff_experience" value="<?= $staff_experience ?>">
                    <input type="hidden" name="emergency_risk" value="<?= $emergency_risk ?>">
                    <input type="hidden" name="started_date" value="<?= $started_date ?>">
                    <input type="hidden" name="pt_base_id" value="<?= $patient['pt_base_id'] ?>">
                    <input type="hidden" name="ot_base_id" value="<?= $patient['ot_base_id'] ?>">
                    <input type="hidden" name="st_base_id" value="<?= $patient['st_base_id'] ?>">
                    <input type="hidden" name="pt_base_num" value="<?= $patient['pt_base_num'] ?>">
                    <input type="hidden" name="ot_base_num" value="<?= $patient['ot_base_num'] ?>">
                    <input type="hidden" name="st_base_num" value="<?= $patient['st_base_num'] ?>">
                    <tbody>
                        <tr>
                            <th class="text">
                                患者氏名
                            </th>
                            <th class="text">
                                <?= $patient_name ?>様
                            </th>
                        </tr>
                        <tr>
                            <td class="text col-4">
                                <?php
                                //PTのみを抽出して連想配列に入れる
                                $job = 0;
                                $staff_pt = new CountUnit($pdo);
                                $pts = $staff_pt->getStaffByJob($job);

                                $pt3unders = array();
                                $pt_3overs = array();
                                $pt_skills = array();

                                //PT男性スタッフのみ取り出し
                                $pt_mens = array();
                                $pt_men_3overs = array();
                                $pt_men_skills = array();

                                foreach ($pts as $pt) {
                                    if ($pt['staff_gender'] == 0) {
                                        array_push($pt_mens, $pt);
                                        //3年目以上
                                        if (Date::getExperience($day, $pt)) {
                                            array_push($pt_men_3overs, $pt);
                                            //スキルの有無
                                            if ($pt['emergency_skill'] == 1) {
                                                array_push($pt_men_skills, $pt);
                                            }
                                        }
                                    }
                                }
                                //女性スタッフ取り出し
                                $pt_womens = array();
                                $pt_women_3overs = array();
                                $pt_women_skills = array();

                                foreach ($pts as $pt) {
                                    if ($pt['staff_gender'] == 1) {
                                        array_push($pt_womens, $pt);
                                        //3年目以上
                                        if (Date::getExperience($day, $pt)) {
                                            array_push($pt_women_3overs, $pt);
                                            //スキルの有無
                                            if ($pt['emergency_skill'] == 1) {
                                                array_push($pt_women_skills, $pt);
                                            }
                                        }
                                    }
                                }
                                foreach ($pts as $pt) {
                                    if (Date::getExperience($day, $pt)) {
                                        array_push($pt_3overs, $pt);
                                    } else {
                                        array_push($pt3unders, $pt);
                                    }
                                }
                                foreach ($pts as $pt) {
                                    if ($pt['emergency_skill'] == 1) {
                                        array_push($pt_skills, $pt);
                                    }
                                }
                                ?>
                                <label class="control-label" for="pt_base_id">担当PT</label>
                                <select name="pt_base_id" id="pt_base=id" class="form-control input-md" type="select">
                                    <?php
                                    foreach ($pt_men_skills as $pt_men_skill) {
                                        if ($hope_gender == 1 && $staff_experience == 1 && $emergency_risk == 1) {
                                            if ($pt_men_skill['id'] == $pt_base_id) {
                                                echo '<option value="' . $pt_men_skill['id'] . '" selected>' . $pt_men_skill['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $pt_men_skill['id'] . '">' . $pt_men_skill['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }

                                    //男性、３年目以上(救急対応不可含む全員)
                                    foreach ($pt_men_3overs as $pt_men_3over) {
                                        if ($hope_gender == 1 && $staff_experience == 1 && $emergency_risk == 0) {
                                            if ($pt_men_3over['id'] == $pt_base_id) {
                                                echo '<option value="' . $pt_men_3over['id'] . '" selected>' . $pt_men_3over['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $pt_men_3over['id'] . '">' . $pt_men_3over['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }

                                    //男性、３年目以下、救急対応不可(＝全員)
                                    foreach ($pt_mens as $pt_men) {
                                        if ($hope_gender == 1 && $staff_experience == 0 && $emergency_risk == 0) {
                                            if ($pt_men['id'] == $pt_base_id) {
                                                echo '<option value="' . $pt_men['id'] . '" selected>' . $pt_men['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $pt_men['id'] . '">' . $pt_men['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }

                                    //女性、３年目以上、救急対応可能
                                    foreach ($pt_women_skills as $pt_women_skill) {
                                        if ($hope_gender == 2 && $staff_experience == 1 && $emergency_risk == 1) {
                                            if ($pt_women_skill['id'] == $pt_base_id) {
                                                echo '<option value="' . $pt_women_skill['id'] . '" selected>' . $pt_women_skill['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $pt_women_skill['id'] . '">' . $pt_women_skill['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //女性、３年目以上(救急対応不可含む全員)
                                    foreach ($pt_women_3overs as $pt_women_3over) {
                                        if ($hope_gender == 2 && $staff_experience == 1 && $emergency_risk == 0) {
                                            if ($pt_women_3over['id'] == $pt_base_id) {
                                                echo '<option value="' . $pt_women_3over['id'] . '" selected>' . $pt_women_3over['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $pt_women_3over['id'] . '">' . $pt_women_3over['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //女性全員
                                    foreach ($pt_womens as $pt_women) {
                                        if ($hope_gender == 2 && $staff_experience == 0 && $emergency_risk == 0) {
                                            if ($pt_women['id'] == $pt_base_id) {
                                                echo '<option value="' . $pt_women['id'] . '" selected>' . $pt_women['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $pt_women['id'] . '">' . $pt_women['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //性別指定なし、3年目以上
                                    foreach ($pt_3overs as $pt_3over) {
                                        if ($hope_gender == 0 && $staff_experience == 1 && $emergency_risk == 0) {
                                            if ($pt_3over['id'] == $pt_base_id) {
                                                echo '<option value="' . $pt_3over['id'] . '" selected>' . $pt_3over['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $pt_3over['id'] . '">' . $pt_3over['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //性別指定なし、３年目以上、救急対応可能
                                    foreach ($pt_skills as $pt_skill) {
                                        if ($hope_gender == 0 && $staff_experience == 1 && $emergency_risk == 1) {
                                            if ($pt_skill['id'] == $pt_base_id) {
                                                echo '<option value="' . $pt_skill['id'] . '" selected>' . $pt_skill['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $pt_skill['id'] . '">' . $pt_skill['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //PT全員
                                    foreach ($pts as $pt) {
                                        if ($hope_gender == 0 && $staff_experience == 0 && $emergency_risk == 0) {
                                            if ($pt['id'] == $pt_base_id) {
                                                echo '<option value="' . $pt['id'] . '" selected>' . $pt['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $pt['id'] . '">' . $pt['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    ?>
                            </td>
                            <td class="text col-2">
                                <label class="control-label" for="pt_num">単位数</label>
                                <select name="pt_base_num" id="pt_base_num" class="form-control input-md" type="select">
                                    <option <?= $pt_base_num == 0 ? 'selected' : '' ?>>0</option>
                                    <option <?= $pt_base_num == 1 ? 'selected' : '' ?>>1</option>
                                    <option <?= $pt_base_num == 2 ? 'selected' : '' ?>>2</option>
                                    <option <?= $pt_base_num == 3 ? 'selected' : '' ?>>3</option>
                                    <option <?= $pt_base_num == 4 ? 'selected' : '' ?>>4</option>
                                    <option <?= $pt_base_num == 5 ? 'selected' : '' ?>>5</option>
                                    <option <?= $pt_base_num == 6 ? 'selected' : '' ?>>6</option>
                                    <option <?= $pt_base_num == 7 ? 'selected' : '' ?>></option>
                                    <option <?= $pt_base_num == 8 ? 'selected' : '' ?>>8</option>
                                    <option <?= $pt_base_num == 9 ? 'selected' : '' ?>>9</option>

                            </td>
                            <td class="col-6">
                            </td>
                        </tr>
                        <tr>
                            <td class="text col-4">
                                <?php
                                //OTのみ抽出して連想配列に入れる
                                $job = 1;
                                $staff_ot = new CountUnit($pdo);
                                $ots = $staff_ot->getStaffByJob($job);

                                $ot_3unders = array();
                                $ot_3overs = array();
                                $ot_skills = array();

                                //PT男性スタッフのみ取り出し
                                $ot_mens = array();
                                $ot_men_3overs = array();
                                $ot_men_skills = array();

                                foreach ($ots as $ot) {
                                    if ($ot['staff_gender'] == 0) {
                                        array_push($ot_mens, $ot);
                                        //3年目以上
                                        if (Date::getExperience($day, $ot)) {
                                            array_push($ot_men_3overs, $ot);
                                            //スキルの有無
                                            if ($ot['emergency_skill'] == 1) {
                                                array_push($ot_men_skills, $ot);
                                            }
                                        }
                                    }
                                }
                                //女性スタッフ取り出し
                                $ot_womens = array();
                                $ot_women_3overs = array();
                                $ot_women_skills = array();

                                foreach ($ots as $ot) {
                                    if ($ot['staff_gender'] == 1) {
                                        array_push($ot_womens, $ot);
                                        //3年目以上
                                        if (Date::getExperience($day, $ot)) {
                                            array_push($ot_women_3overs, $ot);
                                            //スキルの有無
                                            if ($ot['emergency_skill'] == 1) {
                                                array_push($ot_women_skills, $ot);
                                            }
                                        }
                                    }
                                }
                                foreach ($ots as $ot) {
                                    if (Date::getExperience($day, $ot)) {
                                        array_push($ot_3overs, $ot);
                                    } else {
                                        array_push($ot_3unders, $ot);
                                    }
                                }
                                foreach ($ots as $ot) {
                                    if ($ot['emergency_skill'] == 1) {
                                        array_push($ot_skills, $ot);
                                    }
                                }
                                ?>
                                <label class="control-label" for="ot_base_id">担当OT</label>
                                <select name="ot_base_id" id="ot_base_id" class="form-control input-md" type="select">
                                    <?php
                                    //男性、３年目以上、救急対応可能
                                    foreach ($ot_men_skills as $ot_men_skill) {
                                        if ($hope_gender == 1 && $staff_experience == 1 && $emergency_risk == 1) {
                                            if ($ot_men_skill['id'] == $ot_base_id) {
                                                echo '<option value="' . $ot_men_skill['id'] . '" selected>' . $ot_men_skill['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $ot_men_skill['id'] . '">' . $ot_men_skill['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //男性、３年目以上(救急対応不可含む全員)
                                    foreach ($ot_men_3overs as $ot_men_3over) {
                                        if ($hope_gender == 1 && $staff_experience == 1 && $emergency_risk == 0) {
                                            if ($ot_men_3over['id'] == $ot_base_id) {
                                                echo '<option value="' . $ot_men_3over['id'] . '" selected>' . $ot_men_3over['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $ot_men_3over['id'] . '">' . $ot_men_3over['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //男性、３年目以下、救急対応不可(＝全員)
                                    foreach ($ot_mens as $ot_men) {
                                        if ($hope_gender == 1 && $staff_experience == 0 && $emergency_risk == 0) {
                                            if ($ot_men['id'] == $ot_base_id) {
                                                echo '<option value="' . $ot_men['id'] . '" selected>' . $ot_men['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $ot_men['id'] . '">' . $ot_men['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //女性、３年目以上、救急対応可能
                                    foreach ($ot_women_skills as $ot_women_skill) {
                                        if ($hope_gender == 2 && $staff_experience == 1 && $emergency_risk == 1) {
                                            if ($ot_women_skill['id'] == $ot_base_id) {
                                                echo '<option value="' . $ot_women_skill['id'] . '" selected>' . $ot_women_skill['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $ot_women_skill['id'] . '">' . $ot_women_skill['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //女性、３年目以上(救急対応不可含む全員)
                                    foreach ($ot_women_3overs as $ot_women_3over) {
                                        if ($hope_gender == 2 && $staff_experience == 1 && $emergency_risk == 0) {
                                            if ($ot_women_3over['id'] == $ot_base_id) {
                                                echo '<option value="' . $ot_women_3over['id'] . '" selected>' . $ot_women_3over['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $ot_women_3over['id'] . '">' . $ot_women_3over['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //女性全員
                                    foreach ($ot_womens as $ot_women) {
                                        if ($hope_gender == 2 && $staff_experience == 0 && $emergency_risk == 0) {
                                            if ($ot_women['id'] == $ot_base_id) {
                                                echo '<option value="' . $ot_women['id'] . '" selected>' . $ot_women['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $ot_women['id'] . '">' . $ot_women['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //性別指定なし、3年目以上
                                    foreach ($ot_3overs as $ot_3over) {
                                        if ($hope_gender == 0 && $staff_experience == 1 && $emergency_risk == 0) {
                                            if ($ot_3over['id'] == $ot_base_id) {
                                                echo '<option value="' . $ot_3over['id'] . '" selected>' . $ot_3over['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $ot_3over['id'] . '">' . $ot_3over['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //性別指定なし、３年目以上、救急対応可能
                                    foreach ($ot_skills as $ot_skill) {
                                        if ($hope_gender == 0 && $staff_experience == 1 && $emergency_risk == 1) {
                                            if ($ot_skill['id'] == $ot_base_id) {
                                                echo '<option value="' . $ot_skill['id'] . '" selected>' . $ot_skill['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $ot_skill['id'] . '">' . $ot_skill['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //OT全員
                                    foreach ($ots as $ot) {
                                        if ($hope_gender == 0 && $staff_experience == 0 && $emergency_risk == 0) {
                                            if ($ot['id'] == $ot_base_id) {
                                                echo '<option value="' . $ot['id'] . '" selected>' . $ot['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $ot['id'] . '">' . $ot['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    ?>
                            </td>
                            <td class="text col-2">
                                <label class="control-label" for="ot_num">単位数</label>
                                <select name="ot_base_num" id="ot_base_num" class="form-control input-md" type="select">
                                    <option <?= $ot_base_num == 0 ? 'selected' : '' ?>>0</option>
                                    <option <?= $ot_base_num == 1 ? 'selected' : '' ?>>1</option>
                                    <option <?= $ot_base_num == 2 ? 'selected' : '' ?>>2</option>
                                    <option <?= $ot_base_num == 3 ? 'selected' : '' ?>>3</option>
                                    <option <?= $ot_base_num == 4 ? 'selected' : '' ?>>4</option>
                                    <option <?= $ot_base_num == 5 ? 'selected' : '' ?>>5</option>
                                    <option <?= $ot_base_num == 6 ? 'selected' : '' ?>>6</option>
                                    <option <?= $ot_base_num == 7 ? 'selected' : '' ?>>7</option>
                                    <option <?= $ot_base_num == 8 ? 'selected' : '' ?>>8</option>
                                    <option <?= $ot_base_num == 9 ? 'selected' : '' ?>>9</option>

                            </td>
                            <td class="col-6 ">
                            </td>
                        </tr>
                        <tr>
                            <td class="text col-4">
                                <?php
                                //STのみ抽出して連想配列に入れる
                                $job = 2;
                                $staff_st = new CountUnit($pdo);
                                $sts = $staff_st->getStaffByJob($job);

                                $st_3unders = array();
                                $st_3overs = array();
                                $st_skills = array();

                                //ST男性スタッフのみ取り出し
                                $st_mens = array();
                                $st_men_3overs = array();
                                $st_men_skills = array();

                                foreach ($sts as $st) {
                                    if ($st['staff_gender'] == 0) {
                                        array_push($st_mens, $st);
                                        //3年目以上
                                        if (Date::getExperience($day, $st)) {
                                            array_push($st_men_3overs, $st);
                                            //スキルの有無
                                            if ($st['emergency_skill'] == 1) {
                                                array_push($st_men_skills, $st);
                                            }
                                        }
                                    }
                                }
                                //女性スタッフ取り出し
                                $st_womens = array();
                                $st_women_3overs = array();
                                $st_women_skills = array();

                                foreach ($sts as $st) {
                                    if ($st['staff_gender'] == 1) {
                                        array_push($st_womens, $st);
                                        //3年目以上
                                        if (Date::getExperience($day, $st)) {
                                            array_push($st_women_3overs, $st);
                                            //スキルの有無
                                            if ($st['emergency_skill'] == 1) {
                                                array_push($st_women_skills, $st);
                                            }
                                        }
                                    }
                                }
                                foreach ($sts as $st) {
                                    if (Date::getExperience($day, $st)) {
                                        array_push($st_3overs, $st);
                                    } else {
                                        array_push($st_3unders, $st);
                                    }
                                }
                                foreach ($sts as $st) {
                                    if ($st['emergency_skill'] == 1) {
                                        array_push($st_skills, $st);
                                    }
                                }
                                ?>
                                <label class="control-label" for="st_name">担当ST</label>
                                <select name="st_base_id" id="st_base_id" class="form-control input-md" type="select">

                                    <?php
                                    //男性、３年目以上、救急対応可能
                                    foreach ($st_men_skills as $st_men_skill) {
                                        if ($hope_gender == 1 && $staff_experience == 1 && $emergency_risk == 1) {
                                            if ($st_men_skill['id'] == $st_base_id) {
                                                echo '<option value="' . $st_men_skill['id'] . '" selected>' . $st_men_skill['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $st_men_skill['id'] . '">' . $st_men_skill['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //男性、３年目以上(救急対応不可含む全員)
                                    foreach ($st_men_3overs as $st_men_3over) {
                                        if ($hope_gender == 1 && $staff_experience == 1 && $emergency_risk == 0) {
                                            if ($st_men_3over['id'] == $st_base_id) {
                                                echo '<option value="' . $st_men_3over['id'] . '" selected>' . $st_men_3over['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $st_men_3over['id'] . '">' . $st_men_3over['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //男性、３年目以下、救急対応不可(＝全員)
                                    foreach ($st_mens as $st_men) {
                                        if ($hope_gender == 1 && $staff_experience == 0 && $emergency_risk == 0) {
                                            if ($st_men['id'] == $st_base_id) {
                                                echo '<option value="' . $st_men['id'] . '" selected>' . $st_men['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $st_men['id'] . '">' . $st_men['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //女性、３年目以上、救急対応可能
                                    foreach ($st_women_skills as $st_women_skill) {
                                        if ($hope_gender == 2 && $staff_experience == 1 && $emergency_risk == 1) {
                                            if ($st_women_skill['id'] == $st_base_id) {
                                                echo '<option value="' . $st_women_skill['id'] . '" selected>' . $st_women_skill['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $st_women_skill['id'] . '">' . $st_women_skill['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //女性、３年目以上(救急対応不可含む全員)
                                    foreach ($st_women_3overs as $st_women_3over) {
                                        if ($hope_gender == 2 && $staff_experience == 1 && $emergency_risk == 0) {
                                            if ($st_women_3over['id'] == $st_base_id) {
                                                echo '<option value="' . $st_women_3over['id'] . '" selected>' . $st_women_3over['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $st_women_3over['id'] . '">' . $st_women_3over['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //女性全員
                                    foreach ($st_womens as $st_women) {
                                        if ($hope_gender == 2 && $staff_experience == 0 && $emergency_risk == 0) {
                                            if ($st_women['id'] == $st_base_id) {
                                                echo '<option value="' . $st_women['id'] . '" selected>' . $st_women['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $st_women['id'] . '">' . $st_women['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //性別指定なし、3年目以上
                                    foreach ($st_3overs as $st_3over) {
                                        if ($hope_gender == 0 && $staff_experience == 1 && $emergency_risk == 0) {
                                            if ($st_3over['id'] == $st_base_id) {
                                                echo '<option value="' . $st_3over['id'] . '" selected>' . $st_3over['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $st_3over['id'] . '">' . $st_3over['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //性別指定なし、３年目以上、救急対応可能
                                    foreach ($st_skills as $st_skill) {
                                        if ($hope_gender == 0 && $staff_experience == 1 && $emergency_risk == 1) {
                                            if ($st_skill['id'] == $st_base_id) {
                                                echo '<option value="' . $st_skill['id'] . '" selected>' . $st_skill['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $st_skill['id'] . '">' . $st_skill['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    //OT全員
                                    foreach ($sts as $st) {
                                        if ($hope_gender == 0 && $staff_experience == 0 && $emergency_risk == 0) {
                                            if ($st['id'] == $st_base_id) {
                                                echo '<option value="' . $st['id'] . '" selected>' . $st['staff_name'] . '</option>';
                                                continue;
                                            } else {
                                                echo '<option value="' . $st['id'] . '">' . $st['staff_name'] . '</option>';
                                                continue;
                                            }
                                        }
                                    }
                                    ?>
                            </td>
                            <td class="text col-2">
                                <label class="control-label" for="st_num">単位数</label>
                                <select name="st_base_num" id="st_base_num" class="form-control input-md" type="select">
                                    <option <?= $st_base_num == 0 ? 'selected' : '' ?>>0</option>
                                    <option <?= $st_base_num == 1 ? 'selected' : '' ?>>1</option>
                                    <option <?= $st_base_num == 2 ? 'selected' : '' ?>>2</option>
                                    <option <?= $st_base_num == 3 ? 'selected' : '' ?>>3</option>
                                    <option <?= $st_base_num == 4 ? 'selected' : '' ?>>4</option>
                                    <option <?= $st_base_num == 5 ? 'selected' : '' ?>>5</option>
                                    <option <?= $st_base_num == 6 ? 'selected' : '' ?>>6</option>
                                    <option <?= $st_base_num == 7 ? 'selected' : '' ?>>7</option>
                                    <option <?= $st_base_num == 8 ? 'selected' : '' ?>>8</option>
                                    <option <?= $st_base_num == 9 ? 'selected' : '' ?>>9</option>
                            </td>
                            <td class="col-6">
                            </td>
                        </tr>
                        <tr>
                            <td class="text col-4 text-danger font-weight-bold">
                                <?php if (isset($_SESSION['err']['msg'])) {
                                    echo $_SESSION['err']['msg'];
                                }
                                ?></td>
                            <td class="col-2"></td>
                            <td>
                                <button type="submit" class="btn btn-lg btn-danger text-white">患者登録</button>
                            </td>
                        </tr>
                    </tbody>
                </form>
            </table>
            <div class=" mt-2 mb-5 ml-2 text-right">
                <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
                <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
            </div>
        </div>
</body>
</html>