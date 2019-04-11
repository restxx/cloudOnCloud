<?php

namespace Application\FirmApiAdapter;

use Application\FirmApiAdapter\Sdk\UCloud\UCloudApiClient;


/**
 * Class UCloud
 *
 * @package Application\FirmApiAdapter
 * @author  Xuman
 * @version $Id$
 */
class UCloud extends FirmAbstract
{
    protected $host = 'http://api.ucloud.cn';
    protected $PublicKey = null;
    protected $PrivateKey = null;
    protected $conn = null;
    static $region = [
        'cn-north-01' => '北京BGP-A',
        'cn-north-02' => '北京BGP-B',
        'cn-north-03' => '北京BGP-C',
        'cn-north-04' => '北京BGP-D',
        'cn-east-01'  => '华东双线',
        'cn-east-02'  => '金融云',
        'cn-south-01' => '华南双线',
        'cn-south-02' => '广州BGP',
        'hk-01'       => '亚太',
        'us-west-01'  => '北美'
    ];
    static $ipTypes = [
        'China-telecom' => '电信',
        'China-unicom'  => '联通',
        'Internation'   => '国际',
        'Bgp'           => 'BGP',
        'Private'       => '内网',
        'Duplet'        => '双线'
    ];
    static $states = [
        'Initializing'     => '初始化',
        'Starting'         => '启动中',
        'Running'          => '运行中',
        'Stopping'         => '关机中',
        'Stopped'          => '关机',
        'Install Fail'     => '安装失败',
        'ChangingPassword' => '修改密码中',
        'Rebooting'        => '重启中'
    ];
    static $chargeType = [
        'Year'    => '按年付费',
        'Month'   => '按月付费',
        'Dynamic' => '按需付费',
        'Trial'   => '试用'
    ];

    protected $monitor = [
        0 => [
            'caption' => '基础监控',
            0         => [
                'id'    => 'CPUUtilization',
                'title' => 'CPU使用率'
            ],
            1         => [
                'id'    => 'MemUsage',
                'title' => '内存使用量'
            ],
        ],
        1 => [
            'caption' => '网卡监控',
            0         => [
                'id'    => 'NICIn',
                'title' => '网卡入带宽'
            ],
            1         => [
                'id'    => 'NICOut',
                'title' => '网卡出带宽'
            ],
        ],
        2 => [
            0 => [
                'id'    => 'NetPacketIn',
                'title' => '内网入包量'
            ],
            1 => [
                'id'    => 'NetPacketOut',
                'title' => '内网出包量'
            ],
        ],
        3 => [
            'caption' => '磁盘监控',
            0         => [
                'id'    => 'RootSpaceUsage',
                'title' => '系统盘使用率'
            ],
            1         => [
                'id'    => 'DataSpaceUsage',
                'title' => '数据盘使用率'
            ],
        ],
        4 => [
            0 => [
                'id'    => 'IORead',
                'title' => '磁盘读吞吐'
            ],
            1 => [
                'id'    => 'IOWrite',
                'title' => '磁盘写吞吐'
            ],
        ],
        5 => [
            0 => [
                'id'    => 'DiskReadOps',
                'title' => '磁盘读次数'
            ],
            1 => [
                'id'    => 'DiskWriteOps',
                'title' => '磁盘写次数'
            ],
        ],
        6 => [
            0 => [
                'id'    => 'ReadonlyDiskCount',
                'title' => '只读磁盘个数'
            ],
            1 => [
                'id'    => 'RunnableProcessCount',
                'title' => '运行进程数量'
            ],
        ],
        7 => [
            0 => [
                'id'    => 'BlockProcessCount',
                'title' => '阻塞进程数量'
            ]
        ]
    ];


    protected $baseOperationStatus = [
        self::CAN_START      => ['Stopped'],
        self::CAN_STOP       => ['Running'],
        self::CAN_RESTART    => ['Stopped', 'Running'],
        self::CAN_DELETE     => ['Stopped'],
        self::CAN_RESET_PASS => ['Stopped']
    ];

    public function __construct($params = [])
    {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
        $this->conn = new UCloudApiClient($this->host, $this->PublicKey, $this->PrivateKey, '');
    }

    public function getProjectList()
    {
        return [0 => '全部'];
    }

    public function getRegionList()
    {
        return self::$region;
    }

    public function authorization()
    {

        $params           = [
            'Action' => 'DescribeUHostInstance'
        ];
        $params['Region'] = current(array_keys(self::$region));
        $ret              = $this->conn->get($params);
        $code             = $ret['RetCode'];
        return !in_array($code, [172, 171]);
    }

    public function getInstanceList($data)
    {
        $params           = [
            'Action' => 'DescribeUHostInstance'
        ];
        $params['Offset'] = intval($data['start']);
        $params['Limit']  = intval($data['limit']);
        if (isset($data['pid']) && $data['pid']) {
            $this->conn->setProjectId($data['pid']);
        }
        $params['Region'] = $data['rid'];
        $ret              = $this->conn->get($params);
        if ($ret === false || $ret['RetCode'] != 0) {
            return ['totalCount' => 0, 'data' => []];
        }
        $data = [];
        foreach ($ret['UHostSet'] as $item) {
            $t         = array();
            $t['name'] = $item['Name'];
            $t['unId'] = $item['UHostId'];
            $wanIp     = '';
            $ip        = '';
            foreach ($item['IPSet'] as $ipItem) {
                if ($ipItem['Type'] != 'Private' && !$wanIp) {
                    $wanIp = $ipItem['IP'];
                }
                if ($ip) {
                    $ip .= '<br>' . $ipItem['IP'] . '(' . self::$ipTypes[$ipItem['Type']] . ')';
                } else {
                    $ip .= $ipItem['IP'] . '(' . self::$ipTypes[$ipItem['Type']] . ')';
                }

            }
            $t['lanIp']     = $ip;
            $t['wanIpSet']  = $wanIp;
            $t['status']    = $item['State'];
            $q              = '';
            $operationsList = $this->getOperationsStatus();
            foreach ($operationsList as $val) {
                if (in_array($item['State'], $val)) {
                    $q .= '1';
                } else {
                    $q .= '0';
                }
            }
            $q .= '11';
            $t['operationStatus'] = $q;
            $t['statusName']      = self::$states[$item['State']];
            $t['deadlineTime']    = date('Y-m-d H:i:s', $item['ExpireTime']);
            $t['rid']             = $params['Region'];
            $data[]               = $t;
        }
        return ['totalCount' => $ret['TotalCount'], 'data' => $data];
    }

    public function getInstanceInfo($hid, $rid)
    {
        $params               = [
            'Action' => 'DescribeUHostInstance'
        ];
        $params['Region']     = $rid;
        $params['UHostIds.0'] = $hid;
        $ret                  = $this->conn->get($params);
        $info                 = $ret['UHostSet'][0];
        $diskSet              = '';
        foreach ($info['DiskSet'] as $disk) {
            $diskSet .= '(' . $disk['Type'] . ' | ' . $disk['Drive'] . ' | ' . $disk['Size'] . 'GB) ';
        }
        $ip = '';
        foreach ($info['IPSet'] as $IPItem) {
            if ($ip) {
                $ip .= '<br>' . $IPItem['IP'] . '(' . self::$ipTypes[$IPItem['Type']] . ')';
            } else {
                $ip .= $IPItem['IP'] . '(' . self::$ipTypes[$IPItem['Type']] . ')';
            }
        }
        $wIp = '';
        foreach ($info['IPSet'] as $val) {
            if ($val['Type'] != "Private") {
                $wIp = $val['IP'];
                break;
            }
        }

        $patternData = [
            '主机信息'    => [
                '名称'     => $info['Name'],
                '服务器ID'  => $info['UHostId'],
                '状态'     => self::$states[$info['State']],
                '创建时间'   => date('Y-m-d H:i:s', $info['CreateTime']),
                '到期时间'   => date('Y-m-d H:i:s', $info['ExpireTime']),
                '地域'     => self::$region[$rid],
                '主机计费模式' => self::$chargeType[$info['ChargeType']],
                '业务组'    => $info['Tag'],
                'IP'     => $ip,
                /*'所属网络'   => $info[''],
                '用作公网网关' => $info[''],
                '所在子网'   => $info['subnetId']*/
            ],
            '机器配置'    => [
                '操作系统' => $info['OsName'],
                'CPU'  => $info['CPU'],
                '内存'   => $info['Memory'] . "MB",
                '磁盘'   => $diskSet
            ],
            '系统镜像'    => [
                '基础镜像名称' => $info['BasicImageName'],
                '基础镜像ID' => $info['BasicImageId'],
                '镜像ID'   => $info['ImageId']
            ],
            'monitor' => $this->monitor,
            'wIp'     => $wIp
        ];
        return $patternData;
    }

    public function start($ids, $others = null)
    {
        set_time_limit(300);
        $params           = [
            'Action' => 'StartUHostInstance'
        ];
        $params['Region'] = $others['rid'];
        foreach ($ids as $id) {
            $params['UHostId'] = $id;
            $this->conn->get($params);
        }
        return true;
    }

    public function stop($ids, $others = null)
    {
        set_time_limit(300);
        $params           = [
            'Action' => 'StopUHostInstance'
        ];
        $params['Region'] = $others['rid'];
        foreach ($ids as $id) {
            $params['UHostId'] = $id;
            $this->conn->get($params);
        }
        return true;
    }

    public function restart($ids, $others = null)
    {
        set_time_limit(300);
        $params           = [
            'Action' => 'RebootUHostInstance'
        ];
        $params['Region'] = $others['rid'];
        foreach ($ids as $id) {
            $params['UHostId'] = $id;
            $this->conn->get($params);
        }
        return true;
    }

    public function getMonitorData($hid, $mid, $qtype, $rid = null)
    {
        $params = [
            'Action' => 'GetMetric'
        ];

        date_default_timezone_set("PRC");

        switch ($qtype) {
            case 0:
                $startTime = strtotime(date("Y-m-d H:i:s", strtotime("-1 hour")));
                $period    = 600;
                break;
            case 1:
                $startTime = strtotime(date("Y-m-d"));
                $period    = 600;
                break;
            case 2:
                $startTime = strtotime(date("Y-m-d", strtotime("-1 day")));
                $endTime   = strtotime(date("Y-m-d"));
                $period    = 3600;
                break;
            case 3:
                $startTime = strtotime(date("Y-m-d", strtotime("-7 day")));
                $endTime   = strtotime(date("Y-m-d"));
                $period    = 86400;
                break;
            case 4:
                $startTime = strtotime(date("Y-m-d", strtotime("-14 day")));
                $endTime   = strtotime(date("Y-m-d"));
                $period    = 86400;
                break;
            default:
                $startTime = strtotime(date("Y-m-d"));
                break;
        }

        $params['ResourceType'] = "uhost";
        $params['ResourceId']   = $hid;
        $params['Region']       = $rid;
        $params['MetricName.0'] = $mid;
        $params['BeginTime']    = $startTime;
        $params['EndTime']      = isset($endTime) ? $endTime : time();
        $params['Period']       = isset($period) ? $period : 300;
        $ret                    = $this->conn->get($params);

        if ($ret === false || $ret['RetCode'] != 0) {
            return ['dataPoints' => [], 'categories' => []];
        } else {
            $dataPoints = current($ret['DataSets']);

            $Categories = [];
            $Points     = [];

            foreach ($dataPoints as $dataPoint) {

                if ($period == 86400) {
                    $date_format = date("Y-m-d H:i", $dataPoint['Timestamp']);
                } else {
                    $date_format = date("H:i", $dataPoint['Timestamp']);
                }

                array_push($Categories, $date_format);
                array_push($Points, $dataPoint['Value'] ? floatval($dataPoint['Value']) : 0);
            }

            return ['categories' => $Categories, 'points' => $Points];
        }
    }

    public function resetPassword($ids, $others = null)
    {
        set_time_limit(300);
        $params             = [
            'Action' => 'ResetUHostInstancePassword'
        ];
        $params['Region']   = $others['rid'];
        $params['UHostId']  = $ids[0];
        $params['Password'] = base64_encode($others['password']);
        $ret                = $this->conn->get($params);
        if ($ret['RetCode'] == 0) {
            return true;
        }
        return true;
    }

    /** --- operations methods end --- */
    public function returnInstance($ids, $others = null)
    {
        $params            = [
            'Action' => 'TerminateUHostInstance'
        ];
        $params['Region']  = $others['rid'];
        $params['UHostId'] = $others['hid'];
        $ret               = $this->conn->get($params);
        if ($ret && $ret['RetCode'] == 0) {
            return true;
        }
        return false;
    }

    public function addAlertHost($hid, $rid, $username)
    {
        $params               = [
            'Action' => 'DescribeUHostInstance'
        ];
        $params['Region']     = $rid;
        $params['UHostIds.0'] = $hid;
        $ret                  = $this->conn->get($params);
        $info                 = $ret['UHostSet'][0];

        $ips = [];
        foreach ($info['IPSet'] as $ip) {
            if ($ip['Type'] != "Private") {
                array_push($ips, $ip['IP']);
            }
        }

        $ips = implode(PHP_EOL, $ips);

        $gid = $this->initUser($username);
        if (!$gid) return ['code' => -1, 'msg' => "数据初始化失败"];
        $ret = $this->addHost($ips, $gid);

        return ['code' => 0, 'msg' => $ret['data']];
    }

    public function getExcel($data)
    {
        $params           = [
            'Action' => 'DescribeUHostInstance'
        ];
        $params['Offset'] = 0;
        $params['Limit']  = 10000000;
        if (isset($data['pid']) && $data['pid']) {
            $this->conn->setProjectId($data['pid']);
        }
        $params['Region'] = $data['rid'];
        $rid              = $data['rid'];
        $ret              = $this->conn->get($params);
        if ($ret === false || $ret['RetCode'] != 0) {
            return ['totalCount' => 0, 'data' => []];
        }
        $data = [];
        foreach ($ret['UHostSet'] as $item) {
            $diskSet = '';
            foreach ($item['DiskSet'] as $disk) {
                $diskSet .= '(' . $disk['Type'] . ' | ' . $disk['Drive'] . ' | ' . $disk['Size'] . 'GB) ';
            }
            $ip = '';
            foreach ($item['IPSet'] as $IPItem) {
                if ($ip) {
                    $ip .= '|' . $IPItem['IP'] . '(' . self::$ipTypes[$IPItem['Type']] . ')';
                } else {
                    $ip .= $IPItem['IP'] . '(' . self::$ipTypes[$IPItem['Type']] . ')';
                }
            }
            $t      = [
                '名称'     => $item['Name'],
                '服务器ID'  => $item['UHostId'],
                '状态'     => self::$states[$item['State']],
                '创建时间'   => date('Y-m-d H:i:s', $item['CreateTime']),
                '到期时间'   => date('Y-m-d H:i:s', $item['ExpireTime']),
                '地域'     => self::$region[$rid],
                '主机计费模式' => self::$chargeType[$item['ChargeType']],
                '业务组'    => $item['Tag'],
                'IP'     => $ip,
                '操作系统'   => $item['OsName'],
                'CPU'    => $item['CPU'],
                '内存'     => $item['Memory'] . "MB",
                '磁盘'     => $diskSet,
                '基础镜像名称' => $item['BasicImageName'],
                '基础镜像ID' => $item['BasicImageId'],
                '镜像ID'   => $item['ImageId']
            ];
            $data[] = $t;
        }
        $excel = null;
        return $this->createExcel($excel, $data);
    }
}