<?php

/** 介入表・担当者一覧表の表示に関するクラス */
class Table
{
    private $pdo;
    const NOT_DELETE = 0;
    const DELETE = 1;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    /**
     * スタッフと紐づけした患者と単位数を表示するメソッド 
     * @param int $job
     * @param string $reservation_date
     * @return array スタッフごとの当日の担当患者のid・名前・単位数、その日の合計単位を取得した多次元配列
     */
    public function getTodayNumbersByStaff(
        $job,
        $reservation_date
    ) {
        $sql = 'SELECT tmp.staff_name
                    ,tmp.patient_id 
    , MAX(CASE tmp.seq WHEN 1 THEN tmp.patient_name ELSE null END) AS patient_name1
    , MAX(CASE tmp.seq WHEN 2 THEN tmp.patient_name ELSE null END) AS patient_name2
    , MAX(CASE tmp.seq WHEN 3 THEN tmp.patient_name ELSE null END) AS patient_name3
    , MAX(CASE tmp.seq WHEN 4 THEN tmp.patient_name ELSE null END) AS patient_name4
    , MAX(CASE tmp.seq WHEN 5 THEN tmp.patient_name ELSE null END) AS patient_name5
    , MAX(CASE tmp.seq WHEN 6 THEN tmp.patient_name ELSE null END) AS patient_name6
    , MAX(CASE tmp.seq WHEN 7 THEN tmp.patient_name ELSE null END) AS patient_name7
    , MAX(CASE tmp.seq WHEN 8 THEN tmp.patient_name ELSE null END) AS patient_name8
    , MAX(CASE tmp.seq WHEN 9 THEN tmp.patient_name ELSE null END) AS patient_name9
    , MAX(CASE tmp.seq WHEN 10 THEN tmp.patient_name ELSE null END) AS patient_name10
    , MAX(CASE tmp.seq WHEN 1 THEN tmp.today_staff_num ELSE null END) AS today_staff_num1
    , MAX(CASE tmp.seq WHEN 2 THEN tmp.today_staff_num ELSE null END) AS today_staff_num2
    , MAX(CASE tmp.seq WHEN 3 THEN tmp.today_staff_num ELSE null END) AS today_staff_num3
    , MAX(CASE tmp.seq WHEN 4 THEN tmp.today_staff_num ELSE null END) AS today_staff_num4
    , MAX(CASE tmp.seq WHEN 5 THEN tmp.today_staff_num ELSE null END) AS today_staff_num5
    , MAX(CASE tmp.seq WHEN 6 THEN tmp.today_staff_num ELSE null END) AS today_staff_num6
    , MAX(CASE tmp.seq WHEN 7 THEN tmp.today_staff_num ELSE null END) AS today_staff_num7
    , MAX(CASE tmp.seq WHEN 8 THEN tmp.today_staff_num ELSE null END) AS today_staff_num8
    , MAX(CASE tmp.seq WHEN 9 THEN tmp.today_staff_num ELSE null END) AS today_staff_num9
    , MAX(CASE tmp.seq WHEN 10 THEN tmp.today_staff_num ELSE null END) AS today_staff_num10
    , MAX(CASE tmp.seq WHEN 1 THEN tmp.patient_id ELSE null END) AS patient_id1
    , MAX(CASE tmp.seq WHEN 2 THEN tmp.patient_id ELSE null END) AS patient_id2
    , MAX(CASE tmp.seq WHEN 3 THEN tmp.patient_id ELSE null END) AS patient_id3
    , MAX(CASE tmp.seq WHEN 4 THEN tmp.patient_id ELSE null END) AS patient_id4
    , MAX(CASE tmp.seq WHEN 5 THEN tmp.patient_id ELSE null END) AS patient_id5
    , MAX(CASE tmp.seq WHEN 6 THEN tmp.patient_id ELSE null END) AS patient_id6
    , MAX(CASE tmp.seq WHEN 7 THEN tmp.patient_id ELSE null END) AS patient_id7
    , MAX(CASE tmp.seq WHEN 8 THEN tmp.patient_id ELSE null END) AS patient_id8
    , MAX(CASE tmp.seq WHEN 9 THEN tmp.patient_id ELSE null END) AS patient_id9
    , MAX(CASE tmp.seq WHEN 10 THEN tmp.patient_id ELSE null END) AS patient_id10
    , SUM(tmp.today_staff_num) 
   FROM 
   ( SELECT
     s.staff_name
   , ps.patient_id
   , p.patient_name
   , ps.today_staff_id
   , ps.today_staff_num
   , row_number() over (partition by ps.today_staff_id) as seq
   FROM patient_staff as ps
   INNER JOIN patient_list as p
   ON ps.patient_id = p.id
   INNER JOIN staff_list as s
   ON ps.today_staff_id = s.id
   WHERE s.job = :job 
   AND ps.reservation_date=:reservation_date 
   AND ps.is_deleted=:is_deleted 
   ) tmp
   GROUP BY staff_name';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindValue(':reservation_date', $reservation_date, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 全てのスタッフの担当患者と基本単位を取得
     * @param int $job
     * @param string $reservation_date
     * @return array スタッフごとの担当患者の名前・単位数、合計単位を取得した多次元配列
     */
    public function getBaseNumbersByStaff(
        $job,
        $reservation_date
    ) {
     $sql = 'SELECT tmp.staff_name
    , MAX(CASE tmp.seq WHEN 1 THEN tmp.patient_name ELSE null END) AS patient_name1
    , MAX(CASE tmp.seq WHEN 2 THEN tmp.patient_name ELSE null END) AS patient_name2
    , MAX(CASE tmp.seq WHEN 3 THEN tmp.patient_name ELSE null END) AS patient_name3
    , MAX(CASE tmp.seq WHEN 4 THEN tmp.patient_name ELSE null END) AS patient_name4
    , MAX(CASE tmp.seq WHEN 5 THEN tmp.patient_name ELSE null END) AS patient_name5
    , MAX(CASE tmp.seq WHEN 6 THEN tmp.patient_name ELSE null END) AS patient_name6
    , MAX(CASE tmp.seq WHEN 7 THEN tmp.patient_name ELSE null END) AS patient_name7
    , MAX(CASE tmp.seq WHEN 8 THEN tmp.patient_name ELSE null END) AS patient_name8
    , MAX(CASE tmp.seq WHEN 9 THEN tmp.patient_name ELSE null END) AS patient_name9
    , MAX(CASE tmp.seq WHEN 10 THEN tmp.patient_name ELSE null END) AS patient_name10
    , MAX(CASE tmp.seq WHEN 1 THEN tmp.pt_base_num ELSE null END) AS base_num1
    , MAX(CASE tmp.seq WHEN 2 THEN tmp.pt_base_num ELSE null END) AS base_num2
    , MAX(CASE tmp.seq WHEN 3 THEN tmp.pt_base_num ELSE null END) AS base_num3
    , MAX(CASE tmp.seq WHEN 4 THEN tmp.pt_base_num ELSE null END) AS base_num4
    , MAX(CASE tmp.seq WHEN 5 THEN tmp.pt_base_num ELSE null END) AS base_num5
    , MAX(CASE tmp.seq WHEN 6 THEN tmp.pt_base_num ELSE null END) AS base_num6
    , MAX(CASE tmp.seq WHEN 7 THEN tmp.pt_base_num ELSE null END) AS base_num7
    , MAX(CASE tmp.seq WHEN 8 THEN tmp.pt_base_num ELSE null END) AS base_num8
    , MAX(CASE tmp.seq WHEN 9 THEN tmp.pt_base_num ELSE null END) AS base_num9
    , MAX(CASE tmp.seq WHEN 10 THEN tmp.pt_base_num ELSE null END) AS base_num10
    , SUM(tmp.pt_base_num) 
   FROM 
   ( SELECT
     s.staff_name
   , p.patient_name
   , p.pt_base_num
   , row_number() over (partition by s.staff_name) as seq
   FROM staff_list as s
   LEFT JOIN patient_list as p
   ON s.id = p.pt_base_id
   WHERE s.job = :job 
   AND p.started_date <= :reservation_date 
   AND p.is_deleted=:is_deleted    
   ) tmp
   GROUP BY staff_name';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':job', $job, PDO::PARAM_INT);
        $stmt->bindParam(':reservation_date', $reservation_date, \PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
