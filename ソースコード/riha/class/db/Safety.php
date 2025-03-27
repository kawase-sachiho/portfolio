<?php
/** セキュリティ対策に関するメソッド */
class Safety
{
const RANDAM_PSEUDO_STRING_LENGTH=32;
/**
 * ランダムトークンを生成して返却するメソッド
 * @param string $tokenName
 * @return string
 */
public static function generateToken(string $tokenName = 'token') : string
{
    $token=bin2hex(openssl_random_pseudo_bytes(self::RANDAM_PSEUDO_STRING_LENGTH));
    $_SESSION[$tokenName]=$token;
    return $token;
}
/**
 * トークンが一致しているか判断するメソッド
 * @param string $token
 * @param string $tokenName
 * @return bool トークンが一致していればtrue
 */
public static function isValidToken(string $token,string $tokenName='token'):bool
{
    if(!isset($_SESSION[$tokenName]) || $_SESSION[$tokenName] != $token)
    {
    return false;
    }
    return true;
}
/**
 * 送られてきた配列データをサニタイズするメソッド
 * @param array $before サニタイズする前の配列
 * @return array サニタイズされた後の配列
 */
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