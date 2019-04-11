<?php

namespace Application\Controller;

use Account\Model\UsersTable;
use Application\FirmApiAdapter\ACloud;
use Application\FirmApiAdapter\FirmAbstract;
use Application\FirmApiAdapter\PhysicalHost;
use Application\FirmApiAdapter\QCloud;

use Application\FirmApiAdapter\SCloud;
use Application\Forms\FinancialProductForm;
use Application\Forms\FinancialProductGroupForm;
use Application\Forms\FinancialProductGroupPricesForm;
use Application\Forms\FinancialPublicGroupEditForm;
use Application\Forms\FinancialPublicGroupForm;
use Application\Forms\OptAlertUserForm;
use Application\Forms\ScloudNetflowForm;
use Application\Log\Log;
use Application\Model\AuditLogTable;
use Application\Model\CcApplicationBaseTable;
use Application\Model\CcHostBaseTable;
use Application\Model\CcModuleBaseTable;
use Application\Model\CcModuleHostConfigTable;
use Application\Model\CcSetBaseTable;
use Application\Model\CcUserTable;
use Application\Model\JsPublicKey;
use Application\Model\JsPublicKeyTable;
use Application\Model\JumpServerBindTable;
use Application\Model\OperationLogTable;
use Application\Model\ProjectsTable;
use Application\Model\ScloudmProductTable;
use Application\Model\UserFirmPassportTable;
use Application\Model\UsersProjectsTable;
use Application\Plugin\OutputResult;
use Application\Traits\Falcon;
use Application\Traits\Lib;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Writer_Excel5;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\Json;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ManagerController extends AbstractActionController
{
    protected $fid = 0;
    protected $fAuth = null;
    protected $pList = null;
    use Falcon;
    use Lib;

    public function onDispatch(MvcEvent $e)
    {
        if (!$this->identity()) {
            return $this->redirect()->toRoute('login');
        }
        $firm_config = $this->getServiceLocator()->get('config')['firm_config'];
        $this->fid = intval($this->params()->fromRoute('id', 0));
        $action = $this->params()->fromRoute('action');
        $globalActions = ['index', 'jump-server-manager'];
        if (in_array($action, $globalActions)) {
            $this->fid = 0;
        } else {
            if (!array_key_exists($this->fid, $firm_config)) {
                $this->fid = 100000;
            }
        }
        if ($this->isSubUser()) {
            $this->pList = ProjectsTable::getInstance()->getFPidByPid(
                UsersProjectsTable::getInstance()->getPidByUid($this->identity()['uid'])
            );
            if (!$this->pList) {
                $firm_config = [];
            }
            $fidList = array_keys($this->pList);
            foreach ($firm_config as $id => $item) {
                if (!in_array($id, $fidList)) {
                    unset($firm_config[$id]);
                }
            }
        }

        $this->layout()->setTemplate('layout/manager');
        $this->layout()->setVariables(
            [
                'action' => $action,
                'account' => $this->identity(),
                'firms' => $firm_config,
                'fid' => $this->fid
            ]
        );
        $view = parent::onDispatch($e);
        return $view;
    }

    public function isSubUser()
    {
        if ($this->identity()) {
            return $this->identity()['puid'] > 0;
        }
        return false;
    }

    public function indexAction()
    {
        $firms = $this->getServiceLocator()->get('config')['firm_config'];
        $boundFirms = UserFirmPassportTable::getInstance()->getBoundFirms($this->identity()['uid']);
        $list = [];
        foreach ($boundFirms as $firm) {
            $list[$firm['fid']] = $firms[$firm['fid']]['name'];
        }
        return new ViewModel(['list' => $list, 'firms' => $firms]);
    }

    public function qqPingDataAction()
    {
        if ($this->getRequest()->isPost()) {

            return new JsonModel(["code" => -1, "msg" => "Method Not Allow"]);

        }

        $table = $this->getTableAdapter("ping_log");
        $data = $table->getLast15mData();

        return new ViewModel([
            "datas" => $data
        ]);
    }

    public function controlAction()
    {
        $config = $this->getFirmConfig();
        $firm = $this->getFirmByManagerConfig($config);
        if ($firm === false) {
            return $this->redirect()->toUrl('/server-bind/' . $this->fid);
        } else if ($firm === null) {
            return new ViewModel(['projects' => [], 'regions' => []]);
        }
        $osTypeConfig = $this->getServiceLocator()->get('config')['os_types'];
        if ($this->fid == 100005) {
            $view = new ViewModel([
                'auth' => $this->fAuth, 'osTypes' => $osTypeConfig, 'sid' => $firm->getSessionId(), 'username' => $firm->getUsername()
            ]);
            $view->setTemplate('application/manager/control' . $this->fid);
            return $view;
        }
        try {
            $projects = $firm->getProjectList();
            $regions = $firm->getRegionList();
        } catch (\Exception $e) {
            $projects = [];
            $regions = [];
            $this->errorMessage($e);
        }
        if ($this->isSubUser()) {

            foreach ($projects as $id => $name) {
                if (!in_array($id, $this->pList[$this->fid])) {
                    unset($projects[$id]);
                }
            }
        }
        $getParams = [];
        $getParams['p'] = $this->params()->fromQuery('pid', 0); //project id
        $getParams['r'] = $this->params()->fromQuery('rid', 0);//region id
        $getParams['s'] = $this->params()->fromQuery('s', '');//search param
        $getParams['n'] = $this->params()->fromQuery('n', 0);//page num

        $view = new ViewModel([
            'projects' => $projects, 'regions' => $regions, 'auth' => $this->fAuth, 'osTypes' => $osTypeConfig, 'get' => $getParams
        ]);
        $view->setTemplate('application/manager/control' . $this->fid);
        return $view;
    }

    public function addAlertHostAction()
    {
        $hid = $this->params()->fromRoute('hid');
        $rid = $this->params()->fromRoute('rid');

        $username = $this->identity()['username'];

        $config = $this->getFirmConfig();
        $firm = $this->getFirmByManagerConfig($config);

        $data = $firm->addAlertHost($hid, $rid, $username);

        return new JsonModel($data);
    }

    public function detailAction()
    {
        $hid = $this->params()->fromRoute('hid');
        $rid = $this->params()->fromRoute('rid');
        $config = $this->getFirmConfig();
        $firm = $this->getFirmByManagerConfig($config);
        if ($firm === false) {
            return $this->redirect()->toUrl('/server-bind/' . $this->fid);
        } else if ($firm === null) {
            return new ViewModel(['projects' => [], 'regions' => []]);
        }
        try {
            $data = $firm->getInstanceInfo($hid, $rid);
        } catch (\Exception $e) {
            $data = [];
            $this->errorMessage($e);
        }

        $view = new ViewModel(['data' => $data, 'id' => $hid, 'rid' => $rid]);
        $view->setTemplate('application/manager/detail' . $this->fid);
        return $view;
    }

    public function monitorDataAction()
    {
        $hid = $this->getRequest()->getPost("hid");
        $rid = $this->getRequest()->getPost("rid");

        $mid = $this->getRequest()->getPost('mid');
        $qtype = $this->getRequest()->getPost('qtype');

        $config = $this->getFirmConfig();
        $firm = $this->getFirmByManagerConfig($config);

        $data = $firm->getMonitorData($hid, $mid, $qtype, $rid);

        return new JsonModel($data);
    }

    /**
     * @param $config
     * @return null|FirmAbstract
     */
    protected function getFirmByManagerConfig($config)
    {
        $class = $config['config']['invokable'];
        if (!$config['auth']) {
            return false;
        }
        if (!$class) {
            return null;
        }
        $config['auth']['firmId'] = $this->fid;
        $firm = new $class($config['auth']);
        if (isset($config['host'])) {
            $firm->setHost($config['host']);
        }
        return $firm;
    }

    protected function getFirmConfig()
    {
        if (!$this->fAuth) {
            $uid = $this->isSubUser() ? $this->identity()['puid'] : $this->identity()['uid'];
            $authInfo = UserFirmPassportTable::getInstance()->getByFirmID($uid, $this->fid);
            $this->fAuth = null;
            if ($authInfo) {
                $this->fAuth = json_decode($authInfo['passport_info'], true);
            }
        }
        $config = [
            'config' => $this->getServiceLocator()->get('config')['firm_config'][$this->fid],
            'auth' => $this->fAuth
        ];
        $firmHosts = $this->getServiceLocator()->get('config')['firm_hosts'];
        if (isset($firmHosts[$this->fid]) && $firmHosts[$this->fid]) {
            $config['host'] = $firmHosts[$this->fid];
        }
        return $config;
    }

    public function downloadAlertAgentAction()
    {
        $this->layout("layout/layout");
        return new ViewModel();
    }

    public function doTestSpeedAction()
    {
        if ($this->getRequest()->isPost()) {
            set_time_limit(300);

            $ip = $this->getRequest()->getPost("ip");
            $time = $this->getRequest()->getPost("time");

            if (!is_numeric($time) || $time > 60 || $time < 0) {
                return new JsonModel(['code' => -3, 'msg' => '查询时间异常']);
            }

//            sleep($time);
//            $ret = <<<json
//{"count":"8","data":[{"nettype":"ZR-WT","ip":"172.30.250.13","loss":"0％ packet loss","avg":"0.130 ms","mdev":"0.024 ms"},
//{"nettype":"ZR-DX","ip":"172.30.250.30","loss":"0％ packet loss","avg":"0.131 ms","mdev":"0.019 ms"},
//{"nettype":"ZR-BGP","ip":"172.30.250.21","loss":"0％ packet loss","avg":"0.131 ms","mdev":"0.022 ms"},
//{"nettype":"BJ-WT","ip":"172.30.250.14","loss":"0％ packet loss","avg":"0.133 ms","mdev":"0.023 ms"},
//{"nettype":"BJ-DX","ip":"172.30.250.22","loss":"0％ packet loss","avg":"0.134 ms","mdev":"0.010 ms"},
//{"nettype":"BJ-BGP","ip":"172.30.250.24","loss":"0％ packet loss","avg":"0.161 ms","mdev":"0.023 ms"}]}
//json;

            $path = getcwd() . "/bin/net_check.pl";

            $ret = `$path -ip {$ip} -count {$time} 2>/dev/null`;

            $ret = json_decode($ret, true);

            if ($ret && isset($ret['data'])) {
                return new JsonModel(['code' => 0, 'data' => $ret['data']]);
            }

            return new JsonModel(['code' => -2, 'msg' => '测速失败，没有获取测试数据']);
        }
        return new JsonModel(['code' => -1, 'msg' => '无效的请求']);
    }

    public function serverListAction()
    {
        if ($this->request->isPost()) {
            $pid = $this->params()->fromPost('p', '');
            $ret = [
                'draw' => $_POST['draw']++,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            $config = $this->getFirmConfig();
            $firm = $this->getFirmByManagerConfig($config);
            if (!$firm) {
                return new JsonModel($ret);
            }
            if ($this->isSubUser()) {
                if (!array_key_exists($this->fid, $this->pList) || !in_array($pid, $this->pList[$this->fid])) {
                    return new JsonModel($ret);
                }
            }
            $params = [];
            $params['start'] = $this->params()->fromPost('start');
            $params['limit'] = $this->params()->fromPost('length');
            $params['search'] = $this->params()->fromPost('search')['value'];
            $params['pid'] = $pid;
            $params['rid'] = $this->params()->fromPost('r', '');
            try {
                $result = $firm->getInstanceList($params);
            } catch (\Exception $e) {
                $result = null;
                //$this->errorMessage($e);
                $ret['error'] = '获取主机列表失败';
                return new JsonModel($ret);

            }
            if ($result) {
                $ret = [
                    'draw' => $_POST['draw']++,
                    'recordsTotal' => $result['totalCount'],
                    'recordsFiltered' => $result['totalCount'],
                    'data' => $result['data']
                ];
                return new JsonModel($ret);
            }
            return new JsonModel($ret);
        }
        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function serverOperationAction()
    {
        if ($this->request->isPost()) {
            $config = $this->getFirmConfig();
            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }
            $serverIds = $this->params()->fromPost('serverIds');
            $action = $this->params()->fromPost('action');
            if (!in_array($action, FirmAbstract::$supportedActions)) {
                return new JsonModel(['code' => 1, 'error' => '操作暂不支持!']);
            }
            if (!$serverIds) {
                return new JsonModel(['code' => 1, 'error' => '服务器ID参数不能为空']);
            }
            $serverIds = explode(',', $serverIds);
            try {
                $result = $firm->$action($serverIds, $this->params()->fromPost());
            } catch (\Exception $e) {
                $result = null;
                $this->errorMessage($e);
            }
            foreach ($serverIds as $id) {
                Log::log(
                    $this->identity()['username'], $this->fid, $id, $this->params()->fromPost('pid'),
                    FirmAbstract::$supportedActions2Name[$action] ?: $action, intval($result)
                );
            }
            if (!$result) {
                return new JsonModel(['code' => 1, 'error' => '操作失败' . $firm->getMessage()]);
            }

            return new JsonModel(['code' => 0, 'data' => $result !== true ? $result : []]);
        }
        return new JsonModel(['code' => 1, 'error' => 'Bad Request!']);
    }

    public function serverBindAction()
    {
        if ($this->request->isPost()) {
            if (UserFirmPassportTable::getInstance()->getByFirmID($this->identity()['uid'], $this->fid)) {
                return new JsonModel(['code' => 302, 'url' => '/control/' . $this->fid]);
            }
            $authParams = $this->getServiceLocator()->get('config')['firm_config'][$this->fid]['authForm'];
            $params = [];
            $post = $this->params()->fromPost();
            foreach ($authParams as $name => $l) {
                if (isset($post[$name]) && $post[$name]) {
                    $params[$name] = $post[$name];
                }
            }
            if (count($params) != count($authParams)) {
                return new JsonModel(['code' => 1, 'error' => '参数缺失或为空']);
            }
            $class = $this->getServiceLocator()->get('config')['firm_config'][$this->fid]['invokable'];
            if (!$class) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }
            /** @var FirmAbstract $firm */
            $firm = new $class($params);
            if (!$firm->authorization()) {
                return new JsonModel(['code' => 1, 'error' => '参数验证失败，无法继续绑定，请确认填写的绑定信息是否正确']);
            }
            $ret = UserFirmPassportTable::getInstance()->addPassport($this->identity()['uid'], $this->fid, $params);
            return new JsonModel(['code' => intval(!$ret)]);
        }
        if (UserFirmPassportTable::getInstance()->getByFirmID($this->identity()['uid'], $this->fid)) {
            return $this->redirect()->toUrl('/control/' . $this->fid);
        }
        return new ViewModel();
    }

    public function serverUnbindAction()
    {
        if ($this->request->isPost()) {
            $ret = UserFirmPassportTable::getInstance()->removePassport($this->identity()['uid'], $this->fid);
            if (!$ret) {
                return new JsonModel(['code' => 1, 'error' => '绑定信息不存在，请刷新页面重试']);
            }
            return new JsonModel(['code' => 0]);
        }
        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function addPhysicalHostAction()
    {

        if ($this->request->isPost() && $this->fid == 100004) {
            $config = $this->getFirmConfig();
            if ($this->fAuth['username'] != 'admin') {
                return new JsonModel(['code' => 1, 'error' => '权限不足']);
            }

            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }
            try {
                if ($firm->addNewInstance($this->params()->fromPost())) {
                    return new JsonModel(['code' => 0]);
                }
            } catch (\Exception $e) {
                return new JsonModel(['code' => 1, 'error' => '接口请求异常，请确认是否存在重复提交']);
            }

        }

        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function addToJumpServerAction()
    {
        if ($this->request->isPost()) {
            $id = $this->params()->fromPost('id');
            $name = $this->params()->fromPost('name');
            $ip = $this->params()->fromPost('ip');
            $port = $this->params()->fromPost('port');
            $hostUsername = $this->params()->fromPost('username');
            if (!$id || !$ip || !$hostUsername || !$name) {
                return new JsonModel(['code' => 1, 'error' => '参数错误']);
            }
            if (count($id) > 1) {
                $ret = [];
                foreach ($id as $k => $i) {
                    $ret[$i] = JumpServerBindTable::getInstance()->addNew(
                        [
                            'host_id' => $id[$k],
                            'host_ip' => $ip[$k],
                            'host_username' => $hostUsername[$k],
                            'host_port' => $port[$k],
                            'host_name' => $name[$k],
                            'username' => $this->identity()['username'],
                            'firm_id' => $this->fid
                        ]
                    );
                }
                return new JsonModel(['code' => 0, 'data' => $ret]);
            } else {
                if (JumpServerBindTable::getInstance()->addNew(
                    [
                        'host_id' => current($id),
                        'host_ip' => current($ip),
                        'host_username' => current($hostUsername),
                        'host_port' => current($port),
                        'host_name' => current($name),
                        'username' => $this->identity()['username'],
                        'firm_id' => $this->fid
                    ]
                )
                ) {
                    return new JsonModel(['code' => 0]);
                } else {
                    return new JsonModel(['code' => 1, 'error' => '已经存在，无需重复添加']);
                }
            }

        }
        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function jumpServerManagerAction()
    {
        $username = $this->identity()['username'];
        $JsInfo = JsPublicKeyTable::getInstance()->getByUsername($username);
        if ($this->request->isPost()) {
            if (JsPublicKeyTable::getInstance()->regJs($username)) {
                return new JsonModel(['code' => 0]);
            } else {
                return new JsonModel(['code' => 1, 'error' => '已注册，无需重复操作']);
            }
        }
        return new ViewModel(['jsInfo' => $JsInfo]);
    }

    public function auditLogAction()
    {
        if ($this->getRequest()->isPost()) {

            $table = $this->getTable("audit_log");

            $username = $this->identity()['username'];

            $data = $table->getFilter($this->ui()->getData(), $username);

            return new JsonModel(
                [
                    "sEcho" => intval($this->getRequest()->getPost("sEcho")),
                    "iTotalRecords" => intval($table->getTotal()),
                    "iTotalDisplayRecords" => intval($data['count']),
                    "aaData" => isset($data['data']) && $data['data'] ? $data['data'] : [],
                ]
            );

        }
        return new ViewModel();
    }

    public function financialReportAction()
    {
        if ($this->getRequest()->isPost()) {

            $type = $this->params("type", 1);
            switch ($type) {
                case 1:
                    $tablename = "scloudm_product";
                    break;
                case 2:
                    $tablename = "scloudm_product_group";
                    break;
                case 3:
                    $tablename = "scloudm_public_group";
                    break;
                default:
                    $tablename = "scloudm_product";
                    break;
            }
            $table = $this->getTable($tablename);
            $data = $table->getFilter($this->ui()->getData());

            return new JsonModel(
                [
                    "sEcho" => intval($this->getRequest()->getPost("sEcho")),
                    "iTotalRecords" => intval($table->getTotal()),
                    "iTotalDisplayRecords" => intval($data['count']),
                    "aaData" => isset($data['data']) && $data['data'] ? $data['data'] : [],
                ]
            );
        }

        $products = $this->getTable("scloudm_product")->getAll();

        $table = $this->getTable("scloudm_product_group");
        $groups = $table->getList();

        $table = $this->getTable("scloudm_netflow");
        $projects = $table->getProject($products);

        if ($this->identity()['username'] != "lijun1234" && $this->isSubUser()) {
            foreach ($projects as $key => $project) {
                if (isset($project['product_id']) && $this->pList[$this->fid]
                    && in_array(
                        $project['product_id'], $this->pList[$this->fid]
                    )
                ) continue;

                unset($projects[$key]);
            }
        };

        $table = $this->getTable("scloudm_netflow");
        $idcs = $table->getIdc();

        $month = [];
        for ($i = 0; $i < 5; $i++) {
            $temp = [];
            $temp['month'] = date("Y-m", strtotime("-$i month", time()));
            $temp['index'] = $i;
            $month[] = $temp;
        }

        return new ViewModel(
            [
                'groups' => $groups,
                'projects' => $projects,
                'idcs' => $idcs,
                'months' => $month
            ]
        );
    }

    public function getScloudReportAction()
    {
        if ($this->getRequest()->isPost()) {

            $overseasCloudServerFee = $this->params()->fromPost('overseasCloudServerFee');

            if (!is_numeric($overseasCloudServerFee) || $overseasCloudServerFee < 0) {
                return new JsonModel(['code' => -2, 'msg' => "海外云费用错误"]);
            }
            $table = $this->getTable("scloudm_product");
            $products = $table->getList();

            $table = $this->getTable("scloudm_public_group");
            $public_group = $table->getList();

            $config = $this->getFirmConfig();

            $firm = $this->getFirmByManagerConfig($config);

            try {
                $reports = $firm->getFinancialReport($products, $public_group, $overseasCloudServerFee);

            } catch (\Exception $e) {

                return new JsonModel(['code' => $e->getCode(), 'msg' => $e->getMessage()]);
            }

            return new JsonModel(['code' => 1, 'data' => $reports]);
        }

        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function getNetflowGrapAction()
    {
        if ($this->getRequest()->isPost()) {

            $form = new ScloudNetflowForm();

            $form->loadInputFilter();

            $form->setData($_POST);

            if (!$form->isValid()) {
                return new JsonModel(
                    [
                        'code' => -3,
                        'msg' => $this->get_error_form_message($form->getMessages())
                    ]
                );
            }

            $data = $form->getData();

            $table = $this->getTable("scloudm_netflow");

            return new JsonModel($table->getMonitorData($data));
        }
        return new ViewModel();
    }

    const MOBILE_TYPE = 2;

    public function getBandwidthReportAction()
    {
        if ($this->getRequest()->isPost()) {

            $table = $this->getTable("scloudm_bandwidth");

            $data = $table->getFilter($this->ui()->getData());

            return new JsonModel(
                [
                    "sEcho" => intval($this->getRequest()->getPost("sEcho")),
                    "iTotalRecords" => intval($table->getTotal()),
                    "iTotalDisplayRecords" => intval($data['count']),
                    "aaData" => isset($data['data']) && $data['data'] ? $data['data'] : [],
                ]
            );

        }
        return new ViewModel();
    }

    public function exportBandwidthReportAction()
    {

        if ($this->getRequest()->isPost()) {

            return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);

        }

        $table = $this->getTable("scloudm_product");
        $products = $table->getList();

        $table = $this->getTable("scloudm_public_group");
        $public_group = $table->getList();

        $table = $this->getTable("scloudm_bandwidth");

        $table->getDataForCsv($products, $public_group);

        exit;
    }

    public function exportNetflowReportAction()
    {
        if ($this->getRequest()->isPost()) {

            return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);

        }

        $index = $this->params("index", 0);

        $table = $this->getTable("scloudm_netflow");

        $table->getDataForCsv($this->getTable("scloudm_product"), $index);

        exit;
    }

    public function addFinancialProductAction()
    {
        if ($this->request->isPost()) {

            $form = new FinancialProductForm();

            $form->loadInputFilter();

            $form->setData($_POST);

            if (!$form->isValid()) {
                return new JsonModel(
                    [
                        'code' => -3,
                        'msg' => $this->get_error_form_message($form->getMessages())
                    ]
                );
            }

            $data = $form->getData();

            $table = $this->getTable("scloudm_product");

            return new JsonModel($table->add($data));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function editFinancialProductAction()
    {
        if ($this->request->isPost()) {

            $form = new FinancialProductForm();

            $form->loadInputFilter();

            $form->setData($_POST);

            if (!$form->isValid()) {
                return new JsonModel(
                    [
                        'code' => -3,
                        'msg' => $this->get_error_form_message($form->getMessages())
                    ]
                );
            }

            $data = $form->getData();

            $table = $this->getTable("scloudm_product");

            return new JsonModel($table->update($data));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function getFinancialProductAction()
    {
        if (!$this->request->isPost()) {

            $id = $this->params("id");

            $table = $this->getTable("scloudm_product");

            return new JsonModel($table->get($id));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function delFinancialProductAction()
    {
        if ($this->request->isPost()) {
            $id = $this->params("id");

            $table = $this->getTable("scloudm_product");

            return new JsonModel($table->del($id));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function addFinancialProductGroupAction()
    {
        if ($this->request->isPost()) {

            $form = new FinancialProductGroupForm();

            $form->loadInputFilter();

            $form->setData($_POST);

            if (!$form->isValid()) {
                return new JsonModel(
                    [
                        'code' => -3,
                        'msg' => $this->get_error_form_message($form->getMessages())
                    ]
                );
            }

            $data = $form->getData();

            $table = $this->getTable("scloudm_product_group");

            return new JsonModel($table->add($data));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function editFinancialProductGroupAction()
    {
        if ($this->request->isPost()) {

            $form = new FinancialProductGroupForm();

            $form->loadInputFilter();

            $form->setData($_POST);

            if (!$form->isValid()) {
                return new JsonModel(
                    [
                        'code' => -3,
                        'msg' => $this->get_error_form_message($form->getMessages())
                    ]
                );
            }

            $data = $form->getData();

            $table = $this->getTable("scloudm_product_group");

            return new JsonModel($table->update($data));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function editFinancialProductGroupPricesAction()
    {
        if ($this->request->isPost()) {

            $form = new FinancialProductGroupPricesForm();

            $form->loadInputFilter();

            $form->setData($_POST);

            if (!$form->isValid()) {
                return new JsonModel(
                    [
                        'code' => -3,
                        'msg' => $this->get_error_form_message($form->getMessages())
                    ]
                );
            }

            $data = $form->getData();

            $table = $this->getTable("scloudm_product_group");

            return new JsonModel($table->updatePrices($data));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function getFinancialProductGroupAction()
    {
        if (!$this->request->isPost()) {
            $id = $this->params("id");

            $table = $this->getTable("scloudm_product_group");

            return new JsonModel($table->get($id));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function delFinancialProductGroupAction()
    {
        if ($this->request->isPost()) {
            $id = $this->params("id");

            $table = $this->getTable("scloudm_product_group");

            return new JsonModel($table->del($id));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function getFinancialProductGroupPricesAction()
    {
        if (!$this->request->isPost()) {
            $id = $this->params("id");

            $table = $this->getTable("scloudm_product_group");

            return new JsonModel($table->getPrices($id));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function addFinancialPublicGroupAction()
    {
        if ($this->request->isPost()) {

            $form = new FinancialPublicGroupForm();

            $form->loadInputFilter();

            $form->setData($_POST);

            if (!$form->isValid()) {
                return new JsonModel(
                    [
                        'code' => -3,
                        'msg' => $this->get_error_form_message($form->getMessages())
                    ]
                );
            }

            $data = $form->getData();

            $table = $this->getTable("scloudm_public_group");

            return new JsonModel($table->add($data));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function editFinancialPublicGroupAction()
    {
        if ($this->request->isPost()) {

            $form = new FinancialPublicGroupEditForm();

            $form->loadInputFilter();

            $form->setData($_POST);

            if (!$form->isValid()) {
                return new JsonModel(
                    [
                        'code' => -3,
                        'msg' => $this->get_error_form_message($form->getMessages())
                    ]
                );
            }

            $data = $form->getData();

            $table = $this->getTable("scloudm_public_group");

            return new JsonModel($table->update($data));
        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function delFinancialPublicGroupAction()
    {
        if ($this->request->isPost()) {
            $pid  = $this->params("pid");
            $pid2 = $this->params("pid2");

            $table = $this->getTable("scloudm_public_group");

            return new JsonModel($table->del($pid, $pid2));
        }

        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);
    }

    public function getFinancialPublicGroupAction()
    {
        $pid = $this->params("pid");
        $pid2 = $this->params("pid2");

        $table = $this->getTable("scloudm_public_group");

        return new JsonModel($table->get($pid, $pid2));
    }

    public function getAlertGroupAction()
    {
        if ($this->request->isPost()) {

            $username = $this->identity()['username'];

            $data = $this->getAlertGroup($username);

            if ($data) {
                return new JsonModel(['code' => 0, 'msg' => '', 'data' => $data]);
            }
            return new JsonModel(['code' => -2, 'msg' => '记录不存在']);

        }
        return new JsonModel(['code' => -1, 'msg' => 'Bad Request']);

    }

    public function optAlertUserAction()
    {
        if ($this->request->isPost()) {

            $username = $this->identity()['username'];

            $type = $this->params("type");

            $form = new OptAlertUserForm();

            $form->loadInputFilter();

            $form->setData($_POST);

            if (!$form->isValid()) {
                return new JsonModel(
                    [
                        'code' => -3,
                        'msg' => $this->get_error_form_message($form->getMessages())
                    ]
                );
            }

            $data = $form->getData();

            $ret = $this->optAlertUser($username, $data, $type);

            if ($ret) {
                return new JsonModel($ret);
            } else {
                return new JsonModel(['code' => -1, 'msg' => '操作失败,请确保用户名没有重复']);
            }
        }

        return new JsonModel(['code' => -2, 'msg' => '操作失败']);

    }

    public function jumpServerBindListAction()
    {
        if ($this->request->isPost()) {
            $draw = $this->params()->fromPost('draw');
            $ret = [
                'draw' => $draw++,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            $start = intval($this->params()->fromPost('start'));
            $limit = intval($this->params()->fromPost('length'));
            $username = $this->identity()['username'];
            if (($total = JumpServerBindTable::getInstance()->getCountByUsername($username))
                && ($data = JumpServerBindTable::getInstance()->getByUsername($username, $start, $limit))
            ) {
                foreach ($data as $k => $item) {
                    $data[$k]['firm'] =
                        $this->getServiceLocator()->get('config')['firm_config'][$item['firm_id']]['name'];
                }
                $ret['data'] = $data;
                $ret['recordsTotal'] = $total;
                $ret['recordsFiltered'] = $total;
            }
            return new JsonModel($ret);
        } else if ($this->params()->fromQuery('id')) {
            $id = intval($this->params()->fromQuery('id'));
            if (JumpServerBindTable::getInstance()->deleteById($id)) {
                return new JsonModel(['code' => 0]);
            } else {
                return new JsonModel(['code' => 1, 'error' => '记录不存在']);
            }
        }
        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    protected function errorMessage(\Exception $e)
    {
        $this->layout()->setVariables(['errorMessage' => $e->getMessage()]);
    }

    public function getOperationLogAction()
    {
        if ($this->request->isPost()) {
            $draw = $this->params()->fromPost('draw');
            $ret = [
                'draw' => $draw++,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            $start = intval($this->params()->fromPost('start'));
            $limit = intval($this->params()->fromPost('length'));
            $hid = intval($this->params()->fromPost('hid'));
            if (($total = OperationLogTable::getInstance()->getCountByHid($hid, $this->fid))
                && ($data = OperationLogTable::getInstance()->getByHid($hid, $this->fid, $start, $limit))
            ) {
                $ret['data'] = $data;
                $ret['recordsTotal'] = $total;
                $ret['recordsFiltered'] = $total;
            }
            return new JsonModel($ret);
        }
        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function getExcelAction()
    {

        $params['search'] = $this->params()->fromQuery('s');
        $params['pid'] = $this->params()->fromQuery('p', '');
        $params['rid'] = $this->params()->fromQuery('r', '');
        $config = $this->getFirmConfig();
        $firm = $this->getFirmByManagerConfig($config);
        if ($firm === false) {
            return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
        } else if ($firm === null) {
            return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
        }
        $objPHPExcel = $firm->getExcel($params);
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="' . $config['config']['name'] . date('YmdHis') . '.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        return false;

    }

    public function pGetOrdersAction()
    {
        if ($this->fid == 100004) {
            //$type   = $this->params()->fromQuery('type');
            $config = $this->getFirmConfig();
            /** @var PhysicalHost $firm */
            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }
            $draw = $this->params()->fromPost('draw');
            $ret = [
                'draw' => $draw++,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            try {
                if ($data = $firm->getOrders($_REQUEST)) {
                    $ret['data'] = $data;
                    $ret['recordsTotal'] = count($data);
                    $ret['recordsFiltered'] = count($data);
                }
            } catch (\Exception $e) {
                $ret['error'] = '获取订单失败';
                return new JsonModel($ret);
            }

            return new JsonModel($ret);
        }

        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function pCreateOrderAction()
    {
        if ($this->fid == 100004) {
            $config = $this->getFirmConfig();
            /** @var PhysicalHost $firm */
            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }
            try {
                $ret = $firm->createOrder($_REQUEST);
            } catch (\Exception $e) {
                //$this->errorMessage($e);
                return new JsonModel(['code' => 1, 'error' => '创建订单失败']);
            }
            /*Log::log(
                $this->identity()['username'], $this->fid, $_REQUEST['ids'], $this->params()->fromPost('pid'),
                '创建订单', intval($ret)
            );*/
            return new JsonModel(['code' => 0, 'data' => $ret]);
        }

        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function pDealOrderAction()
    {
        if ($this->fid == 100004) {
            $id = intval($this->params()->fromPost('order_ids'));
            $accept = intval($this->params()->fromPost('accept'));
            if (!$id || !in_array($accept, [0, 1])) {
                return new JsonModel(['code' => 1, 'error' => '参数错误']);
            }
            $config = $this->getFirmConfig();
            /** @var PhysicalHost $firm */
            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }

            try {
                $ret = $firm->dealOrder($_REQUEST);
            } catch (\Exception $e) {
                // $this->errorMessage($e);
                return new JsonModel(['code' => 1, 'error' => '订单操作失败']);
            }
            /*$orderDealTypes = ["拒绝", "接受"];
            Log::log(
                $this->identity()['username'], $this->fid, $_REQUEST['ids'], $this->params()->fromPost('pid'),
                $orderDealTypes[$accept] . '订单', intval($ret)
            );*/
            return new JsonModel(['code' => 0, 'data' => $ret]);
        }

        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function pCancelOrderAction()
    {
        if ($this->fid == 100004) {
            $id = intval($this->params()->fromPost('order_ids'));
            if (!$id) {
                return new JsonModel(['code' => 1, 'error' => '参数错误']);
            }
            $config = $this->getFirmConfig();
            /** @var PhysicalHost $firm */
            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }

            try {
                $ret = $firm->cancelOrder($_REQUEST);
            } catch (\Exception $e) {
                // $this->errorMessage($e);
                return new JsonModel(['code' => 1, 'error' => '插销订单失败']);
            }

            return new JsonModel(['code' => 0, 'data' => $ret]);
        }

        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function getImageListAction()
    {
        if ($this->fid == '100001') {
            $config = $this->getFirmConfig();
            /** @var QCloud $firm */
            $firm = $this->getFirmByManagerConfig($config);
            $type = intval($this->params()->fromPost('type'));
            return new JsonModel(['code' => 0, 'data' => $firm->getImageList($type)]);
        }
        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function pContactsListAction()
    {
        if ($this->fid == 100004) {
            $config = $this->getFirmConfig();
            /** @var PhysicalHost $firm */
            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }
            $draw = $this->params()->fromPost('draw');
            $ret = [
                'draw' => $draw++,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            try {
                if ($data = $firm->getContacts($_REQUEST)) {
                    $ret['data'] = $data;
                    $ret['recordsTotal'] = count($data);
                    $ret['recordsFiltered'] = count($data);
                }

            } catch (\Exception $e) {
                //$this->errorMessage($e);
                $ret['error'] = '获取项目联系人列表失败';
                return new JsonModel($ret);
            }

            return new JsonModel($ret);
        }

        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function pContactsCreateAction()
    {
        if ($this->fid == 100004) {
            $config = $this->getFirmConfig();
            /** @var PhysicalHost $firm */
            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }

            try {
                $ret = $firm->createContacts($_REQUEST);
            } catch (\Exception $e) {
                //$this->errorMessage($e);
                return new JsonModel(['code' => 1, 'error' => '新增项目联系人失败']);
            }

            if ($ret) return new JsonModel(['code' => 0, 'data' => '']);
        }

        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function pContactsDeleteAction()
    {
        if ($this->fid == 100004) {
            $config = $this->getFirmConfig();
            /** @var PhysicalHost $firm */
            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }

            try {
                $ret = $firm->delContacts($_REQUEST);
            } catch (\Exception $e) {
                //$this->errorMessage($e);
                return new JsonModel(['code' => 1, 'error' => '删除项目联系人失败']);
            }

            if ($ret) return new JsonModel(['code' => 0, 'data' => '']);
        }

        return new JsonModel(['code' => 1, 'error' => 'Bad Request']);
    }

    public function vncConsoleAction()
    {
        $this->fid = $this->params()->fromPost('fid');
        if ($this->fid == 100000 || $this->fid = 100007) {
            $id = $this->params()->fromPost('id');
            $pid = $this->params()->fromPost('pid');
            $rid = $this->params()->fromPost('rid');
            $config = $this->getFirmConfig();
            /** @var SCloud $firm */
            $firm = $this->getFirmByManagerConfig($config);
            if ($firm === false) {
                return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
            } else if ($firm === null) {
                return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
            }
            try {
                $ret = $firm->vncConsole($id, $pid, $rid);
            } catch (\Exception $e) {
                return new JsonModel(['code' => 1, 'error' => '连接服务器失败']);
            }
            if ($ret) {
                return new JsonModel(['code' => 0, 'data' => ['url' => $ret]]);
            }
            return new JsonModel(['error' => '连接失败']);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function panelAction()
    {
        $config = $this->getFirmConfig();
        $firm = $this->getFirmByManagerConfig($config);
        if ($firm === false) {
            return $this->redirect()->toUrl('/server-bind/' . $this->fid);
        } else if ($firm === null) {
            return new ViewModel(['projects' => [], 'regions' => []]);
        }
        try {
            $projects = $firm->getProjectList();
        } catch (\Exception $e) {
            $projects = [];
            $this->errorMessage($e);
        }

        if ($this->isSubUser()) {

            foreach ($projects as $id => $name) {
                if (!in_array($id, $this->pList[$this->fid])) {
                    unset($projects[$id]);
                }
            }
        }

        $month = [];
        for ($i = 1; $i <= 6; $i++) {
            $temp = [];
            $temp['month'] = date("Y-m", strtotime("-$i month", time()));
            $temp['index'] = $i;
            $month[] = $temp;
        }

        $view = new ViewModel(
            [
                'projects' => $projects,
                'months' => $month,
                'logs' => OperationLogTable::getInstance()->getLastLogs($this->fid, $this->identity()['username'])
            ]
        );

        $view->setTemplate('application/manager/panel' . $this->fid);

        return $view;
    }

    public function getPriceByConfigAction()
    {
        if ($this->fid == 100001) {
            $config = $this->getFirmConfig();
            /** @var QCloud $firm */
            $firm = $this->getFirmByManagerConfig($config);
            $ret = $firm->getPrice($_REQUEST);
            return new JsonModel(['code' => 0, 'ret' => $ret]);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function getPriceByIdAction()
    {
        if ($this->fid == 100001) {
            $config = $this->getFirmConfig();
            /** @var QCloud $firm */
            $firm = $this->getFirmByManagerConfig($config);
            $id = $this->params()->fromPost('id');
            $data = ['id' => $id, 'period' => 1];
            $ret = $firm->getPriceById($data);
            if ($ret['price'] > 0) {
                $key = 'renewPrice_' . $this->fid;
                if (!isset($_SESSION[$key])) {
                    $_SESSION[$key] = [];
                }
                $_SESSION[$key][$id] = $ret['price'];
            }
            return new JsonModel(['code' => 0, 'ret' => $ret]);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function createNewInstanceAction()
    {
        if ($this->fid == 100001) {
            $config = $this->getFirmConfig();
            /** @var QCloud $firm */
            $firm = $this->getFirmByManagerConfig($config);
            if ($this->isSubUser()) {
                $rt = $firm->getPrice($_REQUEST);

                if (!isset($rt['price']) || !$rt['price'] || $rt['price'] * 0.75 > $this->identity()['money'] * 100) {
                    return new JsonModel(['error' => '可用余额不足']);
                }
                $price = $rt['price'] * 0.75;

            }
            //var_dump($firm->getPrice($_REQUEST));exit;
            $ret = $firm->createInstance($_REQUEST);
            if (isset($price) && $ret) {
                $this->identity()->costMoney($price);
            }
            return new JsonModel(['code' => 0]);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function getZoneListAction()
    {
        if ($this->fid == 100001) {
            $config = $this->getFirmConfig();
            /** @var QCloud $firm */
            $firm = $this->getFirmByManagerConfig($config);
            $ret = $firm->getZoneList($_REQUEST);
            return new JsonModel(['code' => 0, 'ret' => $ret]);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function getPanelDataAction()
    {
        $config = $this->getFirmConfig();
        $firm = $this->getFirmByManagerConfig($config);

        $pid = $this->params()->fromPost('pid');
        $month = $this->params()->fromPost('month');

        if ($firm === false) {
            return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
        } else if ($firm === null) {
            return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
        }
        try {
            $datas = $firm->getPanelData($pid, $month);
        } catch (\Exception $e) {
            $datas = [];
            $this->errorMessage($e);
        }

        return new JsonModel($datas);
    }

    public function getPanelFlowDetailAction()
    {
        $config = $this->getFirmConfig();
        $firm = $this->getFirmByManagerConfig($config);

        $pid = $this->params()->fromPost('pid');
        $month = $this->params()->fromPost('month');
        $region = $this->params()->fromPost('region');

        if ($firm === false) {
            return new JsonModel(['code' => 1, 'error' => '未有绑定厂商']);
        } else if ($firm === null) {
            return new JsonModel(['code' => 1, 'error' => '功能未就绪']);
        }
        try {
            $datas = $firm->getPanelFlowDetail($pid, $month, $region);
        } catch (\Exception $e) {
            $datas = [];
            $this->errorMessage($e);
        }
        return new JsonModel($datas);
    }

    public function addToBlueWhaleAction()
    {
        if ($this->request->isPost()) {
            $wanip = $this->params()->fromPost('wanip');
            $lanip = $this->params()->fromPost('lanip');
            $hostid = $this->params()->fromPost('hostid');
            $wanipList = explode(',', $wanip);
            $lanipList = explode(',', $lanip);
            if (count($wanipList) > 0) {
                $wanip = $wanipList[0];
            }
            if (count($lanipList) > 0) {
                $lanip = $lanipList[0];
            }
            $appIDs = $this->params()->fromPost('appid');
            if (!$wanip || !$lanip) {
                return new JsonModel(['error' => '参数错误']);
            }
            if (CcHostBaseTable::getInstance()->isAdd($lanip, $wanip)) {
                return new JsonModel(['error' => '主机已经添加到蓝鲸，无需重复操作']);
            }
            $user = CcUserTable::getInstance()->findByUsername($this->identity()['username']);
            if (!$user) {
                if ($this->identity()['bpassword']) {
                    $ret = CcUserTable::getInstance()->addNew(
                        [
                            'UserName' => $this->identity()['username'],
                            'Password' => $this->identity()['bpassword']
                        ]
                    );
                }

                if (!$ret) {
                    return new JsonModel(['error' => '账号未同步,请联系管理员']);
                }

            }
            $appInfo = explode('|', $appIDs);
            $data = [
                'InnerIP' => $lanip,
                'OuterIP' => $wanip
            ];
            $id = CcHostBaseTable::getInstance()->addNewHost($data);
            if (!$id) {
                return new JsonModel(['error' => '添加失败']);
            }
            $data = [
                'ApplicationID' => $appInfo[0],
                'SetID' => $appInfo[1],
                'ModuleID' => $appInfo[2],
                'HostID' => $id,

            ];
            $ret = CcModuleHostConfigTable::getInstance()->setConfig($data);
            if (!$ret) {
                CcHostBaseTable::getInstance()->deleteById($id);
                return new JsonModel(['error' => '分配到指定业务失败，已清除失败数据，请联系管理员']);
            }
            Log::log(
                $this->identity()['username'], $this->fid, $hostid, $this->params()->fromPost('pid'),
                '添加主机到蓝鲸系统', 1
            );
            return new JsonModel(['code' => 0]);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function getAppListAction()
    {
        $list = CcModuleHostConfigTable::getInstance()->getApplicationByUsername($this->identity()['username']);
        return new JsonModel(['code' => 0, 'data' => $list]);
    }

    public function createNewAppAction()
    {
        if ($this->request->isPost()) {
            $name = $this->params()->fromPost('name');
            if (!$name) {
                return new JsonModel(['error' => '请填写业务名称']);
            }
            $username = $this->identity()['username'];
            $appID = CcApplicationBaseTable::getInstance()->addNew($username, $name);
            if ($appID == -1) {
                return new JsonModel(['error' => '业务已存在，不可重复创建!']);
            } else if ($appID) {
                return new JsonModel(['error' => '创建业务失败']);
            }
            $setID = CcSetBaseTable::getInstance()->addNew($appID);
            if ($moduleId = CcModuleBaseTable::getInstance()->addNew($appID, $setID)) {
                return new JsonModel(['code' => 0, 'data' => [$appID . '|' . $setID . '|' . $moduleId, $name]]);
            }
            return new JsonModel(['error' => '创建新业务失败']);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function renewInstanceAction()
    {
        if ($this->fid == 100001) {
            $config = $this->getFirmConfig();
            /** @var QCloud $firm */
            $firm = $this->getFirmByManagerConfig($config);
            $id = $this->params()->fromPost('id');
            $key = 'renewPrice_' . $this->fid;
            if (!isset($_SESSION[$key]) || !isset($_SESSION[$key][$id])) {
                $price = 0;
            } else {
                $price = $_SESSION[$key][$id];
            }
            if ($this->isSubUser()) {
                if (!$price || $price > intval($this->identity()['money']) * 100) {
                    return new JsonModel(['error' => '可用余额不足']);
                }
            }

            $data = ['id' => $id, 'period' => 1];
            $ret = $firm->reNewInstance($data);
            return new JsonModel(['code' => 0, 'ret' => $ret]);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

}

