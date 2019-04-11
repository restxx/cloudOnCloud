<?php

namespace Application\Model;

use Application\Plugin\Table;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;


/**
 * Class AuditLogTable
 *
 * @package Application\Model
 * @author  Lijun
 * @version $Id$
 */
class AuditLogTable extends Table
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getFilter($uiData, $username)
    {
        $cols = ["host_ip", "type", "log", "create_at"];

        $like = [];

        $date = ['create_at'];

        $row = $this->tableGateway->select(
            function (Select $select) use ($uiData, $cols, $like, $date, $username) {

                $parse = $this->parseWhere($cols, $uiData['where'], $like, $date);

                $select->where($parse['where'] + ['username' => $username]);

                if (!empty($parse['like'])) {
                    foreach ($parse['like'] as $key => $value) {
                        $select->where->like($key, $value);
                    }
                }

                $dateParse = $parse['date'];

                if (isset($dateParse['begin']) && !empty($dateParse['begin'])) {
                    $select->where->greaterThanOrEqualTo("create_at", $dateParse['begin']);
                }

                if (isset($dateParse['end']) && !empty($dateParse['end'])) {
                    $select->where->lessThan("create_at", $dateParse['end']);
                }

                $select->columns($cols, false);
                $select->columns(
                    array(
                        'total' => new Expression("count(1)")
                    )
                );
            }
        )->current();

        $rows = $this->tableGateway->select(
            function (Select $select) use ($cols, $uiData, $like, $date, $username) {

                $parse = $this->parseWhere($cols, $uiData['where'], $like, $date);

                $select->where($parse['where'] + ['username' => $username]);

                if (!empty($parse['like'])) {
                    foreach ($parse['like'] as $key => $value) {
                        $select->where->like($key, $value);
                    }
                }

                $dateParse = $parse['date'];
                if (isset($dateParse['begin']) && !empty($dateParse['begin'])) {
                    $select->where->greaterThanOrEqualTo("create_at", $dateParse['begin']);
                }

                if (isset($dateParse['end']) && !empty($dateParse['end'])) {
                    $select->where->lessThan("create_at", $dateParse['end']);
                }

                if ($uiData) {
                    $sort = $this->parseSort($cols, $uiData['sort']);
                    $select->order($sort);
                    $select->offset($uiData['offset']);
                    $select->limit($uiData['limit']);
                }

                $select->columns($cols, false);
            }
        )->toArray();

        $data = [];

        $data['count'] = $row['total'];

        $data['data'] = $rows;

        return $data;
    }

    public function getTotal()
    {
        $row = $this->tableGateway->select(
            function (Select $select) {

                $select->columns(
                    array(
                        'total' => new Expression("count(1)")
                    )
                );
            }
        )->current();

        return $row['total'];
    }
} 