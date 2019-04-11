<?php

namespace Account\Model;
use Zend\Db\TableGateway\TableGateway;


/**
 * Class AbstractTableGateway
 * @package Account\Model
 * @author Xuman
 * @version $Id$
 */
class AbstractTableGateway extends TableGateway
{
    static $tableName = null;
    static $dbDriver = null;
    /**
     * TableName => Table Class Name Format : table_name => TableNameTable
     * @return static || mixed
     */
    static public function getInstance()
    {
        $class = static::class;
        if(!static::$tableName){
            $t = explode("\\",$class);
            $tableName = array_pop($t);
            $t = explode('Table', $tableName);
            $tableName = $t[0];
            $tableName[0] = strtolower($tableName[0]);
            $tableName = strtolower(preg_replace('/[A-Z]/','_' . '$0',$tableName));
        } else {
            $tableName = static::$tableName;
        }
        $driverConfigName = static::$dbDriver ? static::$dbDriver : 'db';
        /** @var \Zend\Db\Adapter\Adapter $dbAdapter */
        //$dbAdapter = GlobalTools::getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $dbAdapter = new \Zend\Db\Adapter\Adapter(GlobalTools::getServiceLocator()->get('config')[$driverConfigName]);
        return new $class($tableName, $dbAdapter);
    }
} 