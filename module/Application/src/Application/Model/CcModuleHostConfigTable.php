<?php

namespace Application\Model;
use Account\Model\AbstractTableGateway;
use Zend\Db\Sql\Select;


/**
 * Class CcModuleHostConfigTable
 * @package Application\Model
 * @author Xuman
 * @version $Id$
 */
class CcModuleHostConfigTable extends AbstractTableGateway
{
    static $tableName = 'cc_ModuleHostConfig';
    static $dbDriver  = 'db2';

    /**
     * @param $data = [
     *  'ApplicationID' => 1,
     *  'HostID' => 1,
     *  'ModuleID' => 1,
     *  'SetID' => 1,
     * ]
     * @return int
     */
    public function setConfig($data)
    {
        return $this->insert($data);
    }

    public function getApplicationByUsername($username)
    {
        $select = new Select('cc_ApplicationBase');
        $select->columns(['ApplicationID','ApplicationName']);
        $select->join('cc_ModuleBase','cc_ApplicationBase.ApplicationID=cc_ModuleBase.ApplicationID',['SetID','ModuleID']);
        $select->where(['ModuleName' => '空闲机', new \Zend\Db\Sql\Predicate\Expression('Maintainers LIKE "%_'.$username.'_%"')]);
        $result = $this->selectWith($select)->toArray();
        $list = [];
        foreach($result as $item)
        {
            $key = $item['ApplicationID'] . '|' . $item['SetID'] .'|' . $item['ModuleID'];
            $list[$key] = $item['ApplicationName'];
        }
        return $list;
    }
} 