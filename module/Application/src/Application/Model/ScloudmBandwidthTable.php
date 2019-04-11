<?php

namespace Application\Model;

use Application\Plugin\Table;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;


/**
 * Class ScloudmBandWidthTable
 *
 * @package Application\Model
 * @author  Lijun
 * @version $Id$
 */
class ScloudmBandWidthTable extends Table
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getFilter($uiData)
    {
        $cols = ["date", "product_id", "name", "idc", "bandwidth"];

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

    public function getDataForCsv($products, $public_group)
    {
        $cols = ["date", "product_id", "name", "idc", "bandwidth"];

        $cdate = date("Y-m", strtotime("-1 month"));

        $rows = $this->tableGateway->select(
            function (Select $select) use ($cols, $cdate) {

                $select->where(['date' => $cdate]);

                $select->columns($cols, false);
            }
        )->toArray();

        $filename = "scloudm_bandwidth_" . $cdate . ".csv";

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        $data        = $this->i('日期,项目ID,项目名称,机房,带宽(M),备注') . "\n";
        $size_result = sizeof($rows);
        for ($i = 0; $i < $size_result; $i++) {
            $product_id = $rows[$i]['product_id'];

            $groups = array_filter(
                $public_group, function ($val) use ($product_id) {
                    return $val['product_id'] == $product_id;
                }
            );

            if ($groups) {
                foreach ($groups as $group) {

                    $product2 = current(
                        array_filter(
                            $products, function ($val) use ($group) {
                                return $val['product_id'] == $group['product_id2'];
                            }
                        )
                    );

                    $data .=
                        chr(1) . "{$rows[$i]['date']}" . ',' . $product2['product_id'] . ',' . $this->i(
                            $product2['name']
                        ) . ","
                        . $rows[$i]['idc'] . ',' . sprintf(
                            '%.2f', bcmul($rows[$i]['bandwidth'], $group['percent'] / 100, 4)
                        ) . ',' . $this->i("综合平摊项目") . "\n";
                }
            } else {
                $data .=
                    chr(1) . "{$rows[$i]['date']}" . ',' . $rows[$i]['product_id'] . ',' . $this->i($rows[$i]['name'])
                    . ","
                    . $rows[$i]['idc'] . ',' . $rows[$i]['bandwidth'] . ',' . $this->i("常规项目") . "\n";
            }
        }

        echo $data;
    }

    private function i($strInput)
    {
        return iconv('utf-8', 'gb2312', $strInput);
    }
} 