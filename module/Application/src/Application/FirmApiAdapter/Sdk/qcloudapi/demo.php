<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once './src/QcloudApi/QcloudApi.php';


$config = array('SecretId'      => "AKIDjFNVAqwXBDg1SOboLGTEKeWeHlVMp6Sz",
                'SecretKey'     => "rKMOvroqS1enWv7IwqScMdTg8TRmqeAv",
                'RequestMethod' => 'GET',
                'DefaultRegion' => 'sh');

$cvm = QcloudApi::load(QcloudApi::MODULE_MONITOR, $config);

$package = [
    'namespace'          => 'qce/cvm',
    'metricName'         => 'cpu_usage',
    'dimensions.0.name'  => 'unInstanceId',
    'dimensions.0.value' => 'ins-brzlmk1t',
    'startTime'          => '2016-03-31 17:25:00',
    "period"             => 300,
];

$a = $cvm->GetMonitorData($package);
// $a = $cvm->generateUrl('DescribeInstances', $package);

if ($a === false) {
    $error = $cvm->getError();
    echo "Error code:" . $error->getCode() . ".\n";
    echo "message:" . $error->getMessage() . ".\n";
    echo "ext:" . var_export($error->getExt(), true) . ".\n";
} else {
    var_dump($a);
}

echo "\nRequest :" . $cvm->getLastRequest();
echo "\nResponse :" . $cvm->getLastResponse();
echo "\n";