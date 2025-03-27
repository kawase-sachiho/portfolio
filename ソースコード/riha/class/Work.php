<?php
/** 勤務管理に関するメソッド */
class Work
{
    private $pdo;
    const NOT_DELETE = 0;
    const DELETE = 1;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    /**
     * 出勤しているスタッフの情報を取得するメソッド 
     * @param int $job
     * @param string $working_date
     * @return array 指定した日に出勤しているスタッフ全員の情報を入れた多次元配列
     */
    public function getWorkingStaff(
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
        $sql .= 'staff_id as working_staff ';
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
    /**
     * 職種ごとにスタッフの情報を獲得し、出勤の有無を確認するメソッド 
     * @param int $job
     * @param string $working_date
     * @return array スタッフの出勤の有無を含めた情報を含んだ多次元配列
     */
    public function getStaffListForWork(
        $job,
        $working_date
    ) {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'staff_list.id,';
        $sql .= 'staff_name,';
        $sql .= 'staff_list.job,';
        $sql .= 'staff_gender,';
        $sql .= 'job_started_date,';
        $sql .= 'emergency_skill,';
        $sql .= 'riha_work.is_deleted,';
        $sql .= 'staff_id as working_staff ';
        $sql .= 'FROM staff_list ';
        $sql .= 'LEFT JOIN riha_work ';
        $sql .= 'ON staff_list.id=riha_work.staff_id AND working_date=:working_date ';
        $sql .= 'WHERE staff_list.job=:job ';
        $sql .= 'AND staff_list.is_deleted=:is_deleted';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':working_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 職種ごとにリハビリ予約のデータが存在するか確認するメソッド
     * @param string $working_date
     * @param int $job
     * @return array リハビリ予約が存在するスタッフの名前が入った多次元配列
     */
    public function checkReservation(
        $working_date,
        $job
    ) {
        $reservation_id = 0;
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 's.staff_name ';
        $sql .= 'FROM patient_staff AS ps ';
        $sql .= 'INNER JOIN staff_list AS s ';
        $sql .= 'ON ps.today_staff_id=s.id ';
        $sql .= 'WHERE ps.reservation_date=:reservation_date ';
        $sql .= 'AND ps.job=:job ';
        $sql .= 'AND ps.today_staff_id > :reservation_id ';
        $sql .= 'AND ps.is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':reservation_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 出勤テーブルに出勤登録のデータが存在するか確認するメソッド
     * @param string $working_date
     * @param int $job
     * @return array 出勤テーブルに存在する出勤登録のスタッフのデータを取得した多次元配列
     */
    public function checkWorkingData(
        $working_date,
        $job
    ) {
        $sql = '';
        $sql .= 'SELECT * ';
        $sql .= 'FROM riha_work ';
        $sql .= 'WHERE working_date=:working_date ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND is_deleted=:is_deleted';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':working_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 出勤テーブルから削除済のデータを取得するメソッド 
     * @param string $working_date
     * @param int $job
     * @return array 出勤から欠勤へ変更したスタッフのデータを取得した多次元配列 
     */
    public function getDeleteWorkers(
        $working_date,
        $job
    ) {
        $sql = '';
        $sql .= 'SELECT * ';
        $sql .= 'FROM riha_work ';
        $sql .= 'WHERE working_date=:working_date ';
        $sql .= 'AND job=:job ';
        $sql .= 'AND is_deleted=:is_deleted';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':working_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 新規で追加した人の出勤データを登録する 
     * @param string $working_date
     * @param int $job
     * @param int $insert_worker
     * @return bool 出勤データを新規で挿入した結果
     */
    public function addWorkers(
        $working_date,
        $job,
        $insert_worker
    ) {
        $sql = '';
        $sql .= 'INSERT ';
        $sql .= 'INTO ';
        $sql .= 'riha_work(';
        $sql .= 'working_date,';
        $sql .= 'job,';
        $sql .= 'staff_id) ';
        $sql .= 'VALUES(';
        $sql .= ':working_date,';
        $sql .= ':job,';
        $sql .= ':staff_id)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':working_date', $working_date, PDO::PARAM_STR);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':staff_id', $insert_worker, PDO::PARAM_INT);
        $result = $stmt->execute();
        return $result;
    }
    /**
     * 出勤データを削除するメソッド
     * @param int $delete_worker
     * @return bool 出勤データを削除(欠勤に変更)した結果
     */
    public function deleteWorkers($delete_worker)
    {
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'riha_work ';
        $sql .= 'SET ';
        $sql .= 'is_deleted=:is_deleted ';
        $sql .= 'WHERE ';
        $sql .= 'staff_id=:delete_worker';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':is_deleted', self::DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':delete_worker', $delete_worker, PDO::PARAM_INT);
        $result = $stmt->execute();
        return $result;
    }
    /**
     * 欠勤から出勤に戻すメソッド
     * @param int $re_insert_worker
     * @return bool 出勤データを欠勤から再度出勤に変更した結果
     */
    public function reAddWorkers($re_insert_worker)
    {
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'riha_work ';
        $sql .= 'SET ';
        $sql .= 'is_deleted=:is_deleted ';
        $sql .= 'WHERE ';
        $sql .= 'staff_id=:re_insert_worker';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':re_insert_worker', $re_insert_worker, PDO::PARAM_INT);
        $result = $stmt->execute();
        return $result;
    }
}
