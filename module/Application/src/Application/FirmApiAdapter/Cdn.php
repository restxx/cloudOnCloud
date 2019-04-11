<?php

namespace Application\FirmApiAdapter;


/**
 * Class Cdn
 * @package Application\FirmApiAdapter
 * @author Xuman
 * @version $Id$
 */
class Cdn extends FirmAbstract
{
    protected $host = "http://cdn.cloud.ztgame.com";
    public function getProjectList()
    {
        // TODO: Implement getProjectList() method.
    }

    public function getRegionList()
    {
        // TODO: Implement getRegionList() method.
    }

    public function getInstanceList($data)
    {
        // TODO: Implement getInstanceList() method.
    }

    public function getInstanceInfo($hid, $rid)
    {
        // TODO: Implement getInstanceInfo() method.
    }

    public function getMonitorData($hid, $mid, $qtype, $rid = null)
    {
        // TODO: Implement getMonitorData() method.
    }

    public function addAlertHost($hid, $rid, $username)
    {
        // TODO: Implement addAlertHost() method.
    }

    /** --- operations methods start --- */
    public function start($ids, $others = null)
    {
        // TODO: Implement start() method.
    }

    public function stop($ids, $others = null)
    {
        // TODO: Implement stop() method.
    }

    public function restart($ids, $others = null)
    {
        // TODO: Implement restart() method.
    }

    public function resetPassword($ids, $others = null)
    {
        // TODO: Implement resetPassword() method.
    }

    /** --- operations methods end --- */
    public function returnInstance($ids, $others = null)
    {
        // TODO: Implement returnInstance() method.
    }

    public function getExcel($data)
    {
        // TODO: Implement getExcel() method.
    }

    public function getSessionId()
    {
        file_get_contents($this->host);
        $data = ['username' => $this->username,'password' => $this->password];
        $data = http_build_query($data);
        $opts = array(
            'http' => array(
                'timeout' => 30,
                'method'  => "POST",
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $data,
            )
        );
        $cxContext = stream_context_create($opts);
        file_get_contents($this->host . '/login/', false, $cxContext);
        $h = '';
        foreach($http_response_header as $item)
        {
            if(strpos($item, 'sessionid')!==false)
            {
                $h .= ' ' . $item;
            }
        }
        $matches = array();
        $sessionid = null;
        if(preg_match('/sessionid=([a-z0-9]+);/', $h, $matches)){
            $sessionid = $matches[1];
        }
        return $sessionid;
    }

    public function getUsername()
    {
        return $this->username;
    }
}