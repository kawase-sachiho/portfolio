<?php
class Patients
{
    private $pdo;

    const NOT_DELETE = 0;
    const DELETE = 1;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /*　患者情報を全員分取得して表示するメソッド
    patient/index
    @return $result 全ての患者の情報が入った多次元配列
    @var    array　*/
    public function getAllPatients()
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'patient_list.id,';
        $sql .= 'patient_name,';
        $sql .= 'pt_base_id,';
        $sql .= 'ot_base_id,';
        $sql .= 'st_base_id,';
        $sql .= 'pt_staff.staff_name as pt_name,';
        $sql .= 'ot_staff.staff_name as ot_name,';
        $sql .= 'st_staff.staff_name as st_name,';
        $sql .= 'pt_base_num,';
        $sql .= 'ot_base_num,';
        $sql .= 'st_base_num ';
        $sql .= 'FROM patient_list ';
        $sql .= 'JOIN staff_list as pt_staff ';
        $sql .= 'ON patient_list.pt_base_id = pt_staff.id ';
        $sql .= 'JOIN staff_list as ot_staff ';
        $sql .= 'ON patient_list.ot_base_id = ot_staff.id ';
        $sql .= 'JOIN staff_list as st_staff ';
        $sql .= 'ON patient_list.st_base_id = st_staff.id ';
        $sql .= 'WHERE patient_list.is_deleted=:is_deleted';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /* 患者を新規で追加するメソッド
    patient/add 
    @param  $patient_name str
            $patient_family_name str
            $patient_first_name str/
            $hope_gender int
            $staff_experience int
            $emergency_ris int
            $started_date date
            $pt_base_id int
            $ot_base_id int
            $st_base_id int
            $pt_base_num int
            $ot_base_num int
            $st_base_num int
    @return $result 患者のデータを新たに挿入した結果   
    @var    bool   */
    public function addPatients(
        $patient_name,
        $patient_family_name,
        $patient_first_name,
        $hope_gender,
        $staff_experience,
        $emergency_risk,
        $started_date,
        $pt_base_id,
        $ot_base_id,
        $st_base_id,
        $pt_base_num,
        $ot_base_num,
        $st_base_num
    ) {
        $sql = '';
        $sql .= 'INSERT INTO ';
        $sql .= 'patient_list(';
        $sql .= 'patient_name,';
        $sql .= 'patient_family_name,';
        $sql .= 'patient_first_name,';
        $sql .= 'hope_gender,';
        $sql .= 'staff_experience,';
        $sql .= 'emergency_risk,';
        $sql .= 'started_date,';
        $sql .= 'pt_base_id,';
        $sql .= 'ot_base_id,';
        $sql .= 'st_base_id,';
        $sql .= 'pt_base_num,';
        $sql .= 'ot_base_num,';
        $sql .= 'st_base_num) ';
        $sql .= 'VALUES(';
        $sql .= ':patient_name,';
        $sql .= ':patient_family_name,';
        $sql .= ':patient_first_name,';
        $sql .= ':hope_gender,';
        $sql .= ':staff_experience,';
        $sql .= ':emergency_risk,';
        $sql .= ':started_date,';
        $sql .= ':pt_base_id,';
        $sql .= ':ot_base_id,';
        $sql .= ':st_base_id,';
        $sql .= ':pt_base_num,';
        $sql .= ':ot_base_num,';
        $sql .= ':st_base_num)';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':patient_name', $patient_name, PDO::PARAM_STR);
        $stmt->bindValue(':patient_family_name', $patient_family_name, PDO::PARAM_STR);
        $stmt->bindValue(':patient_first_name', $patient_first_name, PDO::PARAM_STR);
        $stmt->bindValue(':hope_gender', $hope_gender, PDO::PARAM_INT);
        $stmt->bindValue(':staff_experience', $staff_experience, PDO::PARAM_INT);
        $stmt->bindValue(':emergency_risk', $emergency_risk, PDO::PARAM_INT);
        $stmt->bindValue(':started_date', $started_date, PDO::PARAM_STR);
        $stmt->bindValue(':pt_base_id', $pt_base_id, PDO::PARAM_INT);
        $stmt->bindValue(':ot_base_id', $ot_base_id, PDO::PARAM_INT);
        $stmt->bindValue(':st_base_id', $st_base_id, PDO::PARAM_INT);
        $stmt->bindValue(':pt_base_num', $pt_base_num, PDO::PARAM_INT);
        $stmt->bindValue(':ot_base_num', $ot_base_num, PDO::PARAM_INT);
        $stmt->bindValue(':st_base_num', $st_base_num, PDO::PARAM_INT);

        $result = $stmt->execute();
        return $result;
    }

    /*指定した患者の情報を表示するメソッド
    patient/info, patient/delete, patient/edit 
    @param  $id　int
    @return $result 指定した患者の情報が入った連想配列
    @var    array */
    public function showPatientInfo($id)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'patient_name,';
        $sql .= 'patient_family_name,';
        $sql .= 'patient_first_name,';
        $sql .= 'hope_gender,';
        $sql .= 'staff_experience,';
        $sql .= 'emergency_risk,';
        $sql .= 'started_date,';
        $sql .= 'pt_base_id,';
        $sql .= 'ot_base_id,';
        $sql .= 'st_base_id,';
        $sql .= 'pt_base_num,';
        $sql .= 'ot_base_num,';
        $sql .= 'st_base_num ';
        $sql .= 'FROM patient_list ';
        $sql .= 'WHERE id=:id ';
        $sql .= 'AND is_deleted=:is_deleted';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /*患者情報を更新するメソッド
    patient/edit_aciton
    @param  $id int 
            $patient_name str 
            $patient_family_name str
            $patient_first_name str
            $hope_gender int
            $staff_experience int
            $emergency_ris int
            $started_date date
            $pt_base_id int
            $ot_base_id int
            $st_base_id int
            $pt_base_num int
            $ot_base_num int
            $st_base_num int
    @return $result 患者データを更新した結果
    @var    bool */
    public function editPatients(
        $id,
        $patient_name,
        $patient_family_name,
        $patient_first_name,
        $hope_gender,
        $staff_experience,
        $emergency_risk,
        $started_date,
        $pt_base_id,
        $ot_base_id,
        $st_base_id,
        $pt_base_num,
        $ot_base_num,
        $st_base_num
    ) {
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'patient_list ';
        $sql .= 'SET ';
        $sql .= 'id=:id,';
        $sql .= 'patient_name=:patient_name,';
        $sql .= 'patient_family_name=:patient_family_name,';
        $sql .= 'patient_first_name=:patient_first_name,';
        $sql .= 'hope_gender=:hope_gender,';
        $sql .= 'staff_experience=:staff_experience,';
        $sql .= 'emergency_risk=:emergency_risk,';
        $sql .= 'started_date=:started_date,';
        $sql .= 'pt_base_id=:pt_base_id,';
        $sql .= 'ot_base_id=:ot_base_id,';
        $sql .= 'st_base_id=:st_base_id,';
        $sql .= 'pt_base_num=:pt_base_num,';
        $sql .= 'ot_base_num=:ot_base_num,';
        $sql .= 'st_base_num=:st_base_num ';
        $sql .= 'WHERE ';
        $sql .= 'id=:id ';
        $sql .= 'AND is_deleted=:is_deleted ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':patient_name', $patient_name, PDO::PARAM_STR);
        $stmt->bindValue(':patient_family_name', $patient_family_name, PDO::PARAM_STR);
        $stmt->bindValue(':patient_first_name', $patient_first_name, PDO::PARAM_STR);
        $stmt->bindValue(':hope_gender', $hope_gender, PDO::PARAM_INT);
        $stmt->bindValue(':staff_experience', $staff_experience, PDO::PARAM_INT);
        $stmt->bindValue(':emergency_risk', $emergency_risk, PDO::PARAM_INT);
        $stmt->bindValue(':started_date', $started_date, PDO::PARAM_STR);
        $stmt->bindValue(':pt_base_id', $pt_base_id, PDO::PARAM_INT);
        $stmt->bindValue(':ot_base_id', $ot_base_id, PDO::PARAM_INT);
        $stmt->bindValue(':st_base_id', $st_base_id, PDO::PARAM_INT);
        $stmt->bindValue(':pt_base_num', $pt_base_num, PDO::PARAM_INT);
        $stmt->bindValue(':ot_base_num', $ot_base_num, PDO::PARAM_INT);
        $stmt->bindValue(':st_base_num', $st_base_num, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);

        $result = $stmt->execute();
        return $result;
    }

    /*削除画面に関するメソッド */
    /*予約済のレコードがあるか確認するメソッド
    @param  $id int
            $day date
    @return $result 指定した患者の当日以降のリハビリ予約を確認し、存在すれば日付を返却する
    @var    array */
    public function checkReservationByPatient(
        $id,
        $day
    ) {
        $reservation_id = 0;
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'ps.reservation_date ';
        $sql .= 'FROM patient_staff as ps ';
        $sql .= 'WHERE patient_id = :patient_id ';
        $sql .= "AND is_deleted = :is_deleted ";
        $sql .= "AND reservation_date >= :delete_date ";
        $sql .= "AND today_staff_id > :reservation_id ";
        $sql .= "GROUP BY ps.reservation_date ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindParam(':delete_date', $day, \PDO::PARAM_STR);
        $stmt->bindParam(':reservation_id', $reservation_id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }

    /*患者情報を削除するメソッド
    @param $id int
    @return $result 患者データを削除した結果
    @var    bool */
    public function deletePatients($id)
    {
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'patient_list ';
        $sql .= 'SET ';
        $sql .= 'is_deleted=:is_deleted ';
        $sql .= 'WHERE ';
        $sql .= 'id=:id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':is_deleted', self::DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $result = $stmt->execute();
        return $result;
    }
}
