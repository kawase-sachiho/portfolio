<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Common.php');
require_once('../class/CountUnit.php');
require_once('../class/SelectStaff.php');

if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}

try {
    $pdo = Base::getInstance();
    $token = Safety::generateToken();

    //本日の日付を取得し$dayに入れる
    $day = Date::getDate();

    //前のページで計算した値を入れると共に、editのページで保存した値を代入したい
    if (isset($_SESSION['select'])) {
        $reservation_date = $_SESSION['select']['reservation_date'];
        $job = $_SESSION['select']['job'];
    } else {
        $reservation_date = $_POST['reservation_date'];
        $job = $_POST['job'];
    }

    if (isset($_SESSION['select']['patient_id'])) {
        $patient_id = $_SESSION['select']['patient_id'];
    } else {
        $patient_id = $_POST['patient_id'];
    }

    //患者と担当者情報の獲得するメソッド
    $patient_and_staff = new SelectStaff($pdo);
    $patient_info = $patient_and_staff->getPatientAndStaff($patient_id);

    $hope_gender = $patient_info['hope_gender'];
    $staff_experience = $patient_info['staff_experience'];
    $emergency_risk = $patient_info['emergency_risk'];
    $pt_base_id = $patient_info['pt_base_id'];
    $ot_base_id = $patient_info['ot_base_id'];
    $st_base_id = $patient_info['st_base_id'];

    $working_date = $reservation_date;

    //担当PTの獲得
    $base_id = $pt_base_id;
    $base_pt_info = new SelectStaff($pdo);
    $base_pt = $base_pt_info->getBaseStaffInfo(
        $base_id,
        $job
    );

    //担当OTの獲得
    $base_id = $ot_base_id;
    $base_ot_info = new SelectStaff($pdo);
    $base_ot = $base_ot_info->getBaseStaffInfo(
        $base_id,
        $job
    );

    //担当STの獲得
    $base_id = $st_base_id;
    $base_st_info = new SelectStaff($pdo);
    $base_st = $base_st_info->getBaseStaffInfo(
        $base_id,
        $job
    );

    //単位調整済かどうか確認するメソッド
    $check_unit_changed = new CountUnit($pdo);
    $patient = $check_unit_changed->checkUpadateUnit(
        $patient_id,
        $working_date
    );

    if ($patient) {
        $patient_name = $patient['patient_name'];
        if ($job == 0) {
            //調整済の単位を獲得するメソッド
            $pt_changed_num = new CountUnit($pdo);
            $pt_number = $pt_changed_num->getTodayNum(
                $job,
                $patient_id,
                $working_date
            );
            //PTのレコードが見つからなかった場合
            if (!$pt_number) {
                $pt_basic_num = new CountUnit($pdo);
                $pt_base_number = $pt_basic_num->getBaseNumByjob(
                    $job,
                    $patient_id
                );
                $pt_today_num = $pt_base_number['pt_base_num'];
            } else {
                $pt_today_num = $pt_number['today_staff_num'];
            }
        } elseif ($job == 1) {
            //調整済の単位を獲得するメソッド
            $ot_changed_num = new CountUnit($pdo);
            $ot_number = $ot_changed_num->getTodayNum(
                $job,
                $patient_id,
                $working_date
            );
            //OTのレコードが見つからなかった場合
            if (!$ot_number) {
                $ot_basic_num = new CountUnit($pdo);
                $ot_base_number = $ot_basic_num->getBaseNumByjob(
                    $job,
                    $patient_id
                );
                $ot_today_num = $ot_base_number['ot_base_num'];
            } else {
                $ot_today_num = $ot_number['today_staff_num'];
            }
        } elseif ($job == 2) {
            //調整済の単位を獲得するメソッド
            $st_changed_num = new CountUnit($pdo);
            $st_number = $st_changed_num->getTodayNum(
                $job,
                $patient_id,
                $working_date
            );
            //STのレコードが見つからなかった場合
            if (!$st_number) {
                $st_basic_num = new CountUnit($pdo);
                $st_base_number = $st_basic_num->getBaseNumByjob(
                    $job,
                    $patient_id
                );
                $st_today_num = $st_base_number['st_base_num'];
            } else {
                $st_today_num = $st_number['today_staff_num'];
            }
        }
    } else {
        //単位調整していない場合の単位数を獲得するメソッド
        $not_changed_numbers = new CountUnit($pdo);
        $patient_numbers = $not_changed_numbers->getBaseNumbers($patient_id);

        $pt_today_num = $patient_numbers['pt_base_num'];
        $ot_today_num = $patient_numbers['ot_base_num'];
        $st_today_num = $patient_numbers['st_base_num'];
        $patient_name = $patient_numbers['patient_name'];
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
    <title>担当者の選択</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">

</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../">TOPへ戻る</a>
        </div>
        <h1 class="my-5 text-center text-warning"><u>担当者の選択</u></h1>
        <div class="row my-3">
            <div class="text col-auto font-weight-bold text-danger"><?php echo Date::showDate($reservation_date); ?></div>
            <div class="text col-auto font-weight-bold"><?php echo $patient_name . "さんの担当者調整"; ?></div>
            <div class="text font-weight-bold">
                <?php if (isset($_SESSION['err']['msg'])) {
                    echo $_SESSION['err']['msg'];
                } ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <form method="post" action="./select_action.php">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="reservation_date" value="<?= $reservation_date ?>">
                    <input type="hidden" name="job" value="<?= $job ?>">
                    <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
                    <input type="hidden" name="pt_today_num" value="<?= $pt_today_num ?>">
                    <input type="hidden" name="ot_today_num" value="<?= $ot_today_num ?>">
                    <input type="hidden" name="st_today_num" value="<?= $st_today_num ?>">
                    <tbody>
                        <tr class="table-primary">
                            <th class="col-4"><?php Info::showJob($job); ?></th>
                            <th class="col-2">単位数</th>
                            <td class="col-2 bg-white" style="border:none" ;></td>
                        </tr>
                        <tr>
                            <td class="col-4">
                                <?php if ($job == 0) {

                                    //PTのみを抽出して連想配列に入れる
                                    $staff_pt = new CountUnit($pdo);
                                    $pts = $staff_pt->getWorkingStaffByJob(
                                        $job,
                                        $working_date
                                    );

                                    $pt_3unders = array();
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
                                            array_push($pt_3unders, $pt);
                                        }
                                    }
                                    foreach ($pts as $pt) {
                                        if ($pt['emergency_skill'] == 1) {
                                            array_push($pt_skills, $pt);
                                        }
                                    }
                                ?>
                                    <select name="pt_today_id" id="pt_today_id" class="form-control input-md" type="select">
                                        <?php
                                        //男性、３年目以上、救急対応可能
                                        foreach ($pt_men_skills as $pt_men_skill) {
                                            if ($hope_gender == 1 && $staff_experience == 1 && $emergency_risk == 1) {
                                                if ($pt_men_skill['id'] == $base_pt['staff_id']) {
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
                                                if ($pt_men_3over['id'] == $base_pt['staff_id']) {
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
                                                if ($pt_men['id'] == $base_pt['staff_id']) {
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
                                                if ($pt_women_skill['id'] == $base_pt['staff_id']) {
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
                                                if ($pt_women_3over['id'] == $base_pt['staff_id']) {
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
                                                if ($pt_women['id'] == $base_pt['staff_id']) {
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
                                                if ($pt_3over['id'] == $base_pt['staff_id']) {
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
                                                if ($pt_skill['id'] == $base_pt['staff_id']) {
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
                                                if ($pt['id'] == $base_pt['staff_id']) {
                                                    echo '<option value="' . $pt['id'] . '" selected>' . $pt['staff_name'] . '</option>';
                                                    continue;
                                                } else {
                                                    echo '<option value="' . $pt['id'] . '">' . $pt['staff_name'] . '</option>';
                                                    continue;
                                                }
                                            }
                                        }
                                    } elseif ($job == 1) {
                                        //OTのみ抽出して連想配列に入れる
                                        $staff_ot = new CountUnit($pdo);
                                        $ots = $staff_ot->getWorkingStaffByJob($job, $working_date);

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
                                        <select name="ot_today_id" id="ot_today_id" class="form-control input-md" type="select">
                                            <?php
                                            //男性、３年目以上、救急対応可能
                                            foreach ($ot_men_skills as $ot_men_skill) {
                                                if ($hope_gender == 1 && $staff_experience == 1 && $emergency_risk == 1) {
                                                    if ($ot_men_skill['id'] == $base_ot['staff_id']) {
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
                                                    if ($ot_men_3over['id'] == $base_ot['staff_id']) {
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
                                                    if ($ot_men['id'] == $base_ot['staff_id']) {
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
                                                    if ($ot_women_skill['id'] == $base_ot['staff_id']) {
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
                                                    if ($ot_women_3over['id'] == $base_ot['staff_id']) {
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
                                                    if ($ot_women['id'] == $base_ot['staff_id']) {
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
                                                    if ($ot_3over['id'] == $base_ot['staff_id']) {
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
                                                    if ($ot_skill['id'] == $base_ot['staff_id']) {
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
                                                    if ($ot['id'] == $base_ot['staff_id']) {
                                                        echo '<option value="' . $ot['id'] . '" selected>' . $ot['staff_name'] . '</option>';
                                                        continue;
                                                    } else {
                                                        echo '<option value="' . $ot['id'] . '">' . $ot['staff_name'] . '</option>';
                                                        continue;
                                                    }
                                                }
                                            }
                                        } else {
                                            $job = 2;
                                            $staff_st = new CountUnit($pdo);
                                            $sts = $staff_st->getWorkingStaffByJob($job, $working_date);

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
                                            <select name="st_today_id" id="st_today_id" class="form-control input-md" type="select">

                                            <?php
                                            //男性、３年目以上、救急対応可能
                                            foreach ($st_men_skills as $st_men_skill) {
                                                if ($hope_gender == 1 && $staff_experience == 1 && $emergency_risk == 1) {
                                                    if ($st_men_skill['id'] == $base_st['staff_id']) {
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
                                                    if ($st_men_3over['id'] == $base_st['staff_id']) {
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
                                                    if ($st_men['id'] == $base_st['staff_id']) {
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
                                                    if ($st_women_skill['id'] == $base_st['staff_id']) {
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
                                                    if ($st_women_3over['id'] == $base_st['staff_id']) {
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
                                                    if ($st_women['id'] == $base_st['staff_id']) {
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
                                                    if ($st_3over['id'] == $base_st['staff_id']) {
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
                                                    if ($st_skill['id'] == $base_st['staff_id']) {
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
                                                    if ($st['id'] == $base_st['staff_id']) {
                                                        echo '<option value="' . $st['id'] . '" selected>' . $st['staff_name'] . '</option>';
                                                        continue;
                                                    } else {
                                                        echo '<option value="' . $st['id'] . '">' . $st['staff_name'] . '</option>';
                                                        continue;
                                                    }
                                                }
                                            }
                                        }
                                            ?>
                            </td>
                            <td class="col-2"><?php if ($job == 0) {
                                                    echo $pt_today_num;
                                                } elseif ($job == 1) {
                                                    echo $ot_today_num;
                                                } else {
                                                    echo $st_today_num;
                                                }
                                                ?>
                            </td>
                            <td class="col-2" style="border:none" ;>
                                <button type="submit" class="btn btn-danger  text-white btn-lg" style="text-decoration:none;">保存</button>
                            </td>
                        </tr>
                    </tbody>
                </form>
            </table>
        </div>
        <div class="scope-row mt-3">
            <form method="post" action="./select_patient.php">
                <input type="hidden" name="token" value="<?= $token ?>">
                <input type="hidden" name="reservation_date" value="<?= $reservation_date ?>">
                <input type="hidden" name="job" value="<?= $job ?>">
                <div class="col-auto text-right" style="display:inline-block;">
                    <button type="submit" class="btn btn-warning mb-3 text-white text-left btn-lg" style="text-decoration:none;">患者を選び直す</button>
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