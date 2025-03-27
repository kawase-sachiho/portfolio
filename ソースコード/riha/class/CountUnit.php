<?php
/*単位調整に関するメソッド*/
class CountUnit
{
    private $pdo;
    const NOT_DELETE = 0;
    const DELETE = 1;
    const EDIT = 1;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    /**
     * 勤務人数を集計するメソッド 
     * @param string $working_date
     * @return array 出勤人数を職種ごとに計算したデータを入れた配列
     */
    public function countWorkingStaff($working_date)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'count(staff_id),';
        $sql .= 'job ';
        $sql .= 'FROM riha_work ';
        $sql .= 'WHERE working_date=:working_date ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $sql .= 'GROUP BY job';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':working_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * PT/OT/STの合計単位数を取得するメソッド
     * @param string $working_date
     * @return array 出勤日に対するPT/OT/STそれぞれの合計単位数
     */
    public function sumNumbers($working_date)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'sum(pt_base_num),';
        $sql .= 'sum(ot_base_num),';
        $sql .= 'sum(st_base_num) ';
        $sql .= 'FROM patient_list ';
        $sql .= "WHERE started_date <= :working_date ";
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindParam(':working_date', $working_date, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 予約日とリハビリ開始日に基づいて患者一覧を取得するメソッド
     * @param string $working_date
     * @return array 指定した日にリハビリの対象となる患者一覧
     */
    public function getPatientsByRihaDate($working_date)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'id as patient_id,';
        $sql .= 'patient_name,';
        $sql .= 'started_date ';
        $sql .= 'FROM patient_list ';
        $sql .= "WHERE started_date <= :working_date ";
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':working_date', $working_date, \PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 単位調整が実施済かを確認するメソッド
     * @param int $patient_id
     * @param string $working_date
     * @return array リハビリ‐患者テーブルに存在する患者のデータ
     */
    public function checkUpadateUnit(
        $patient_id,
        $working_date
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'patient_id,';
        $sql .= 'patient_name ';
        $sql .= 'FROM patient_staff as ps ';
        $sql .= 'LEFT join patient_list as p ';
        $sql .= 'ON ps.patient_id=p.id ';
        $sql .= 'WHERE patient_id=:patient_id ';
        $sql .= 'AND reservation_date=:reservation_date ';
        $sql .= 'AND ps.is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * PT/OT/STの調整済の単位を取得するメソッド
     * @param int $job
     * @param int $patient_id
     * @param string $working_date
     * @return array 単位調整済の患者のid/リハビリ職種/単位数/リハビリ予約日を格納した配列
     */
    public function getTodayNum(
        $job,
        $patient_id,
        $working_date
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'patient_id,';
        $sql .= 'today_staff_num,';
        $sql .= 'job,';
        $sql .= 'reservation_date ';
        $sql .= 'FROM patient_staff ';
        $sql .= 'WHERE patient_id=:patient_id AND job=:job ';
        $sql .= 'AND reservation_date=:reservation_date ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * いずれかの職種のレコードが存在した場合に、PT・OT・STの基本単位を個別で取得
     * @param int $job
     * @param int $patient_id
     * @return int 職種ごとの基本単位数
     */
    public function getBaseNumByJob(
        $job,
        $patient_id
    ) {
        if ($job == 0) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'pt_base_num ';
            $sql .= 'FROM patient_list ';
            $sql .= 'WHERE id=:id ';
            $sql .= 'AND is_deleted=:is_deleted ';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $patient_id, PDO::PARAM_INT);
            $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } elseif ($job == 1) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'ot_base_num ';
            $sql .= 'FROM patient_list ';
            $sql .= 'WHERE id=:id ';
            $sql .= 'AND is_deleted=:is_deleted ';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $patient_id, PDO::PARAM_INT);
            $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } elseif ($job == 2) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'st_base_num ';
            $sql .= 'FROM patient_list ';
            $sql .= 'WHERE id=:id ';
            $sql .= 'AND is_deleted=:is_deleted ';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $patient_id, PDO::PARAM_INT);
            $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        }
    }
    /**
     * 初回の単位調整の時 PT・OT・STの基本単位をまとめて取得する
     * @param int $patient_id
     * @return array PT・OT・STの基本単位と患者氏名が入った連想配列
     */
    public function getBaseNumbers($patient_id)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'patient_name,';
        $sql .= 'pt_base_num,';
        $sql .= 'ot_base_num,';
        $sql .= 'st_base_num ';
        $sql .= 'FROM patient_list ';
        $sql .= 'WHERE id=:id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 初回の単位調整か確認するメソッド 
     * @param int $job
     * @param int $patient_id
     * @param string $working_date
     * @return array リハビリ‐患者テーブルに条件が合致するデータがあれば返却
     */
    public function checkUpdateUnitByJob(
        $job,
        $patient_id,
        $working_date
    ) {
        if ($job == 0) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'patient_id ';
            $sql .= 'FROM patient_staff ';
            $sql .= 'WHERE patient_id=:patient_id AND job=:job ';
            $sql .= 'AND reservation_date=:reservation_date ';
            $sql .= 'AND is_deleted=:is_deleted ';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
            $stmt->bindValue(':job', $job, PDO::PARAM_INT);
            $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
            $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } elseif ($job == 1) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'patient_id ';
            $sql .= 'FROM patient_staff ';
            $sql .= 'WHERE patient_id=:patient_id AND job=:job ';
            $sql .= 'AND reservation_date=:reservation_date ';
            $sql .= 'AND is_deleted=:is_deleted ';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
            $stmt->bindValue(':job', $job, PDO::PARAM_INT);
            $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
            $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } elseif ($job == 2) {
            $sql = '';
            $sql .= 'SELECT ';
            $sql .= 'patient_id ';
            $sql .= 'FROM patient_staff ';
            $sql .= 'WHERE patient_id=:patient_id AND job=:job ';
            $sql .= 'AND reservation_date=:reservation_date ';
            $sql .= 'AND is_deleted=:is_deleted ';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
            $stmt->bindValue(':job', $job, PDO::PARAM_INT);
            $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
            $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        }
    }
    /**
     * 2回目以降の単位変更を行うメソッド
     * @param int $patient_id
     * @param int $job
     * @param int $num
     * @param string $working_date
     * @return bool リハビリ-患者テーブルの情報の更新した結果
     */
    public function reUpdateUnitByJob(
        $patient_id,
        $job,
        $num,
        $working_date
    ) {
        if ($job == 0) {
            $sql = '';
            $sql .= 'UPDATE ';
            $sql .= 'patient_staff ';
            $sql .= 'SET ';
            $sql .= 'patient_id=:patient_id,';
            $sql .= 'job=:job,';
            $sql .= 'today_staff_num=:today_staff_num,';
            $sql .= 'reservation_date=:reservation_date,';
            $sql .= 'edit=:edit ';
            $sql .= 'WHERE patient_id=:patient_id ';
            $sql .= 'AND job=:job ';
            $sql .= 'AND reservation_date=:reservation_date ';
            $sql .= 'AND is_deleted=:is_deleted ';
        } elseif ($job == 1) {
            $sql = '';
            $sql .= 'UPDATE ';
            $sql .= 'patient_staff ';
            $sql .= 'SET ';
            $sql .= 'patient_id=:patient_id,';
            $sql .= 'job=:job,';
            $sql .= 'today_staff_num=:today_staff_num,';
            $sql .= 'reservation_date=:reservation_date,';
            $sql .= 'edit=:edit ';
            $sql .= 'WHERE patient_id=:patient_id ';
            $sql .= 'AND job=:job ';
            $sql .= 'AND reservation_date=:reservation_date ';
            $sql .= 'AND is_deleted=:is_deleted ';
        } elseif ($job == 2) {
            $sql = '';
            $sql .= 'UPDATE ';
            $sql .= 'patient_staff ';
            $sql .= 'SET ';
            $sql .= 'patient_id=:patient_id,';
            $sql .= 'job=:job,';
            $sql .= 'today_staff_num=:today_staff_num,';
            $sql .= 'reservation_date=:reservation_date,';
            $sql .= 'edit=:edit ';
            $sql .= 'WHERE patient_id=:patient_id ';
            $sql .= 'AND job=:job ';
            $sql .= 'AND reservation_date=:reservation_date ';
            $sql .= 'AND is_deleted=:is_deleted ';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':today_staff_num', $num, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':edit', self::EDIT, PDO::PARAM_INT);
        $result = $stmt->execute();
        return $result;
    }
    /**
     *  初回の単位調整を行うメソッド
     * @param int $patient_id
     * @param int $job
     * @param string $working_date
     * @param int $num
     * @return bool リハビリ‐患者テーブルにリハビリ情報を登録した結果結果
     */
    public function updateUnitByJob(
        $patient_id,
        $job,
        $working_date,
        $num
    ) {
        if ($job == 0) {
            $sql = '';
            $sql .= 'INSERT ';
            $sql .= 'INTO ';
            $sql .= 'patient_staff(';
            $sql .= 'patient_id,';
            $sql .= 'job,';
            $sql .= 'reservation_date,';
            $sql .= 'today_staff_num,';
            $sql .= 'edit) ';
            $sql .= 'VALUES(';
            $sql .= ':patient_id,';
            $sql .= ':job,';
            $sql .= ':reservation_date,';
            $sql .= ':today_staff_num,';
            $sql .= ':edit)';
        } elseif ($job == 1) {
            $sql = '';
            $sql .= 'INSERT ';
            $sql .= 'INTO ';
            $sql .= 'patient_staff(';
            $sql .= 'patient_id,';
            $sql .= 'job,';
            $sql .= 'reservation_date,';
            $sql .= 'today_staff_num,';
            $sql .= 'edit) ';
            $sql .= 'VALUES(';
            $sql .= ':patient_id,';
            $sql .= ':job,';
            $sql .= ':reservation_date,';
            $sql .= ':today_staff_num,';
            $sql .= ':edit)';
        } elseif ($job == 2) {
            $sql = '';
            $sql .= 'INSERT ';
            $sql .= 'INTO ';
            $sql .= 'patient_staff(';
            $sql .= 'patient_id,';
            $sql .= 'job,';
            $sql .= 'reservation_date,';
            $sql .= 'today_staff_num,';
            $sql .= 'edit) ';
            $sql .= 'VALUES(';
            $sql .= ':patient_id,';
            $sql .= ':job,';
            $sql .= ':reservation_date,';
            $sql .= ':today_staff_num,';
            $sql .= ':edit)';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':today_staff_num', $num, PDO::PARAM_INT);
        $stmt->bindValue(':edit', self::EDIT, PDO::PARAM_INT);
        $result = $stmt->execute();
        return $result;
    }
    /**
     * 単位調整した患者一覧を獲得するメソッド
     * @param string $working_date
     * @return array 単位調整を行った全ての患者の名前・idが入った多次元配列
     */
    public function getPatientsUnitUpdated($working_date)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'patient_id,';
        $sql .= 'patient_name ';
        $sql .= 'FROM patient_staff as ps ';
        $sql .= 'inner join patient_list as p ';
        $sql .= 'ON ps.patient_id=p.id ';
        $sql .= 'WHERE reservation_date=:reservation_date ';
        $sql .= 'AND p.is_deleted=:is_deleted ';
        $sql .= 'AND ps.edit=:edit ';
        $sql .= 'GROUP BY ps.patient_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':edit', self::EDIT, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 調整した単位一覧を獲得するメソッド
     * @param int $job
     * @param string $working_date
     * @return array 単位調整を行った全ての患者の単位数を獲得する多次元配列
     */
    public function getUnitUpdated(
        $job,
        $working_date
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'today_staff_num ';
        $sql .= 'FROM patient_staff ';
        $sql .= 'WHERE reservation_date=:reservation_date ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $sql .= 'AND edit=:edit ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':job', $job, PDO::PARAM_STR);
        $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':edit', self::EDIT, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 職種ごとにスタッフの情報を取得する
     * @param int $job
     * @return array 職種ごとのすべてのスタッフの情報が入った多次元配列
     */
    public function getStaffByJob($job)
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
        $sql .= 'WHERE job=:job ';
        $sql .= 'AND is_deleted=:is_deleted';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 勤務しているスタッフの情報を取得
     * @param int $job
     * @param string $working_date
     * @return array 出勤しているスタッフの情報が入った多次元配列(職種の指定要)
     */
    public function getWorkingStaffByJob(
        $job,
        $working_date
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'staff_name,';
        $sql .= 'staff_list.job,';
        $sql .= 'staff_gender,';
        $sql .= 'job_started_date,';
        $sql .= 'emergency_skill,';
        $sql .= 'staff_id as id ';
        $sql .= 'FROM staff_list ';
        $sql .= 'INNER JOIN riha_work ';
        $sql .= 'ON staff_list.id=riha_work.staff_id AND working_date=:working_date ';
        $sql .= 'WHERE staff_list.job=:job ';
        $sql .= 'AND riha_work.is_deleted=:is_deleted';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':working_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
