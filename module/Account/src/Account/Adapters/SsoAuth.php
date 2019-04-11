<?php
/**
 * User: zhoubin
 * Date: 2017/5/19
 * Time: 12:04
 */

namespace Account\Adapters;

use Account\Model\Account;
use Account\Model\UsersTable;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;



class SsoAuth implements AdapterInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private $username = '';
    private $password = '';
    private $soapClient = '';
    private $config = '';

    function __construct($username, $password, $config)
    {
        $this->username = $username;
        $this->password = $password;
        $this->config = $config;
        $this->soapClient = new \SoapClient($this->config['url']);
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        $messages = [
            Result::SUCCESS                    => '登录成功',
            Result::FAILURE                    => '登录失败',
            Result::FAILURE_CREDENTIAL_INVALID => '用户名或密码错误'
        ];
        $return = $this->soapClient->ValidateAdByPasswd(
            $this->username,
            $this->password,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['REMOTE_PORT'],
            $this->config['source_system_code']
        );

        $user = null;
        if ($return->return_flag){
            $code = Result::SUCCESS;
            $user = UsersTable::getInstance()->genUser($this->username,rand(0,PHP_INT_MAX));
            if ($user == false)
                $code = Result::FAILURE;
            else
                $user = new Account($user);
        }else{
            $code = Result::FAILURE_CREDENTIAL_INVALID;
        }
        return new Result($code, $user, [$messages[$code]]);
    }
}