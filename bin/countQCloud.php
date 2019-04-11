<?php
chdir(dirname(__DIR__));
require 'init_autoloader.php';
$secrets = [
    [
        'secretId'  => 'AKIDypVJyRrly50CFAxUt67yeBYjaLuHmREx',
        'secretKey' => 'f8PUN4pQyNabQ3viMocM2Zx9E7oLgssi',
        "firmId"    => 100001
    ],
    [
        "secretId"  => "AKIDjFNVAqwXBDg1SOboLGTEKeWeHlVMp6Sz",
        "secretKey" => "rKMOvroqS1enWv7IwqScMdTg8TRmqeAv",
        "firmId"    => 100001
    ]
];
$firm1 = new \Application\FirmApiAdapter\QCloud($secrets[0]);
$firm2 = new \Application\FirmApiAdapter\QCloud($secrets[1]);
$result1 = $firm1->getProjectCountList();
$result2 = $firm2->getProjectCountList();
$list = [];
foreach($result1 as $item)
{
    if(isset($list[$item['pName']])){
        $list[$item['pName']] += $item['counts'];
    } else {
        $list[$item['pName']] = $item['counts'];
    }
}
foreach($result2 as $item)
{
    if(isset($list[$item['pName']])){
        $list[$item['pName']] += $item['counts'];
    } else {
        $list[$item['pName']] = $item['counts'];
    }
}
$strData = '';
foreach($list as $name => $num)
{
    $strData .= $name . " | " . $num ."\n";
}
file_put_contents('data/countQCloud/data'.date("Ym").'.log', $strData);