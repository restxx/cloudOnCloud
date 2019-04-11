<?php

namespace Account\Adapters;

use Account\Model\Account;
use Account\Model\UsersTable;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayObject;


/**
 * Class Auth
 *
 * @package Account\Adapters
 * @author  Xuman
 * @version $Id$
 */
class Auth implements AdapterInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private $username = '';
    private $password = '';

    function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
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
            Result::FAILURE_CREDENTIAL_INVALID => '用户名或密码错误'
        ];
        $user = new Account(UsersTable::getInstance()->getByUsername($this->username));
        if ($user['password'] == md5($this->password)) {
            $code = Result::SUCCESS;
        } else {
            $code = Result::FAILURE_CREDENTIAL_INVALID;
        }
        return new Result($code, $user, [$messages[$code]]);
    }
}