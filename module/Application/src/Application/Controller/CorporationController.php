<?php

namespace Application\Controller;

use Account\Model\UsersTable;
use Account\Traits\Validate;
use Application\FirmApiAdapter\FirmAbstract;
use Application\Model\ProjectsTable;
use Application\Model\UserFirmPassportTable;
use Application\Model\UsersProjectsTable;
use Application\Validator\Mobile;
use Aws\Ec2\Ec2Client;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;


/**
 * Class CorporationController
 *
 * @package Application\Controller
 * @author  Xuman
 * @version $Id$
 */
class CorporationController extends AbstractActionController
{
    use Validate;

    protected $id = 1;
    protected $menus = [
        1 => "项目管理",
        2 => "子账号管理",
        3 => "子账号权限分配",
        4 => "项目映射关系",
        5 => "项目可用余额分配"
    ];

    public function onDispatch(MvcEvent $e)
    {
        if (!$this->identity()) {
            return $this->redirect()->toRoute('login');
        }

        if($this->identity()['pid'] != 0)
        {
            return $this->redirect()->toRoute('control');
        }
        $this->id = intval($this->params()->fromRoute('id', 1));
        $this->layout()->setTemplate('layout/corporation');
        $firms = $this->getServiceLocator()->get('config')['firm_config'];
        $firmList = [];
        foreach($firms as $id => $c)
        {
            $firmList[$id] = $c['name'];
        }
        $this->layout()->setVariables(
            [
                'account'    => $this->identity(),
                'id'         => $this->id,
                'menus'      => $this->menus,
                'firmList'   => $firmList
            ]
        );
        $view = parent::onDispatch($e);
        return $view;
    }

    /**
     * @param $fid
     * @return null|FirmAbstract
     */
    protected function getFirmById($fid)
    {
        $authInfo = UserFirmPassportTable::getInstance()->getByFirmID($this->identity()['uid'], $fid);
        $fAuth    = null;
        if ($authInfo) {
            $fAuth = json_decode($authInfo['passport_info'], true);
        }
        $config = [
            'config' => $this->getServiceLocator()->get('config')['firm_config'][$fid],
            'auth'   => $fAuth
        ];
        $class  = $config['config']['invokable'];
        if (!$config['auth']) {
            return false;
        }
        if (!$class) {
            return null;
        }
        $config['auth']['firmId'] = $fid;
        $firm                     = new $class($config['auth']);
        return $firm;
    }

    public function indexAction()
    {
        if (!array_key_exists($this->id, $this->menus)) {
            return new JsonModel(['error' => 'Bad Request']);
        }
        $tpl  = "application/corporation/page" . $this->id;
        $view = new \Zend\View\Model\ViewModel([]);
        $view->setTemplate($tpl);
        if (in_array($this->id, [3, 4, 5])) {
            if (in_array($this->id, [3, 5])) {
                if ($this->id == 3) {
                    $view->setVariable('subUsers', UsersTable::getInstance()->getSubUsers($this->identity()['uid']));
                }
                $view->setVariable('projects', $this->getProjectsList(true));
            } else {
                $view->setVariable('projects', $this->getProjectsList());
            }

        }
        return $view;
    }

    public function testAction()
    {
        $client = new Ec2Client([]);
    }

    public function getProjectsList($check = false)
    {
        $result = ProjectsTable::getInstance()->getAllByUsername($this->identity()['username'], $check);

        if (count($result) > 0) {
            $temp = [];
            foreach ($result as $item) {
                $temp[$item['id']] = $item['name'];
            }
            $result = $temp;
        }
        return $result;
    }

    public function getProjectsAction()
    {
        if ($this->request->isPost()) {
            $start = intval($this->params()->fromPost('start'));
            $limit = intval($this->params()->fromPost('length'));
            $list  = ProjectsTable::getInstance()->getByUsername($this->identity()['username'], $start, $limit);
            $ret   = [
                'draw'            => $_POST['draw']++,
                'recordsTotal'    => $list[0],
                'recordsFiltered' => $list[0],
                'data'            => $list[1]
            ];
            return new JsonModel($ret);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function newProjectAction()
    {
        if ($this->request->isPost()) {
            $name = $this->params()->fromPost('name');
            if (mb_strlen($name, 'utf8') > 20) {
                return new JsonModel(['error' => '项目名长度不能超过20']);
            }
            if (ProjectsTable::getInstance()->add($name, $this->identity()['username'])) {
                return new JsonModel(['code' => 0]);
            } else {
                return new JsonModel(['error' => '项目名已创建，请尝试其他']);
            }
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function delProjectAction()
    {
        if ($this->request->isPost()) {
            $id = $this->params()->fromPost('id');
            if (ProjectsTable::getInstance()->del($id, $this->identity()['username'])) {
                return new JsonModel(['code' => 0]);
            } else {
                return new JsonModel(['error' => '删除失败']);
            }

        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function setBalanceAction()
    {
        if ($this->request->isPost()) {
            $id    = intval($this->params()->fromPost('id'));
            $money = abs(intval($this->params()->fromPost('money')));
            if (!$id || !$money) {
                return new JsonModel(['error' => '参数错误']);
            }
            if (ProjectsTable::getInstance()->setBalance($id, $money, $this->identity()['username'])) {
                return new JsonModel(['code' => 0]);
            }
            return new JsonModel(['error' => '设置余额失败']);

        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function getSubUsersAction()
    {
        $start  = intval($this->params()->fromPost('start'));
        $limit  = intval($this->params()->fromPost('length'));
        $result = UsersTable::getInstance()->getSubUsers($this->identity()['uid'], $start, $limit);
        $count  = UsersTable::getInstance()->getSubUsersCount($this->identity()['uid']);
        $ret    = [
            'draw'            => $_POST['draw']++,
            'recordsTotal'    => $count,
            'recordsFiltered' => $count,
            'data'            => $result
        ];
        return new JsonModel($ret);
    }

    public function addSubUserAction()
    {
        if ($this->request->isPost()) {
            $username = $this->params()->fromPost('username');
            $password = $this->params()->fromPost('password');
            $mobile   = $this->params()->fromPost('mobile');
            $email    = $this->params()->fromPost('email');

            if (!preg_match('/^[_a-zA-Z0-9]{6,10}$/', $username)) {
                return new JsonModel(['code' => 1, 'error' => '用户名格式不正确']);
            }

            if (strlen($password) < 8 || strlen($password) > 20) {
                return new JsonModel(['code' => 1, 'error' => '密码长度不合要求']);
            }
            if (!$this->isSafePassword($username, $password)) {
                return new JsonModel(['code' => 1, 'error' => '密码复杂度不合要求,8-20位大小写字母、数字和特殊字符(#$%^&*@!)']);
            }
            if (!(new Mobile())->isValid($mobile)) {
                return new JsonModel(['code' => 1, 'error' => '手机号格式不正确']);
            }

            if (!preg_match('/.+@.+/', $email)) {
                return new JsonModel(['code' => 1, 'error' => '邮箱格式不正确']);
            }
            $data = [
                'username' => $username,
                'password' => md5($password),
                'mobile'   => $mobile,
                'email'    => $email,
                'puid'     => $this->identity()['uid']
            ];
            if (UsersTable::getInstance()->addSubUser($data)) {
                return new JsonModel(['code' => 0]);
            }
            return new JsonModel(['error' => '添加子账号失败']);

        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function delSubUserAction()
    {
        if ($this->request->isPost()) {
            $subUid = intval($this->params()->fromPost('uid'));
            if (!$subUid) {
                return new JsonModel(['error' => '参数错误']);
            }
            if (UsersTable::getInstance()->delSubUser($this->identity()['uid'], $subUid)) {
                return new JsonModel(['code' => 0]);
            }
            return new JsonModel(['error' => '删除失败']);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function bindToUserAction()
    {
        if ($this->request->isPost()) {
            $uid = $this->params()->fromPost('uid');
            $pid = $this->params()->fromPost('pid');
            if (UsersProjectsTable::getInstance()->bindToUser($pid, $uid)) {
                return new JsonModel(['code' => 0]);
            }
            return new JsonModel(['error' => '分配失败']);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function getPermissionByUidAction()
    {
        $subUid = intval($this->params()->fromQuery('uid'));
        if (!$subUid) return new JsonModel(['error' => '参数错误']);
        $result = ProjectsTable::getInstance()->getNamesByIds(UsersProjectsTable::getInstance()->getPidByUid($subUid));
        return new JsonModel(['code' => 0, 'data' => $result]);
    }

    public function getFirmProjectsAction()
    {
        $fid  = $this->params()->fromQuery('fid');
        $firm = $this->getFirmById($fid);
        $list = $firm->getProjectList();
        return new JsonModel(['code' => 0, 'data' => $list]);
    }

    public function bindProjectsAction()
    {
        if ($this->request->isPost()) {
            $pid  = intval($this->params()->fromPost('pid'));
            $fid  = intval($this->params()->fromPost('fid'));
            $fpid = $this->params()->fromPost('fpid');
            $name = $this->params()->fromPost('name');
            if (ProjectsTable::getInstance()->bindProjects($pid, $fid, $fpid, $name, $this->identity()['username'])) {
                return new JsonModel(['code' => 0]);
            }
            return new JsonModel(['error' => '相同当前关联，无更改']);
        }
        return new JsonModel(['error' => 'Bad Request']);
    }

    public function getProjectInfoAction()
    {
        $pid = $this->params()->fromQuery('pid');
        $ret = ProjectsTable::getInstance()->getMappedProjectInfo($pid, $this->identity()['username']);

        $info = '';
        if($ret && $ret['mid'])
        {
            $name = $this->getServiceLocator()->get('config')['firm_config'][$ret['mfid']]['name'];
            $info = $name . ' - ' . $ret['mname'];
        }
        return new JsonModel(['code' => 0, 'data' => $info]);
    }
}