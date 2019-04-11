<?php

namespace Application\Model;
use Account\Model\AbstractTableGateway;


/**
 * Class UsersProjectsTable
 * @package Application\Model
 * @author Xuman
 * @version $Id$
 */
class UsersProjectsTable extends AbstractTableGateway
{
    public function bindToUser($pid, $uid)
    {
        if($this->select(['uid' => $uid, 'pid' => $pid])->count() == 0)
        {
            try{
                return $this->insert(['uid' => $uid, 'pid' => $pid]);
            } catch(\Exception $e)
            {
                return 0;
            }
        }
        return 0 ;
    }

    public function getPidByUid($uid)
    {
        $result = $this->select(['uid' => $uid])->toArray();
        $pidList = [];
        if($result) {
            foreach($result as $item)
            {
                $pidList[] = $item['pid'];
            }

        }
        return $pidList;
    }

    public function delByPUid($uid)
    {
        return $this->delete(['uid' => $uid]);
    }

    public function delByPid($pid)
    {
        return $this->delete(['pid' => $pid]);
    }
} 