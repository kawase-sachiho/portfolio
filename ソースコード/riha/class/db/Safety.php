<?php
/*　セキュリティ対策に関するメソッド */
class Safety
{
const RANDAM_PSEUDO_STRING_LENGTH=32;

/* 
    @param　tokenName :string
    @return $token　ランダムトークンを生成して返却する
    @var    string 
*/
public static function generateToken(string $tokenName = 'token') : string
{
    $token=bin2hex(openssl_random_pseudo_bytes(self::RANDAM_PSEUDO_STRING_LENGTH));
    $_SESSION[$tokenName]=$token;
    return $token;
}

/* 
@param  string $token
        string $tokenName
@return bool トークンが一致しているかを判断する
@var    bool 
*/
public static function isValidToken(string $token,string $tokenName='token'):bool
{
    if(!isset($_SESSION[$tokenName]) || $_SESSION[$tokenName] != $token)
    {
    return false;
    }
    return true;
}

/* 
@param  $before サニタイズする前の配列
@return $after サニタイズされた後の配列
@var    array */
public static function sanitaize($before)
    {
        $after = array();
        foreach ($before as $k => $v) {
            $after[$k] = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        }
        return $after;
    }
}
?>