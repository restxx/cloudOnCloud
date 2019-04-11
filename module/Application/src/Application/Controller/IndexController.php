<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Account\Model\UsersTable;
use Application\Captcha\ImageCaptcha;
use Application\Forms\RegisterForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    private $name = null;

    public function onDispatch(MvcEvent $e)
    {
        $this->layout()->setTemplate('layout/unlogin');

        $this->layout()->setVariables(
            [
                'account' => $this->identity()
            ]
        );

        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        return new ViewModel();
    }

    public function submitResourceAction()
    {
        return new ViewModel();
    }

    public function resourceAction()
    {
        return new ViewModel();
    }

    public function aboutAction()
    {
        return new ViewModel();
    }

    public function loginAction()
    {
        if($this->identity())
        {
            return $this->redirect()->toRoute('home');
        }
        $this->layout()->setTemplate('layout/layout');
        $captcha = $this->getCaptcha();
        $form = new RegisterForm($captcha);
        return new ViewModel(['form' => $form]);
    }

    public function registerAction()
    {
        $this->layout()->setTemplate('layout/layout');
        $captcha = $this->getCaptcha();
        $form = new RegisterForm($captcha);
        return new ViewModel(['form' => $form]);
    }

    public function refreshCaptchaAction()
    {
        $captcha = $this->getCaptcha();
        $id = $captcha->generate();
        return new JsonModel(['code' => 0, 'id' => $id]);
    }
    protected function getCaptcha()
    {
        return new ImageCaptcha(
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
    }
    public function yfwqAction()
    {
        return new ViewModel();
    }

    public function cdnAction()
    {
        return new ViewModel();
    }

    public function ysjkAction()
    {
        return new ViewModel();
    }

    public function yaqAction()
    {
        return new ViewModel();
    }

    public function fzjhAction()
    {
        return new ViewModel();
    }

    public function yjkAction()
    {
        return new ViewModel();
    }


}
