<?php

namespace Application\Model;

use Application\Plugin\Table;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;


/**
 * Class ScloudmProductTable
 *
 * @package Application\Model
 * @author  Lijun
 * @version $Id$
 */
class ScloudmProductTable extends Table
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getFilter($uiData)
    {
        $cols = [
            "product_id",
            "name"       => "scloudm_product.name",
            "type",
            "desc"       => "scloudm_product.desc",
            "group_name" => "scloudm_product_group.name",
            "group_id"   => "scloudm_product_group.group_id",
            'alias'      => 'scloudm_product.alias'
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
                $select->join(
                    "scloudm_product_group", "scloudm_product.group_id=scloudm_product_group.group_id", []
                );
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
                    "scloudm_product_group", "scloudm_product.group_id=scloudm_product_group.group_id", []
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

        $id = $data['product_id'];

        unset($data['product_id']);

        try {
            $this->tableGateway->update($data, ['product_id' => $id]);

        } catch (\Exception $e) {

            return ["code" => -3, "msg" => $e->getMessage()];
        }

        return ["code" => 0, "msg" => "修改成功"];

    }

    public function del($id)
    {
        try {
            $this->tableGateway->delete(['product_id' => $id]);

        } catch (\Exception $e) {

            return ["code" => -3, "msg" => $e->getMessage()];
        }

        return ["code" => 0, "msg" => "删除成功"];

    }

    public function get($id)
    {
        $row = $this->tableGateway->select(
            function (Select $select) use ($id) {
                $select->where(['product_id' => $id]);
            }
        )->current();

        return $row ? ["code" => 0, "data" => $row] : ["code" => -3, "msg" => "获取数据失败"];
    }

    public function getByAlias($alias)
    {
        $row = $this->tableGateway->select(
            function (Select $select) use ($alias) {
                $select->where(['alias' => $alias]);
            }
        )->current();

        return $row;
    }

    public function getByGroup($id)
    {
        $row = $this->tableGateway->select(
            function (Select $select) use ($id) {
                $select->where(['group_id' => $id]);
            }
        )->toArray();

        return $row;
    }

    public function getList()
    {
        $rows = $this->tableGateway->select(
            function (Select $select) {

                $cols = [
                    "product_id",
                    "name" => "scloudm_product.name",
                    "prices",
                ];
                $select->columns($cols, false);

                $select->join(
                    "scloudm_product_group", "scloudm_product.group_id=scloudm_product_group.group_id", [], "left"
                );
            }
        )->toArray();

        $datas = [];
        foreach ($rows as $row) {

            if (isset($row['prices'])) {

                $prices = json_decode($row['prices'], true);
            }

            $temp = $row + (is_array($prices) ? $prices : []);

            unset($temp['prices']);
            $datas[] = $temp;

        }

        return $datas;
    }

    public function getAll()
    {
        $rows = $this->tableGateway->select(
            function (Select $select) {
                $select->columns(["product_id", "name", "alias"]);
                $select->where(["alias <> ''"]);
            }
        )->toArray();

        return $rows;
    }
} 