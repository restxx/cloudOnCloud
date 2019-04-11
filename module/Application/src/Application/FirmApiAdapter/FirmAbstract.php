<?php

namespace Application\FirmApiAdapter;

use Application\Traits\Excel;
use Application\Traits\Falcon;


/**
 * Class FirmAbstract
 *
 * @package Application\FirmApiAdapter
 * @author  Xuman
 * @version $Id$
 */
abstract class FirmAbstract
{
    use Falcon;
    use  Excel;
    public $error = '';
    const CAN_START      = 'canStart';
    const CAN_STOP       = 'canStop';
    const CAN_RESTART    = 'canRestart';
    const CAN_RESET_PASS = 'canResetPass';
    const CAN_DELETE     = 'canDelete';
    protected $host = '';
    protected $baseOperationStatus = [
        self::CAN_START      => [],
        self::CAN_STOP       => [],
        self::CAN_RESTART    => [],
        self::CAN_RESET_PASS => [],
        self::CAN_DELETE     => []

    ];
    static $supportedActions = ['start', 'stop', 'restart', 'resetPassword', 'returnInstance'];
    static $supportedActions2Name = [
        'start'          => '开机',
        'stop'           => '关机',
        'restart'        => '重启',
        'resetPassword'  => '修改密码',
        'returnInstance' => '删除实例'
    ];
    protected $firmId = 0;
    protected $operationsStatus = [];

    public function __construct($params = [])
    {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
    }
    public function setHost($host)
    {
        $this->host = $host;
    }
    public function getMessage()
    {
        return $this->error;
    }

    public function getOperationsStatus()
    {
        return $this->baseOperationStatus;
    }

    public function authorization()
    {
        return true;
    }

    abstract public function getProjectList();

    abstract public function getRegionList();

    abstract public function getInstanceList($data);

    abstract public function getInstanceInfo($hid, $rid);

    abstract public function getMonitorData($hid, $mid, $qtype, $rid = null);

    abstract public function addAlertHost($hid, $rid, $username);

    /** --- operations methods start --- */
    abstract public function start($ids, $others = null);

    abstract public function stop($ids, $others = null);

    abstract public function restart($ids, $others = null);

    abstract public function resetPassword($ids, $others = null);

    /** --- operations methods end --- */

    abstract public function returnInstance($ids, $others = null);
    abstract public function getExcel($data);

    public function createInstance($params)
    {

    }
}