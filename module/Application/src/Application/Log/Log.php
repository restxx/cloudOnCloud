<?php

namespace Application\Log;
use Application\Model\OperationLogTable;


/**
 * Class Log
 * @package Application\Log
 * @author Xuman
 * @version $Id$
 */
class Log 
{
    static function log($username, $fid, $hid,$pid, $operation, $result)
    {
        return OperationLogTable::getInstance()->log($username, $fid, $hid, $pid, $operation, $result);
    }
} 