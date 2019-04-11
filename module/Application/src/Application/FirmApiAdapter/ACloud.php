<?php

namespace Application\FirmApiAdapter;
include __DIR__ . '/Sdk/aliyun/aliyun-php-sdk-core/Config.php';
use DefaultAcsClient;
use DefaultProfile;
use Ecs\Request\V20140526 as Ecs;
use Zend\Validator\Ip;

/**
 * Class ACloud
 *
 * @package Application\FirmApiAdapter
 * @author  Xuman
 * @version $Id$
 */
class ACloud extends FirmAbstract
{
    protected $client = null;
    protected $AccessKeyId = '';
    protected $AccessKeySecret = '';
    protected $baseOperationStatus = [
        self::CAN_START      => ['Stopped'],
        self::CAN_STOP       => ['Running'],
        self::CAN_RESTART    => ['Running'],
        self::CAN_DELETE     => ['Stopped'],
        self::CAN_RESET_PASS => ['Running', 'Stopped', 'Stopping']
    ];
    static $status = [
        'Pending'  => '准备中',
        'Stopped'  => '已停止',
        'Starting' => '启动中',
        'Running'  => '运行中',
        'Stopping' => '停止中',
        'Deleted'  => '已释放'
    ];
    static $InternetChargeType = [
        'PayByTraffic'   => '按流量计费',
        'PayByBandwidth' => '按带宽计费'
    ];
    static $InstanceChargeType = [
        'PrePaid'  => '预付费(包年包月)',
        'PostPaid' => '后付费(按量付费)'
    ];

    protected $monitor = [
        0 => [
            'caption' => '常规监控',
            0         => [
                'id'    => 'CPU',
                'title' => 'CPU使用率'
            ],
            1         => [
                'id'    => 'IntranetRX',
                'title' => '接收的数据流量'
            ],
        ],
        1 => [
            0 => [
                'id'    => 'IntranetTX',
                'title' => '发送的数据流量'
            ],
            1 => [
                'id'    => 'IntranetBandwidth',
                'title' => '带宽'
            ],
        ],
        2 => [
            0 => [
                'id'    => 'IOPSRead',
                'title' => 'IO读操作'
            ],
            1 => [
                'id'    => 'IOPSWrite',
                'title' => 'IO写操作'
            ],
        ],
        3 => [
            0 => [
                'id'    => 'BPSRead',
                'title' => '磁盘读带宽'
            ],
            1 => [
                'id'    => 'BPSWrite',
                'title' => '磁盘写带宽'
            ],
        ],
    ];

    public function __construct($params)
    {
        parent::__construct($params);
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $this->AccessKeyId, $this->AccessKeySecret);
        /** @var DefaultAcsClient client */
        $this->client = new DefaultAcsClient($iClientProfile);

    }

    public function getProjectList()
    {
        return [0 => '全部'];
    }

    public function authorization()
    {
        $code = -1;
        try {
            $this->getRegionList();
        } catch (\Exception $e) {
            $code = $e->getCode();
        }

        return ($code == -1);
    }

    public function getRegionList()
    {
        $request = new Ecs\DescribeRegionsRequest();
        $request->setMethod("GET");
        $response = $this->client->getAcsResponse($request);
        $regions  = [];
        foreach ($response->Regions->Region as $region) {
            $regions[$region->RegionId] = $region->LocalName;
        }
        return $regions;
    }

    public function getInstanceList($data)
    {
        $request = new Ecs\DescribeInstancesRequest();
        $request->setMethod("GET");
        $pageNumber = intval($data['start']) / intval($data['limit']) + 1;
        $request->setPageNumber($pageNumber);
        $request->setPageSize(intval($data['limit']));
        $rid = $data['rid'];
        $request->setRegionId($rid);

        if ($data['search']) {
            $search   = str_replace(['"', "'", "<", ">"], '', $data['search']);
            $searches = array_filter(explode(',', $search));
            $ips      = [];
            if ((new IP())->isValid(current($searches))) {
                foreach ($searches as $ip) {
                    if ((new IP())->isValid($ip)) {
                        $ips[] = $ip;
                    }
                }
                if (!empty($ips)) {
                    $request->setInnerIpAddresses(json_encode($ips));
                }
            } else {
                $request->setInstanceName($search);
            }
        }
        $response = $this->client->getAcsResponse($request);
        $data     = [];
        foreach ($response->Instances->Instance as $item) {
            $t               = array();
            $t['name']       = $item->InstanceName;
            $t['unId']       = $item->InstanceId;
            $t['lanIp']      = implode(',', $item->InnerIpAddress->IpAddress);
            $t['pid']        = '-';
            $t['wanIpSet']   = implode(',', $item->PublicIpAddress->IpAddress);
            $t['zoneName']   = $item->ZoneId;
            $t['status']     = $item->Status;
            $t['statusName'] = self::$status[$item->Status];
            $q               = '';
            $operationsList  = $this->getOperationsStatus();
            foreach ($operationsList as $val) {
                if (in_array($item->Status, $val)) {
                    $q .= '1';
                } else {
                    $q .= '0';
                }
            }
            $q .= '11';
            $t['operationStatus'] = $q;

            $t['deadlineTime'] = date('Y-m-d H:i:s', strtotime($item->ExpiredTime));
            $t['rid']          = $rid;
            $data[]            = $t;
        }
        return ['totalCount' => $response->TotalCount, 'data' => $data];
    }

    public function start($ids, $others = null)
    {
        set_time_limit(300);
        foreach ($ids as $id) {
            $request = new Ecs\StartInstanceRequest();
            $request->setInstanceId($id);
            $this->client->getAcsResponse($request);
        }
        return true;
    }

    public function stop($ids, $others = null)
    {
        set_time_limit(300);
        foreach ($ids as $id) {
            $request = new Ecs\StopInstanceRequest();
            $request->setInstanceId($id);
            $this->client->getAcsResponse($request);
        }
        return true;
    }

    public function restart($ids, $others = null)
    {
        set_time_limit(300);
        foreach ($ids as $id) {
            $request = new Ecs\RebootInstanceRequest();
            $request->setInstanceId($id);
            $this->client->getAcsResponse($request);
        }
        return true;
    }

    public function getInstanceInfo($hid, $rid)
    {
        $request = new Ecs\DescribeInstancesRequest();
        $request->setMethod("GET");
        $request->setRegionId($rid);
        $request->setInstanceIds(json_encode([$hid]));
        $response    = $this->client->getAcsResponse($request);
        $info        = $response->Instances->Instance[0];
        $patternData = [
            '主机信息'    => [
                '名称'     => $info->InstanceName,
                '服务器ID'  => $info->InstanceId,
                '状态'     => self::$status[$info->Status],
                '公网IP'   => implode(',', $info->PublicIpAddress->IpAddress),
                '内网IP'   => implode(',', $info->InnerIpAddress->IpAddress),
                '创建时间'   => date('Y-m-d H:i:s', strtotime($info->CreationTime)),
                '到期时间'   => date('Y-m-d H:i:s', strtotime($info->ExpiredTime)),
                '地域'     => $info->RegionId,
                '可用区'    => $info->ZoneId,
                '主机计费模式' => self::$InstanceChargeType[$info->InstanceChargeType],
                '网络计费模式' => self::$InternetChargeType[$info->InternetChargeType],
                /*'所属网络'   => $info[''],
                '用作公网网关' => $info[''],
                '所在子网'   => $info['subnetId']*/
            ],
            '机器配置'    => [
                'CPU' => $info->Cpu,
                '内存'  => $info->Memory . "MB",
            ],
            '系统镜像'    => [
                '镜像ID' => $info->ImageId
            ],
            'monitor' => $this->monitor
        ];
        return $patternData;

    }

    public function getMonitorData($hid, $mid, $qtype, $rid = null)
    {

        $request = new Ecs\DescribeInstanceMonitorDataRequest();

        //QcBYM+fH3YBEgGEBIX3Y3JJBinm70tSkh9YGslUcMklyOtXaAdIRyA==
        //88f55195c2487265172fd13a68f7deb435c280ee

        date_default_timezone_set('Etc/GMT+8');

        switch ($qtype) {
            case 0:
                $startTime = date("Y-m-d\TH:i:s\Z", strtotime("-1 hour"));
                $endTime   = date("Y-m-d\TH:i:s\Z");
                $period    = 60;
                break;
            case 1:
                $startTime = date("Y-m-d\T00:00:00\Z");
                $endTime   = date("Y-m-d\TH:i:s\Z");
                $period    = 3600;
                break;
            case 2:
                $startTime = date("Y-m-d\T00:00:00\Z", strtotime("-1 day"));
                $endTime   = date("Y-m-d\T00:00:00\Z");
                $period    = 3600;
                break;
            case 3:
                $startTime = date("Y-m-d\T00:00:00\Z", strtotime("-7 day"));
                $endTime   = date("Y-m-d\T00:00:00\Z");
                $period    = 3600;
                break;
            case 4:
                $startTime = date("Y-m-d\T00:00:00\Z", strtotime("-14 day"));
                $endTime   = date("Y-m-d\T00:00:00\Z");
                $period    = 3600;
                break;
            default:
                $startTime = date("Y-m-d H:i:s", strtotime("-1 hour"));
                $endTime   = date("Y-m-d H:i:s");
                $period    = 60;
                break;
        }

        $request->setInstanceId($hid);
        $request->setStartTime($startTime);
        $request->setEndTime($endTime);
        $request->setPeriod($period);
        $response = $this->client->getAcsResponse($request);

        $ret = json_decode(json_encode($response->MonitorData->InstanceMonitorData), true);;

        if (!$ret) {
            return ['dataPoints' => [], 'categories' => []];
        } else {

            $Categories = [];
            $Points     = [];

            foreach ($ret as $dataPoint) {

                if ($period == 3600) {
                    $date_format = date("Y-m-d H:i", strtotime($dataPoint['TimeStamp']) - 8 * 3600);
                } else {
                    $date_format = date("H:i", strtotime($dataPoint['TimeStamp']) - 8 * 3600);
                }

                unset($dataPoint['TimeStamp'], $dataPoint['InstanceId']);

                foreach ($dataPoint as $key => $val) {

                    $Points[$key][] = $val;
                }

                array_push($Categories, $date_format);

            }

            return ['categories' => $Categories, 'points' => $Points];
        }

    }

    public function resetPassword($ids, $others = null)
    {
        $request = new Ecs\ModifyInstanceAttributeRequest();
        $request->setInstanceId($ids[0]);
        $request->setPassword($others['password']);
        try {
            $response = $this->client->getAcsResponse($request);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }

    public function returnInstance($ids, $others = null)
    {
        $request = new Ecs\DeleteInstanceRequest();
        $request->setInstanceId($ids[0]);
        try {
            $response = $this->client->getAcsResponse($request);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }

    /** --- operations methods end --- */

    public function addAlertHost($hid, $rid, $username)
    {
        $request = new Ecs\DescribeInstancesRequest();
        $request->setMethod("GET");
        $request->setRegionId($rid);
        $request->setInstanceIds(json_encode([$hid]));
        $response = $this->client->getAcsResponse($request);
        $info     = $response->Instances->Instance[0];

        $ips = implode(PHP_EOL, $info->PublicIpAddress->IpAddress);

        $gid = $this->initUser($username);
        if (!$gid) return ['code' => -1, 'msg' => "数据初始化失败"];
        $ret = $this->addHost($ips, $gid);

        return ['code' => 0, 'msg' => $ret['data']];

    }

    public function getExcel($data)
    {
        $request = new Ecs\DescribeInstancesRequest();
        $request->setMethod("GET");
        $pageSize   = 100;
        $pageNumber = 1;
        $request->setPageNumber($pageNumber);
        $request->setPageSize($pageSize);
        $rid = $data['rid'];
        $request->setRegionId($rid);

        if ($data['search']) {
            $search   = str_replace(['"', "'", "<", ">"], '', $data['search']);
            $searches = array_filter(explode(',', $search));
            $ips      = [];
            if ((new IP())->isValid(current($searches))) {
                foreach ($searches as $ip) {
                    if ((new IP())->isValid($ip)) {
                        $ips[] = $ip;
                    }
                }
                if (!empty($ips)) {
                    $request->setInnerIpAddresses(json_encode($ips));
                }
            } else {
                $request->setInstanceName($search);
            }
        }
        $response = $this->client->getAcsResponse($request);
        $data     = [];
        foreach ($response->Instances->Instance as $item) {
            $t      = [
                '名称'     => $item->InstanceName,
                '服务器ID'  => $item->InstanceId,
                '状态'     => self::$status[$item->Status],
                '公网IP'   => implode(',', $item->PublicIpAddress->IpAddress),
                '内网IP'   => implode(',', $item->InnerIpAddress->IpAddress),
                '创建时间'   => date('Y-m-d H:i:s', strtotime($item->CreationTime)),
                '到期时间'   => date('Y-m-d H:i:s', strtotime($item->ExpiredTime)),
                '地域'     => $item->RegionId,
                '可用区'    => $item->ZoneId,
                '主机计费模式' => self::$InstanceChargeType[$item->InstanceChargeType],
                '网络计费模式' => self::$InternetChargeType[$item->InternetChargeType],
                'CPU'    => $item->Cpu,
                '内存'     => $item->Memory . "MB",
                '镜像ID'   => $item->ImageId
            ];
            $data[] = $t;
        }
        $pageCount = $response->TotalCount / $pageSize + 1;
        if ($pageCount > 1) {
            for ($i = 2; $i <= $pageCount; $i++) {
                $request->setPageNumber($i);
                $request->setPageSize($pageSize);
                $response = $this->client->getAcsResponse($request);
                foreach ($response->Instances->Instance as $item) {
                    $t      = [
                        '名称'     => $item->InstanceName,
                        '服务器ID'  => $item->InstanceId,
                        '状态'     => self::$status[$item->Status],
                        '公网IP'   => implode(',', $item->PublicIpAddress->IpAddress),
                        '内网IP'   => implode(',', $item->InnerIpAddress->IpAddress),
                        '创建时间'   => date('Y-m-d H:i:s', strtotime($item->CreationTime)),
                        '到期时间'   => date('Y-m-d H:i:s', strtotime($item->ExpiredTime)),
                        '地域'     => $item->RegionId,
                        '可用区'    => $item->ZoneId,
                        '主机计费模式' => self::$InstanceChargeType[$item->InstanceChargeType],
                        '网络计费模式' => self::$InternetChargeType[$item->InternetChargeType],
                        'CPU'    => $item->Cpu,
                        '内存'     => $item->Memory . "MB",
                        '镜像ID'   => $item->ImageId
                    ];
                    $data[] = $t;
                }
            }
        }
        $excel = null;
        return $this->createExcel($excel, $data);
    }
}