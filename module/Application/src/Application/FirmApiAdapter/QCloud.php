<?php

namespace Application\FirmApiAdapter;

include __DIR__ . '/Sdk/qcloudapi/src/QcloudApi/QcloudApi.php';

use QcloudApi;
use Zend\Validator\Ip;


/**
 * Class QCloud
 *
 * @package Application\FirmApiAdapter
 * @author  Xuman
 * @version $Id$
 */
class QCloud extends FirmAbstract
{
    protected $path = '/v2/index.php';
    protected $secretId = '';
    protected $secretKey = '';
    protected $method = 'GET';
    protected $baseParams = [];
    protected $status = [
        1  => '故障',
        2  => '运行中',
        3  => '创建中',
        4  => '已关机',
        5  => '已退还',
        6  => '退还中',
        7  => '重启中',
        8  => '开机中',
        9  => '关机中',
        10 => '密码重置中',
        11 => '格式化中',
        12 => '镜像制作中',
        13 => '带宽设置中',
        14 => '重装系统中',
        15 => '域名绑定中',
        16 => '域名解绑中',
        17 => '负载均衡绑定中',
        18 => '负载均衡解绑中',
        19 => '升级中',
        20 => '秘钥下发中',
        21 => '热迁移中'
    ];
    protected $cvmPayMode = [
        0 => '按月结算的后付费',
        1 => '包年/包月',
        2 => '按量计费'
    ];
    protected $networkPayMode = [
        0 => '按月结算的后付费',
        1 => '包年/包月',
        2 => '按流量',
        3 => '按带宽'
    ];
    protected $diskType = [
        1 => '本地盘',
        2 => '云盘'
    ];
    protected $imageType = [
        1 => '私有镜像',
        2 => '公共镜像',
        3 => '服务市场',
        4 => '共享镜像'
    ];
    protected $baseOperationStatus = [
        self::CAN_START      => [4],
        self::CAN_STOP       => [2],
        self::CAN_RESTART    => [2, 4],
        self::CAN_RESET_PASS => [4]
    ];

    public static $regionZoneMap = array(
        "bj" => array("800001" => "北京一区"),
        "sh" => array("200001" => "上海一区"),
        "gz" => array(
            "100001" => "广州一区",
            "100002" => "广州二区",
            "100003" => "广州三区",
        ),
        "hk" => array("300001" => "香港一区"),
        "ca" => array("400001" => "北美一区")
    );

    protected $monitor = [
        0 => [
            'caption' => 'CPU监控',
            0         => [
                'id'    => 'cpu_usage',
                'title' => 'CPU使用率'
            ],
            1         => [
                'id'    => 'cpu_loadavg',
                'title' => 'cpu平均负载'
            ],
        ],
        1 => [
            'caption' => '内存监控',
            0         => [
                'id'    => 'mem_used',
                'title' => '内存使用量'
            ],
            1         => [
                'id'    => 'mem_usage',
                'title' => '内存利用率'
            ],
        ],
        2 => [
            'caption' => '内网监控',
            0         => [
                'id'    => 'lan_outtraffic',
                'title' => '内网出带宽'
            ],
            1         => [
                'id'    => 'lan_intraffic',
                'title' => '内网入带宽'
            ],
        ],
        3 => [
            0 => [
                'id'    => 'lan_outpkg',
                'title' => '内网出包量'
            ],
            1 => [
                'id'    => 'lan_inpkg',
                'title' => '内网入包量'
            ],
        ],
        4 => [
            'caption' => '外网监控',
            0         => [
                'id'    => 'wan_outtraffic',
                'title' => '外网出带宽'
            ],
            1         => [
                'id'    => 'wan_intraffic',
                'title' => '外网入带宽'
            ],
        ],
        5 => [
            0 => [
                'id'    => 'wan_outpkg',
                'title' => '外网出包量'
            ],
            1 => [
                'id'    => 'wan_inpkg',
                'title' => '外网入包量'
            ],
        ],
        6 => [
            'caption' => '磁盘监控',
            0         => [
                'id'    => 'disk_read_traffic',
                'title' => '磁盘读流量'
            ],
            1         => [
                'id'    => 'disk_write_traffic',
                'title' => '磁盘写流量'
            ],
        ],
        7 => [
            0 => [
                'id'    => 'disk_io_await',
                'title' => '磁盘IO等待'
            ],
        ]
    ];

    public static $region = [
        'sh' => '上海',
        'bj' => '北京',
        'gz' => '广州',
        'hk' => '香港',
        'ca' => '北美'
    ];

    public function __construct($params)
    {
        parent::__construct($params);
        $this->baseParams = [
            'Action'    => 'DescribeInstances',
            'Region'    => 'sh',
            'Timestamp' => time(),
            'Nonce'     => rand(1, 9999),
            'SecretId'  => $this->secretId
            //'Signature' => ''
        ];
    }

    protected function getService($moduleName)
    {
        $config = array(
            'SecretId'      => $this->secretId,
            'SecretKey'     => $this->secretKey,
            'RequestMethod' => 'GET',
            'DefaultRegion' => 'sh'
        );
        // 第一个参数表示使用哪个域名
        // 已有的模块列表：
        // QcloudApi::MODULE_CVM      对应   cvm.api.qcloud.com
        // QcloudApi::MODULE_CDB      对应   cdb.api.qcloud.com
        // QcloudApi::MODULE_LB       对应   lb.api.qcloud.com
        // QcloudApi::MODULE_TRADE    对应   trade.api.qcloud.com
        // QcloudApi::MODULE_SEC      对应   csec.api.qcloud.com
        // QcloudApi::MODULE_IMAGE    对应   image.api.qcloud.com
        // QcloudApi::MODULE_MONITOR  对应   monitor.api.qcloud.com
        // QcloudApi::MODULE_CDN      对应   cdn.api.qcloud.com
        return QcloudApi::load($moduleName, $config);

    }

    public function createSignature($api, $data)
    {
        ksort($data);
        $query   = urldecode(http_build_query($data));
        $str     = $this->method . $api . "?" . $query;
        $signStr = base64_encode(hash_hmac('sha1', $str, $this->secretKey, true));
        return $signStr;
    }

    public function createRequestURL($api, $data)
    {
        $api               = $api . $this->path;
        $signature         = $this->createSignature($api, $data);
        $data['Signature'] = $signature;
        $url               = "https://" . $api . '?' . http_build_query($data);
        return $url;
    }

    public function callRemoteApi($url)
    {
        $opts      = array(
            'http' => array(
                'method'  => 'GET',
                'timeout' => 30
            )
        );
        $cxContext = stream_context_create($opts);
        return json_decode(file_get_contents($url, false, $cxContext), true);
    }

    public function getInstanceList($data)
    {
        $service          = $this->getService(QcloudApi::MODULE_CVM);
        $params           = $this->baseParams;
        $params['offset'] = intval($data['start']);
        $params['limit']  = intval($data['limit']);
        if (isset($data['pid']) && $data['pid']) {
            $params['projectId'] = intval($data['pid']);
        }
        $rid              = $data['rid'];
        $params['Region'] = $rid;
        $lanIp            = 'lanIps.';
        if ($data['search']) {
            $search   = str_replace(['"', "'", "<", ">"], '', $data['search']);
            $searches = array_filter(explode(',', $search));
            if ((new IP())->isValid(current($searches))) {
                $i = 1;
                foreach ($searches as $ip) {
                    if ((new IP())->isValid($ip)) {
                        $params[$lanIp . $i] = $ip;
                        $i++;
                    }
                }
            } else {
                $params['searchWord'] = $search;
            }

        }
        $ret = $service->DescribeInstances($params);
        if ($ret === false) {
            return ['totalCount' => 0, 'data' => []];
        }
        $data = [];
        foreach ($ret['instanceSet'] as $item) {
            $t              = array();
            $t['name']      = $item['instanceName'];
            $t['unId']      = $item['unInstanceId'];
            $t['lanIp']     = $item['lanIp'];
            $t['wanIpSet']  = implode(',', $item['wanIpSet']);
            $t['zoneName']  = $item['zoneName'];
            $t['status']    = $item['status'];
            $t['pid']       = $item['projectId'];
            $q              = '';
            $operationsList = $this->getOperationsStatus();
            foreach ($operationsList as $val) {
                if (in_array($item['status'], $val)) {
                    $q .= '1';
                } else {
                    $q .= '0';
                }
            }
            if ($item['cvmPayMode'] == 2) {
                $q .= '1';
            } else {
                $q .= '0';
            }
            $q .= '111';
            $t['operationStatus'] = $q;
            $t['payMode']         = $this->cvmPayMode[$item['cvmPayMode']];
            $t['statusName']      = $this->status[$item['status']];
            $t['deadlineTime']    = $item['deadlineTime'];
            $t['rid']             = $rid;
            $data[]               = $t;
        }
        return ['totalCount' => $ret['totalCount'], 'data' => $data];
    }

    public function getInstanceInfo($hid, $rid)
    {
        $service = $this->getService(QcloudApi::MODULE_CVM);
        $ret     = $service->DescribeInstances(['instanceIds.0' => $hid, 'Region' => $rid]);
        if (!$ret) {
            return [];
        }

        $info = $ret['instanceSet'][0];
        /*$service      = $this->getService(QcloudApi::MODULE_IMAGE);
        $iRet         = $service->DescribeImages(['imageIds.0' => $info['unImgId'], 'imageType' => 2]);
        $imageInfo    = ($iRet && $iRet['totalCount'] > 0) ? $iRet['imageSet'][0] : [];
        $imagePattern = [];
        if (!empty($imageInfo)) {
            $imagePattern = [
                '镜像名称' => $imageInfo['imageName'],
                '类型'   => $this->imageType[$imageInfo['imageType']],
                '镜像ID' => $info['unImgId']
            ];
        }*/
        $patternData = [
            '主机信息'    => [
                '名称'     => $info['instanceName'],
                '服务器ID'  => $info['instanceId'],
                '状态'     => $this->status[$info['status']],
                '公网IP'   => implode(',', $info['wanIpSet']),
                '内网IP'   => $info['lanIp'],
                '创建时间'   => $info['createTime'],
                '到期时间'   => $info['deadlineTime'],
                '地域'     => self::$region[$info['Region']],
                '可用区'    => $info['zoneName'],
                '主机计费模式' => $this->cvmPayMode[$info['cvmPayMode']],
                '网络计费模式' => $this->networkPayMode[$info['networkPayMode']],
                /*'所属网络'   => $info[''],
                '用作公网网关' => $info[''],
                '所在子网'   => $info['subnetId']*/
            ],
            '机器配置'    => [
                '操作系统' => $info['os'],
                'CPU'  => $info['cpu'],
                '内存'   => $info['mem'] . "GB",
                '系统盘'  => $info['diskInfo']['rootSize'] . 'GB(' . $this->diskType[$info['diskInfo']['rootType']] . ')',
                '数据盘'  =>
                    $info['diskInfo']['storageSize'] . 'GB(' . $this->diskType[$info['diskInfo']['storageType']] . ')',
                '公网带宽' => $info['bandwidth'] . 'MB'
            ],
            '系统镜像'    => [
                '镜像ID' => $info['unImgId']
            ],
            'monitor' => $this->monitor
        ];

        return $patternData;
    }

    public function getRegionList()
    {
        $service = $this->getService(QcloudApi::MODULE_TRADE);
        $ret     = $service->DescribeProductRegionList(['instanceType' => 1]);
        if (!$ret) {
            return self::$region;
        }
        if(isset($ret['availableRegion']['sh'])) {
            $sh = $ret['availableRegion']['sh'];
            unset($ret['availableRegion']['sh']);
            $ret['availableRegion']['sh'] = $sh;
            $ret['availableRegion'] =  array_reverse($ret['availableRegion']);
        }

        return $ret['availableRegion'];
    }

    public function getMonitorData($hid, $mid, $qtype, $other = [])
    {
        $service = $this->getService(QcloudApi::MODULE_MONITOR);
        date_default_timezone_set("PRC");

        switch ($qtype) {
            case 0:
                $startTime = date("Y-m-d H:i:s", strtotime("-1 hour"));
                break;
            case 1:
                $startTime = date("Y-m-d");
                break;
            case 2:
                $startTime = date("Y-m-d", strtotime("-1 day"));
                $endTime   = date("Y-m-d");
                break;
            case 3:
                $startTime = date("Y-m-d", strtotime("-7 day"));
                $endTime   = date("Y-m-d");
                $period    = 86400;
                break;
            case 4:
                $startTime = date("Y-m-d", strtotime("-14 day"));
                $endTime   = date("Y-m-d");
                $period    = 86400;
                break;
            default:
                $startTime = date("Y-m-d");
                break;
        }

        $params = [
            'namespace'          => 'qce/cvm',
            'metricName'         => $mid,
            'dimensions.0.name'  => 'unInstanceId',
            'dimensions.0.value' => $hid,
            'startTime'          => $startTime,
            'endTime'            => isset($endTime) ? $endTime : date("Y-m-d H:i:s"),
            "period"             => isset($period) ? $period : 300,
        ];

        $ret = $service->GetMonitorData($params);

        if ($ret === false) {
            return ['dataPoints' => [], 'categories' => []];
        } else {
            $dataPoints = $ret['dataPoints'];
            $start      = $ret['startTime'];
            $period     = $ret['period'];

            $Categories = [];
            $Points     = [];
            $times      = 0;
            foreach ($dataPoints as $dataPoint) {
                $times++;

                if ($period == 86400) {
                    $date_format = date("Y-m-d", strtotime($start) + $times * $period);
                } else {
                    $date_format = date("H:i", strtotime($start) + $times * $period);
                }

                array_push($Categories, $date_format);
                array_push($Points, $dataPoint ? floatval($dataPoint) : 0);
            }

            return ['categories' => $Categories, 'points' => $Points];
        }
    }

    public function authorization()
    {
        $api              = 'account.api.qcloud.com';
        $params           = $this->baseParams;
        $params['Action'] = 'DescribeProject';
        $ret              = $this->callRemoteApi($this->createRequestURL($api, $params));
        return ($ret['code'] != 4100);
    }

    public function getProjectList()
    {
        $api              = 'account.api.qcloud.com';
        $params           = $this->baseParams;
        $params['Action'] = 'DescribeProject';
        $ret              = $this->callRemoteApi($this->createRequestURL($api, $params));
        if (!is_array($ret) || $ret['code'] != 0 || empty($ret['data'])) {
            return [];
        }
        $data = [];
        foreach ($ret['data'] as $item) {
            $data[$item['projectId']] = $item['projectName'];
        }
        $data[0] = '全部';

        ksort($data);
        return $data;
    }

    public function start($unIds, $others = null)
    {
        $api              = 'cvm.api.qcloud.com';
        $params           = $this->baseParams;
        $params['Action'] = 'StartInstances';
        $name             = 'instanceIds.';
        if (empty($unIds)) {
            return false;
        }
        foreach ($unIds as $k => $v) {
            $params[$name . ($k + 1)] = $v;
        }
        $result = $this->callRemoteApi($this->createRequestURL($api, $params));
        if (!is_array($result) || $result['code'] == 5100) {
            return false;
        }
        if ($result['code'] == 0) {
            return true;
        }
        if ($result['code'] == 5400) {
            $detail = [];
            foreach ($result['detail'] as $k => $v) {
                $detail[$k] = !boolval($v);
            }
            return $detail;
        }
        return false;
    }

    public function stop($unIds, $others = null)
    {
        $api              = 'cvm.api.qcloud.com';
        $params           = $this->baseParams;
        $params['Action'] = 'StopInstances';
        $name             = 'instanceIds.';
        if (empty($unIds)) {
            return false;
        }
        foreach ($unIds as $k => $v) {
            $params[$name . ($k + 1)] = $v;
        }
        $result = $this->callRemoteApi($this->createRequestURL($api, $params));
        if (!is_array($result) || $result['code'] == 5100) {
            return false;
        }
        if ($result['code'] == 0) {
            return true;
        }
        if ($result['code'] == 5400) {
            $detail = [];
            foreach ($result['detail'] as $k => $v) {
                $detail[$k] = !boolval($v);
            }
            return $detail;
        }
        return false;
    }

    public function restart($unIds, $others = null)
    {
        $api              = 'cvm.api.qcloud.com';
        $params           = $this->baseParams;
        $params['Action'] = 'RestartInstances';
        $name             = 'instanceIds.';
        if (empty($unIds)) {
            return false;
        }
        foreach ($unIds as $k => $v) {
            $params[$name . ($k + 1)] = $v;
        }
        $result = $this->callRemoteApi($this->createRequestURL($api, $params));
        if (!is_array($result) || $result['code'] == 5100) {
            return false;
        }
        if ($result['code'] == 0) {
            return true;
        }
        if ($result['code'] == 5400) {
            $detail = [];
            foreach ($result['detail'] as $k => $v) {
                $detail[$k] = !boolval($v);
            }
            return $detail;
        }
        return false;
    }

    public function resetPassword($ids, $others = null)
    {
        $name = 'instanceIds.';
        if (empty($ids)) {
            return false;
        }
        foreach ($ids as $k => $v) {
            $params[$name . ($k + 1)] = $v;
        }
        $params['password'] = $others['password'];
        $service            = $this->getService(QcloudApi::MODULE_CVM);
        $ret                = $service->ResetInstancePassword($params);
        if ($ret === false) {
            $error       = $service->getError();
            $this->error = $error->getMessage();
            return false;
        } else {
            return true;
        }
    }

    /** --- operations methods end --- */
    public function returnInstance($ids, $others = null)
    {
        $service              = $this->getService(QcloudApi::MODULE_CVM);
        $params               = $this->baseParams;
        $params['instanceId'] = $ids[0];

        $ret = $service->ReturnInstance($params);
        if ($ret === false) {
            $error       = $service->getError();
            $this->error = $error->getMessage();
            return false;
        }
        return true;
    }

    public function addAlertHost($hid, $rid, $username)
    {
        $service = $this->getService(QcloudApi::MODULE_CVM);
        $ret     = $service->DescribeInstances(['instanceIds.0' => $hid, 'Region' => $rid]);
        if (!$ret) {
            return [];
        }

        $info = $ret['instanceSet'][0];

        $ips = implode(PHP_EOL, $info['wanIpSet']);

        $gid = $this->initUser($username);

        if (!$gid) return ['code' => -1, 'msg' => "数据初始化失败"];

        $ret = $this->addHost($ips, $gid);

        return ['code' => 0, 'msg' => $ret['data']];

    }

    public function getExcel($data)
    {
        $service = $this->getService(QcloudApi::MODULE_CVM);
        $params  = $this->baseParams;

        if (isset($data['pid']) && $data['pid']) {
            $params['projectId'] = intval($data['pid']);
        }
        $rid              = $data['rid'];
        $params['Region'] = $rid;
        $lanIp            = 'lanIps.';
        if ($data['search']) {
            $search   = str_replace(['"', "'", "<", ">"], '', $data['search']);
            $searches = array_filter(explode(',', $search));
            if ((new IP())->isValid(current($searches))) {
                $i = 1;
                foreach ($searches as $ip) {
                    if ((new IP())->isValid($ip)) {
                        $params[$lanIp . $i] = $ip;
                        $i++;
                    }
                }
            } else {
                $params['searchWord'] = $search;
            }

        }
        $start            = 0;
        $limit            = 100;
        $params['offset'] = $start;
        $params['limit']  = $limit;
        $ret              = $service->DescribeInstances($params);
        if ($ret == false) {
            throw new \Exception('接口访问异常');
        }
        $data = [];

        foreach ($ret['instanceSet'] as $item) {
            $t      = [
                '名称'     => $item['instanceName'],
                '服务器ID'  => $item['instanceId'],
                '状态'     => $this->status[$item['status']],
                '公网IP'   => implode(',', $item['wanIpSet']),
                '内网IP'   => $item['lanIp'],
                '创建时间'   => $item['createTime'],
                '到期时间'   => $item['deadlineTime'],
                '地域'     => self::$region[$item['Region']],
                '可用区'    => $item['zoneName'],
                '主机计费模式' => $this->cvmPayMode[$item['cvmPayMode']],
                '网络计费模式' => $this->networkPayMode[$item['networkPayMode']],
                '操作系统'   => $item['os'],
                'CPU'    => $item['cpu'],
                '内存'     => $item['mem'] . "GB",
                '系统盘'    =>
                    $item['diskInfo']['rootSize'] . 'GB(' . $this->diskType[$item['diskInfo']['rootType']] . ')',
                '数据盘'    =>
                    $item['diskInfo']['storageSize'] . 'GB(' . $this->diskType[$item['diskInfo']['storageType']] . ')',
                '公网带宽'   => $item['bandwidth'] . 'MB',
                '镜像ID'   => $item['unImgId']
            ];
            $data[] = $t;
        }
        $total = $ret['totalCount'];
        if ($total / $limit > 1) {
            $pageCount = ceil($total / $limit);
            for ($i = 1; $i < $pageCount; $i++) {
                $params['offset'] = $start + $limit * $i;
                $params['limit']  = $limit;
                $ret              = $service->DescribeInstances($params);
                if ($ret == false) {
                    throw new \Exception('接口访问异常');
                }
                foreach ($ret['instanceSet'] as $item) {
                    $t      = [
                        '名称'     => $item['instanceName'],
                        '服务器ID'  => $item['instanceId'],
                        '状态'     => $this->status[$item['status']],
                        '公网IP'   => implode(',', $item['wanIpSet']),
                        '内网IP'   => $item['lanIp'],
                        '创建时间'   => $item['createTime'],
                        '到期时间'   => $item['deadlineTime'],
                        '地域'     => self::$region[$item['Region']],
                        '可用区'    => $item['zoneName'],
                        '主机计费模式' => $this->cvmPayMode[$item['cvmPayMode']],
                        '网络计费模式' => $this->networkPayMode[$item['networkPayMode']],
                        '操作系统'   => $item['os'],
                        'CPU'    => $item['cpu'],
                        '内存'     => $item['mem'] . "GB",
                        '系统盘'    =>
                            $item['diskInfo']['rootSize'] . 'GB(' . $this->diskType[$item['diskInfo']['rootType']]
                            . ')',
                        '数据盘'    =>
                            $item['diskInfo']['storageSize'] . 'GB(' . $this->diskType[$item['diskInfo']['storageType']]
                            . ')',
                        '公网带宽'   => $item['bandwidth'] . 'MB',
                        '镜像ID'   => $item['unImgId']
                    ];
                    $data[] = $t;
                }
            }

        }
        $excel = null;
        return $this->createExcel($excel, $data);
    }

    public function getImageList($type = 2)
    {
        $service             = $this->getService(QcloudApi::MODULE_IMAGE);
        $params              = $this->baseParams;
        $params['imageType'] = intval($type);

        $ret = $service->DescribeImages($params);
        if ($ret === false) {
            $error       = $service->getError();
            $this->error = $error->getMessage();
            return false;
        }
        $list = [];
        foreach ($ret['imageSet'] as $item) {
            $list[$item['unImgId']] = $item['imageName'];
        }
        return $list;
    }

    public function createInstance($configData)
    {
        $service                     = $this->getService(QcloudApi::MODULE_CVM);
        $params                      = $this->baseParams;
        $params['imageType']         = $configData['imageType'];
        $params['imageId']           = $configData['imageId'];
        $params['cpu']               = $configData['cpu'];
        $params['mem']               = $configData['mem'];
        $params['bandwidth']         = $configData['bandwidth'];
        $params['wanIp']             = $configData['wanIp'];
        $params['bandwidthType']     = $configData['bandwidthType'];
        $params['storageType']       = $configData['storageType'];
        $params['storageSize']       = $configData['storageSize'];
        $params['rootSize']          = $configData['rootSize'];
        $params['period']            = $configData['period'];
        $params['goodsNum']          = $configData['goodsNum'];
        if(isset($configData['password'])) {
            $params['password']          = $configData['password'];
        }

        $params['zoneId']            = $configData['zoneId'];
        if(isset($params['vpcId']))
            $params['vpcId']             = $configData['vpcId'];
        if(isset($params['subnetId']))
            $params['subnetId']          = $configData['subnetId'];
        if(isset($params['isVpcGateway'])) {
            $params['isVpcGateway']      = $configData['isVpcGateway'];
        }
        if(isset($params['needSecurityAgent'])){
            $params['needSecurityAgent'] = $configData['needSecurityAgent'];
        }
        if(isset($params['needMonitorAgent'])) {
            $params['needMonitorAgent']  = $configData['needMonitorAgent'];
        }

        $params['projectId']         = $configData['projectId'];
        if(isset($params['keyId'])){
            $params['keyId']             = $configData['keyId'];
        }


        $ret = $service->RunInstances($params);
        if ($ret === false) {
            $error       = $service->getError();
            $this->error = $error->getMessage();
            return false;
        }
        return true;
    }

    public function getPrice($configs)
    {
        $service                 = $this->getService(QcloudApi::MODULE_CVM);
        $params                  = $this->baseParams;
        $params['instanceType']  = isset($configs['instanceType'])?$configs['instanceType']:1;
        $params['imageType']     = isset($configs['imageType'])?intval($configs['imageType']):2;
        $params['imageId']       = isset($configs['imageId'])?$configs['imageId']:0;
        $params['cpu']           = isset($configs['cpu'])?intval($configs['cpu']):0;
        $params['mem']           = isset($configs['mem'])?intval($configs['mem']):0;
        $params['bandwidth']     = isset($configs['bandwidth'])?intval($configs['bandwidth']):0;
        $params['bandwidthType'] = isset($configs['bandwidthType'])?$configs['bandwidthType']:'PayByBandwidth';
        /*$params['storageType']   = isset($configs['storageType'])?$configs['storageType']:0;
        $params['storageSize']   = isset($configs['storageSize'])?$configs['storageSize']:0;
        $params['rootSize']      = isset($configs['rootSize'])?$configs['rootSize']:0;*/
        $params['period']        = isset($configs['period'])?abs(intval($configs['period'])):0;
        $params['goodsNum']      = isset($configs['goodsNum'])?intval($configs['goodsNum']):0;
        $ret                     = $service->InquiryInstancePrice($params);
        if ($ret === false) {
            $error       = $service->getError();
            $this->error = $error->getMessage();
            return $this->error;
        }
        return $ret;
    }
    public function getPriceById($configs)
    {
        $service                 = $this->getService(QcloudApi::MODULE_CVM);
        $params                  = $this->baseParams;
        $params['instanceType']  = 1;
        $params['period']        = $configs['period'];
        $params['instanceId']       = $configs['id'];

        $ret                = $service->InquiryInstancePrice($params);
        if ($ret === false) {
            $error       = $service->getError();
            $this->error = $error->getMessage();
            return $this->error;
        }
        return $ret;
    }

    public function getZoneList($params)
    {
        $service                 = $this->getService(QcloudApi::MODULE_CVM);
        $params                  = $this->baseParams;
        $ret                     = $service->DescribeAvailabilityZones($params);
        if ($ret === false) {
            $error       = $service->getError();
            $this->error = $error->getMessage();
            return $this->error;
        }
        $list = array();
        foreach ($ret['zoneSet'] as $zone) {
            $list[$zone['zoneId']] = $zone['zoneName'];
        }
        return $list;
    }

    public function reNewInstance($data)
    {
        $service = $this->getService(QcloudApi::MODULE_CVM);
        $params  = $this->baseParams;
        $params['instanceId'] = $data['id'];
        $params['period']     = $data['period'];
        $ret     = $service->RenewInstance($params);
        if ($ret === false) {
            $error       = $service->getError();
            $this->error = $error->getMessage();
            return $this->error;
        }
        return ($ret['code'] === 0);
    }

    public function getCountByPidAndRid($pid, $rid)
    {
        $service          = $this->getService(QcloudApi::MODULE_CVM);
        $params           = $this->baseParams;
        $params['offset'] = intval(0);
        $params['limit']  = intval(5);
        $params['projectId'] = intval($pid);
        $params['Region'] = $rid;
        $ret = $service->DescribeInstances($params);
        if ($ret === false) {
            return 0;
        }
        return $ret['totalCount'];
    }

    public function getProjectCountList()
    {
        $projects = $this->getProjectList();
        unset($projects[0]);
        $regions  = $this->getRegionList();
        $result = [];
        foreach($projects as $pid => $pName)
        {
            foreach($regions as $rid => $rName)
            {
                if(isset($result[$pid]))
                {
                    $result[$pid]['counts'] += $this->getCountByPidAndRid($pid, $rid);
                } else {
                    $result[$pid] = [
                        'counts' => $this->getCountByPidAndRid($pid, $rid),
                        'pName'  => $pName
                    ];
                }
            }
        }
        return $result;
    }
}