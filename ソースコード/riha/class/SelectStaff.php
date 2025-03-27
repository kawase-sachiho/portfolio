<?php
/*スタッフ調整に関するメソッド */
class SelectStaff
{
    private $pdo;
    const NOT_DELETE = 0;
    const DELETE = 1;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    /**
     * 出勤者がいるか確認するメソッド
     * @param string $reservation_date
     * @param int $job
     * @return array 出勤しているスタッフの人数を入れた配列
     */
    public function countStaffByRihaDate(
        $reservation_date,
        $job
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'count(id) as count ';
        $sql .= 'FROM riha_work ';
        $sql .= 'WHERE working_date=:working_date ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':working_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * リハビリ‐患者テーブルのスタッフの合計単位を計算 
     * @param int $today_staff_id
     * @param string $reservation_date
     * @param int $job
     * @return array 指定したスタッフが割り当てられている単位の合計値を入れた連想配列
     */
    public function sumNumberByStaff(
        $today_staff_id,
        $reservation_date,
        $job
    ) {
        $sql = ' ';
        $sql .= 'SELECT ';
        $sql .= 'SUM(today_staff_num) ';
        $sql .= 'FROM patient_staff ';
        $sql .= 'WHERE today_staff_id=:today_staff_id ';
        $sql .= 'AND reservation_date=:reservation_date ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':today_staff_id', $today_staff_id, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * スタッフが当日担当する患者の人数を計算
     * @param int $today_staff_id
     * @param string $reservation_date
     * @param int $job
     * @return array 指定したスタッフが割り当てられている人数(データの数)の合計値を入れた連想配列
     */
    public function countPatientByStaff(
        $today_staff_id,
        $reservation_date,
        $job
    ) {
        $sql = ' ';
        $sql .= 'SELECT ';
        $sql .= 'COUNT(id) ';
        $sql .= 'FROM patient_staff ';
        $sql .= 'WHERE today_staff_id=:today_staff_id ';
        $sql .= 'AND reservation_date=:reservation_date ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':today_staff_id', $today_staff_id, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 患者とその担当スタッフの情報を獲得するメソッド 
     * @param int $patient_id
     * @return array 患者とその担当スタッフの情報を入れた連想配列
     */
    public function getPatientAndStaff($patient_id)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'hope_gender,';
        $sql .= 'staff_experience,';
        $sql .= 'emergency_risk,';
        $sql .= 'pt_base_id,';
        $sql .= 'ot_base_id,';
        $sql .= 'st_base_id ';
        $sql .= 'FROM patient_list ';
        $sql .= 'WHERE id=:id ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $patient_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 担当リハビリスタッフを獲得するメソッド  
     * @param int $base_id
     * @param int $job
     * @return array 担当スタッフの名前とidが入った連想配列
     */
    public function getBaseStaffInfo(
        $base_id,
        $job
    ) {
        if ($job == 0) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'id as staff_id,';
            $sql .= 'staff_name ';
            $sql .= 'FROM staff_list ';
            $sql .= 'WHERE id=:id ';
            $sql .= 'AND job=:job ';
        } elseif ($job == 1) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'id as staff_id,';
            $sql .= 'staff_name ';
            $sql .= 'FROM staff_list ';
            $sql .= 'WHERE id=:id ';
            $sql .= 'AND job=:job ';
        } elseif ($job == 2) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'id as staff_id,';
            $sql .= 'staff_name ';
            $sql .= 'FROM staff_list ';
            $sql .= 'WHERE id=:id ';
            $sql .= 'AND job=:job ';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $base_id, PDO::PARAM_INT);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * リハビリ‐患者テーブルに単位調整済のデータが存在するか確認するメソッド 
     * @param int $patient_id
     * @param string $reservation_date
     * @param int $job
     * @return array id・予約日・職種が一致するリハビリ予約のデータのidを入れた連想配列
     */
    public function searchUnitUpdated(
        $patient_id,
        $reservation_date,
        $job
    ) {
        $sql = ' ';
        $sql .= 'SELECT ';
        $sql .= 'id ';
        $sql .= 'FROM patient_staff ';
        $sql .= 'WHERE patient_id=:patient_id ';
        $sql .= 'AND reservation_date=:reservation_date ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 担当者の調整を行うメソッド  
     * @param int $patient_id
     * @param int $today_staff_id
     * @param int $today_staff_num
     * @param string $reservation_date
     * @return bool 担当者を調整した結果(既にレコードが存在した場合)
     */
    public function selectTodayStaff(
        $patient_id,
        $today_staff_id,
        $today_staff_num,
        $reservation_date
    ) {
        $job = $_POST['job'];
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'patient_staff ';
        $sql .= 'SET ';
        $sql .= 'today_staff_id=:today_staff_id,';
        $sql .= 'today_staff_num=:today_staff_num ';
        $sql .= 'WHERE patient_id=:patient_id ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND reservation_date=:reservation_date';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':today_staff_id', $today_staff_id, PDO::PARAM_INT);
        $stmt->bindValue(':today_staff_num', $today_staff_num, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_STR);
        $result = $stmt->execute();
        return $result;
    }
    /**
     * 担当者の調整と同時に単位数を決定してデータを挿入するメソッド
     * @param int $patient_id
     * @param string $reservation_date
     * @param int $today_staff_id
     * @param int $today_staff_num
     * @return bool 担当者を調整した結果(レコードが存在しない場合)
     */
    public function selectStaffAndNumber(
        $patient_id,
        $reservation_date,
        $today_staff_id,
        $today_staff_num
    ) {
        $job = $_POST['job'];
        $sql = '';
        $sql .= 'INSERT INTO ';
        $sql .= 'patient_staff(';
        $sql .= 'patient_id,';
        $sql .= 'job,';
        $sql .= 'reservation_date,';
        $sql .= 'today_staff_id,';
        $sql .= 'today_staff_num) ';
        $sql .= 'VALUES(';
        $sql .= ':patient_id,';
        $sql .= ':job,';
        $sql .= ':reservation_date,';
        $sql .= ':today_staff_id,';
        $sql .= ':today_staff_num)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':today_staff_id', $today_staff_id, PDO::PARAM_INT);
        $stmt->bindValue(':today_staff_num', $today_staff_num, PDO::PARAM_INT);
        $result = $stmt->execute();
        return $result;
    }
    /**
     * リハビリ予約の削除＝リハビリ担当者をリセットするメソッド 
     * @param int $patient_id
     * @param int $job
     * @param string $reservation_date
     * @return bool 担当者をリセット(リハビリ予約を削除)した結果
     */
    public function deleteReservation(
        $patient_id,
        $job,
        $reservation_date
    ) {
        $today_staff_id = 0;
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'patient_staff ';
        $sql .= 'SET ';
        $sql .= 'today_staff_id=:today_staff_id ';
        $sql .= 'WHERE patient_id=:patient_id ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND reservation_date=:reservation_date ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':today_staff_id', $today_staff_id, PDO::PARAM_INT);
        $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $result = $stmt->execute();
        return $result;
    }
    /**
     * 患者が割り当てられているスタッフの氏名を獲得するメソッド  
     * @param int $job
     * @param string $reservation_date
     * @return array 患者のリハビリ予約が入っているスタッフの名前を入れた多次元配列
     */
    public function getSelectedWorkers(
        $job,
        $reservation_date
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'staff_name ';
        $sql .= 'FROM staff_list as s ';
        $sql .= 'INNER JOIN patient_staff as ps ';
        $sql .= 'ON s.id=ps.today_staff_id AND reservation_date=:reservation_date ';
        $sql .= 'WHERE s.job=:job ';
        $sql .= 'AND ps.is_deleted=:is_deleted';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 出勤しているスタッフ全員の氏名を取得するメソッド 
     * @param int $job
     * @param string $reservation_date
     * @return array 出勤しているすべてのスタッフの名前を入れた多次元配列 
     */
    public function getWorkersNames(
        $job,
        $reservation_date
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'staff_name ';
        $sql .= 'FROM staff_list as s ';
        $sql .= 'INNER JOIN riha_work as rw ';
        $sql .= 'ON s.id=rw.staff_id AND working_date=:working_date ';
        $sql .= 'WHERE s.job=:job ';
        $sql .= 'AND rw.is_deleted=:is_deleted';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':working_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 担当者の調整が終わっている患者の氏名を取得するメソッド 
     * @param string $reservation_date
     * @param int $job
     * @return array 当日のリハビリスタッフが登録された患者の名前を入れた多次元配列
     */
    public function getSelectedNames(
        $reservation_date,
        $job
    ) {
        $reservation_id = 0;
        $sql = ' ';
        $sql .= 'SELECT ';
        $sql .= 'patient_name ';
        $sql .= 'FROM patient_staff as ps ';
        $sql .= 'INNER JOIN patient_list as p ';
        $sql .= 'ON ps.patient_id=p.id ';
        $sql .= 'WHERE reservation_date=:reservation_date ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND ps.is_deleted=:is_deleted ';
        $sql .= "AND today_staff_id > :reservation_id ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 0単位に調整した患者の名前を取得するメソッド
     * @param string $reservation_date
     * @return array 指定した職種において調整した後の単位が0の患者名前を入れた多次元配列
     */
    public function getNoUnitNames($reservation_date)
    {
        $updated_num = 0;
        $job = $_POST['job'];
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'p.patient_name ';
        $sql .= 'FROM patient_list as p ';
        $sql .= 'LEFT JOIN patient_staff as ps ';
        $sql .= 'ON p.id=ps.patient_id ';
        $sql .= 'WHERE ps.reservation_date=:reservation_date ';
        $sql .= 'AND ps.job=:job ';
        $sql .= 'AND ps.is_deleted=:is_deleted ';
        $sql .= 'AND ps.today_staff_num=:today_staff_num ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_INT);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':today_staff_num', $updated_num, PDO::PARAM_INT);
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 全ての患者の名前を取得するメソッド
     * @param int $job
     * @param string $reservation_date
     * @return array 全ての患者の名前を入れた多次元配列(基本単位が0の患者は除く)
     */
    public function getAllNamesByJob(
        $job,
        $reservation_date
    ) {
        $base_number = 0;
        if ($job == 0) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'patient_name ';
            $sql .= 'FROM patient_list ';
            $sql .= 'WHERE started_date <= :reservation_date ';
            $sql .= 'AND is_deleted=:is_deleted ';
            $sql .= 'AND pt_base_num > :base_number ';
        } elseif ($job == 1) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'patient_name ';
            $sql .= 'FROM patient_list ';
            $sql .= 'WHERE started_date <= :reservation_date ';
            $sql .= 'AND is_deleted=:is_deleted ';
            $sql .= 'AND ot_base_num > :base_number ';
        } elseif ($job == 2) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'patient_name ';
            $sql .= 'FROM patient_list ';
            $sql .= 'WHERE started_date <= :reservation_date ';
            $sql .= 'AND is_deleted=:is_deleted ';
            $sql .= 'AND st_base_num > :base_number ';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':reservation_date', $reservation_date, \PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindParam(':base_number', $base_number, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
