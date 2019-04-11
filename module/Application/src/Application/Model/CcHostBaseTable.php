<?php

namespace Application\Model;
use Account\Model\AbstractTableGateway;


/**
 * Class CcHostBaseTable
 * @package Application\Model
 * @author Xuman
 * @version $Id$
 */
class CcHostBaseTable extends AbstractTableGateway
{
    static $tableName = 'cc_HostBase';
    static $dbDriver = 'db2';

    /**
     * @param $data = [
     *  //'CreateTime' => 1,
     *  'InnerIP' => 1,
     *  'OuterIP' => 1,
     * ]
     * @return int
     */
    public function addNewHost($data)
    {
        $data['CreateTime'] = date('Y-m-d H:i:s');
        $this->insert($data);
        return $this->getLastInsertValue();
    }

    public function deleteById($id)
    {
        return $this->delete(['HostId' => $id]);
    }

    public function isAdd($InnerIP, $OuterIP)
    {
        return ($this->select(['InnerIP' => $InnerIP, 'OuterIP' => $OuterIP])->count() > 0);
    }
} 