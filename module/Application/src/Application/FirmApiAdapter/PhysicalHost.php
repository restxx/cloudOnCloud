<?php

namespace Application\FirmApiAdapter;


/**
 * Class PhysicalHost
 *
 * @package Application\FirmApiAdapter
 * @author  Xuman
 * @version $Id$
 */
class PhysicalHost extends FirmAbstract
{

    protected $username = '';
    protected $password = '';
    protected $host = 'http://api.scloudm.com'; //'http://m.scloudm.com';
    protected $regions = [];
    protected $projects = [];
    protected $baseOperationStatus = [
        self::CAN_START   => ['stop'],
        self::CAN_STOP    => ['running'],
        self::CAN_RESTART => ['running', 'stop'],
        self::CAN_DELETE  => ['running']
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
            throw new \Exception('接口请求异常' . $result);
        }
        return json_decode($result, true);
    }

    public function put($url, $data)
    {
        $opts      = array(
            'http' => array(
                'timeout' => 30,
                'method'  => "PUT",
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
            throw new \Exception('接口请求异常' . $result);
        }
        return json_decode($result, true);
    }

    public function get($url)
    {
        $opts      = array(
            'http' => array(
                'timeout' => 30,
                'header'  => "Sp-Agent: scloudm\r\nX-Auth-Token: " . $this->getToken(),
            )
        );
        $cxContext = stream_context_create($opts);
        $result    = @file_get_contents($url, false, $cxContext);
        if (isset($http_response_header) && !preg_match('/200/', $http_response_header[0])) {
            $this->clearTokenCache();
            throw new \Exception('接口请求异常' . $result);
        }
        return $result;
    }
    public function delete($url)
    {
        $opts      = array(
            'http' => array(
                'timeout' => 30,
                'method'  => "DELETE",
                'header'  => "Sp-Agent: scloudm\r\nX-Auth-Token: " . $this->getToken(),
            )
        );
        $cxContext = stream_context_create($opts);
        $result    = @file_get_contents($url, false, $cxContext);
        if (isset($http_response_header) && !preg_match('/200/', $http_response_header[0])) {
            $this->clearTokenCache();
            throw new \Exception('接口请求异常' . $result);
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
                'header'  => "Content-type: application/json\r\n" .
                    "Sp-Agent: scloudm",
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
            throw new \Exception('获取token失败' . $ret);
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
        $api                 = "/mana_api/pm?";
        $params              = [];
        $params['f']         = intval($data['start']);
        $params['t']         = $params['f'] + intval($data['limit']);
        $params['tenant_id'] = $data['pid'];
        $params['region']    = $data['rid'];
        $url                 = $this->host . $api . http_build_query($params);
        /*
        $url = $this->host . $api . '/' . $params['tenant_id'] . '/' . $params['region'] . '/' . $params['f'] . '/' . $params['t'];*/
        $ret = json_decode($this->get($url), true);
        if (!$ret || !isset($ret['pm_servers'])) {
            throw new \Exception('获取物理机列表失败');
        }
        $data  = [];
        $cache = [];
        foreach ($ret['pm_servers'] as $item) {
            $snid           = isset($item['snid']) ? $item['snid'] : $item['system_snid'];
            $cache[$snid]   = $item;
            $t              = array();
            $t['name']      = $item['host_name'];
            $t['unId']      = $snid;
            $t['ip']        = $item['ip'];
            $t['lanIp']     = $item['lan_ip'];
            $t['wanIpSet']  = $item['wan_ip'];
            $t['status']    = $item['status'];
            $t['state']     = isset($item['state']) ? $item['state'] : '-';
            $t['pid']       = $item['tenant_id'];
            $t['remark']    = $item['remark'];
            $t['create_at'] = $item['create_at'];
            $t['update_at'] = $item['update_at'];
            $q              = '';
            $operationsList = $this->getOperationsStatus();
            foreach ($operationsList as $val) {
                if (in_array($item['status'], $val)) {
                    $q .= '1';
                } else {
                    $q .= '0';
                }
            }
            $q .= '1';
            if($t['state'] == 'active') {
                $q .= '1';
            } else {
                $q .= '0';
            }
            $q .= '1';
            $t['operationStatus'] = $q;
            $data[]               = $t;
        }
        $sessionKey            = "firm_" . $this->firmId . "_ins_list";
        $_SESSION[$sessionKey] = $cache;
        return ['totalCount' => $ret['total_count'], 'data' => $data];
    }

    public function getInstanceInfo($hid, $rid)
    {
        $sessionKey  = "firm_" . $this->firmId . "_ins_list";
        $data        = $_SESSION[$sessionKey];
        $info        = $data[$hid];
        $patternData = [
            '主机信息' => [
                '名称'    => $info['host_name'],
                '资产编号'  => $info['asset_id'],
                '系统序列号' => isset($info['snid']) ? $info['snid'] : $info['system_snid'],
                '物理机状态' => $info['status'],
                '物理状态'  => isset($info['state']) ? $info['state'] : '-',
                'IP'    => $info['ip'],
                '内网IP'  => $info['lan_ip'],
                '外网IP'  => $info['wan_ip'],
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

    public function start($ids, $others = null)
    {
        $api    = "/mana_api/pm_act";
        $url    = $this->host . $api . '/' . $others['pid'];
        $params = [
            'snids'    => implode(',', $ids),
            'act'      => 'on',
            'username' => $this->username
        ];
        $ret    = $this->post($url, json_encode($params));
        if ($ret['code'] != 200) {
            return false;
        }
        return true;
    }

    public function stop($ids, $others = null)
    {
        $api    = "/mana_api/pm_act";
        $url    = $this->host . $api . '/' . $others['pid'];
        $params = [
            'snids'    => implode(',', $ids),
            'act'      => 'off',
            'username' => $this->username
        ];
        $ret    = $this->post($url, json_encode($params));
        if ($ret['code'] != 200) {
            return false;
        }
        return true;
    }

    public function restart($ids, $others = null)
    {
        $api    = "/mana_api/pm_act";
        $url    = $this->host . $api . '/' . $others['pid'];
        $params = [
            'snids'    => implode(',', $ids),
            'act'      => 'reset',
            'username' => $this->username
        ];
        $ret    = $this->post($url, json_encode($params));

        if ($ret['code'] != 200) {
            return false;
        }
        return true;
    }

    public function getMonitorData($hid, $mid, $qtype, $rid = null)
    {
        return [];
    }

    public function resetPassword($ids, $others = null)
    {
        return false;
    }

    /** --- operations methods end --- */
    public function returnInstance($ids, $others = null)
    {
        $api    = "/mana_api/pm_refund";
        $url    = $this->host . $api . '/' . $others['pid'];
        $params = [
            'snids' => implode(',', $ids)
        ];
        $ret    = $this->post($url, json_encode($params));
        if ($ret['code'] != 200) {
            return false;
        }
        return true;
    }

    public function addNewInstance($data)
    {
        $api = '/mana_api/pm_add';
        $url = $this->host . $api;
        $ret = $this->post($url, json_encode($data));
        if ($ret['code'] != 200) {
            return false;
        }
        return true;
    }

    public function addAlertHost($hid, $rid, $username)
    {
        $sessionKey = "firm_" . $this->firmId . "_ins_list";
        $data       = $_SESSION[$sessionKey];
        $info       = $data[$hid];

        $ips = $info['ip'];

        $gid = $this->initUser($username);
        if (!$gid) return ['code' => -1, 'msg' => "数据初始化失败"];
        $ret = $this->addHost($ips, $gid);

        return ['code' => 0, 'msg' => $ret['data']];
    }

    public function getExcel($data)
    {
        $api                 = "/mana_api/pm?";
        $params              = [];
        $params['f']         = 0;
        $params['t']         = 10000;
        $params['tenant_id'] = $data['pid'];
        $params['region']    = $data['rid'];
        $url                 = $this->host . $api . http_build_query($params);

        $ret = json_decode($this->get($url), true);
        if (!$ret || !isset($ret['pm_servers'])) {
            throw new \Exception('获取物理机列表失败');
        }
        $data = [];
        foreach ($ret['pm_servers'] as $item) {
            $t      = [
                '名称'    => $item['host_name'],
                '资产编号'  => $item['asset_id'],
                '系统序列号' => isset($item['snid']) ? $item['snid'] : $item['system_snid'],
                '物理机状态' => $item['status'],
                '物理状态'  => isset($item['state']) ? $item['state'] : '-',
                'IP'    => $item['ip'],
                '内网IP'  => $item['lan_ip'],
                '外网IP'  => $item['wan_ip'],
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

    public function getOrders($data)
    {
        $api            = "/mana_api/pm_order/" . $data['pid'] . "?";
        $params         = [];
        if(isset($data['t'])){
            $t = intval($data['t']);
            if($t<0 || $t >4)
            {
                $t = 0;
            }
        } else {
            $t = 0;
        }

        $params['type'] = $t;
        $url            = $this->host . $api . http_build_query($params);
        $ret = json_decode($this->get($url), true);
        if (!$ret || !isset($ret['orders'])) {
            throw new \Exception('获取物理机列表失败');
        }
        return $ret['orders'];
    }

    public function createOrder($data)
    {
        $api                    = "/mana_api/pm_order/" . $data['pid'];
        $params                 = [];
        $params['snids']        = $data['ids'];
        $params['dest_project'] = $data['tpid'];
        $url                    = $this->host . $api;
        //var_dump($params);
        $ret    = $this->post($url, json_encode($params));
        if (!$ret || !isset($ret['code'])) {
            throw new \Exception('获取物理机列表失败');
        }
        return ($ret['code'] == 200);
    }

    public function dealOrder($data)
    {
        $api                    = "/mana_api/pm_order/" . $data['pid'];
        $params                 = [];
        $params['order_ids']    = $data['order_ids'];
        $params['accept']       = $data['accept'] ? 1:0;
        $url                    = $this->host . $api;
        $ret    = $this->put($url, json_encode($params));
        if (!$ret || !isset($ret['code'])) {
            throw new \Exception('获取物理机列表失败');
        }
        return $ret['detail'];
    }

    public function cancelOrder($data)
    {
        $api                    = "/mana_api/pm_order_cancel/" . $data['pid'];
        $params                 = [];
        $params['order_ids']    = $data['order_ids'];
        $params['flag']         = 0;
        $url                    = $this->host . $api;
        $ret    = $this->post($url, json_encode($params));
        if (!$ret || !isset($ret['code'])) {
            throw new \Exception('获取物理机列表失败');
        }
        return ($ret['code'] == 200);
    }

    public function createContacts($data)
    {
        $api                    = "/mana_api/pm_contact/" . $data['pid'];
        $params                 = [];
        $params['email']        = $data['email'];
        $params['phone']        = $data['mobile'];
        $params['name']         = $data['name'];
        $url                    = $this->host . $api;
        $ret    = $this->post($url, json_encode($params));
        if (!$ret || !isset($ret['code'])) {
            throw new \Exception('新增项目联系人失败');
        }
        return ($ret['code'] == 200);
    }
    public function getContacts($data)
    {
        $api                    = "/mana_api/pm_contact/" . $data['pid'];
        $url                    = $this->host . $api;
        $ret    = json_decode($this->get($url), true);
        if (!$ret || !isset($ret['code']) || intval($ret['code']) != 200) {
            throw new \Exception('获取项目联系人列表失败');
        }
        return $ret['contact_list'];
    }

    public function delContacts($data)
    {
        $api                    = "/mana_api/pm_contact/" . $data['pid'];
        $params = [];
        $params['ids']          = $data['ids'];
        $url                    = $this->host . $api . '?' . http_build_query($params);
        $ret    = json_decode($this->delete($url), true);
        if (!$ret || !isset($ret['code']) || intval($ret['code']) != 200) {
            throw new \Exception('删除项目联系人失败');
        }
        return ($ret['code'] == 200);
    }

    public function vncConsole($ids, $others = null)
    {
        // TODO: Implement vncConsole() method.
    }
}