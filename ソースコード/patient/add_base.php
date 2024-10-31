<?php
session_start();
session_regenerate_id();

require_once('../class/db/Base.php');
require_once('../class/db/Safety.php');
require_once('../class/Patients.php');

//ログイン状態のチェック
if (empty($_SESSION['user'])) {
    header('Location:../login/index.php');
    exit;
}
try {
    //セッションにデータがあれば代入する
    if (isset($_SESSION['patient'])) {
        $patient_family_name = $_SESSION['patient']['patient_family_name'];
        $patient_first_name = $_SESSION['patient']['patient_first_name'];
        $hope_gender = $_SESSION['patient']['hope_gender'];
        $staff_experience = $_SESSION['patient']['staff_experience'];
        $emergency_risk = $_SESSION['patient']['emergency_risk'];
        $started_date = $_SESSION['patient']['started_date'];
    }
    $pdo = Base::getInstance();
    $token = Safety::generateToken();
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
        <div class="mt-5 mb-2 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../">TOPへ戻る</a>
        </div>
        <h1 class="mt-5 text-center text-warning"><u>患者登録</u></h1>
        <div class="my-3 ml-3 text-danger font-weight-bold"><u>
                <?php if (isset($_SESSION['err']['msg'])) {
                    echo $_SESSION['err']['msg'];
                }
                ?></u>
        </div>
        <div class="table-responsive">
            <table class="table table-borderless ">
                <form method="post" action="./add_detail.php">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="patient_family_name">氏名を入力
                                </label>
                            </th>
                            <td class="col-4">
                                <label class="text control-label" for="patient_family_name">(姓)</label>
                                <input name="patient_family_name" id="patient_family_name" class="form-control input-md" type="text" value="<?= isset($patient_family_name) ? $patient_family_name : '' ?>">
                            </td>
                            <td class="col-4">
                                <label class="text control-label" for="patient_first_name">(名)</label>
                                <input name="patient_first_name" id="patient_first_name" class="form-control input-md" type="text" value="<?= isset($patient_first_name) ? $patient_first_name : '' ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="hope_gender">担当スタッフの性別</label>
                            </th>
                            <td>
                                <label class="text radio-inline" for="hope_gender[0]">
                                    <input name="hope_gender" id="hope_gender[0]" value="0" type="radio" <?= !isset($hope_gender) || $hope_gender == 0 ? 'checked' : '' ?>>
                                    <span style="margin-right:2em;">どちらでもよい</span>
                                </label>
                                <label class="text radio-inline" for="hope_gender[1]">
                                    <input name="hope_gender" id="hope_gender[1]" value="1" type="radio" <?= isset($hope_gender) && $hope_gender == 1 ? 'checked' : '' ?>>
                                    <span style="margin-right:2em;">男性</span>
                                </label>
                                <label class="text radio-inline" for="hope_gender[2]">
                                    <input name="hope_gender" id="hope_gender[2]" value="2" type="radio" <?= isset($hope_gender) && $hope_gender == 2 ? 'checked' : '' ?>>
                                    女性
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="staff_experience">担当スタッフの経験年数</label>
                            </th>
                            <td>
                                <label class="text radio-inline" for="staff_experience[0]">
                                    <input name="staff_experience" id="staff_experience[0]" value="0" type="radio" <?= !isset($staff_experience) || $staff_experience == 0 ? 'checked' : '' ?>>
                                    <span style="margin-right:4em;">誰でも可</span>
                                </label>
                                <label class="text radio-inline" for="staff_experience[1]">
                                    <input name="staff_experience" id="staff_experience[1]" value="1" type="radio" <?= isset($staff_experience) && $staff_experience == 1 ? 'checked' : '' ?>>
                                    3年目以上
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="emergency_risk">急変リスク</label>
                            </th>
                            <td>
                                <label class="text radio-inline" for="emergency_risk[0]">
                                    <input name="emergency_risk" id="emergency_risk[0]" value="0" type="radio" <?= !isset($emergency_risk) || $emergency_risk == 0 ? 'checked' : '' ?>>
                                    <span style="margin-right:4em;">なし</span>
                                </label>
                                <label class="text radio-inline" for="emergency_risk[1]">
                                    <input name="emergency_risk" id="emergency_risk[1]" value="1" type="radio" <?= isset($emergency_risk) && $emergency_risk == 1 ? 'checked' : '' ?>>
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
                                <input name="started_date" id="started_date" class="form-control input-md" type="date" value="<?= isset($started_date)  ? $started_date : '' ?>">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <button type="submit" class="btn btn-primary btn-lg">担当者の選択へ</button>
                            </th>
                        </tr>

                    </tbody>
                </form>
            </table>
            <div class="mt-2 mb-5 ml-2 text-right">
                <span class="text font-weight-bold mr-3">スタッフ <u><?= $_SESSION['user']['staff_name'] ?></u> さん</span>
                <a class="btn btn-warning btn-lg" href="../login/logout.php" style="text-decoration:none;">ログアウト</a>
            </div>
        </div>
</body>

</html>