<?php

namespace Application\Model;

use Account\Model\AbstractTableGateway;


/**
 * Class CcModuleBaseTable
 *
 * @package Application\Model
 * @author  Xuman
 * @version $Id$
 */
class CcModuleBaseTable extends AbstractTableGateway
{
    static $tableName = 'cc_ModuleBase';
    static $dbDriver = 'db2';


    public function addNew($appID, $setID, $name = '空闲机')
    {
        if ($this->insert(
            [
                'ApplicationID' => $appID,
                'SetID'         => $setID,
                'ModuleName'    => $name,
                'CreateTime'    => date("Y-m-d H:i:s"),
                'default'       => ($name == '空闲机'?1:0)
            ]
        )
        ) {
            return $this->getLastInsertValue();
        }
        return 0;
    }
} 