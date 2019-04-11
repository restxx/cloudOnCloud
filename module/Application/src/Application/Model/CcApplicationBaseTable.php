<?php

namespace Application\Model;

use Account\Model\AbstractTableGateway;


/**
 * Class CcApplicationBaseTable
 *
 * @package Application\Model
 * @author  Xuman
 * @version $Id$
 */
class CcApplicationBaseTable extends AbstractTableGateway
{
    static $tableName = 'cc_ApplicationBase';
    static $dbDriver = 'db2';


    public function addNew($username, $name)
    {
        if($this->select(['ApplicationName' => $name])->count() > 0)
        {
            return -1;
        }
        if ($this->insert([
                'ApplicationName' => $name,
                'Creator'         => $username,
                'CreateTime'      => date("Y-m-d H:i:s"),
                'Maintainers'     => "_" . $username . "_"
            ])
        ) {
            return $this->getLastInsertValue();
        }
        return 0;
    }
} 