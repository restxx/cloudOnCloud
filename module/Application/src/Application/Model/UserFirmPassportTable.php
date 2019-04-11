<?php

namespace Application\Model;

use Account\Model\AbstractTableGateway;
use Account\Model\GlobalTools;


/**
 * Class UserFirmPassportTable
 *
 * @package Application\Model
 * @author  Xuman
 * @version $Id$
 */
class UserFirmPassportTable extends AbstractTableGateway
{
    public function getByFirmID($uid, $id)
    {
        $item = $this->select(['fid' => $id, 'uid' => $uid, 'state' => 0])->current();
        if($item)
        {
            $item['passport_info'] = GlobalTools::decrypt($item['passport_info']);
        }
        return $item;
    }

    public function addPassport($uid, $id, $info)
    {
        return $this->insert(['uid' => $uid, 'fid' => $id, 'passport_info' => GlobalTools::encrypt(json_encode($info))]);
    }

    public function removePassport($uid, $id) {
        return $this->delete(['uid' => $uid, 'fid' => $id]);
    }

    public function getBoundFirms($uid)
    {
        $list = $this->select(['uid' => $uid, 'state' => 0])->toArray();
        foreach ($list as $k => $item)
        {
            $list[$k]['passport_info'] = GlobalTools::decrypt($item['passport_info']);
        }
        return $list;
    }
} 