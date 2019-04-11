<?php
/**
 *
 * User: lijun1
 * Date: 16-5-26
 * Time: 上午10:37
 */

header('Content-type: text/html; charset=utf-8;');
date_default_timezone_set("PRC");

$url = 'http://222.73.63.22:9200/api/v1/project/item';

$post = [
    'start' => 1464140708,
    'end'   => 1464140708
];

$opts = [
    'http' => [
        'method'  => 'POST',
        'timeout' => 10,
        'header'  => "Content-Type: application/json",
        'content' => http_build_query($post)
    ],
];

$context = stream_context_create($opts);

$ret = @json_decode(file_get_contents($url, false, $context), true);

if (!$ret || $ret['status'] != 0) {
    echo -1;
    exit;
}

$projects = $ret['data'];

$url = 'http://222.73.63.22:9200/api/v1/project/netflow_idc';

$start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

$idcs = ['ZR', 'NH', 'TJ', 'CHJ', 'YP'];

if (PHP_OS != "WINNT") {
    $mysqli = new mysqli('172.29.211.187', 'twocloud', 'mobile', 'twocloud');
} else {
    $mysqli = new mysqli('localhost', 'root', '111111', 'towcloud');
}
if (mysqli_connect_errno($mysqli)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}

$mysqli->query("set names utf8");

foreach ($projects as $project) {

    foreach ($idcs as $idc) {

        $query = <<<sql
insert into scloudm_netflow(`project`,`idc`,`time`,`netflow`)
sql;

        $post = [
            'start'   => $start - 86400,
            'end'     => $start,
            'project' => $project,
            'idc'     => $idc
        ];

        $opts = [
            'http' => [
                'method'  => 'POST',
                'timeout' => 10,
                'header'  => "Content-Type: application/json",
                'content' => json_encode($post)
            ],
        ];

        $context = stream_context_create($opts);

        $ret = file_get_contents($url, false, $context);

        $ret = @json_decode(file_get_contents($url, false, $context), true);

        if (!$ret) {
            echo -1;
            exit;
        } else if ($ret['status'] != 0) {
            continue;
        }

        $netflows = json_decode($ret['data'], true);

        $sqlValues = [];
        foreach ($netflows as $time => $netflow) {
            $time = substr($time, 0, -3);

            if ($time % 300 != 0 && $netflow == 0) continue;

            $netflow     = sprintf("%.4f", $netflow);
            $sqlValues[] = "('$project','$idc',$time,$netflow)";
        }

        $query .= " values" . implode(",", $sqlValues) . ";";

        $mysqli->query($query);
    }
}

$mysqli->close();

echo date("Y-m-d H:i:s", time()) . " ok";

exit;