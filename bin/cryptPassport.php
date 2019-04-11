<?php
if (PHP_OS != "WINNT") {
    $mysqli = new mysqli('172.29.211.187', 'twocloud', 'mobile', 'twocloud');
} else {
    $mysqli = new mysqli('localhost', 'root', '111111', 'twocloud');
}
if (mysqli_connect_errno($mysqli)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}

$mysqli->query("set names utf8");

$sql = "select id,passport_info from user_firm_passport";

$result = $mysqli->query($sql);

$products = [];
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    if(json_decode($row['passport_info'])){
        $products[] = $row;
    }
};
$sql = "update user_firm_passport set passport_info='%s' where id=%s";
foreach($products as $item)
{
    $id = $item['id'];
    $passportInfo = encrypt($item['passport_info']);
    $mysqli->query(sprintf($sql,$passportInfo,$id));
}


function encrypt($str, $key='o0o00o00')
{
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = $block - (strlen($str) % $block);
    $str .= str_repeat(chr($pad), $pad);
    return base64_encode(mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB));
}
function decrypt($str, $key='o0o00o00')
{
    $str = base64_decode($str);
    $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = ord($str[($len = strlen($str)) - 1]);
    return substr($str, 0, strlen($str) - $pad);
}