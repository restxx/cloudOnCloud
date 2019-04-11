<?php

namespace Application\Model;
use Account\Model\AbstractTableGateway;


/**
 * Class CcUserTable
 * @package Application\Model
 * @author Xuman
 * @version $Id$
 */
class CcUserTable extends AbstractTableGateway
{
    static $tableName = 'cc_User';
    static $dbDriver = 'db2';

    public function addNew($data)
    {
        $this->insert($data);
        return $this->getLastInsertValue();
    }

    public function findByUsername($username)
    {
        return $this->select(['UserName' => $username])->toArray();
    }

    public function changePassword($username, $newPass)
    {
        return $this->update(['Password' => $newPass],['UserName' => $username]);
    }
}