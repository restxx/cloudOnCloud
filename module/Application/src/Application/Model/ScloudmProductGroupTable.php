<?php

namespace Application\Model;

use Application\Plugin\Table;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;


/**
 * Class ScloudmProductGroupTable
 *
 * @package Application\Model
 * @author  Lijun
 * @version $Id$
 */
class ScloudmProductGroupTable extends Table
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getFilter($uiData)
    {
        $cols = ["group_id", "name", "desc"];

        $like = [];

        $date = [];

        $row = $this->tableGateway->select(
            function (Select $select) use ($uiData, $cols, $like, $date) {

                $parse = $this->parseWhere($cols, $uiData['where'], $like, $date);

                $select->where($parse['where']);

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
            function (Select $select) use ($cols, $uiData, $like, $date) {

                $parse = $this->parseWhere($cols, $uiData['where'], $like, $date);

                $select->where($parse['where']);

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

    public function getList()
    {
        $rows = $this->tableGateway->select(
            function (Select $select) {

                $select->columns(
                    ["group_id", "name"]
                );

                $select->order(['group_id' => 'asc']);
            }
        )->toArray();

        return $rows;
    }

    public function add($data)
    {
        try {
            $this->tableGateway->insert($data + ['prices' => ""]);
        } catch (\Exception $e) {
            return ["code" => -3, "msg" => $e->getMessage()];
        }

        return ["code" => 0, "msg" => "添加成功"];
    }

    public function update($data)
    {

        $id = $data['group_id'];

        unset($data['group_id']);

        try {
            $this->tableGateway->update($data, ['group_id' => $id]);

        } catch (\Exception $e) {

            return ["code" => -3, "msg" => $e->getMessage()];
        }

        return ["code" => 0, "msg" => "修改成功"];

    }

    public function updatePrices($data)
    {

        $id = $data['group_id'];

        unset($data['group_id']);

        try {
            $this->tableGateway->update(['prices' => json_encode($data)], ['group_id' => $id]);

        } catch (\Exception $e) {

            return ["code" => -3, "msg" => $e->getMessage()];
        }

        return ["code" => 0, "msg" => "修改成功"];

    }

    public function get($id)
    {
        $row = $this->tableGateway->select(
            function (Select $select) use ($id) {
                $select->columns(["group_id", "name", "desc"]);
                $select->where(['group_id' => $id]);
            }
        )->current();

        return $row ? ["code" => 0, "data" => $row] : ["code" => -3, "msg" => "获取数据失败"];
    }

    public function getPrices($id)
    {
        $row = current(
            $this->tableGateway->select(
                function (Select $select) use ($id) {
                    $select->columns(["group_id", "prices"]);
                    $select->where(['group_id' => $id]);
                }
            )->toArray()
        );

        if ($row) {

            $prices = @json_decode($row['prices'], true);

            $prices = is_array($prices) ? $prices : [];

            $row += $prices;

            unset($row['prices']);

        }

        return $row ? ["code" => 0, "data" => $row] : ["code" => -3, "msg" => "获取数据失败"];
    }
} 