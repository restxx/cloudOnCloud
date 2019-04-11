<?php

namespace Application\Model;

use Account\Model\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;


/**
 * Class PingLogTable
 *
 * @package Application\Model
 * @author  zhoubin
 * @version $Id$
 */
class PingLogTable extends AbstractTableGateway
{

    public function getChartData($startTime)
    {
        $select = new Select();
        $select->from($this->table);
        $select->columns(['province','ping','create_time']);
        $select->order(['create_time' => 'asc']);
        $select->where->greaterThanOrEqualTo("create_time", $startTime);
        $result = $this->selectWith($select);
        return $result->toArray();
    }


    public function getLastChartData()
    {
        $result = $this->adapter
            ->query('SELECT province,ping,create_time FROM ping_log WHERE create_time = (select max(create_time) from ping_log)', Adapter::QUERY_MODE_EXECUTE)
            ->toArray();
        return $result;
    }


    //获取过去15分钟数据
    public function getLast15mData()
    {
        $time = strtotime('-15 minutes');

//        $time = strtotime('-10 days');

        $result = $this->adapter->query(
            "SELECT pl.province,pl.source,pl.ping,pg.group_id,pg.sort,create_time
FROM ping_log pl,province_group pg
WHERE pl.province=pg.province
and create_time>?
order by group_id,sort,create_time",
            [$time]
        )->toArray();

        $data = [];
        foreach ($result as $row) {
            $data[$row['group_id']][$row['province']]['ping'][] = floatval($row['ping']);
            $data[$row['group_id']][$row['province']]['time'][] = date("H:i", $row['create_time']);
        }

        return $data;
    }


    /**
     * 查询某一历史阶段的数据
     * @param $startTime
     * @param string $endTime
     * @param array $province
     * @return mixed
     */
    public function getHisChartData($startTime,$endTime, $province = [])
    {
        $select = new Select();
        $select->from($this->table);
        $select->columns(['province','ping','create_time']);
        $select->order(['create_time' => 'asc']);
        $select->where->between('create_time', $startTime,$endTime);
        if ($province != [])
            $select->where->in('province',$province);
        $result = $this->selectWith($select);
        return $result->toArray();
    }

} 