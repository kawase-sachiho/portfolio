<?php
/* ログインに関するクラス */
class Users
{
    private $pdo;

    const NOT_DELETE = 0;
    const DELETE = 1;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /*ログインの際、メールアドレスが一致するユーザーがいるか確認するメソッド
    login/login
    @param  $mail str
    @return $result メールアドレスが一致したユーザーの情報が入った連想配列
    @var    array */
    public function searchUserByMail($mail)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'id,';
        $sql .= 'staff_name,';
        $sql .= 'pass ';
        $sql .= 'FROM staff_list ';
        $sql .= 'WHERE mail=:mail ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':mail', $mail, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /*ログイン中のユーザーのメールアドレス・名前を取得し、変更画面で表示するメソッド
    user/edit 
    @param  $id str
    @return $result ログインしているユーザーの氏名・メールアドレスの情報が入った連想配列
    @var    array */
    public function getUserInfo($id)
    {
        $sql = '';
        $sql .= 'SELECT ';
        $sql .= 'staff_name,';
        $sql .= 'mail ';
        $sql .= 'FROM staff_list ';
        $sql .= 'WHERE id=:id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /* ログイン中のユーザーのメールアドレスを変更するメソッド
    user/edit_action
    @param  $id int
            $mail str
    @return $result メールアドレスの変更が実施できたかどうかを判断する
    @var    bool */
    public function changeMail(
        $id,
        $mail
    ) {
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'staff_list ';
        $sql .= 'SET ';
        $sql .= 'mail=:mail ';
        $sql .= 'WHERE ';
        $sql .= 'id=:id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':mail', $mail, PDO::PARAM_STR);
        $result = $stmt->execute();
        return $result;
    }

    /* ログイン中のユーザーのパスワードを変更するメソッド
    user/edit_action
    @param  $id int
            $pass str
    @return $result パスワードの変更が実施できたかどうかを判断する
    @var    bool */
    public function changePass(
        $id,
        $pass
    ) {
        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= 'staff_list ';
        $sql .= 'SET ';
        $sql .= 'pass=:pass ';
        $sql .= 'WHERE ';
        $sql .= 'id=:id ';
        $sql .= 'AND is_deleted=:is_deleted ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', self::NOT_DELETE, PDO::PARAM_INT);
        $stmt->bindValue(':pass', $pass, PDO::PARAM_STR);
        $result = $stmt->execute();
        return $result;
    }
}
