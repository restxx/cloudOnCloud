<?php

namespace Application\Model;

use Application\Plugin\Table;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;


/**
 * Class ScloudmNetflowTable
 *
 * @package Application\Model
 * @author  Lijun
 * @version $Id$
 */
class ScloudmNetflowTable extends Table
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getMonitorData($data)
    {
        $month    = $data['net_month'];
        $monthEnd = $month - 1;

        $data['start'] = strtotime(date("Y-m", strtotime("-$month month", time())));
        $data['end']   = strtotime(date("Y-m", strtotime("-$monthEnd month", time())));

        $rows = $this->tableGateway->select(
            function (Select $select) use ($data) {

                $select->columns(['time', 'netflow']);

                $select->where(['project' => $data['net_project'], 'idc' => $data['net_idc']]);

                $select->where->greaterThanOrEqualTo("time", $data['start']);

                $select->where->lessThan("time", $data['end']);

            }

        )->toArray();

        if (!$rows) {
            return ["code" => -1, "msg" => "查询无数据,请重新选择"];
        } else {
            $Categories = [];
            $Points     = [];

            $max_in      = "";
            $max_in_date = "";

            $tick = ceil(sizeof($rows) / 14);
            foreach ($rows as $row) {
                $time = date("Y-m-d H:i:s", $row['time']);

                $netflow = $row['netflow'] ? floatval(sprintf("%.4f", $row['netflow'] / 1024 / 1024 * 8)) : 0;

                array_push($Categories, $time);
                array_push($Points, $netflow);

                if ($max_in < $netflow) {
                    $max_in      = $netflow;
                    $max_in_date = $time;
                }
            }

            $ret = [
                "code"        => 0,
                "categories"  => $Categories,
                "points"      => $Points,
                "tick"        => $tick,
                "max_in"      => $max_in,
                "max_in_date" => $max_in_date,
            ];

            return $ret;
        }
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

    public function getProject($products)
    {
        $rows = $this->tableGateway->select(
            function (Select $select) {

                $select->columns(
                    array(
                        'projects' => new Expression("distinct project")
                    )
                );
            }
        )->toArray();

        $projects = [];
        foreach ($rows as $row) {
            $alias = current($row);

            $product = array_filter(
                $products, function ($products) use ($alias) {
                return $products['alias'] == $alias;
            }
            );

            $projects[] = $product ? current($product) : ['alias' => $alias];
        }
        return $projects;
    }

    public function getIdc()
    {
        $rows = $this->tableGateway->select(
            function (Select $select) {

                $select->columns(
                    array(
                        'idcs' => new Expression("distinct idc")
                    )
                );
            }
        )->toArray();

        $idcs = [];
        foreach ($rows as $row) {
            $idcs[] = current($row);
        }

        return $idcs;
    }

    public function getDataForCsv($productTable, $monthIndex)
    {
        $month    = 0 - $monthIndex;
        $monthEnd = $month + 1;

        $start = date("Y-m-1 00:00:00", strtotime("$month month"));
        $end   = date("Y-m-1 00:00:00", strtotime("$monthEnd month"));

        $data['start'] = strtotime($start);
        $data['end']   = strtotime($end);

        $rows = $this->tableGateway->select(
            function (Select $select) use ($data) {

                $select->columns(['project', 'idc', 'netflow']);

                $select->where->greaterThanOrEqualTo("time", $data['start']);

                $select->where->lessThan("time", $data['end']);
                
            }

        )->toArray();

        $points = [];

        foreach ($rows as $row) {
            $points[$row['idc'] . '-' . $row['project']][] = $row['netflow'];
        }

        $datas = [];

        $i = 0;
        foreach ($points as $key => $point) {
            $i++;
            rsort($point);

            //$index = floor(sizeof($point) * 0.05);
            $temp = [];
            list($temp['idc'], $temp['project']) = explode("-", $key);

            $temp['point'] = sprintf("%.4f", $point[0] / 1024 / 1024 * 8);

            $productTemp = $productTable->getByAlias($temp['project']);

            $temp['name']       = $productTemp ? $productTemp['name'] : "";
            $temp['product_id'] = $productTemp ? $productTemp['product_id'] : "";

            $datas[] = $temp;
        }

        $cdate = date("Y_m", strtotime("$month month"));

        $filename = "netflow_wlj_" . $cdate . ".csv";

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        $content     = $this->i('日期,项目ID,项目名称,项目别名,机房,带宽(Mbit)') . PHP_EOL;
        $size_result = sizeof($datas);
        for ($i = 0; $i < $size_result; $i++) {
            $content .=
                chr(1) . "{$cdate}" . ',' . $datas[$i]['product_id'] . ',' . $this->i($datas[$i]['name']) . ','
                . $datas[$i]['project'] . ","
                . $datas[$i]['idc'] . ',' . $datas[$i]['point'] . PHP_EOL;
        }

        echo $content;

    }

    private function i($strInput)
    {
        return iconv('utf-8', 'gb2312', $strInput);
    }
}