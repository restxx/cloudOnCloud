<?php
/**
 * User: zhoubin
 * Date: 2017/5/19
 * Time: 17:01
 */

namespace Application\Controller;


use Application\Model\PingLogTable;
use Application\Model\ProvinceGroupTable;
use Application\Plugin\OutputResult;
use Zend\Form\Element\DateTime;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Application\Model\UsersProjectsTable;
use Application\Traits\Lib;


class QiuQiuController extends AbstractActionController
{

    protected $fid = 0;
    protected $fAuth = null;
    protected $pList = null;
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
        $provinceGroup = ProvinceGroupTable::getInstance()->getAll()->toArray();
        //合并之前用户想看的省市
        if (isset($_COOKIE['qiuqiu-ping-province'])){
            $histroyProvince = explode(',',$_COOKIE['qiuqiu-ping-province']);
            foreach($provinceGroup as $key=>$value){
                if (in_array($value['province'],$histroyProvince)){
                    $provinceGroup[$key]['checked'] = 1;
                }
            }
        }

        //获取首页原来显示的数据
        $rows = PingLogTable::getInstance()->getChartData(time() - (3600 * 2));
        $minutes = $data = [];
        foreach ($rows as $row) {
            $minute = strtotime(date('Y-m-d H:i:00',$row['create_time']));
            $minutes[$minute] = true;
            $data[$row['province']][$minute]  = floatval($row['ping']);
        }
        $series = [];
        foreach ($data as $province=>$value){
            $tmpData = [];
            foreach ($minutes as $minute=>$bool){
                if (isset($value[$minute])) {
                    $tmpData[] = [
                        'x' =>  $minute * 1000,
                        'y' =>  $value[$minute],
                    ];
                }
            }
            $series[] = [
                'name'  =>  $province,
                'data'  =>  $tmpData,
                'visible'   =>  false
            ];
        }

        return new ViewModel([
            'provinceGroup' =>  $provinceGroup,
            'series'    =>  json_encode($series),
        ]);
    }


    public function curPingAction()
    {
        $rows = PingLogTable::getInstance()->getLastChartData();
        $data = [];
        foreach ($rows as $row) {
            $data[$row['province']] = [
                'x' =>  strtotime(date('Y-m-d H:i:00',$row['create_time'])) * 1000,
                'y' =>  floatval($row['ping']),
            ];
        }

        /*
        return new JsonModel(['succ'=>false]);
        $data['上海'] = [
            'x' =>  strtotime(date('Y-m-d H:i:00',time())) * 1000,
            'y' =>  rand(50,150)
        ];
        */
        if (empty($data)) return new JsonModel(['succ'=>false]);
        return new JsonModel([
            'succ'=> true,
            'data'=> $data,
            //'category'  => $category
        ]);
    }


    public function hisPingAction()
    {
        $maxHours = 4;
        $startTime = \DateTime::createFromFormat('Y/m/d H:i',$this->params()->fromQuery('startTime'));
        $endTime = \DateTime::createFromFormat('Y/m/d H:i',$this->params()->fromQuery('endTime'));
        $province = $this->params()->fromQuery('province');

        if ($startTime == false) return new JsonModel(['succ'=>false,'msg'=>'请选择起始时间']);
        $startTime = $startTime->getTimestamp();
        $endTime = ($endTime != false) ? $endTime->getTimestamp() : $startTime + 3600 * $maxHours;
        if ($endTime - $startTime > 3600 * $maxHours) return new JsonModel(['succ'=>false,'msg'=>'时间跨度过大']);

        $rows = PingLogTable::getInstance()->getHisChartData($startTime,$endTime,$province);
        $data = [];
        foreach ($rows as $row) {
            $data[$row['province']][] = [
                'x' =>  strtotime(date('Y-m-d H:i:00',$row['create_time'])) * 1000,
                'y' =>  floatval($row['ping']),
            ];
        }
        if (empty($data)) return new JsonModel(['succ'=>false,'msg'=>'查无数据']);

        $series = [];
        foreach ($data as $province => $value){
            $series[] = [
                'name'  =>  $province,
                'data'  =>  $value
            ];
        }
        return new JsonModel([
            'succ'=> true,
            'series'=> $series,
        ]);
    }

}