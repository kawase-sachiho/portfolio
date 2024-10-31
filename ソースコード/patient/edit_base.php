<?php
try {
    session_start();
    session_regenerate_id();

    require_once('../class/db/Safety.php');
    require_once('../class/db/Base.php');
    require_once('../class/Patients.php');

    //ログイン状態の確認
    if (empty($_SESSION['user'])) {
        header('Location:../login/index.php');
        exit;
    }

    //データベースへ接続する
    $pdo = Base::getInstance();
    $token = Safety::generateToken();

    //ユーザー名をidから取得する
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    } else {
        $id = $_SESSION['patient']['id'];
    }
    //患者情報を表示するメソッド
    $patient_info = new Patients($pdo);
    $patient = $patient_info->showPatientInfo($id);
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
    <title>患者情報の修正</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>

<body>
    <div class="container">
        <div class="mt-5 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../">TOPへ戻る</a>
        </div>
        <h1 class="mt-5 text-center text-warning"><u>基本情報の修正</u></h1>
        <div class="text my-3 ml-3 text-danger font-weight-bold"><u>
                <?php if (isset($_SESSION['err']['msg'])) {
                    echo $_SESSION['err']['msg'];
                }
                ?></u>
        </div>
        <div class="row my-4">
            <div class="text col-4 font-weight-bold ml-3">患者氏名</div>
            <div class="text col-4"><?= $patient['patient_name'] ?></div>
        </div>
        <div class="table-responsive">
            <table class="table table-borderless">
                <form method="post" action="./edit_detail.php">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="pt_base_id" value="<?= $patient['pt_base_id'] ?>">
                    <input type="hidden" name="ot_base_id" value="<?= $patient['ot_base_id'] ?>">
                    <input type="hidden" name="st_base_id" value="<?= $patient['st_base_id'] ?>">
                    <input type="hidden" name="pt_base_num" value="<?= $patient['pt_base_num'] ?>">
                    <input type="hidden" name="ot_base_num" value="<?= $patient['ot_base_num'] ?>">
                    <input type="hidden" name="st_base_num" value="<?= $patient['st_base_num'] ?>">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="patient_family_name">氏名を入力
                                </label>
                            </th>
                            <td class="col-4">
                                <label class="text control-label" for="patient_family_name">(姓)</label>
                                <input name="patient_family_name" id="family_name" class="form-control input-md" type="text" value="<?= $patient['patient_family_name'] ?>">
                            </td>
                            <td class="col-4">
                                <label class="text control-label" for="patient_first_name">(名)</label>
                                <input name="patient_first_name" id="first_name" class="form-control input-md" type="text" value="<?= $patient['patient_first_name'] ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="gender">担当スタッフの性別</label>
                            </th>
                            <td>
                                <label class="text radio-inline" for="hope_gender[0]">
                                    <input name="hope_gender" id="hope_gender[0]" value="0" type="radio" <?= $patient['hope_gender'] == 0 ? 'checked' : '' ?>>
                                    <span class="text" style="margin-right:2em;">どちらでもよい</span>
                                </label>
                                <label class="text radio-inline" for="hope_gender[1]">
                                    <input name="hope_gender" id="hope_gender[1]" value="1" type="radio" <?= $patient['hope_gender'] == 1 ? 'checked' : '' ?>>
                                    <span style="margin-right:2em;">男性</span>
                                </label>
                                <label class="text radio-inline" for="gender[2]">
                                    <input name="hope_gender" id="hope_gender[2]" value="2" type="radio" <?= $patient['hope_gender'] == 2 ? 'checked' : '' ?>>
                                    女性
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="experience">担当スタッフの経験年数</label>
                            </th>
                            <td>
                                <label class="text radio-inline" for="staff_experience[0]">
                                    <input name="staff_experience" id="staff_experience[0]" value="0" type="radio" <?= $patient['staff_experience'] == 0 ? 'checked' : '' ?>>
                                    <span style="margin-right:4em;">誰でも可</span>
                                </label>
                                <label class="text radio-inline" for="staff_experience[1]">
                                    <input name="staff_experience" id="staff_experience[1]" value="1" type="radio" <?= $patient['staff_experience'] == 1 ? 'checked' : '' ?>>
                                    3年目以上
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="emergency">急変リスク</label>
                            </th>
                            <td>
                                <label class="text radio-inline" for="emergency_risk[0]">
                                    <input name="emergency_risk" id="emergency_risk[0]" value="0" type="radio" <?= $patient['emergency_risk'] == 0 ? 'checked' : '' ?>>
                                    <span style="margin-right:4em;">なし</span>
                                </label>
                                <label class="text radio-inline" for="emergency_risk[1]">
                                    <input name="emergency_risk" id="emergency_risk[1]" value="1" type="radio" <?= $patient['emergency_risk'] == 1 ? 'checked' : '' ?>>
                                    あり
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="started_date">
                                    リハビリ開始日
                                </label>
                            </th>
                            <td>
                                <input name="started_date" id="started_date" class="form-control input-md" type="date" value="<?= $patient['started_date'] ?>">
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th class="text-right">
                                <button type="submit" class="btn btn-lg btn-primary">担当者の修正へ</button>
                            </th>
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