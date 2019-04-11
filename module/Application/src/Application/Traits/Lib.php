<?php
/**
 *
 * User: lijun1
 * Date: 16-4-20
 * Time: 上午10:11
 */

namespace Application\Traits;

trait Lib
{
    public function get_error_form_message($messages)
    {
        $temp = [];
        foreach ($messages as $key => $message) {
            $temp[] = "<span>*" . current($message) . "</span>";
        }

        return implode("<br>", $temp);
    }

    public function convertUnderline($tableName)
    {
        $str = "";

        $temp = explode("_", $tableName);

        foreach ($temp as $value) {

            $str .= ucfirst($value);
        }
        return $str;
    }

    public function getTable($tableName)
    {
        $dbAdapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $tableGateway = new \Zend\Db\TableGateway\TableGateway($tableName, $dbAdapter);

        $class = $this->convertUnderline($tableName) . "Table";

        $class = "\\Application\\Model\\" . $class;

        return new $class($tableGateway);
    }

    public function getTableAdapter($tableName)
    {
        $dbAdapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $class = $this->convertUnderline($tableName) . "Table";

        $class = "\\Application\\Model\\" . $class;

        return new $class($dbAdapter);
    }

} 