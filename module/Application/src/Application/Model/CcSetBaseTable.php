<?php

namespace Application\Model;

use Account\Model\AbstractTableGateway;


/**
 * Class CcSetBaseTable
 *
 * @package Application\Model
 * @author  Xuman
 * @version $Id$
 */
class CcSetBaseTable extends AbstractTableGateway
{
    static $tableName = 'cc_SetBase';
    static $dbDriver = 'db2';


    public function addNew($appID, $name='空闲机池')
    {
        if ($this->insert([
                'ApplicationID'   => $appID,
                'SetName'         => $name,
                'CreateTime'      => date("Y-m-d H:i:s")
            ])
        ) {
            return $this->getLastInsertValue();
        }
        return 0;
    }
} 