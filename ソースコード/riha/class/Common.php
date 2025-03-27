<?php

/** 日付に関するメソッドをまとめたクラス */
class Date
{
    /**
     * 今日の日付を取得するメソッド
     * @return string 今日の日付の情報を入れた変数
     */
    public  static function getDate()
    {
        $dateTime = new Datetime();
        $dateTime->setTimezone(new DateTimeZone('Asia/Tokyo'));
        //形式をそろえる
        $day = $dateTime->format('Y-m-d');
        //retuenがないと呼び出し元に値を返却出来ない
        return $day;
    }
    /**
     * 日付を年月日形式に変更して表示するメソッド
     * @param string $working_date
     * @return void 年月日形式に変換した日付の文字列を出力
     */
    public static function showDate($working_date)
    {
        echo mb_substr($working_date, 0, 4) . "年" . mb_substr($working_date, 5, 2) . "月" . mb_substr($working_date, 8, 2) . "日";
    }
    /**
     * スタッフの経験年数が３年目以上かを日数から計算するメソッド
     * @param string $day
     * @param array $staff
     * @return void
     */
    public static function getExperience(
        $day,
        $staff
    ) {
        $day2 = $staff['job_started_date'];
        if (((strtotime($day) - strtotime($day2)) / 86400) > 1096) {
            return true;
        } else {
            return false;
        }
    }
}

/** スタッフや患者の情報表示に関するクラス */
class Info
{
    /**
     * 数値から職種を判定して文字列で表示するメソッド(変数から数値データを取り出す場合)
     * @param int $job
     * @return void
     */
    public static function showJob($job)
    {
        if ($job == 0) {
            echo "PT";
        } elseif ($job == 1) {
            echo "OT";
        } else {
            echo "ST";
        }
    }
    /**
     * 数値から性別を判定して文字列で表示するメソッド(配列から数値データを取り出す場合)
     * @param array $staff
     * @return void
     */
    public static function getGender($staff)
    {
        if ($staff['staff_gender'] == 0) {
            echo "男性";
        } else {
            echo "女性";
        }
    }
    /**
     * 数値から救急対応の可否を判定して文字列で表示するメソッド(配列から数値データを取り出す場合)
     * @param array $staff
     * @return void
     */
    public static function getEmegency_skill($staff)
    {
        if ($staff['emergency_skill'] == 0) {
            echo "不可";
        } else {
            echo "可";
        }
    }
    /**
     * 数値から希望する性別を判定して文字列で表示するメソッド(配列から数値データを取り出す場合)
     * @param array $patient
     * @return void
     */
    public static function showHopeGender($patient)
    {
        if ($patient['hope_gender'] == 0) {
            echo "どちらでもよい";
        } elseif ($patient['hope_gender'] == 1) {
            echo "男性";
        } else {
            echo "女性";
        }
    }
    /**
     * 数値から急変リスクの有無を判定して文字列で表示するメソッド(配列から数値データを取り出す場合)
     * @param array $patient
     * @return void
     */
    public static function showEmergencyRisk($patient)
    {
        if ($patient['emergency_risk'] == 0) {
            echo "なし";
        } else {
            echo "あり";
        }
    }
    /**
     * 数値から年目未満のスタッフでも対応可能かを判定して文字列で表示するメソッド(配列から数値データを取り出す場合)
     * @param array $patient
     * @return void
     */
    public static function needExperience($patient)
    {
        if ($patient['staff_experience'] == 0) {
            echo "誰でも可";
        } else {
            echo "3年目以上";
        }
    }
}
