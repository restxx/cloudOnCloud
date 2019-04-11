<?php

namespace Account\Model;
use Zend\Stdlib\ArrayObject;


/**
 * Class Account
 * @package Account\Model
 * @author Xuman
 * @version $Id$
 */
class Account extends ArrayObject
{
    public function costMoney($money)
    {
        $this['money'] = $this['money'] - $money;
        return UsersTable::getInstance()->costMoney($this['uid'], $money);
    }

    public function addMoney($money)
    {
        $this['money'] = $this['money'] + $money;
        return UsersTable::getInstance()->addMoney($this['uid'], $money);
    }
} 