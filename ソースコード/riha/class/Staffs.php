<?php
/*スタッフ画面に関するクラス */
class Staffs
{
    private $pdo;
    const NOT_DELETE = 0;
    const DELETE = 1;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    /**
     * スタッフ全員の情報を取得するメソッド
     * @return array 全てのリハビリスタッフの情報を入れた多次元配列 
     */
    public function getStaffInfo()
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'id,';
        $sql .= 'staff_name,';
        $sql .= 'job,';
        $sql .= 'staff_gender,';
        $sql .= 'job_started_date,';
        $sql .= 'emergency_skill ';
        $sql .= 'FROM staff_list ';
        $sql .= 'WHERE is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * メールアドレスが一致するスタッフを省くメソッド
     * @param string $mail
     * @return array メールアドレスが一致したスタッフの情報を取得した連想配列
     */
    public function getStaffByMail($mail)
    {
        $sql = 'SELECT * FROM staff_list ';
        $sql .= 'WHERE mail=:mail ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':mail', $mail, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * スタッフを新規で追加するメソッド
     * @param string $staff_name
     * @param string $staff_family_name
     * @param string $staff_first_name
     * @param int $job
     * @param int $staff_gender
     * @param string $job_started_date
     * @param int $emergency_skill
     * @param string $mail
     * @param string $pass
     * @return bool 新規にスタッフのデータを挿入した結果
     */
    public function addStaff(
        $staff_name,
        $staff_family_name,
        $staff_first_name,
        $job,
        $staff_gender,
        $job_started_date,
        $emergency_skill,
        $mail,
        $pass
    ) {
        $sql = 'INSERT INTO ';
        $sql .= ' staff_list(';
        $sql .= 'staff_name,';
        $sql .= 'staff_family_name,';
        $sql .= 'staff_first_name,';
        $sql .= 'job,';
        $sql .= 'staff_gender,';
        $sql .= 'job_started_date,';
        $sql .= 'emergency_skill,';
        $sql .= 'mail,';
        $sql .= 'pass) ';
        $sql .= 'values(';
        $sql .= ':staff_name,';
        $sql .= ':staff_family_name,';
        $sql .= ':staff_first_name,';
        $sql .= ':job,';
        $sql .= ':staff_gender,';
        $sql .= ':job_started_date,';
        $sql .= ':emergency_skill,';
        $sql .= ':mail,';
        $sql .= ':pass)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':staff_name', $staff_name, PDO::PARAM_STR);
        $stmt->bindValue(':staff_family_name', $staff_family_name, PDO::PARAM_STR);
        $stmt->bindValue(':staff_first_name', $staff_first_name, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':staff_gender', $staff_gender, PDO::PARAM_INT);
        $stmt->bindValue(':job_started_date', $job_started_date, PDO::PARAM_STR);
        $stmt->bindValue(':emergency_skill', $emergency_skill, PDO::PARAM_INT);
        $stmt->bindValue(':mail', $mail, PDO::PARAM_STR);
        $stmt->bindValue(':pass', $pass, PDO::PARAM_STR);

        $result = $stmt->execute();
        return $result;
    }
    /**
     * 指定したスタッフの情報を取得するメソッド 
     * @param int $id
     * @return array idが一致したスタッフの情報を入れた連想配列 
     */
    public function getStaffInfoById($id)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'id,';
        $sql .= 'staff_name,';
        $sql .= 'staff_family_name,';
        $sql .= 'staff_first_name,';
        $sql .= 'job,';
        $sql .= 'staff_gender,';
        $sql .= 'job_started_date,';
        $sql .= 'emergency_skill ';
        $sql .= 'FROM staff_list ';
        $sql .= 'WHERE id=:id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * スタッフのデータを更新するメソッド 
     * @param int $id
     * @param string $staff_name
     * @param string $staff_family_name
     * @param string $staff_first_name
     * @param int $job
     * @param int $staff_gender
     * @param string $job_started_date
     * @param int $emergency_skill
     * @return bool スタッフのデータを変更した結果
     */
    public function editStaff(
        $id,
        $staff_name,
        $staff_family_name,
        $staff_first_name,
        $job,
        $staff_gender,
        $job_started_date,
        $emergency_skill
    ) {
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'staff_list ';
        $sql .= 'SET ';
        $sql .= 'id=:id,';
        $sql .= 'staff_name=:staff_name,';
        $sql .= 'staff_family_name=:staff_family_name,';
        $sql .= 'staff_first_name=:staff_first_name,';
        $sql .= 'job=:job,';
        $sql .= 'staff_gender=:staff_gender,';
        $sql .= 'job_started_date=:job_started_date,';
        $sql .= 'emergency_skill=:emergency_skill ';
        $sql .= 'WHERE ';
        $sql .= 'id=:id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':staff_name', $staff_name, PDO::PARAM_STR);
        $stmt->bindValue(':staff_family_name', $staff_family_name, PDO::PARAM_STR);
        $stmt->bindValue(':staff_first_name', $staff_first_name, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':staff_gender', $staff_gender, PDO::PARAM_INT);
        $stmt->bindValue(':job_started_date', $job_started_date, PDO::PARAM_STR);
        $stmt->bindValue(':emergency_skill', $emergency_skill, PDO::PARAM_INT);
        $result = $stmt->execute();
        return $result;
    }

    /*スタッフ削除に関するメソッド */
    /**
     * @param int $id
     * @return array idが一致するスタッフの担当患者の名前を入れた連想配列
     */
    public function checkPatientByStaff($id)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'p.patient_name ';
        $sql .= 'FROM patient_list as p ';
        $sql .= 'WHERE pt_base_id = :pt_base_id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $sql .= 'OR ot_base_id = :ot_base_id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $sql .= 'OR st_base_id = :st_base_id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':pt_base_id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ot_base_id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':st_base_id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 予約済のレコードがあるか確認するメソッド 
     * @param int $id
     * @param string $day
     * @return array idが一致するスタッフの登録されているリハビリ予約の日程を入れた連想配列
     */
    public function checkReservationByStaff(
        $id,
        $day
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'ps.reservation_date ';
        $sql .= 'FROM patient_staff as ps ';
        $sql .= 'WHERE today_staff_id = :today_staff_id ';
        $sql .= "AND is_deleted = :is_deleted ";
        $sql .= "AND reservation_date >= :delete_date ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':today_staff_id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindParam(':delete_date', $day, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 出勤の予約があるが確認するメソッド
     * @param int $id
     * @param string $day
     * @return array idが一致するスタッフの登録されている出勤日の日程を入れた連想配列
     */
    public function checkWorking(
        $id,
        $day
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'rw.working_date ';
        $sql .= 'FROM riha_work as rw ';
        $sql .= 'WHERE staff_id = :staff_id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $sql .= "AND working_date >= :delete_date ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':staff_id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindParam(':delete_date', $day, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * スタッフを削除するメソッド 
     * @param int $id
     * @return bool idが一致したスタッフを削除した結果 
     */
    public function deleteStaff($id)
    {
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'staff_list ';
        $sql .= 'SET ';
        $sql .= 'is_deleted=:is_deleted ';
        $sql .= 'WHERE ';
        $sql .= 'id=:id ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':is_deleted', self::DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $result = $stmt->execute();
        return $result;
    }
}
