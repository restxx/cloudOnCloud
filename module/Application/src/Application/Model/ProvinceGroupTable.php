<?php
/**
 * User: zhoubin
 * Date: 2017/5/22
 * Time: 18:08
 */

namespace Application\Model;

use Account\Model\AbstractTableGateway;
use Zend\Db\Sql\Select;


class ProvinceGroupTable extends AbstractTableGateway
{
    public function getAll()
    {
        $select = new Select();
        $select->from($this->table);
        $select->order(['sort'=>'asc']);
        return $this->selectWith($select);
    }
}