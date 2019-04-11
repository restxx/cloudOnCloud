<?php

namespace Application\Model;

use Application\Plugin\Table;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;


/**
 * Class ScloudmPublicGroupTable
 *
 * @package Application\Model
 * @author  Lijun
 * @version $Id$
 */
class ScloudmPublicGroupTable extends Table
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getFilter($uiData)
    {
        $cols = [
            "product_id"  => "scloudm_public_group.product_id",
            "name"        => "product1.name",
            "product_id2" => "scloudm_public_group.product_id2",
            "name2"       => "product2.name",
            "percent",
        ];

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

                $select->join(
                    ["product1" => "scloudm_product"], "product1.product_id=scloudm_public_group.product_id", [], "left"
                );

                $select->join(
                    ["product2" => "scloudm_product"], "product2.product_id=scloudm_public_group.product_id2", [],
                    "left"
                );

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

    public function add($data)
    {
        try {
            $this->tableGateway->insert($data);

        } catch (\Exception $e) {

            return ["code" => -3, "msg" => $e->getMessage()];
        }

        return ["code" => 0, "msg" => "添加成功"];
    }

    public function update($data)
    {
        $old_pid  = $data['old_product_id'];
        $old_pid2 = $data['old_product_id2'];

        unset($data['old_product_id'], $data['old_product_id2']);

        try {
            $this->tableGateway->update($data, ['product_id' => $old_pid, 'product_id2' => $old_pid2]);

        } catch (\Exception $e) {

            return ["code" => -3, "msg" => $e->getMessage()];
        }

        return ["code" => 0, "msg" => "修改成功"];

    }

    public function del($pid, $pid2)
    {
        try {
            $this->tableGateway->delete(['product_id' => $pid, 'product_id2' => $pid2]);

        } catch (\Exception $e) {

            return ["code" => -3, "msg" => $e->getMessage()];
        }

        return ["code" => 0, "msg" => "删除成功"];

    }

    public function get($pid, $pid2)
    {
        $row = $this->tableGateway->select(
            function (Select $select) use ($pid, $pid2) {
                $select->columns(["product_id", "product_id2", "percent"]);
                $select->where(['product_id' => $pid, 'product_id2' => $pid2]);
            }
        )->current();

        return $row ? ["code" => 0, "data" => $row] : ["code" => -3, "msg" => "获取数据失败"];
    }

    public function getList()
    {
        $rows = $this->tableGateway->select(
            function (Select $select) {

                $select->columns(
                    ["product_id", "product_id2", "percent"]
                );
            }
        )->toArray();

        return $rows;
    }
} 