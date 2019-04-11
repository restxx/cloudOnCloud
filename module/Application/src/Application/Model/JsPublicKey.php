<?php

namespace Application\Model;

use Account\Model\AbstractTableGateway;


/**
 * Class JsPublicKey
 *
 * @package Application\Model
 * @author  Xuman
 * @version $Id$
 */
class JsPublicKey extends AbstractTableGateway
{
    public function getByUsername($username)
    {
        return $this->select(['username' => $username])->current();
    }

    public function regJs($username)
    {
        if ($this->select(['username' => $username])->count() > 0) {
            return 0;
        }
        return $this->insert(['username' => $username, 'password' => $this->createPassword(16)]);
    }

    public function createPassword($length = 16)
    {
        $baseString = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#$";
        $ret        = '';
        $indexMax   = strlen($baseString) - 1;
        if($length < 8)
        {
            $length = 8;
        }
        while ($length--) {
            $ret .= $baseString[rand(0, $indexMax)];
        }
        return $ret;
    }
} 