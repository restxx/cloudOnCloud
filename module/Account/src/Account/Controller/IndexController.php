<?php

namespace Account\Controller;

use Account\Adapters\Auth;
use Account\Adapters\SsoAuth;
use Account\Model\UsersTable;
use Account\Traits\Validate;
use Application\Captcha\ImageCaptcha;
use Application\Forms\RegisterForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    use Validate;
    public function indexAction()
    {
        if ($this->request->isPost()) {
            $captcha = new ImageCaptcha(
                [
                    'name'    => 'name',
                    'wordLen' => '4',
                    'timeout' => 600,
                    'height'  => 36,
                    'width'   => 100,
                    'fsize'   => 18,
                    'fonts'   => array('data/font/CooperBlackStd.otf', 'data/font/BOOKOSB.TTF', 'data/font/BOOKOSI.TTF')
                ]
            );
            $this->layout('layout/layout');
            $form = new RegisterForm($captcha);
            $form->setData($this->request->getPost());
            if (!$form->isValid()) {
                return new JsonModel(['code' => 2, 'error' => current(current($form->getMessages()))]);
            }
            $loginWay = $this->params()->fromPost('login_way');
            $username = $this->params()->fromPost('username');
            $password = $this->params()->fromPost('password');

            /** @var \Zend\Authentication\AuthenticationService $auth */
            $auth   = $this->getServiceLocator()->get('auth');
            if ($loginWay == 'account')
                $authObj = new Auth($username, $password);
            elseif ($loginWay == 'sso')
                $authObj = new SsoAuth($username,$password,$this->getServiceLocator()->get('config')['params']['passport']);

            $result = $auth->authenticate($authObj);
            if ($result->isValid()) {
                if(!$this->identity()['bpassword'])
                {
                    UsersTable::getInstance()->changePassword($username,$password,$password);
                }
                return new JsonModel(['code' => 0, 'data' => ['username' => $username]]);
            }

            return new JsonModel(['code' => 1, 'error' => current($result->getMessages())]);
        }

        return new JsonModel(['code' => 1, 'error' => '']);
    }

    public function registerAction()
    {
        if ($this->request->isPost()) {
            $captcha = new ImageCaptcha(
                [
                    'name'    => 'name',
                    'wordLen' => '4',
                    'timeout' => 600,
                    'height'  => 36,
                    'width'   => 100,
                    'fsize'   => 18,
                    'fonts'   => array('data/font/CooperBlackStd.otf', 'data/font/BOOKOSB.TTF', 'data/font/BOOKOSI.TTF')
                ]
            );
            $this->layout('layout/layout');
            $form = new RegisterForm($captcha);
            $form->setData($this->request->getPost());
            if (!$form->isValid()) {
                return new JsonModel(['code' => 2, 'error' => current(current($form->getMessages()))]);
            }
            $username = $this->params()->fromPost('username');
            $password = $this->params()->fromPost('password');
            if (!preg_match('/^[_a-zA-Z0-9]{6,10}$/', $username)) {
                return new JsonModel(['code' => 1, 'error' => '用户名格式不正确']);
            }

            if (strlen($password) < 8 || strlen($password) > 20) {
                return new JsonModel(['code' => 1, 'error' => '密码长度不合要求']);
            }
            if(!$this->isSafePassword($username, $password))
            {
                return new JsonModel(['code' => 1, 'error' => '密码复杂度不合要求,8-20位大小写字母、数字和特殊字符(#$%^&*@!)']);
            }
            $ret = UsersTable::getInstance()->addUser($username, $password);
            if ($ret) {
                return new JsonModel(['code' => 0, 'data' => $ret]);
            } else if($ret == false)
            {
                return new JsonModel(['code' => 1,'error' => '用户名已存在']);
            }
            return new JsonModel(['code' => 1, 'error' => '注册失败']);
        }
        return new JsonModel(['code' => 1, 'error' => '']);
    }

    public function loginOutAction()
    {
        if ($this->identity()) {
            /** @var \Zend\Authentication\AuthenticationService $auth */
            $auth = $this->getServiceLocator()->get('auth');
            $auth->clearIdentity();
        }
        return $this->redirect()->toRoute('login');
    }

    public function changePasswordAction()
    {
        if($this->request->isPost())
        {
            $oldPassword = $this->params()->fromPost('oldPassword');
            $newPassword = $this->params()->fromPost('newPassword');
            if (strlen($newPassword) < 8 || strlen($newPassword) > 20) {
                return new JsonModel(['code' => 1, 'error' => '密码长度不合要求']);
            }
            if(UsersTable::getInstance()->changePassword($this->identity()['username'], $oldPassword, $newPassword)){
                return new JsonModel(['code' => 0]);
            }
            return new JsonModel(['code' => 1, 'error' => '旧密码错误']);

        }
        return new JsonModel(['code' => 0]);
    }

}

