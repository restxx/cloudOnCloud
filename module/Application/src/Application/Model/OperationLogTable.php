<?php

namespace Application\Model;

use Account\Model\AbstractTableGateway;
use Zend\Db\Sql\Select;


/**
 * Class OperationLogTable
 *
 * @package Application\Model
 * @author  Xuman
 * @version $Id$
 */
class OperationLogTable extends AbstractTableGateway
{
    public function log($username, $fid, $hid, $pid, $operation, $result)
    {
        return $this->insert(
            [
                'username'  => $username,
                'firm_id'   => $fid,
                'host_id'   => $hid,
                'operation' => $operation,
                'result'    => $result,
                'pid'       => $pid
            ]
        );
    }

    public function getByHid($hid, $fid, $start, $limit)
    {
        $select = new Select();
        $select->from($this->table);
        $select->where(['host_id' => $hid, 'firm_id' => $fid])->limit($limit)->offset($start)->order('id desc');
        $result = $this->selectWith($select);
        if (!$result) {
            return [];
        }
        return $result->toArray();
    }

    public function getLastLogs($fid, $username)
    {
        $select = new Select();
        $select->from($this->table);
        $select->where(['firm_id' => $fid, 'username' => $username])->limit(10)->offset(0)->order('create_at desc');
        $result = $this->selectWith($select);
        if (!$result) {
            return [];
        }
        return $result->toArray();
    }

    public function getCountByHid($hid, $fid)
    {
        return $this->select(['host_id' => $hid, 'firm_id' => $fid])->count();
    }
} 