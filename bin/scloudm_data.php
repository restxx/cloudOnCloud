<?php
/**
 *
 * User: lijun1
 * Date: 16-5-25
 * Time: 下午4:36
 */
header('Content-type: text/html; charset=utf-8;');

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

$sql = "select product_id from scloudm_product";

$result = $mysqli->query($sql);

$products = [];
while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {

    $products[] = current($row);

};
$url = 'http://api.scloudm.com/mana_api/tokens';

$post = <<<post
{"auth": {"tenantName": "","passwordCredentials": {"username": "admin","password": "NanHui_Cloud@PWXadmin"}}}
post;

$opts = [
    'http' => [
        'method'  => 'POST',
        'timeout' => 10,
        'header'  => "Content-Type:application/json\r\n" .
            "Sp-Agent: scloudm",
        'content' => $post
    ],
];

$context = stream_context_create($opts);

$ret = @json_decode(file_get_contents($url, false, $context), true);

if (isset($ret['code'])) {
    echo -1;
    exit;
}

$token_id = $ret['access']['token']['id'];

$url = 'http://api.scloudm.com/mana_api/region_tenant';

$opts = [
    'http' => [
        'method'  => 'GET',
        'timeout' => 10,
        'header'  =>
            "Content-Type:application/json\r\n" .
            "X-Auth-Token: $token_id\r\n" .
            "Sp-Agent: scloudm",
    ],
];

$context = stream_context_create($opts);

$ret = @json_decode(file_get_contents($url, false, $context), true);

if (!isset($ret['code']) || $ret['code'] != 200) {
    echo -2;
    exit;
}

$tenants = $ret['tenants'];
$regions = $ret['regions'];

$url = 'http://api.scloudm.com/mana_api/flow_monthly';

$cdate = date("Y-m", strtotime("-1 month"));

$opts = [
    'http' => [
        'method'  => 'GET',
        'timeout' => 10,
        'header'  =>
            "Content-Type:application/json\r\n" .
            "X-Auth-Token: $token_id\r\n" .
            "Sp-Agent: default",
    ],
];

$context = stream_context_create($opts);

foreach ($tenants as $tenant) {

    $query = <<<sql
insert into scloudm_bandwidth(`date`,`product_id`,`name`,`idc`,`bandwidth`) values
sql;

    $sqlValues = [];

    foreach ($regions as $region) {

        $tenant_id   = $tenant['id'];
        $tenant_name = $tenant['name'];

        preg_match('/(\d+)-(.*)/', $tenant_name, $match);

        if ($match) {
            $product_id   = $match[1];
            $product_name = $match[2];

            if (!in_array($product_id, $products)) continue;
        } else {
            continue;
        }

        $params = [
            'project_id' => $tenant_id,
            'region'     => $region,
            'month'      => $cdate
        ];

        $ret = @json_decode(file_get_contents($url . "?" . http_build_query($params), false, $context), true);
        if (!$ret || !$ret['monthly_data']) {
            continue;
        }

        $data = current($ret['monthly_data']);

        $bandwidth = sprintf("%.2f", max($data['max_in_rate'], $data['max_out_rate']) / 1000 / 1000 * 8);

        $sqlValues[] = "('$cdate','$product_id','$product_name','$region',$bandwidth)";

    }

    if ($sqlValues) {
        $query .= implode(",", $sqlValues) . ";";
        $mysqli->query($query);
    }
}

$mysqli->close();

echo date("Y-m-d H:i:s", time()) . " ok";

exit;
