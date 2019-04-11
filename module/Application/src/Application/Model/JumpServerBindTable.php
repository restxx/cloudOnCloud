<?php

namespace Application\Model;

use Account\Model\AbstractTableGateway;
use Zend\Db\Sql\Select;


/**
 * Class JumpServerBindTable
 *
 * @package Application\Model
 * @author  Xuman
 * @version $Id$
 */
class JumpServerBindTable extends AbstractTableGateway
{
    function addNew($data)
    {
        if ($this->select(
                [
                    'host_ip'   => $data['host_ip'], 'host_username' => $data['host_username'],
                    'host_port' => $data['host_port'], 'username' => $data['username']
                ]
            )->count() > 0
        ) {
            return 0;
        }
        return $this->insert($data);
    }

    function getByUsername($username, $start = 0, $limit = 10)
    {
        $select = new Select();
        $select->from($this->table);
        $select->where(['username' => $username])->limit($limit)->offset($start)->order('id desc');
        $result = $this->selectWith($select);
        if (!$result) {
            return [];
        }
        return $result->toArray();
    }

    function getCountByUsername($username)
    {
        return $this->select(['username' => $username])->count();
    }

    function deleteById($id)
    {
        return $this->delete(['id' => $id]);
    }
} 