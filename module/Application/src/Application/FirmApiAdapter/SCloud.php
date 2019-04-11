<?php

namespace Application\FirmApiAdapter;

use Application\Traits\Financail;
use Application\Traits\Lib;


/**
 * Class SCloud
 *
 * @package Application\FirmApiAdapter
 * @author  Xuman
 * @version $Id$
 */
class SCloud extends FirmAbstract
{
    use Financail;

    protected $username = '';
    protected $password = '';
    protected $host = 'http://api.scloudm.com'; //'http://m.scloudm.com';
    protected $regions = [];
    protected $projects = [];
    protected $baseOperationStatus = [
        self::CAN_START   => ['STOPPED', 'SHUTOFF'],
        self::CAN_STOP    => ['ACTIVE'],
        self::CAN_RESTART => ['ACTIVE', 'STOPPED', 'SHUTOFF']
    ];

    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function post($url, $data)
    {
        $opts      = array(
            'http' => array(
                'timeout' => 30,
                'method'  => "POST",
                'header'  => "Content-type: application/json\r\n" .
                    "Sp-Agent: scloudm\r\n" .
                    "X-Auth-Token: " . $this->getToken(),
                'content' => $data,
            )
        );
        $cxContext = stream_context_create($opts);
        $result    = @file_get_contents($url, false, $cxContext);
        if (isset($http_response_header) && !preg_match('/200/', $http_response_header[0])) {
            $this->clearTokenCache();
            throw new \Exception('接口请求异常');
        }
        return $result;
    }

    public function get($url)
    {
        $opts = array(
            'http' => array(
                'timeout' => 30,
                'header'  => "Sp-Agent: scloudm\r\nX-Auth-Token: " . $this->getToken(),
            )
        );

        $cxContext = stream_context_create($opts);
        $result    = file_get_contents($url, false, $cxContext);
        if (isset($http_response_header) && !preg_match('/200/', $http_response_header[0])) {
            $this->clearTokenCache();
            throw new \Exception('接口请求异常');
        }
        return $result;
    }

    public function getRegionProject()
    {
        $api = '/mana_api/region_tenant';
        $ret = $this->get($this->host . $api);
        $ret = json_decode($ret, true);
        if (!$ret || !isset($ret['tenants'])) {
            throw new \Exception('获取项目、区域列表失败');
        }
        $this->projects = [];
        foreach ($ret['tenants'] as $tenant) {
            $this->projects[$tenant['id']] = $tenant['name'];
        }
        $this->regions = [];
        foreach ($ret['regions'] as $region) {
            $this->regions[$region] = $region;
        }
    }

    public function authorization()
    {
        $code = -1;
        try {
            $this->getToken();
        } catch (\Exception $e) {
            $code = $e->getCode();
        }
        return ($code != 401);
    }
    private function clearTokenCache()
    {
        $sessionKey = "firm_scloud_token_" . $this->username;
        unset($_SESSION[$sessionKey]);
    }
    public function getToken()
    {
        $sessionKey = "firm_scloud_token_" . $this->username;
        if (isset($_SESSION[$sessionKey])) {
            if (strtotime($_SESSION[$sessionKey]['expires']) > time() + 600) {
                return $_SESSION[$sessionKey]['id'];
            }
        }
        $api        = '/mana_api/tokens';
        $baseParams = [
            'auth' => [
                'tenantName'          => '',
                'passwordCredentials' => [
                    'username' => $this->username,
                    'password' => $this->password
                ]
            ]
        ];
        $data       = json_encode($baseParams);
        $opts       = array(
            'http' => array(
                'timeout' => 30,
                'method'  => "POST",
                'header'  => "Content-type: application/json\r\nSp-Agent: scloudm" ,
                'content' => $data,
            )
        );
        $cxContext  = stream_context_create($opts);
        $ret        = @file_get_contents($this->host . $api, false, $cxContext);
        if (isset($http_response_header) && preg_match('/401/', $http_response_header[0])) {
            throw new \Exception('绑定信息验证失败', 401);
        }
        $result = json_decode($ret, true);
        if (!$result || !isset($result['access'])) {
            throw new \Exception('获取token失败');
        }
        $tokenInfo             = $result['access']['token'];
        $_SESSION[$sessionKey] = $tokenInfo;
        return $tokenInfo['id'];
    }

    public function getProjectList()
    {
        if (empty($this->projects)) {
            $this->getRegionProject();
        }
        return $this->projects;
    }

    public function getRegionList()
    {
        if (empty($this->regions)) {
            $this->getRegionProject();
        }
        return $this->regions;
    }

    public function getInstanceList($data)
    {
        $api                 = "/mana_api/vm?";
        $params              = [];
        $params['f']         = intval($data['start']);
        $params['t']         = $params['f'] + intval($data['limit']);
        $params['tenant_id'] = $data['pid'];
        $params['region']    = $data['rid'];
        $url                 = $this->host . $api . http_build_query($params);
        /*
        $url = $this->host . $api . '/' . $params['tenant_id'] . '/' . $params['region'] . '/' . $params['f'] . '/' . $params['t'];*/
        $ret = json_decode($this->get($url), true);

        if (!$ret || !isset($ret['vm_servers'])) {
            throw new \Exception('获取虚拟机列表失败');
        }
        $data  = [];
        $cache = [];
        foreach ($ret['vm_servers'] as $item) {
            $cache[$item['instance_id']] = $item;
            $t                           = array();
            $t['name']                   = $item['instance_name'];
            $t['unId']                   = $item['instance_id'];
            $t['lanIp']                  = $item['lan_ip_set'];
            $t['wanIpSet']               = $item['wan_ip_set'];
            $t['status']                 = $item['status'];
            $t['create_at']              = $item['create_at'];
            $t['update_at']              = $item['update_at'];
            $q                           = '';
            $operationsList              = $this->getOperationsStatus();
            foreach ($operationsList as $val) {
                if (in_array($item['status'], $val)) {
                    $q .= '1';
                } else {
                    $q .= '0';
                }
            }
            $q .= '1111';
            $t['operationStatus'] = $q;
            $t['rid']             = $params['region'];
            $data[]               = $t;
        }
        $sessionKey            = "firm_" . $this->firmId . "_ins_list";
        $_SESSION[$sessionKey] = $cache;
        return ['totalCount' => $ret['total_count'], 'data' => $data];
    }

    public function start($ids, $others = null)
    {
        $api    = "/mana_api/vm_act";
        $url    = $this->host . $api . '/' . $others['pid'] . '/' . $others['rid'];
        $params = [
            'servers'  => implode(',', $ids),
            'os-start' => null
        ];
        $this->post($url, json_encode($params));
        return true;
    }

    public function stop($ids, $others = null)
    {
        $api    = "/mana_api/vm_act";
        $url    = $this->host . $api . '/' . $others['pid'] . '/' . $others['rid'];
        $params = [
            'servers' => implode(',', $ids),
            'os-stop' => null
        ];
        $this->post($url, json_encode($params));
        return true;
    }

    public function getFinancialReport($products = null, $public_group = null, $overseasCloudServerFee)
    {
        $cdate   = chr(1) . date("Y-m", strtotime("-1 month"));

        $datas = [];

        $overseasCloudServerFeeTemp = [
            'title'      => $this->ctitle,
            'date'       => $cdate,
            'product_id' => '200182',
            'name'       => '球球大作战',
            'type'       => '海外云费用',
            'total'      => sprintf('%.2f', $overseasCloudServerFee),
            'price'      => '',
        ];
        $datas[] = $overseasCloudServerFeeTemp;
        //cdn带宽费用明细，史文俊
        $cdnReport = $this->cdnReport($cdate, $products, $public_group);

        //星云带宽，暂时移到单独模块去统计了
        //$netReport = $this->netReport($cdate, $products, $public_group);
        //星云带宽 卢万达

        //星云费用 董光华
        $scloudNetReport = $this->scloudNetReport($cdate, $products, $public_group);

        //物理机机柜数据明细 可以统一问吕李俊
        $jgReport = $this->jgReport($cdate, $products, $public_group);

        //综合服务数据数据明细，同物理机机柜 可以统一问吕李俊
        $zhfwcbReport = $this->zhfwcbReport($cdate, $products, $public_group);

       //私有云综合服务成本，同星云费用
        $vmzhfwcbReport = $this->vmzhfwcbReport($cdate, $products, $public_group);
 		
        $datas = array_merge($datas, $cdnReport, $scloudNetReport, $vmzhfwcbReport, $jgReport, $zhfwcbReport);

        //计算总价
        $sum = [
            'product_id' => '',
            'name'       => '',
            'type'       => '',
            'price'      => '',
            'total'      => array_sum(
                array_map(
                    function ($val) {
                        return $val['total'];
                    }, $datas
                )
            )
        ];

        $datas[] = $sum;

        return $datas;
    }

    public function exportScloudReport($products = null)
    {


    }

    public function restart($ids, $others = null)
    {
        $api    = "/mana_api/vm_act";
        $url    = $this->host . $api . '/' . $others['pid'] . '/' . $others['rid'];
        $params = [
            'servers' => implode(',', $ids),
            'reboot'  => [
                'type' => 'HARD'
            ]
        ];
        $this->post($url, json_encode($params));
        return true;
    }

    public function getInstanceInfo($hid, $rid)
    {
        $sessionKey  = "firm_" . $this->firmId . "_ins_list";
        $data        = $_SESSION[$sessionKey];
        $info        = $data[$hid];
        $patternData = [
            '主机信息' => [
                '名称'    => $info['instance_name'],
                '服务器ID' => $info['instance_id'],
                '状态'    => $info['status'],
                '公网IP'  => $info['wan_ip_set'],
                '内网IP'  => $info['lan_ip_set'],
                '创建时间'  => $info['create_at'],
                '到期时间'  => $info['update_at']
            ],
            '机器配置' => [
                'CPU' => $info['cpu_num'],
                '内存'  => $info['mem_size'] . "GB",
                '磁盘'  => $info['disk_size']
            ]
        ];
        return $patternData;
    }

    public function getMonitorData($hid, $mid, $qtype, $rid = null)
    {
        return [];
    }

    public function resetPassword($ids, $others = null)
    {
        $api = 'http://m.scloudm.com/api/chgPwd';
        $id  = $ids[0];
        $url = $api . '/' . $others['rid'] . '/' . $id . '/' . base64_encode($others['password']) . "/";
        $this->get($url);
        return true;
    }

    /** --- operations methods end --- */
    public function returnInstance($ids, $others = null)
    {
        return false;
    }

    public function addAlertHost($hid, $rid, $username)
    {
        $sessionKey = "firm_" . $this->firmId . "_ins_list";
        $data       = $_SESSION[$sessionKey];
        $info       = $data[$hid];

        $ips = $info['wan_ip_set'];

        $gid = $this->initUser($username);
        if (!$gid) return ['code' => -1, 'msg' => "数据初始化失败"];
        $ret = $this->addHost($ips, $gid);

        return ['code' => 0, 'msg' => $ret['data']];
    }

    public function getExcel($data)
    {
        $api                 = "/mana_api/vm?";
        $params              = [];
        $params['f']         = 0;
        $params['t']         = 10000;
        $params['tenant_id'] = $data['pid'];
        $params['region']    = $data['rid'];
        $url                 = $this->host . $api . http_build_query($params);
        $ret                 = json_decode($this->get($url), true);

        if (!$ret || !isset($ret['vm_servers'])) {
            throw new \Exception('获取虚拟机列表失败');
        }
        $data = [];
        foreach ($ret['vm_servers'] as $item) {
            $t      = [
                '名称'    => $item['instance_name'],
                '服务器ID' => $item['instance_id'],
                '状态'    => $item['status'],
                '公网IP'  => $item['wan_ip_set'],
                '内网IP'  => $item['lan_ip_set'],
                '创建时间'  => $item['create_at'],
                '到期时间'  => $item['update_at'],
                'CPU'   => $item['cpu_num'],
                '内存'    => $item['mem_size'] . "GB",
                '磁盘'    => $item['disk_size']
            ];
            $data[] = $t;
        }

        $excel = null;
        return $this->createExcel($excel, $data);
    }

    public function vncConsole($id, $pid, $rid)
    {
        //http://api.scloudm.com/mana_api/vnc_console/$tenant_id/$region/$server_uuid <http://api.scloudm.com/mana_api/vnc_console/%24tenant_id/%24region/%24server_uuid>
        $api    = "/mana_api/vnc_console";
        $url    = $this->host . $api . '/' . $pid . '/' . $rid . "/" . $id;
        $params = [];
        $ret    = json_decode($this->post($url, json_encode($params)), true);
        if ($ret && isset($ret['data'])) {
            return $ret['data']['console']['url'];
        }
        return false;
    }

    public function getPanelData($pid, $month)
    {
        if (!is_numeric($month) || $month < 0 || $month > 12) {
            return ['code' => -2, 'msg' => '参数异常,日期'];
        }

        //获取账单汇总数据
        $api = "/mana_api/vm_bill";

        $month = date("Y-m", strtotime("-$month month", time()));

        $attach = [
            'start' => $month,
            'end'   => $month,
        ];

        $url = $this->host . $api . '/' . $pid . "?" . http_build_query($attach);

        $ret = json_decode($this->get($url), true);

        $datas = [];

        if ($ret && isset($ret['code']) && $ret['code'] == 200) {

            $bills = current($ret['data']);

            if ($bills) {
                foreach ($bills as $key => $bill) {
                    $temp = [];

                    $temp['idc']                  = $key;
                    $temp['price']                = $bill['price'];
                    $temp['count']                = $bill['count'];
                    $datas['overviews']['body'][] = $temp;
                    $datas['overviews']['head'][] = $key;
                }
            } else $datas['overviews'] = [];
        }

        //获取流量点
        $api = "/mana_api/flow_v2";

        $attach = [
            'month'      => $month,
            'project_id' => $pid,
        ];

        $url = $this->host . $api . "?" . http_build_query($attach);

        $ret = json_decode($this->get($url), true);

        if ($ret && isset($ret['code']) && $ret['code'] == 200) {

            $netflows = $ret['netFlow'];

            $header = array_keys($netflows);

            foreach ($netflows as $netflow) {
                $temp = [];
                foreach ($netflow as $flow) {

                    $temp['in_rate'][] = floatval(sprintf("%.2f", $flow['in_rate'] / 1024 / 1024 * 8));

                    $temp['out_rate'][]      = floatval(sprintf("%.2f", $flow['out_rate'] / 1024 / 1024 * 8));
                    $temp['sampling_time'][] = $flow['sampling_time'];

                }

                $datas['grap']['body'][] = $temp;
            }

            $maxflows = $ret['maxFlow'];

            foreach ($maxflows as $maxflow) {

                $temp = [];

                $temp['max_in_rate'][] = floatval(sprintf("%.2f", $maxflow['max_in_rate'] / 1024 / 1024 * 8));

                $temp['max_out_rate'][]      = floatval(sprintf("%.2f", $maxflow['max_out_rate'] / 1024 / 1024 * 8));
                $temp['max_in_rate_date'][]  = $maxflow['max_in_rate_date'];
                $temp['max_out_rate_date'][] = $maxflow['max_out_rate_date'];

                $datas['grap']['maxflow'][] = $temp;
            }


            $datas['grap']['head'] = $header;

        }

        return $datas;
    }

    public function getPanelFlowDetail($pid, $month, $region)
    {
        if (!is_numeric($month) || $month < 0 || $month > 12) {
            return ['code' => -2, 'msg' => '参数异常,日期'];
        }

        $api = "/mana_api/vm_bill_detail";

        $month = date("Y-m", strtotime("-$month month", time()));

        $attach = [
            'region' => $region,
            'month'  => $month,
        ];

        $url = $this->host . $api . '/' . $pid . "?" . http_build_query($attach);

        $ret = json_decode($this->get($url), true);

        $datas = [];
        if ($ret['code'] == 200 && count($ret['data']) > 0) {
            $date = key($ret['data']);

            $region = key(current($ret['data']));

            foreach (current(current($ret['data'])) as $key => $value) {

                $temp              = [];
                $temp['date']      = $date;
                $temp['region']    = $region;
                $temp['projectid'] = $key;
                $temp['fee']       = $value;

                $datas[] = $temp;
            }

            return ['code' => 0, 'data' => $datas];
        } else {
            return ['code' => -1, 'msg' => '查询无数据'];
        }
    }
}
