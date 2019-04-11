<?php

namespace Application\FirmApiAdapter;

use Application\Traits\Financail;
use Application\Traits\Lib;


/**
 * Class Aws
 *
 * @package Application\FirmApiAdapter
 * @author  Xuman
 * @version $Id$
 */
class Aws extends FirmAbstract
{
    use Financail;

    protected $access_key = '';
    protected $secret_key = '';
    protected $host = 'http://www.myaws.com';
    protected $regions = [];

      protected $baseOperationStatus = [
          self::CAN_START   => ['stopped'],
          self::CAN_STOP    => ['running'],
          self::CAN_RESTART => ['running']
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
                'header'  => "X-Auth-Token: " . $this->getToken(),
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
        $sessionKey = "firm_aws_token_" . $this->access_key;
        unset($_SESSION[$sessionKey]);
    }

    public function getToken()
    {
        $sessionKey = "firm_aws_token_" . $this->access_key;
        if (isset($_SESSION[$sessionKey])) {
            if (strtotime($_SESSION[$sessionKey]['expires']) > time() + 600) {
                $this->regions = $_SESSION[$sessionKey]['regions'];
                return $_SESSION[$sessionKey]['id'];
            }
        }
        $api        = '/token';
        $baseParams = [
            'access_key' => $this->access_key,
            'secret_key' => $this->secret_key
        ];
        $data       = json_encode($baseParams);
        $opts       = array(
            'http' => array(
                'timeout' => 30,
                'method'  => "POST",
                'header'  => "Content-type: application/json\r\n",
                'content' => $data
            )
        );
        $cxContext  = stream_context_create($opts);
        $ret        = @file_get_contents($this->host . $api, false, $cxContext);
        if (isset($http_response_header) && preg_match('/401/', $http_response_header[0])) {
            throw new \Exception('绑定信息验证失败', 401);
        }
        $result = json_decode($ret, true);
        if (!$result || !is_array($result)) {
            throw new \Exception('获取token失败');
        }
        $this->regions = $result['region'];
        $regions       = [];
        foreach ($this->regions as $re) {
            $regions[$re] = $re;
        }
        $this->regions         = $regions;
        $tokenInfo             = [];
        $tokenInfo['id']       = $result['token'];
        $tokenInfo['expires']  = time() + 3600;
        $tokenInfo['regions']  = $this->regions;
        $_SESSION[$sessionKey] = $tokenInfo;
        return $tokenInfo['id'];
    }

    public function getProjectList()
    {
        return [];
    }

    public function getRegionList()
    {
        if (empty($this->regions)) {
            $this->getToken();
        }
        return $this->regions;
    }

    public function getInstanceList($data)
    {
        $api                  = "/status?";
        $params               = [];
        $f                    = intval($data['start']);
        $l                    = intval($data['limit']);
        $params['instanceid'] = 'all';
        $params['region']     = $data['rid'];
        $url                  = $this->host . $api . http_build_query($params);
        $ret                  = $this->get($url);
        $ret = json_decode($ret, true);
        if (!is_array($ret)) {
            throw new \Exception('获取主机列表失败');
        }
        $pageItems = array_slice($ret, $f, $l);
        $data      = [];
        $cache     = [];
        foreach ($pageItems as $id => $item) {
            $cache[$id]     = $item;
            $t              = array();
            $t['unId']      = $id;
            $t['lanIp']     = $item[3];
            $t['wanIpSet']  = $item[2];
            $t['status']    = $item[5];
            $t['lunchTime'] = $item[1];
            $t['type']      = $item[4];
            $q              = '';
            $operationsList              = $this->getOperationsStatus();
            foreach ($operationsList as $val) {
                if (in_array($item[5], $val)) {
                    $q .= '1';
                } else {
                    $q .= '0';
                }
            }
            $q                    .= '111';
            $t['operationStatus'] = $q;
            $t['rid']             = $params['region'];
            $data[]               = $t;
        }
        $sessionKey            = "firm_" . $this->firmId . "_ins_list";
        $_SESSION[$sessionKey] = $cache;
        return ['totalCount' => count($ret), 'data' => $data];
    }

    public function start($ids, $others = null)
    {
        $api    = "/start";
        $url    = $this->host . $api . '?instanceid=' . implode(',', $ids) . "&region=" . $others['rid'];
        $this->get($url);
        return true;
    }

    public function stop($ids, $others = null)
    {
        $api    = "/stop";
        $url    = $this->host . $api . '?instanceid=' . implode(',', $ids) . "&region=" . $others['rid'];
        $this->get($url);
        return true;
    }


    public function restart($ids, $others = null)
    {
        $api    = "/reboot";
        $url    = $this->host . $api . '?instanceid=' . implode(',', $ids) . "&region=" . $others['rid'];
        $this->get($url);
        return true;
    }

    public function getInstanceInfo($hid, $rid)
    {
        $sessionKey  = "firm_" . $this->firmId . "_ins_list";
        $data        = $_SESSION[$sessionKey];
        $info        = $data[$hid];
        $patternData = [
            '主机信息' => [
                'public DNS'    => $info[0],
                '启动时间' => $info[1],
                '公网IP'  => $info[2],
                '内网IP'  => $info[3],
                '主机类型'=>$info[4],
                '运行状态'    => $info[5],
            ],
            /*'机器配置' => [
                'CPU' => $info['cpu_num'],
                '内存'  => $info['mem_size'] . "GB",
                '磁盘'  => $info['disk_size']
            ]*/
        ];
        return $patternData;
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

    public function getMonitorData($hid, $mid, $qtype, $rid = null)
    {
        // TODO: Implement getMonitorData() method.
    }
}