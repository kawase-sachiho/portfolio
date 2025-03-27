<?php
    session_start();
    session_regenerate_id();

    require_once('../class/db/Safety.php');
    require_once('../class/db/Base.php');
    require_once('../class/Staffs.php');

    //ログイン状態のチェック
    if (empty($_SESSION['user'])) {
        header('Location:../login/index.php');
        exit;
    }
    try{
    //データベースへ接続する
    $pdo = Base::getInstance();
    $token = Safety::generateToken();

    //ユーザー名をidから取得する
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    } elseif (isset($_SESSION['staff']['id'])) 
    {
        $id = $_SESSION['staff']['id'];
    }
    //スタッフ情報を表示するメソッド
    $edit_staff_info = new Staffs($pdo);
    $staff = $edit_staff_info->getStaffInfoById($id);
} catch (Exception $e) 
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
    <title>スタッフデータ編集</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../class/css/style.css">
</head>
<body>
    <div class="container">
        <div class="mt-5 mb-2 text-right">
            <a class="btn-warning btn-lg" style="text-decoration:none;" href="../index.php">TOPへ戻る</a>
        </div>
        <h1 class="mt-5 text-center text-warning"><u>スタッフデータ編集</u></h1>
        <div class="text my-3 ml-3 text-danger font-weight-bold"><u><?php if (isset($_SESSION['err']['msg'])) {
                        echo $_SESSION['err']['msg'];
                    }
                    ?></u></div>
        <div class="text row my-4">
            <div class="col-4 font-weight-bold ml-3">スタッフ氏名</div>
            <div class="col-4"><?= $staff['staff_name'] ?></div>
        </div>
        <div class="table-responsive">
            <table class="table table-borderless ">
                <form method="post" action="./edit_action.php">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="id" value="<?= $staff['id'] ?>">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="staff_family_name">氏名入力
                                </label>
                            </th>
                            <td class="col-4">
                                <label class="text control-label" for="staff_family_name">(姓)</label>
                                <input name="staff_family_name" id="staff_family_name" class="form-control input-md" type="text" value="<?= $staff['staff_family_name'] ?>">
                            </td>
                            <td class="col-4">
                                <label class="text control-label" for="staff_first_name">(名)</label>
                                <?php if (isset($staff['staff_first_name'])) : ?>
                                    <input name="staff_first_name" id="staff_first_name" class="form-control input-md" type="text" value="<?= $staff['staff_first_name'] ?>">
                                <?php elseif (isset($_SESSION['post']['staff_first_name'])) : ?>
                                    <input name="staff_first_name" id="staff_first_name" class="form-control input-md" type="text" value="<?= $_SESSION['post']['staff_first_name'] ?>">
                                <?php else : ?>
                                    <input name="staff_first_name" id="staff_first_name" class="form-control input-md" type="text" value="">
                                <?php endif ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="gender">性別</label>
                            </th>
                            <td>
                                <label class="text radio-inline" for="staff_gender[0]">
                                    <input name="staff_gender" id="staff_gender[0]" value="0" type="radio" <?= $staff['staff_gender'] == 0 ? 'checked' : '' ?>>
                                    <span style="margin-right:4em;">男性</span>
                                </label>
                                <label class="text radio-inline" for="staff_gender[1]">
                                    <input name="staff_gender" id="gender[1]" value="1" type="radio" <?= $staff['staff_gender']==1 ? 'checked' : '' ?>>
                                    女性
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="job">職種</label>
                            </th>
                            <td>
                                <label class="text radio-inline" for="job[0]">
                                    <input name="job" id="job[0]" value="0" type="radio" <?= $staff['job']==0 ? 'checked' : '' ?>>
                                    <span style="margin-right:4em;">PT</span>
                                </label>
                                <label class="text radio-inline" for="job[1]">
                                    <input name="job" id="job[1]" value="1" type="radio" <?= $staff['job']==1 ? 'checked' : '' ?>>
                                    <span style="margin-right:4em;">OT</span>
                                </label>
                                <label class="text radio-inline" for="job[2]">
                                    <input name="job" id="job[2]" value="2" type="radio" <?= $staff['job']==2 ? 'checked' : '' ?>>
                                    ST
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="job_started_date">勤務開始日
                                    <span class="text ml-3">※経験年数</span>
                                </label>
                            </th>
                            <td>
                                <input name="job_started_date" id="job_started_date" class="form-control input-md" type="date" value="<?= $staff['job_started_date'] ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="text control-label" for="emergency_skill">急変時の対応</label>
                            </th>
                            <td>
                                <label class="text radio-inline" for="emergency_skill[0]">
                                    <input name="emergency_skill" id="emergency_skill[0]" value="0" type="radio" <?= $staff['emergency_skill']==0 ? 'checked' : '' ?>>
                                    <span style="margin-right:4em;">不可</span>
                                </label>
                                <label class="text radio-inline" for="emergency_skill[1]">
                                    <input name="emergency_skill" id="emergency_skill[1]" value="1" type="radio" <?= $staff['emergency_skill']==1 ? 'checked' : '' ?>>
                                    可
                                </label>
                            </td>
                        </tr>
                        <tr><th></th>
                        <th></th>
                            
                            <th class="text-right">
                                <button type="submit" class="btn btn-primary btn-lg">データを修正する</button>
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