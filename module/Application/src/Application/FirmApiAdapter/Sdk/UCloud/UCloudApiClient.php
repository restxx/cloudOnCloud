<?php

namespace Application\FirmApiAdapter\Sdk\UCloud;


/**
 * Class UCloudApiClient
 * @package Application\FirmApiAdapter\Sdk\UCloud
 * @author Xuman
 * @version $Id$
 */
class UCloudApiClient
{
    protected $projectId = '';
    protected $api = '';
    static function _verfy_ac($private_key, $params) {
        ksort($params);
        $params_data = "";
        foreach($params as $key => $value){
            $params_data .= $key;
            $params_data .= $value;
        }
        $params_data .= $private_key;
        return sha1($params_data);
    }

    function __construct( $base_url, $public_key, $private_key, $project_id)
    {
        $this->conn = new UConnection($base_url);
        $this->PublicKey = $public_key;
        $this->private_key = $private_key;
        if ($project_id !== "") {
            $this->projectId = $project_id;
        }
    }
    public function setProjectId($projectId)
    {
        if ($projectId !== "") {
            $this->projectId = $projectId;
        }
    }
    function get($params){
        if($this->projectId !== "")
        {
            $params["ProjectId"] = $this->projectId;
        }
        $params["PublicKey"] = $this->PublicKey;
        $params["Signature"] = self::_verfy_ac($this->private_key, $params);
        return $this->conn->get($this->api, $params);
    }
    function post($params){
        if($this->projectId !== "")
        {
            $params["ProjectId"] = $this->projectId;
        }
        $params["PublicKey"] = $this->PublicKey;
        $params["Signature"] = self::_verfy_ac($this->private_key, $params);
        return $this->conn->post($this->api, $params);
    }
} 